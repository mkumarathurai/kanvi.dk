<?php

namespace App\Mail;

class ResponseConfirmationMail extends KanviMail
{
    /** @param list<string> $responseDetails */
    public function __construct(string $eventTitle, string $url, array $responseDetails = [])
    {
        parent::__construct(
            title: 'Dit svar er gemt: '.$eventTitle,
            preheader: 'Tak for dit svar. Du kan se det hos Kanvi.',
            heading: 'Tak for dit svar!',
            introduction: 'Dit svar er gemt. Du hjælper med at få planerne på plads.',
            actionLabel: 'Se dit svar',
            actionUrl: $url,
            details: [$eventTitle, ...$responseDetails],
            reason: 'Du modtager denne mail som bekræftelse på dit svar hos Kanvi.',
        );
    }
}
