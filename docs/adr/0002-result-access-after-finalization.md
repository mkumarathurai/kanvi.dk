# ADR 0002: The final date is public after finalization; totals and names are not

Date: 2026-09-30
Status: Accepted
Ticket: KAN-8

## Context

Section 38.1 of the product specification left one question open: what a visitor
who never answered may see once a poll is finalized or closed. Such a visitor can
no longer unlock the result by answering, because the poll is shut.

An implementation choice was made during the first slice and marked as awaiting
product confirmation. It has been running in production since 2026-09-30 without
ever being confirmed, which is the worst of both states: live behavior that the
specification describes as undecided.

## Decision

The chosen final date is public to anyone holding the public link, including
after closure. Totals, participant names and individual answers keep their
existing access requirement: participant or organizer.

A poll closed without a final date shows only its closed state.

The reasoning Mathi gave on 2026-09-30: a shared link has to be able to tell the
group which day it is. That is the whole point of sending it. Who answered what
is a different question, and it stays behind access.

The alternatives were considered and rejected. Showing only the closed status
would make a shared link useless for announcing the date, which is the moment the
link matters most. Opening totals and names to everyone would break the rule that
names require access, and that rule is load-bearing elsewhere in the product.

## Consequences

No code changes. The behavior was already implemented; this records it as decided
and removes the provisional wording from the specification and the README.

Two tests now assert the rule from the stranger's side: the page state a visitor
receives in both the finalized and closed cases, and the closed-without-a-date
case. They read the controller's view data rather than the rendered HTML, because
the site footer carries a personal name and would satisfy a naive assertion that
no name is visible.

Anyone with the link can learn the final date of a poll they were never part of.
That is accepted: the public identifier carries 96 bits of entropy and is not
guessable, and the poll title is already visible to link holders.

Section 38 of the specification now has no open clarifications left.
