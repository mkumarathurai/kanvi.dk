<?php

return [
    // Explicit opt-in: never write access-bearing mails to the log transport.
    'recovery_mailer' => env('KANVI_RECOVERY_MAILER'),
    'recovery_minutes' => 30,

    // Polls are deleted this many months after their last activity, with their
    // participant names and responses. There is no archive step. The participant
    // cookie expires on the same schedule, so edit access never outlives the data.
    // Decided 2026-09-30; see CLAUDE.md and the Knowledge Base project overview.
    'retention_months' => 12,
];
