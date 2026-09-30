<?php

namespace Tests\Feature;

use App\Actions\Polls\ClosePoll;
use App\Actions\Polls\CreatedPoll;
use App\Actions\Polls\CreatePoll;
use App\Actions\Polls\SubmitResponse;
use App\Domain\Polls\Models\AdminRecoveryLink;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\PollAuditEntry;
use App\Http\PollParticipantCookie;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollRetentionTest extends TestCase
{
    use RefreshDatabase;

    private int $window;

    protected function setUp(): void
    {
        parent::setUp();
        $this->window = (int) config('kanvi.retention_months');
    }

    private function poll(string $title = 'Sommerfest'): CreatedPoll
    {
        return app(CreatePoll::class)->handle($title, ['2026-10-09', '2026-10-10']);
    }

    /** Retention is measured from the poll's own activity timestamp, so tests move that, not the clock. */
    private function lastActive(Poll $poll, ?string $when): void
    {
        Poll::withTrashed()->whereKey($poll->id)->update(['last_activity_at' => $when]);
    }

    private function answer(CreatedPoll $created): void
    {
        app(SubmitResponse::class)->handle($created->poll, bin2hex(random_bytes(32)), [
            'editor_id' => bin2hex(random_bytes(16)),
            'changes' => [
                ['field' => 'name', 'value' => 'Mette', 'revision' => 1],
                ['field' => $created->poll->options()->first()->id, 'value' => 'can', 'revision' => 1],
            ],
        ]);
    }

    public function test_a_poll_is_kept_until_the_retention_window_has_passed(): void
    {
        $kept = $this->poll('Inden for vinduet')->poll;
        $expired = $this->poll('Uden for vinduet')->poll;
        $this->lastActive($kept, now()->subMonths($this->window)->addDay());
        $this->lastActive($expired, now()->subMonths($this->window)->subDay());

        $this->artisan('kanvi:purge-polls')->assertSuccessful();

        $this->assertTrue(Poll::withTrashed()->whereKey($kept->id)->exists());
        $this->assertFalse(Poll::withTrashed()->whereKey($expired->id)->exists());
    }

    public function test_deleting_an_expired_poll_leaves_no_trace_of_its_participants(): void
    {
        $created = $this->poll();
        $this->answer($created);
        AdminRecoveryLink::create([
            'admin_access_id' => $created->adminAccess->id,
            'token_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'email' => 'arrangoer@example.test',
            'register_email' => true,
            'expires_at' => now()->addMinutes(30),
        ]);
        PollAuditEntry::create([
            'poll_id' => $created->poll->id,
            'admin_access_id' => $created->adminAccess->id,
            'action' => 'closed',
            'details' => ['before' => [], 'after' => []],
        ]);
        $tables = ['polls' => 1, 'poll_options' => 2, 'poll_admin_access' => 1, 'participants' => 1,
            'responses' => 1, 'response_revisions' => 2, 'poll_audit_entries' => 1, 'admin_recovery_links' => 1];
        foreach ($tables as $table => $rows) {
            $this->assertDatabaseCount($table, $rows);
        }

        $this->lastActive($created->poll, now()->subMonths($this->window)->subDay());
        $this->artisan('kanvi:purge-polls')->assertSuccessful();

        foreach (array_keys($tables) as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_answering_or_administering_a_poll_restarts_the_retention_clock(): void
    {
        $answered = $this->poll('Nyt svar');
        $administered = $this->poll('Lukket af arrangøren');
        $expired = now()->subMonths($this->window)->subDay();
        $this->lastActive($answered->poll, $expired);
        $this->lastActive($administered->poll, $expired);

        $this->answer($answered);
        app(ClosePoll::class)->handle($administered->poll, $administered->adminAccess->id,
            $administered->poll->refresh()->management_version);

        $this->artisan('kanvi:purge-polls')->assertSuccessful();

        $this->assertTrue(Poll::withTrashed()->whereKey($answered->poll->id)->exists());
        $this->assertTrue(Poll::withTrashed()->whereKey($administered->poll->id)->exists());
    }

    public function test_the_purge_is_idempotent_and_also_clears_soft_deleted_polls(): void
    {
        $expired = $this->poll()->poll;
        $expired->delete();
        $this->lastActive($expired, now()->subMonths($this->window)->subDay());

        $this->artisan('kanvi:purge-polls')->assertSuccessful();
        $this->artisan('kanvi:purge-polls')->assertSuccessful();

        $this->assertDatabaseCount('polls', 0);
    }

    public function test_a_poll_without_a_recorded_activity_timestamp_is_never_deleted(): void
    {
        $poll = $this->poll()->poll;
        $this->lastActive($poll, null);

        $this->artisan('kanvi:purge-polls')->assertSuccessful();

        $this->assertTrue(Poll::withTrashed()->whereKey($poll->id)->exists());
    }

    public function test_creating_a_poll_records_its_first_activity(): void
    {
        $poll = $this->poll()->poll;

        $this->assertNotNull($poll->last_activity_at);
        $this->assertTrue($poll->last_activity_at->isSameMinute(now()));
    }

    /**
     * The command deletes data the privacy page promises to delete. If it ever
     * falls out of the schedule, nothing breaks and nobody notices; the polls
     * just quietly stay forever.
     */
    public function test_the_purge_is_scheduled_daily(): void
    {
        $purge = collect(app(Schedule::class)->events())
            ->first(fn (Event $event) => str_contains($event->command ?? '', 'kanvi:purge-polls'));

        $this->assertNotNull($purge, 'kanvi:purge-polls is not on the schedule.');
        $this->assertSame('30 3 * * *', $purge->expression);
    }

    public function test_the_participant_cookie_expires_with_the_retention_window(): void
    {
        $poll = $this->poll()->poll;

        $cookie = app(PollParticipantCookie::class)->issue(request(), $poll);

        $this->assertEqualsWithDelta(now()->addMonths($this->window)->getTimestamp(),
            $cookie->getExpiresTime(), 60);
    }
}
