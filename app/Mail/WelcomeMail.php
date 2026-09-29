<?php

namespace App\Mail;

class WelcomeMail extends KanviMail
{
    public function __construct(string $url, ?string $firstName = null)
    {
        parent::__construct(
            title: 'Velkommen til Kanvi',
            preheader: 'Planer er bedre sammen. Kom godt i gang med Kanvi.',
            heading: 'Velkommen til Kanvi 👋',
            introduction: ($firstName ? "Hej {$firstName}. " : '').'Dejligt at have dig med. Vi gør det nemmere at finde en dag og lave planer sammen.',
            actionLabel: 'Kom i gang med Kanvi',
            actionUrl: $url,
            reason: 'Du modtager denne mail, fordi du har oprettet dig på Kanvi.',
        );
    }
}
