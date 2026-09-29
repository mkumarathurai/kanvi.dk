<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use InvalidArgumentException;

abstract class KanviMail extends Mailable
{
    /**
     * @param  list<string>  $details
     * @param  list<string>  $paragraphs
     */
    public function __construct(
        public string $title,
        public string $preheader,
        public string $heading,
        public string $introduction,
        public string $actionLabel,
        public string $actionUrl,
        public array $details = [],
        public array $paragraphs = [],
        public ?string $reason = null,
    ) {
        if (! filter_var($actionUrl, FILTER_VALIDATE_URL) || ! in_array(strtolower(parse_url($actionUrl, PHP_URL_SCHEME) ?? ''), ['https', 'http'], true)) {
            throw new InvalidArgumentException('Mailens handling kræver en absolut HTTP(S)-adresse.');
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.message', text: 'emails.message-text');
    }
}
