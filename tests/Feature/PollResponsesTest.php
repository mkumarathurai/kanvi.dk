<?php

namespace Tests\Feature;

use App\Actions\Polls\CreatePoll;
use App\Actions\Polls\SubmitResponse;
use App\Domain\Polls\Models\Participant;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\Response;
use App\Domain\Polls\Services\PollResults;
use App\Http\PollParticipantCookie;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PollResponsesTest extends TestCase
{
    use RefreshDatabase;

    private Poll $poll;

    private string $token;

    private string $editor;

    private string $option;

    protected function setUp(): void
    {
        parent::setUp();
        $this->poll = app(CreatePoll::class)->handle('Middag sammen', ['2026-10-09', '2026-10-10'])->poll;
        $this->option = $this->poll->options()->first()->id;
        $this->token = bin2hex(random_bytes(32));
        $this->editor = bin2hex(random_bytes(16));
    }

    private function payload(string $value = 'can', int $revision = 1, bool $includeName = true): array
    {
        $changes = [['field' => $this->option, 'value' => $value, 'revision' => $revision]];
        if ($includeName) {
            $changes[] = ['field' => 'name', 'value' => 'Mathi', 'revision' => 1];
        }

        return ['editor_id' => $this->editor, 'changes' => $changes];
    }

    private function asParticipant(): static
    {
        return $this->withCredentials()->withCookie(app(PollParticipantCookie::class)->name($this->poll), $this->token);
    }

    private function submit(array $payload): array
    {
        return app(SubmitResponse::class)->handle($this->poll, $this->token, $payload);
    }

    public function test_first_answer_atomically_creates_participant_and_unlocks_results(): void
    {
        $this->get(route('polls.show', $this->poll))->assertOk()
            ->assertCookie(app(PollParticipantCookie::class)->name($this->poll));
        $this->assertDatabaseCount('participants', 0);
        $this->asParticipant()->getJson(route('polls.results', $this->poll))->assertForbidden();
        $this->postJson(route('polls.responses', $this->poll), $this->payload())
            ->assertOk()->assertJsonPath('participant.name', 'Mathi')
            ->assertJsonPath('participant.answers.'.$this->option, 'can');
        $this->assertDatabaseCount('participants', 1);
        $this->assertDatabaseCount('responses', 1);
        $this->assertSame(hash('sha256', $this->token), Participant::sole()->edit_token_hash);
        $this->assertStringNotContainsString($this->token, Participant::sole()->toJson());
        $this->getJson(route('polls.results', $this->poll))->assertOk()->assertJsonPath('count', 1)
            ->assertJsonPath('incomplete', 1)->assertJsonPath('options.1.unanswered', 1);
        $this->get(route('polls.show', $this->poll))->assertOk()->assertSee('Mathi')->assertDontSee($this->token);
    }

    public function test_name_alone_does_not_create_participant_or_unlock_results(): void
    {
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), [
            'editor_id' => $this->editor, 'changes' => [['field' => 'name', 'value' => 'Mathi', 'revision' => 1]],
        ])->assertUnprocessable();
        $this->assertDatabaseCount('participants', 0);
        $this->getJson(route('polls.results', $this->poll))->assertForbidden();
    }

    public function test_missing_cookie_is_not_an_edit_identity(): void
    {
        $this->postJson(route('polls.responses', $this->poll), $this->payload())->assertStatus(419);
        $this->assertDatabaseCount('participants', 0);
    }

    public function test_reordered_writes_and_lost_ack_retries_do_not_revert_latest_choice(): void
    {
        $this->submit($this->payload());
        $this->submit($this->payload('cannot', 3, false));
        $reply = $this->submit($this->payload('maybe', 2, false));
        $this->assertSame('cannot', $reply['participant']['answers'][$this->option]);
        $this->submit($this->payload());
        $this->assertSame('cannot', Response::sole()->value);
        $this->assertDatabaseCount('participants', 1);
        $this->assertDatabaseCount('responses', 1);
    }

    public function test_revision_cannot_be_reused_for_different_payload(): void
    {
        $this->submit($this->payload());
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $this->payload('maybe'))
            ->assertUnprocessable();
        $this->assertSame('can', Response::sole()->value);
    }

    public function test_other_tab_wins_but_retry_from_first_tab_cannot_write_again(): void
    {
        $this->submit($this->payload());
        $secondTab = $this->payload('maybe', 1, false);
        $secondTab['editor_id'] = bin2hex(random_bytes(16));
        $this->submit($secondTab);
        $reply = $this->submit($this->payload());
        $this->assertSame('maybe', Response::sole()->value);
        $this->assertSame('maybe', $reply['participant']['answers'][$this->option]);
        $this->submit($this->payload('cannot', 2, false));
        $this->assertSame('cannot', Response::sole()->value);
    }

    public function test_two_people_can_have_same_name_but_cannot_edit_each_other(): void
    {
        $this->submit($this->payload());
        $first = Participant::sole();
        $this->token = bin2hex(random_bytes(32));
        $payload = $this->payload('cannot');
        $payload['participant_id'] = $first->id;
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $payload)->assertOk();
        $this->assertDatabaseCount('participants', 2);
        $this->assertSame('can', $first->responses()->sole()->value);
        $this->assertSame(2, app(PollResults::class)->forPoll($this->poll)['count']);
    }

    public function test_foreign_option_is_rejected_without_partial_participant_write(): void
    {
        $other = app(CreatePoll::class)->handle('Anden afstemning', ['2026-10-09', '2026-10-10'])->poll;
        $this->option = $other->options()->first()->id;
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('participants', 0);
        $this->assertDatabaseCount('responses', 0);
    }

    public function test_soft_deleted_option_cannot_receive_a_response(): void
    {
        $this->poll->options()->first()->delete();
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('participants', 0);
    }

    #[DataProvider('inactiveStatuses')]
    public function test_closed_states_reject_new_and_existing_participants(string $status): void
    {
        $this->submit($this->payload());
        $this->poll->update(['status' => $status]);
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $this->payload('maybe', 2))->assertStatus(409);
        $this->assertSame('can', Response::sole()->value);
        $this->token = bin2hex(random_bytes(32));
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $this->payload())->assertStatus(409);
        $this->assertDatabaseCount('participants', 1);
    }

    public static function inactiveStatuses(): array
    {
        return [['finalized'], ['closed'], ['archived']];
    }

    public function test_invalid_value_rejects_entire_batch(): void
    {
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $this->payload('yes'))->assertUnprocessable();
        $this->assertDatabaseCount('responses', 0);
        $this->assertDatabaseCount('participants', 0);
    }

    #[DataProvider('invalidNames')]
    public function test_invalid_name_rejects_first_answer(string $name): void
    {
        $payload = $this->payload();
        $payload['changes'][1]['value'] = $name;
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $payload)->assertUnprocessable();
        $this->assertDatabaseCount('participants', 0);
    }

    public static function invalidNames(): array
    {
        return [['   '], [str_repeat('a', 81)]];
    }

    public function test_database_rejects_cross_poll_response_even_without_action(): void
    {
        $this->submit($this->payload());
        $other = app(CreatePoll::class)->handle('Anden afstemning', ['2026-10-09', '2026-10-10'])->poll;
        $this->expectException(QueryException::class);
        DB::table('responses')->insert([
            'id' => (string) Str::ulid(), 'poll_id' => $this->poll->id,
            'participant_id' => Participant::sole()->id,
            'poll_option_id' => $other->options()->first()->id, 'value' => 'can',
        ]);
    }

    public function test_database_rejects_invalid_value(): void
    {
        $this->submit($this->payload());
        $this->expectException(QueryException::class);
        DB::table('responses')->update(['value' => 'invalid']);
    }

    public function test_database_prevents_duplicate_response(): void
    {
        $this->submit($this->payload());
        $this->expectException(QueryException::class);
        DB::table('responses')->insert([
            'id' => (string) Str::ulid(), 'poll_id' => $this->poll->id,
            'participant_id' => Participant::sole()->id, 'poll_option_id' => $this->option, 'value' => 'maybe',
        ]);
    }

    public function test_results_are_not_embedded_in_public_html_or_available_to_another_browser(): void
    {
        $payload = $this->payload();
        $payload['changes'][1]['value'] = 'Privat Deltagernavn';
        $this->submit($payload);
        $this->get(route('polls.show', $this->poll))->assertOk()->assertDontSee('Privat Deltagernavn');
        $this->getJson(route('polls.results', $this->poll))->assertForbidden();
    }

    public function test_admin_can_see_results_without_voting(): void
    {
        $created = app(CreatePoll::class)->handle('Admin', ['2026-10-09', '2026-10-10']);
        $this->get(route('polls.admin', $created->adminToken))->assertRedirect();
        $this->getJson(route('polls.results', $created->poll))->assertOk()->assertJsonPath('count', 0);
    }

    public function test_totals_ranking_ties_and_unanswered_use_active_options(): void
    {
        $this->submit($this->payload());
        $secondOption = $this->poll->options()->get()->last()->id;
        $this->option = $secondOption;
        $this->submit($this->payload('can', 1, false));
        $results = app(PollResults::class)->forPoll($this->poll);
        $this->assertSame(0, $results['incomplete']);
        $this->assertTrue($results['options'][0]['best']);
        $this->assertTrue($results['options'][1]['best']);
        $this->poll->options()->create(['date_value' => '2026-10-11', 'kind' => 'date', 'sort_order' => 2]);
        $results = app(PollResults::class)->forPoll($this->poll);
        $this->assertSame(1, $results['incomplete']);
        $this->assertSame(1, $results['options'][2]['unanswered']);
        $this->assertSame(0, $results['options'][2]['cannot']);
        $this->assertNull($results['options'][2]['people'][0]['value']);
        $this->poll->options()->whereIn('id', [$results['options'][0]['id'], $results['options'][1]['id']])->delete();
        $results = app(PollResults::class)->forPoll($this->poll);
        $this->assertSame(0, $results['count']);
        $this->assertDatabaseCount('participants', 1);
        $this->asParticipant()->getJson(route('polls.results', $this->poll))->assertForbidden();
    }

    public function test_same_stream_name_updates_do_not_revert_on_old_retry(): void
    {
        $first = $this->payload();
        $this->submit($first);
        $this->submit(['editor_id' => $this->editor, 'changes' => [['field' => 'name', 'value' => 'Mette', 'revision' => 3]]]);
        $this->submit($first);
        $this->assertSame('Mette', Participant::sole()->display_name);
    }

    public function test_deleted_participant_cannot_recreate_or_edit_identity(): void
    {
        $this->submit($this->payload());
        Participant::sole()->delete();
        $this->asParticipant()->postJson(route('polls.responses', $this->poll), $this->payload('maybe', 2))->assertForbidden();
        $this->getJson(route('polls.results', $this->poll))->assertForbidden();
    }
}
