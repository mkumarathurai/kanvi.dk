<?php

namespace Tests\Feature;

use App\Actions\Polls\ClosePoll;
use App\Actions\Polls\CreatePoll;
use App\Actions\Polls\FinalizePoll;
use App\Actions\Polls\ReopenPoll;
use App\Actions\Polls\SubmitResponse;
use App\Domain\Polls\Models\AdminAccess;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\PollAuditEntry;
use App\Domain\Polls\Models\PollOption;
use App\Domain\Polls\Services\PollResults;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PollManagementTest extends TestCase
{
    use RefreshDatabase;

    private Poll $poll;

    private AdminAccess $access;

    private string $token;

    private string $option;

    protected function setUp(): void
    {
        parent::setUp();
        $created = app(CreatePoll::class)->handle('Middag', ['2026-10-09', '2026-10-10', '2026-10-11']);
        $this->poll = $created->poll;
        $this->access = $created->adminAccess;
        $this->token = $created->adminToken;
        $this->option = $this->poll->options()->first()->id;
    }

    private function signIn(): void
    {
        $this->get(route('polls.admin', $this->token))->assertRedirect();
    }

    private function mutation(string $action, array $data = [])
    {
        return $this->postJson(route('polls.manage.update', [$this->poll, $action]), [
            'version' => $this->poll->fresh()->management_version, ...$data,
        ]);
    }

    private function vote(): void
    {
        app(SubmitResponse::class)->handle($this->poll, bin2hex(random_bytes(32)), [
            'editor_id' => bin2hex(random_bytes(16)), 'changes' => [
                ['field' => 'name', 'value' => 'Private participant', 'revision' => 1],
                ['field' => $this->option, 'value' => 'can', 'revision' => 2],
            ],
        ]);
    }

    public function test_management_is_available_only_to_current_admin_of_this_poll(): void
    {
        $this->get(route('polls.manage', $this->poll))->assertForbidden();
        $this->mutation('close')->assertForbidden();
        $this->signIn();
        $this->get(route('polls.manage', $this->poll))->assertOk()->assertSee('Hvilken dag vælger I?');
        $other = app(CreatePoll::class)->handle('Anden', ['2026-10-09', '2026-10-10'])->poll;
        $this->get(route('polls.manage', $other))->assertForbidden();
        $this->postJson(route('polls.manage.update', [$other, 'close']), ['version' => 0])->assertForbidden();
        $this->access->update(['revoked_at' => now()]);
        $this->mutation('close')->assertForbidden();
        $this->assertSame('open', $this->poll->fresh()->status);
        $this->assertDatabaseCount('poll_audit_entries', 0);
    }

    public function test_domain_action_rechecks_expiry_and_poll_ownership(): void
    {
        $other = app(CreatePoll::class)->handle('Anden', ['2026-10-09', '2026-10-10']);
        foreach ([$other->adminAccess->id, $this->access->id] as $accessId) {
            $this->access->update(['expires_at' => now()->subSecond()]);
            try {
                app(ClosePoll::class)->handle($this->poll, $accessId, 0);
                $this->fail('Expected access rejection');
            } catch (HttpException $error) {
                $this->assertSame(403, $error->getStatusCode());
            }
        }
        $this->assertSame('open', $this->poll->fresh()->status);
    }

    public function test_add_date_preserves_answers_and_makes_new_option_unanswered(): void
    {
        $this->vote();
        $this->signIn();
        $this->mutation('add', ['date' => '2026-10-08'])->assertRedirect(route('polls.manage', $this->poll));
        $this->assertSame('2026-10-08', $this->poll->options()->first()->date_value->toDateString());
        $this->assertDatabaseCount('responses', 1);
        $results = app(PollResults::class)->forPoll($this->poll->fresh());
        $new = collect($results['options'])->firstWhere('id', $this->poll->options()->first()->id);
        $this->assertSame(1, $new['unanswered']);
        $this->assertSame(0, $new['cannot']);
        $this->assertDatabaseHas('poll_audit_entries', ['action' => 'option_added', 'poll_id' => $this->poll->id]);
        $this->mutation('add', ['date' => '2026-10-08'])->assertUnprocessable();
        $this->mutation('add', ['date' => '2026-02-30'])->assertUnprocessable();
        $this->assertSame(1, $this->poll->fresh()->management_version);
    }

    public function test_removal_requires_confirmation_preserves_history_and_minimum_two(): void
    {
        $this->vote();
        $this->signIn();
        $this->mutation('remove', ['option_id' => $this->option])->assertUnprocessable();
        $this->assertNotNull(PollOption::find($this->option));
        $this->mutation('remove', ['option_id' => $this->option, 'confirmed' => true])->assertRedirect();
        $this->assertSoftDeleted('poll_options', ['id' => $this->option]);
        $this->assertDatabaseCount('responses', 1);
        $this->mutation('remove', ['option_id' => $this->poll->options()->first()->id, 'confirmed' => true])->assertUnprocessable();
        $this->assertSame(2, $this->poll->options()->count());
        $this->assertDatabaseCount('poll_audit_entries', 1);
        $this->mutation('add', ['date' => '2026-10-09'])->assertRedirect();
        $replacement = $this->poll->options()->whereDate('date_value', '2026-10-09')->first();
        $this->assertNotSame($this->option, $replacement->id);
        $this->assertSame(0, $replacement->responses()->count());
    }

    public function test_response_arriving_after_page_load_still_requires_removal_confirmation(): void
    {
        $this->signIn();
        $this->get(route('polls.manage', $this->poll))->assertOk();
        $this->vote();
        $this->mutation('remove', ['option_id' => $this->option])->assertUnprocessable();
        $this->assertNotNull(PollOption::find($this->option));
    }

    public function test_finalize_close_and_reopen_preserve_responses_and_clear_final_choice(): void
    {
        $this->vote();
        $this->signIn();
        $this->mutation('finalize', ['option_id' => $this->option])->assertRedirect();
        $finalized = $this->poll->fresh();
        $this->assertSame('finalized', $finalized->status);
        $this->assertSame($this->option, $finalized->final_option_id);
        $this->assertNotNull($finalized->finalized_at);
        $this->get(route('polls.manage', $this->poll))->assertOk()->assertSee('Genåbn afstemningen')->assertDontSee('Tilføj dato');
        $this->mutation('close')->assertRedirect();
        $closed = $this->poll->fresh();
        $this->assertSame('closed', $closed->status);
        $this->assertSame($this->option, $closed->final_option_id);
        $this->assertEquals($finalized->finalized_at, $closed->finalized_at);
        $this->mutation('reopen')->assertRedirect();
        $open = $this->poll->fresh();
        $this->assertSame('open', $open->status);
        $this->assertNull($open->final_option_id);
        $this->assertNull($open->finalized_at);
        $this->assertDatabaseCount('responses', 1);
        $this->assertSame(['finalized', 'closed', 'reopened'], PollAuditEntry::orderBy('id')->pluck('action')->all());
        $audit = PollAuditEntry::all()->toJson();
        $this->assertStringNotContainsString($this->token, $audit);
        $this->assertStringNotContainsString('Private participant', $audit);
    }

    public function test_finalized_poll_can_reopen_directly(): void
    {
        $poll = app(FinalizePoll::class)->handle($this->poll, $this->access->id, 0, $this->option);
        $poll = app(ReopenPoll::class)->handle($poll, $this->access->id, 1);
        $this->assertSame('open', $poll->status);
        $this->assertNull($poll->final_option_id);
        $this->assertNull($poll->finalized_at);
    }

    public function test_close_without_selection_can_reopen_without_creating_a_final_date(): void
    {
        $poll = app(ClosePoll::class)->handle($this->poll, $this->access->id, 0);
        $this->assertSame('closed', $poll->status);
        $this->assertNull($poll->final_option_id);
        $poll = app(ReopenPoll::class)->handle($poll, $this->access->id, 1);
        $this->assertSame('open', $poll->status);
    }

    #[DataProvider('forbiddenActions')]
    public function test_permissions_matrix_rejects_invalid_transitions(string $status, string $action): void
    {
        $this->poll->update(['status' => $status]);
        $this->signIn();
        $this->mutation($action, ['option_id' => $this->option, 'date' => '2026-10-12', 'confirmed' => true])->assertStatus(409);
        $this->assertSame($status, $this->poll->fresh()->status);
        $this->assertSame(3, $this->poll->options()->count());
        $this->assertDatabaseCount('poll_audit_entries', 0);
    }

    public static function forbiddenActions(): array
    {
        $cases = [['open', 'reopen'], ['closed', 'close']];
        foreach (['finalized', 'closed', 'archived'] as $status) {
            foreach (['add', 'remove', 'finalize'] as $action) {
                $cases[] = [$status, $action];
            }
        }
        $cases[] = ['archived', 'reopen'];
        $cases[] = ['archived', 'close'];

        return $cases;
    }

    public function test_stale_browser_action_is_rejected_even_after_close_and_reopen(): void
    {
        $this->signIn();
        $this->mutation('close')->assertRedirect();
        $this->mutation('reopen')->assertRedirect();
        $this->mutation('finalize', ['option_id' => $this->option, 'version' => 0])->assertStatus(409);
        $this->from(route('polls.manage', $this->poll))->post(route('polls.manage.update', [$this->poll, 'close']), ['version' => 0])
            ->assertRedirect(route('polls.manage', $this->poll))->assertSessionHasErrors('management');
        $this->assertSame('open', $this->poll->fresh()->status);
    }

    public function test_foreign_and_deleted_options_cannot_be_selected_or_removed(): void
    {
        $other = app(CreatePoll::class)->handle('Anden', ['2026-10-09', '2026-10-10']);
        $this->signIn();
        foreach (['remove', 'finalize'] as $action) {
            $this->mutation($action, ['option_id' => $other->poll->options()->first()->id])->assertNotFound();
        }
        PollOption::findOrFail($this->option)->delete();
        $this->mutation('finalize', ['option_id' => $this->option])->assertNotFound();
        $this->assertDatabaseCount('poll_audit_entries', 0);
    }

    public function test_database_rejects_final_option_from_another_poll(): void
    {
        $other = app(CreatePoll::class)->handle('Anden', ['2026-10-09', '2026-10-10']);
        $this->expectException(QueryException::class);
        DB::table('polls')->where('id', $this->poll->id)->update(['final_option_id' => $other->poll->options()->first()->id]);
    }

    public function test_reopening_requires_at_least_two_active_options(): void
    {
        $poll = app(ClosePoll::class)->handle($this->poll, $this->access->id, 0);
        $poll->options()->where('id', '!=', $this->option)->delete();
        try {
            app(ReopenPoll::class)->handle($poll, $this->access->id, 1);
            $this->fail('Expected minimum option rejection');
        } catch (ValidationException) {
            $this->assertSame('closed', $poll->fresh()->status);
        }
    }

    public function test_audit_failure_rolls_back_the_domain_change(): void
    {
        PollAuditEntry::creating(fn () => throw new RuntimeException('Audit storage failed'));
        try {
            app(FinalizePoll::class)->handle($this->poll, $this->access->id, 0, $this->option);
            $this->fail('Expected audit failure');
        } catch (RuntimeException) {
            $this->assertSame('open', $this->poll->fresh()->status);
            $this->assertNull($this->poll->fresh()->final_option_id);
            $this->assertSame(0, $this->poll->fresh()->management_version);
        } finally {
            PollAuditEntry::flushEventListeners();
        }
    }

    public function test_new_visitor_sees_final_date_but_cannot_access_votes_or_names(): void
    {
        $this->vote();
        app(FinalizePoll::class)->handle($this->poll, $this->access->id, 0, $this->option);
        $this->get(route('polls.show', $this->poll))->assertOk()->assertSee('9. oktober 2026')->assertDontSee('Private participant');
        $this->getJson(route('polls.results', $this->poll))->assertForbidden();
    }
}
