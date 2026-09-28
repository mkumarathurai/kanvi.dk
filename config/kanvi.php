<?php

return [
    // Explicit opt-in: never write access-bearing mails to the log transport.
    'recovery_mailer' => env('KANVI_RECOVERY_MAILER'),
    'recovery_minutes' => 30,
];
