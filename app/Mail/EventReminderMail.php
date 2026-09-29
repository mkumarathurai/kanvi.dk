<?php

namespace App\Mail;

class EventReminderMail extends KanviMail
{
    /** @param list<string> $eventDetails */
    public function __construct(string $eventTitle, string $url, array $eventDetails = [])
    {
        parent::__construct(
            title: 'Vi ses snart: '.$eventTitle,
            preheader: 'En lille påmindelse om jeres planer – se detaljerne hos Kanvi.',
            heading: 'Vi ses snart',
            introduction: 'Her er en lille påmindelse om det, I har planlagt.',
            actionLabel: 'Se arrangementet',
            actionUrl: $url,
            details: [$eventTitle, ...$eventDetails],
            reason: 'Du modtager denne påmindelse, fordi du deltager i arrangementet på Kanvi.',
        );
    }
}
