<?php

namespace App\Mail;

class PasswordResetMail extends KanviMail
{
    public function __construct(string $url, string $expiresAt)
    {
        parent::__construct(
            title: 'Vælg en ny adgangskode til Kanvi',
            preheader: 'Her er dit link til at vælge en ny adgangskode.',
            heading: 'Lad os hjælpe dig tilbage',
            introduction: 'Du bad om at nulstille din adgangskode. Her kan du vælge en ny.',
            actionLabel: 'Nulstil adgangskode',
            actionUrl: $url,
            paragraphs: [
                "Linket udløber {$expiresAt}. Del det ikke med andre.",
                'Har du ikke bedt om det, kan du ignorere denne mail. Din adgangskode ændres først, når du selv vælger en ny.',
            ],
            reason: 'Du modtager denne mail, fordi der er bedt om at nulstille adgangskoden til din mailadresse.',
        );
    }
}
