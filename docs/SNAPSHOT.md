# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `a177d49` · Recorded: 2026-10-01 00:30 CEST.

The snapshot's own commit follows this one, so HEAD is one behind by design.

Technical handover only. Project knowledge is in the Knowledge Base overview,
now at `02-Projekter/Mathi ApS/Kanvi` (same Drive link):
https://drive.google.com/file/d/1Fe-URgOMgkIXbZ5v6xawiORAYvPkTUKQ/view
Outstanding work is on the KAN board, not restated here:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi is live at https://kanvi.dk and launches in week 41 (5–11 October 2026),
day not set. Launch means announcing it in Mathi's network and submitting the
sitemap to search engines. Mathi decided that everything on the KAN board must be
done first.

This session closed every open question in the overview, cleaned the fictional
polls out of production, fixed a retention bug that would have stopped the purge
at the first finalized poll, and built the three funnel events, proved in Umami.
Four tickets remain: backup restore, Search Console, mail clients and article
images. The backup test is blocked until CloudPanel's nightly backup has run once.

## 2. What was done

- `545dee6`, `c9fcbb0` KAN-22 · The purge now clears `final_option_id` before
  deleting, so MySQL lets a finalized poll go. Test was red on the MySQL CI job
  first.
- `23911fe` KAN-23 · Deployment and recovery docs describe the current state.
- `4fce139` KAN-6 · Recovery mail reached the Gmail inbox, not spam.
- `374af6a` KAN-23 · `CLAUDE.md` and `AGENTS.md` point at the moved KB folder.
- `01ac0fd`, `3116b57`, `9559605` KAN-14 · ADR 0003 and server-side funnel events
  `poll-created`, `first-response`, `final-date-chosen`; privacy page discloses them.
- `a177d49` KAN-14 · `Http::preventStrayRequests()` in `tests/TestCase.php`.

Outside the repository:

- Production: three fictional polls deleted by Mathi on the server. Zero polls
  with "fiktive data" in the title remain.
- Jira: KAN-22 and KAN-23 created and closed, KAN-14 closed, KAN-6 and KAN-7
  commented.
- Knowledge Base: folder moved from `01-Indbakke/Kanvi` to
  `02-Projekter/Mathi ApS/Kanvi`; decisions and status added.

## 3. Unfinished

Working tree clean, nothing unpushed, no stashes. Unfinished work lives on the
board:

- **KAN-7 backup restore.** `~/backups/databases` held no dump on 2026-09-30,
  because the database was created that day. CloudPanel's job in
  `/etc/cron.d/clp` runs at 03:15. Nothing has been restored yet.
- **KAN-13.** Umami ingestion is effectively shown: Mathi's screenshot had
  4 visitors and all three funnel events. Nobody has looked at the Pageviews view
  on purpose, and Search Console is untouched.
- **KAN-16** mail clients: not started. Gmail inbox placement is the only
  provider result.
- **KAN-11** article images: eight articles without images; the mobile screenshot
  still needs a method other than resizing Chrome.
- **Umami holds three false `poll-created` events** from 2026-09-30
  22:15–22:20 UTC, sent by the test suite before `a177d49`. They cannot be removed
  individually as far as is known; subtract them when reading that night.

## 4. Next steps

1. After 03:15 CEST on 2026-10-01, KAN-7 with Mathi, one step at a time. First
   step, run by Mathi as `kanvi` on the server:

   ```sh
   find ~/backups -type f -printf '%TY-%Tm-%Td %TH:%TM  %10s  %p\n' | sort | tail -20
   ```

   Then restore the newest dump into a scratch database, never over `kanvi`,
   inspect tables, migrations and poll rows, and write the procedure into
   `docs/GITHUB-ACTIONS-DEPLOYMENT.md` next to the rollback section.

2. KAN-13 with Mathi: confirm pageviews in Umami, then add the domain in Search
   Console and submit `https://kanvi.dk/sitemap.xml`.

3. KAN-16 and KAN-11 in the order Mathi picks.

4. After any code change:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 5. Waiting on Mathi

- The backup test needs his server shell; the site user cannot run `clpctl`.
- Search Console and any Umami view need his accounts.
- The five mail clients need his devices and inboxes.
- Article images: which images to use.
- The launch day within week 41.

## 6. Decisions made, and why

