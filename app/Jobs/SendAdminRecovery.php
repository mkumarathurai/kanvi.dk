<?php

namespace App\Jobs;

use App\Domain\Polls\Models\AdminRecoveryLink;
use App\Domain\Polls\Services\PollLinks;
use App\Domain\Polls\Services\RecoveryMail;
use App\Mail\AdminRecoveryMail;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendAdminRecovery implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public string $linkId, #[\SensitiveParameter] public string $token) {}

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(PollLinks $urls, RecoveryMail $settings): void
    {
        $link = AdminRecoveryLink::usable()->find($this->linkId);
        if (! $link || ! $link->emailStillValid()) {
            return;
        }
        if (! $settings->enabled()) {
            throw new RuntimeException('Recovery-mail er ikke konfigureret.');
        }
        try {
            Mail::mailer(config('kanvi.recovery_mailer'))->to($link->email)->send(new AdminRecoveryMail(
                $link->access->poll->title,
                $urls->route('recovery.open', $this->token),
                $link->expires_at->timezone('Europe/Copenhagen')->format('H:i'),
                $link->register_email,
            ));
        } catch (Throwable) {
            // Transport exceptions can contain message bodies or credentials. Do not persist those.
            throw new RuntimeException('Recovery-mail kunne ikke afleveres til mailtjenesten.');
        }
    }
}
