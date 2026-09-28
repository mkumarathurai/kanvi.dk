<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminRecoveryMail extends Mailable
{
    public function __construct(public string $pollTitle, public string $accessUrl, public string $expiresAt, public bool $registerEmail) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Din arrangøradgang til Kanvi');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.admin-recovery', text: 'mail.admin-recovery-text');
    }
}
