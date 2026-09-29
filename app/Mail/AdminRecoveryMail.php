<?php

namespace App\Mail;

class AdminRecoveryMail extends KanviMail
{
    public function __construct(public string $pollTitle, public string $accessUrl, public string $expiresAt, public bool $registerEmail)
    {
        parent::__construct(
            title: 'Din arrangøradgang til Kanvi',
            preheader: $registerEmail ? 'Bekræft din mail, så du kan få arrangøradgangen tilbage.' : 'Her er dit link til at få arrangøradgangen tilbage.',
            heading: $registerEmail ? 'Pas godt på din afstemning' : 'Tilbage til din afstemning',
            introduction: $registerEmail ? 'Bekræft din mailadresse, så vi kan hjælpe dig tilbage, hvis du mister adgangen.' : 'Du bad om et link til din afstemning. Her kan du åbne den som arrangør.',
            actionLabel: $registerEmail ? 'Bekræft din mail' : 'Åbn som arrangør',
            actionUrl: $accessUrl,
            details: [$pollTitle],
            paragraphs: [
                "Linket kan bruges én gang og udløber kl. {$expiresAt} (dansk tid). På siden får du et administrationslink, som du kan gemme.",
                'Del ikke denne mail eller linket med gruppen. Alle med linket kan få arrangøradgang.',
                'Har du ikke bedt om mailen, kan du ignorere den. Din nuværende adgang ændres ikke.',
            ],
            reason: 'Du modtager denne mail, fordi der er bedt om arrangøradgang eller bekræftelse af din mailadresse på Kanvi.',
        );
    }
}
