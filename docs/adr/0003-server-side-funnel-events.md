# ADR 0003: Funnel events are sent by the server, not the browser

Date: 2026-09-30
Status: Accepted
Ticket: KAN-14

## Context

Only pageviews are measured. The product contract asks for three funnel events:
a poll is created, a poll gets its first response, and a final date is chosen.
Without them there is no way to see where organizers drop out.

The privacy page promises that polls, results, share screens, administration and
recovery pages do not load the statistics at all. Two of the three events happen
on exactly those pages, so client-side tracking there would break a published
promise.

## Decision

Kanvi's server sends the three events to Mathi's self-hosted Umami, through its
`/api/send` endpoint, from a queued job dispatched after the database commit.
Mathi chose this on 2026-09-30.

The payload is fixed: the website ID, hostname `kanvi.dk`, URL `/`, language `da`
and the event name. Nothing else. No poll ID, title, participant name, token or
visitor IP leaves the server; Umami sees only the server's own address. Events
are sent only in production, the same rule as the pageview script.

Umami ignores requests without a browser-like `User-Agent`, so the job sends a
fixed one that names Kanvi and carries no bot marker.

"First response" is the first participant ever created on a poll, decided under
the poll lock, so two simultaneous first answers still count once. "Final date
chosen" is sent on every finalization; a reopened and refinalized poll counts
twice.

Rejected alternatives: counting on public pages only would measure one event of
three. Loading the tracker on private pages for these two events would send each
participant's browser to Umami and reverse the privacy promise.

## Consequences

Private pages still load no tracker, and the existing tests for that are
unchanged. The privacy page gains one sentence saying the server counts three
anonymous events.

A failed send is retried by the queue and then lands in `failed_jobs`. It never
affects the poll, because the job runs after the commit. Umami answers bot-flagged
requests with success, so only a production walkthrough can prove that the events
are registered.
