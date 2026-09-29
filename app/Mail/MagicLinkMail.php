<?php

namespace App\Mail;

class MagicLinkMail extends KanviMail
{
    public function __construct(string $url, string $expiresAt)
    {
        parent::__construct(
            title: 'Dit loginlink til Kanvi',
            preheader: 'Dit link er klar – log ind på Kanvi.',
            heading: 'Velkommen tilbage 👋',
            introduction: 'Du bad om et link til at logge ind på Kanvi. Her er det.',
            actionLabel: 'Log ind på Kanvi',
            actionUrl: $url,
            paragraphs: [
                "Linket udløber {$expiresAt}. Del det ikke med andre.",
                'Har du ikke bedt om linket, kan du bare ignorere denne mail.',
            ],
            reason: 'Du modtager denne mail, fordi der er bedt om et loginlink til din mailadresse.',
        );
    }
}
