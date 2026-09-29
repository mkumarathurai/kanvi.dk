<?php

namespace App\Mail;

class VerifyEmailMail extends KanviMail
{
    public function __construct(string $url, string $expiresAt)
    {
        parent::__construct(
            title: 'Bekræft din mailadresse hos Kanvi',
            preheader: 'Du er næsten klar – bekræft din mailadresse.',
            heading: 'Du er næsten klar',
            introduction: 'Bekræft din mailadresse, så vi ved, at vi har fat i dig.',
            actionLabel: 'Bekræft din mail',
            actionUrl: $url,
            paragraphs: [
                "Linket udløber {$expiresAt}.",
                'Har du ikke bedt om denne mail, kan du bare ignorere den.',
            ],
            reason: 'Du modtager denne mail, fordi din mailadresse er blevet brugt på Kanvi.',
        );
    }
}
