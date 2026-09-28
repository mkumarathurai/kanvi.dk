<?php

namespace Tests\Feature;

use App\Actions\Polls\CreatePoll;
use App\Actions\Polls\RedeemAdminRecovery;
use App\Actions\Polls\RequestAdminRecovery;
use App\Domain\Polls\Models\AdminRecoveryLink;
use App\Domain\Polls\Models\PollAuditEntry;
use App\Domain\Polls\Services\PollLinks;
use App\Domain\Polls\Services\RecoveryMail;
use App\Jobs\SendAdminRecovery;
use App\Mail\AdminRecoveryMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PollRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['kanvi.recovery_mailer' => 'array']);
        Queue::fake();
    }

    private function poll()
    {
        return app(CreatePoll::class)->handle('Sommerfest', ['2026-10-09', '2026-10-10']);
    }

    private function registration($created, string $email = 'owner@example.test'): SendAdminRecovery
    {
        app(RequestAdminRecovery::class)->handle($created->poll, $email, $created->adminAccess->id);

        return Queue::pushed(SendAdminRecovery::class)->last();
    }

    public function test_registration_requires_scoped_active_admin_access(): void
    {
        $created = $this->poll();
        $other = $this->poll();
        $this->post(route('recovery.register', $created->poll), ['email' => 'a@example.test'])->assertForbidden();
        $this->get(route('polls.admin', $other->adminToken));
        $this->post(route('recovery.register', $created->poll), ['email' => 'b@example.test'])->assertForbidden();
        $this->get(route('polls.admin', $created->adminToken));
        $created->adminAccess->update(['revoked_at' => now()]);
        $this->post(route('recovery.register', $created->poll), ['email' => 'c@example.test'])->assertForbidden();
        Queue::assertNothingPushed();
    }

    public function test_email_registration_is_pending_until_post_confirmation_and_link_is_single_use(): void
    {
        $created = $this->poll();
        $this->get(route('polls.admin', $created->adminToken));
        $this->post(route('recovery.register', $created->poll), ['email' => 'Owner@Example.test'])
            ->assertRedirect(route('polls.share', $created->poll));
        $job = Queue::pushed(SendAdminRecovery::class)->first();
        $link = AdminRecoveryLink::first();
        $this->assertNull($created->adminAccess->fresh()->email);
        $this->assertSame(hash('sha256', $job->token), $link->token_hash);
        $this->flushSession();
        $this->get(route('recovery.open', $job->token))->assertRedirect(route('recovery.confirm'))
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertNull($link->fresh()->consumed_at);
        $this->assertStringNotContainsString($job->token, serialize(session()->all()));
        $this->get(route('recovery.confirm'))->assertOk()->assertDontSee($job->token);
        $this->post(route('recovery.redeem'))->assertRedirect(route('polls.share', $created->poll));
        $this->assertNotNull($link->fresh()->consumed_at);
        $this->assertSame('owner@example.test', $created->adminAccess->fresh()->email);
        $this->get(route('polls.manage', $created->poll))->assertOk();
        $this->assertDatabaseCount('poll_admin_access', 2);
        $this->get(route('recovery.open', $job->token))->assertRedirect(route('recovery.confirm'));
        $this->get(route('recovery.confirm'))->assertSee('Linket kan ikke bruges.');
        $this->assertDatabaseCount('poll_admin_access', 2);
    }

    public function test_public_request_does_not_reveal_or_register_unknown_addresses(): void
    {
        $created = $this->poll();
        $created->adminAccess->update(['email' => 'known@example.test', 'email_verified_at' => now()]);
        $first = $this->post(route('recovery.send', $created->poll), ['email' => 'known@example.test']);
        $message = session('recovery_status');
        $first->assertRedirect();
        $this->post(route('recovery.send', $created->poll), ['email' => 'unknown@example.test'])->assertRedirect();
        $this->assertSame($message, session('recovery_status'));
        Queue::assertPushed(SendAdminRecovery::class, 1);
        $job = Queue::pushed(SendAdminRecovery::class)->first();
        $recovered = app(RedeemAdminRecovery::class)->handle($job->token);
        $this->assertSame($created->poll->id, $recovered->poll->id);
        $this->assertSame('known@example.test', $recovered->adminAccess->email);
        $this->assertDatabaseHas('poll_audit_entries', ['action' => 'admin_access_recovered']);
    }

    public function test_expired_revoked_deleted_and_archived_links_are_unusable(): void
    {
        foreach (['expired', 'revoked', 'deleted', 'archived'] as $case) {
            $created = $this->poll();
            $job = $this->registration($created);
            match ($case) {
                'expired' => AdminRecoveryLink::find($job->linkId)->update(['expires_at' => now()->subSecond()]),
                'revoked' => $created->adminAccess->update(['revoked_at' => now()]),
                'deleted' => $created->poll->delete(),
                'archived' => $created->poll->update(['status' => 'archived']),
            };
            $this->flushSession();
            $this->get(route('recovery.open', $job->token))->assertRedirect(route('recovery.confirm'));
            $this->get(route('recovery.confirm'))->assertSee('Linket kan ikke bruges.');
        }
    }

    public function test_latest_registration_wins_and_old_recovery_address_is_replaced_across_browsers(): void
    {
        $created = $this->poll();
        $first = $this->registration($created, 'first@example.test');
        $second = $this->registration($created, 'second@example.test');
        $this->assertNotNull(AdminRecoveryLink::find($first->linkId)->consumed_at);
        $recovered = app(RedeemAdminRecovery::class)->handle($second->token);
        $third = $this->registration($created, 'third@example.test');
        app(RedeemAdminRecovery::class)->handle($third->token);
        $this->assertSame('third@example.test', $created->adminAccess->fresh()->email);
        $this->assertSame('third@example.test', $recovered->adminAccess->fresh()->email);
        $before = AdminRecoveryLink::count();
        app(RequestAdminRecovery::class)->handle($created->poll, 'second@example.test');
        $this->assertSame($before, AdminRecoveryLink::count());
    }

    public function test_expiry_between_get_and_post_and_revocation_before_delivery_are_checked(): void
    {
        $created = $this->poll();
        $job = $this->registration($created);
        $this->get(route('recovery.open', $job->token));
        $created->adminAccess->update(['revoked_at' => now()]);
        $this->post(route('recovery.redeem'))->assertRedirect(route('recovery.confirm'));
        $this->assertDatabaseCount('poll_admin_access', 1);
        Mail::fake();
        $job->handle(app(PollLinks::class), app(RecoveryMail::class));
        Mail::assertNothingSent();
    }

    public function test_mail_has_only_public_title_and_canonical_recovery_url(): void
    {
        $created = $this->poll();
        config(['app.url' => 'https://kanvi.example']);
        $job = $this->registration($created);
        Mail::fake();
        $job->handle(app(PollLinks::class), app(RecoveryMail::class));
        Mail::assertSent(AdminRecoveryMail::class, function ($mail) use ($created, $job) {
            $this->assertSame('https://kanvi.example/adgang/link/'.$job->token, $mail->accessUrl);
            $this->assertStringNotContainsString($created->adminToken, $mail->render());

            return $mail->hasTo('owner@example.test');
        });
        $mail = new AdminRecoveryMail('<script>Bad</script>', 'https://kanvi.example/adgang/link/test', '12:00', true);
        $this->assertStringNotContainsString('<script>Bad</script>', $mail->render());
    }

    public function test_queue_payload_encrypts_access_token_and_email(): void
    {
        $created = $this->poll();
        $job = $this->registration($created);
        $queue = new DatabaseQueue(DB::connection(), 'jobs');
        $queue->setContainer(app());
        $job->beforeCommit();
        $queue->push($job);
        $payload = DB::table('jobs')->value('payload');
        $this->assertStringNotContainsString($job->token, $payload);
        $this->assertStringNotContainsString('owner@example.test', $payload);
    }

    public function test_mail_requires_real_delivery_configuration_and_request_is_rate_limited(): void
    {
        $created = $this->poll();
        config(['kanvi.recovery_mailer' => 'log']);
        $this->get(route('recovery.request', $created->poll))->assertSee('Mail er ikke slået til endnu.');
        $this->post(route('recovery.send', $created->poll), ['email' => 'disabled@example.test'])->assertStatus(503);
        config(['kanvi.recovery_mailer' => 'array']);
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('recovery.send', $created->poll), ['email' => 'limit@example.test'])->assertRedirect();
        }
        $this->post(route('recovery.send', $created->poll), ['email' => 'LIMIT@example.test'])->assertTooManyRequests();
        Queue::assertNothingPushed();
    }

    public function test_audit_failure_rolls_back_consumption_and_new_access(): void
    {
        $created = $this->poll();
        $job = $this->registration($created);
        PollAuditEntry::creating(fn () => throw new \RuntimeException('Audit unavailable'));
        try {
            app(RedeemAdminRecovery::class)->handle($job->token);
            $this->fail('Expected rollback');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        } finally {
            PollAuditEntry::flushEventListeners();
        }
        $this->assertDatabaseCount('poll_admin_access', 1);
        $this->assertNull(AdminRecoveryLink::find($job->linkId)->consumed_at);
        $this->assertNull($created->adminAccess->fresh()->email);
    }
}
