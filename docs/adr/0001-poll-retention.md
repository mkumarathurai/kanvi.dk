# ADR 0001: Polls are deleted twelve months after their last activity

Date: 2026-09-30
Status: Accepted
Ticket: KAN-5

## Context

Section 38.2 of the product specification listed archiving and retention as an
open question that must not get an accidental default in code. It had none, so
polls were kept forever. That is a problem on two counts: Kanvi stores
participant names, and the privacy page cannot state a retention period that
does not exist.

The repository already contained the beginnings of two different answers, neither
finished. The polls table has an `archived` status that every controller treats
as gone, but nothing ever sets it. Three tables carry `softDeletes` columns. A
soft delete is not a retention policy; it hides a row and keeps the data.

The participant edit cookie had a one-year lifetime, documented as provisional
and explicitly waiting for this decision.

## Decision

A poll is deleted twelve months after its last activity, together with its
options, participants, responses, revisions, admin access, audit entries and
recovery links. Deletion is permanent. There is no archive step.

Last activity means the last time anyone did something with the poll: creating
it, saving an answer, any organizer action, or requesting or redeeming a recovery
link. Every one of those paths already takes the poll's row lock, so the
timestamp is written inside a transaction that is already happening.

The participant cookie expires with the same window, so edit access never
outlives the data it unlocks.

Mathi decided the window and the deletion on 2026-09-30, choosing it over a
six-month window and over a twelve-month archive followed by deletion at
twenty-four months.

## Consequences

The retention window is measured from a dedicated `polls.last_activity_at`
column rather than derived from `updated_at`. Responses never touched the poll
row's `updated_at`, so that column was never a truthful record of activity, and
deriving retention from it would have deleted active polls.

The column is nullable and a poll without it is never purged. A missing
timestamp is treated as "do not guess" rather than "delete immediately", which
is the safe direction for an irreversible operation. The migration backfills
existing rows from `updated_at`.

An annually recurring event cannot reuse last year's poll after twelve months of
silence. That is the accepted cost of the shorter window.

Deletion depends on the scheduler running on the server. Without it the code is
inert and any public statement about a retention period would be false. That is
tracked separately as KAN-20.

`admin_recovery_links` references `poll_admin_access` without a cascade, so the
purge deletes those rows explicitly before deleting the poll. The remaining
tables are cleared by the database's own cascades.

The `archived` status stays in the schema and keeps returning 404, but it is not
part of retention and nothing sets it.
