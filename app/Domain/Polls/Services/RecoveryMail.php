<?php

namespace App\Domain\Polls\Services;

final class RecoveryMail
{
    public function enabled(): bool
    {
        $mailer = config('kanvi.recovery_mailer');
        $transport = $mailer ? config("mail.mailers.{$mailer}.transport") : null;

        return in_array($transport, ['smtp', 'ses', 'ses-v2', 'postmark', 'resend', 'mailgun', 'sendmail'], true)
            || (app()->environment('testing') && $transport === 'array');
    }
}
