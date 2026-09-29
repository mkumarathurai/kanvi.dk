<?php

namespace App\Mail;

class InvitationMail extends KanviMail
{
    /** @param list<string> $eventDetails */
    public function __construct(string $eventTitle, string $url, ?string $organizerName = null, array $eventDetails = [])
    {
        parent::__construct(
            title: 'Du er inviteret: '.$eventTitle,
            preheader: 'Se invitationen og fortæl, om du kommer.',
            heading: 'Du er inviteret 🎉',
            introduction: $organizerName ? "{$organizerName} har inviteret dig. Det ville være dejligt at ses." : 'Der er en invitation til dig. Det ville være dejligt at ses.',
            actionLabel: 'Se invitationen',
            actionUrl: $url,
            details: [$eventTitle, ...$eventDetails],
            paragraphs: ['Hos Kanvi kan du se alle detaljer og fortælle, om du kommer.'],
            reason: 'Du modtager denne mail, fordi du er blevet inviteret via Kanvi.',
        );
    }
}
