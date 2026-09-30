<?php

namespace App\Actions\Polls;

use App\Domain\Polls\Models\AdminRecoveryLink;
use App\Domain\Polls\Models\Poll;
use App\Domain\Polls\Services\RecoveryMail;
use App\Jobs\SendAdminRecovery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class RequestAdminRecovery
{
    public function handle(Poll $poll, string $email, ?string $authorizedAccessId = null): void
    {
        abort_unless(app(RecoveryMail::class)->enabled(), 503, 'Mail er ikke slået til endnu. Gem dit administrationslink.');
        $email = mb_strtolower(trim($email));
        Validator::make(['email' => $email], ['email' => ['required', 'email:rfc', 'max:254']],
            ['email.*' => 'Skriv en gyldig mailadresse.'])->validate();

        DB::transaction(function () use ($poll, $email, $authorizedAccessId) {
            $locked = Poll::whereKey($poll->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->status === 'archived', 404);
            $query = $locked->adminAccess()->active()->lockForUpdate();
            $access = $authorizedAccessId
                ? $query->whereKey($authorizedAccessId)->first()
                : $query->where('email', $email)->whereNotNull('email_verified_at')->first();
            if (! $access) {
                abort_if($authorizedAccessId !== null, 403);

                return;
            }
            if ($authorizedAccessId) {
                // Only the latest registration may change the poll's recovery address.
                AdminRecoveryLink::whereIn('admin_access_id', $locked->adminAccess()->select('id'))->where('register_email', true)
                    ->whereNull('consumed_at')->update(['consumed_at' => now()]);
            }
            $token = bin2hex(random_bytes(32));
            $link = AdminRecoveryLink::create([
                'admin_access_id' => $access->id,
                'token_hash' => hash('sha256', $token),
                'email' => $email,
                'register_email' => $authorizedAccessId !== null,
                'expires_at' => now()->addMinutes(config('kanvi.recovery_minutes')),
            ]);
            $locked->markActive()->save();
            SendAdminRecovery::dispatch($link->id, $token)->afterCommit();
        }, 3);
    }
}
