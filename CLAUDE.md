# Kanvi

Read [AGENTS.md](AGENTS.md) for the shared startup sequence, Knowledge Base
links and verification standards. The technical handover is
[docs/SNAPSHOT.md](docs/SNAPSHOT.md).

Knowledge Base folder: `02-Projekter/Mathi ApS/Kanvi`.
Project overview: https://drive.google.com/file/d/1Fe-URgOMgkIXbZ5v6xawiORAYvPkTUKQ/view

## Jira

Project [KAN](https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575)
tracks the outstanding work. Every commit carries its ticket key in a trailer and
no `Co-Authored-By` line:

    Refs: KAN-19

### Exception: the fourteen commits before 2026-09-30

Requirement 9 of the shared Definition of Done asks that a commit can be traced
to a ticket. The commits up to and including `b34465a` carry no key, because the
KAN project did not exist when they were written. History is not rewritten, so
the gap stays. Recorded 2026-09-30, reviewed with the other exceptions once a
quarter.

## Deployment

Pushing to `main` publishes to https://kanvi.dk automatically once CI passes.
There is no separate release step. Treat a push as a deployment.

## Decided product rules

These answer questions that section 38 of the specification left open. Do not
re-open them in code without a new dated decision in the project overview.

- Polls are kept twelve months from their last activity, then deleted with their
  participant names and responses. There is no archive step. The participant
  cookie expires after the same twelve months.
- After finalization or closure, the chosen final date is public to anyone with
  the public link. Totals, participant names and individual answers still
  require participant or organizer access.
- Mail is sent through Resend.
