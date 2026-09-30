<?php

namespace App\Jobs;

use App\Domain\Analytics\FunnelEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RecordFunnelEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    // Umami drops requests without a browser-like agent, and answers success when it does.
    private const USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) Kanvi/1.0';

    public function __construct(public FunnelEvent $event) {}

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(): void
    {
        $umami = config('kanvi.umami');
        // The payload is fixed: nothing about the poll or the person may be added here.
        $response = Http::timeout(10)->withUserAgent(self::USER_AGENT)->post($umami['endpoint'], [
            'type' => 'event',
            'payload' => [
                'website' => $umami['website_id'],
                'hostname' => $umami['hostname'],
                'url' => '/',
                'language' => 'da',
                'name' => $this->event->value,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException("Umami rejected the {$this->event->value} event with status {$response->status()}.");
        }
    }
}
