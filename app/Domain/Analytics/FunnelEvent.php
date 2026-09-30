<?php

namespace App\Domain\Analytics;

use App\Jobs\RecordFunnelEvent;

/**
 * The three funnel steps counted in Umami. The server sends them, so private
 * pages never load a tracker; see docs/adr/0003-server-side-funnel-events.md.
 */
enum FunnelEvent: string
{
    case PollCreated = 'poll-created';
    case FirstResponse = 'first-response';
    case FinalDateChosen = 'final-date-chosen';

    /** Queues the event after the surrounding transaction commits. Production only, like the pageview script. */
    public function record(): void
    {
        if (app()->environment('production')) {
            RecordFunnelEvent::dispatch($this)->afterCommit();
        }
    }
}