All are in the KB overview with dates; the technical ones are in `docs/adr/`.

- **Resend stays** despite US storage. Mathi: it does not matter for sending mail.
  Only the organizer's address is involved, and the privacy page states it.
- **Funnel events are sent by the server** (ADR 0003). The only option that
  measures all three steps without breaking the privacy page's promise that
  private pages load no statistics.
- **Kanvi belongs to Mathi ApS** in the Knowledge Base.
- **Specwise is not used for Kanvi.** The specification stays in the repo.
- **Launch in week 41 with the whole board done**, including images and funnel
  events.
- **Joint tasks go one task and one step at a time.** Give Mathi one command,
  wait for the output, then the next. Never a list of steps across tasks.

## 7. Dead ends and corrections

- **SQLite hides the finalized-poll delete failure.** The composite foreign key
  `polls_final_option_same_poll` points a poll at its own option; MySQL refuses the
  delete, SQLite does not. Any test about deleting polls must run on the MySQL job
  to mean anything. Push to a branch to see the MySQL result without deploying.
- **A test in production mode with the sync queue sends real HTTP.** It happened
  once and put three false events in Umami. `preventStrayRequests()` now fails any
  such test.
- **Umami answers success to requests it drops as bots**, and needs a
  browser-like `User-Agent`. Only a look at the Umami dashboard proves ingestion.
- **`crontab -l` is empty for `kanvi` by design.** CloudPanel keeps site crons in
  `/etc/cron.d/kanvi` and the database backup in `/etc/cron.d/clp`.
- **A hand-made `mysqldump` is not a backup test.** KAN-7 must restore
  CloudPanel's own dump, so it waits for the nightly run.
- **Livewire calendar clicks can be dropped** when a second date is clicked while
  the first is still saving. Clicking the element by reference worked.
- **zsh treats a line of `====` as a command.** Use `echo "-----"` between outputs.

## 8. Traps in this repository

- **A push is a deployment.** CI runs on every branch, but only `main` deploys.
- Deleting a poll outside the purge: delete its `admin_recovery_links`, null
  `final_option_id`, then force-delete. The purge in
  `app/Actions/Polls/PurgeExpiredPolls.php` does this; copy it, do not improvise.
- Funnel events are sent only when `app()->environment('production')`. Tests that
  set the environment to production and create, answer or finalize polls must
  `Queue::fake()`.
- Everything in the previous snapshot's trap list still holds: retention runs on
  `last_activity_at`; a poll without it is never purged; `RecoveryMail::enabled()`
  allows only real transports; the footer carries a personal name, so assert on
  view data; `APP_URL` drives canonical URLs, sitemap, sharing images and mail
  links.
- Secrets live only in `/home/kanvi/htdocs/kanvi.dk/shared/.env` on the server,
  including `RESEND_API_KEY`. Never print or copy it, raw admin or recovery links,
  or real participant data.

## 9. How to check this snapshot still holds

```sh
cd /Users/mathi/www/sites/kanvi.dk
git status --short
git log --oneline -5
curl -sS https://kanvi.dk/deploy-revision.txt
gh run list --limit 3
php artisan test --compact
```

Production should serve `a177d49` or the snapshot commit after it.

## 10. Verification evidence

Run at the time of writing, on `a177d49`:

- `php artisan test --compact`: 151 passed, 1 skipped, 1,479 assertions.
- `npm test`: 38 passed. `vendor/bin/pint --test`: passed. `git diff --check`: clean.
- CI run 36785172929: verify, mysql and deploy green. Production serves
  `a177d49`.
- Production walkthrough in Chrome on 2026-10-01: create, answer, finalize with
  no console errors; Umami showed all three funnel events; the count stayed put
  after the next CI run.

**Not verified:** backup restore, Search Console, mail clients other than Gmail,
funnel behaviour while Umami is down (unit-tested only), load on production MySQL,
a real purge of a finalized poll in production (none old enough before September
2027).

## 11. Skills for the next session

- `snapshot` to resume from this file.
- `tdd` for any code change; `writing-for-agents` when editing this file,
  `AGENTS.md` or `CLAUDE.md`.
- `claude-in-chrome` for production walkthroughs.
- Atlassian connector for the KAN board; the Knowledge Base is on the local Drive
  path.
