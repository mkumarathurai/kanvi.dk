<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\AdminRecoveryLink;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Models\PollAuditEntry;
use Illuminate\Support\Facades\DB;

final class RedeemAdminRecovery
{
    public function handle(#[\SensitiveParameter] string $token): CreatedPoll
    {
        $candidate = AdminRecoveryLink::usable()->where('token_hash', hash('sha256', $token))->firstOrFail();

        return DB::transaction(function () use ($candidate) {
            // Same lock ordering as all other poll mutations: poll, then access.
            $poll = Poll::whereKey($candidate->access->poll_id)->lockForUpdate()->firstOrFail();
            abort_if($poll->status === 'archived', 404);
            $source = $poll->adminAccess()->active()->whereKey($candidate->admin_access_id)->lockForUpdate()->firstOrFail();
            $link = AdminRecoveryLink::usable()->whereKey($candidate->id)->lockForUpdate()->firstOrFail();
            abort_unless($link->emailStillValid(), 404);
            if ($link->register_email) {
                $poll->adminAccess()->active()->update(['email' => $link->email, 'email_verified_at' => now()]);
            }
            $link->update(['consumed_at' => now()]);
            // A fresh permanent admin link can be copied in this browser. The mail token stays single-use.
            $adminToken = bin2hex(random_bytes(32));
            $access = $poll->adminAccess()->create([
                'token_hash' => hash('sha256', $adminToken),
                'email' => $link->email,
                'email_verified_at' => now(),
                'expires_at' => $source->expires_at,
            ]);
            PollAuditEntry::create([
                'poll_id' => $poll->id, 'admin_access_id' => $access->id,
                'action' => $link->register_email ? 'recovery_email_verified' : 'admin_access_recovered',
                'details' => ['source_access_id' => $source->id],
            ]);

            return new CreatedPoll($poll, $access, $adminToken);
        }, 3);
    }
}
