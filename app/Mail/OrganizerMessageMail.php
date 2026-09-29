<?php

namespace App\Mail;

class OrganizerMessageMail extends KanviMail
{
    public function __construct(string $eventTitle, string $url, string $update)
    {
        parent::__construct(
            title: 'Nyt om: '.$eventTitle,
            preheader: 'Der er nyt om dine planer hos Kanvi.',
            heading: 'Der er nyt om dine planer',
            introduction: $update,
            actionLabel: 'Se overblikket',
            actionUrl: $url,
            details: [$eventTitle],
            reason: 'Du modtager denne mail, fordi du er arrangør på Kanvi.',
        );
    }
}
