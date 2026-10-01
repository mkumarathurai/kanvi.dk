# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `6a7376f` · Recorded: 2026-10-01 ~21:50 CEST.

The snapshot's own commit follows this one, so HEAD is one behind by design.

Technical handover only. Project knowledge is in the Knowledge Base overview at
`02-Projekter/Mathi ApS/Kanvi`. Outstanding work is on the KAN board:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi is live at https://kanvi.dk and launches in week 41 once the KAN board is
done. This was an autonomous overnight session: Mathi asked for KAN-11 and
KAN-16 to be pushed as far as possible while he slept. Reached: the Gmail
desktop round of the mail-client test is partially done (light mode with
images passes, verified against the real 2026-09-30 recovery mail), and a gap
in the illustration plan was found and closed — the julefrokost brief requires
a fourth image that the prompt list missed, so twelve prompts are now ready,
not eleven. KAN-16 moved to In Progress. All three remaining tickets (KAN-7,
KAN-11, KAN-16) are now blocked on Mathi; nothing further can move without
him.

## 2. What was done

- `901d28b` KAN-11 · Twelfth illustration prompt written
  (julefrokost chat-vs-Kanvi, `julefrokost-chat-vs-kanvi.webp`). The brief at
  `docs/indhold/julefrokost.md` (BILLEDE 4) specified it; the prompt list in
  `docs/design/CLASS-ARTICLE-IMAGES.md` had missed it. December dates use real
  2026 weekday pairs (1 Dec 2026 is a Tuesday).
- `6a7376f` KAN-16 · Gmail desktop result recorded in
  `docs/design/EMAIL-DESIGN.md`: light mode with images passes in Gmail web —
  preheader, logo, heading, poll-title box, green CTA and footer all correct.
  Test object was the real recovery mail from 2026-09-30; valid because no
  commit after the send touched any mail-rendering file (last template commit
  is `cca3918`, 2026-09-29).
- KAN-16 transitioned to In Progress; evidence comments added to KAN-16 and
  KAN-11. KB overview updated (status entry, next-steps counts).
- CI green on both commits; production serves `6a7376f`.

## 3. Unfinished

Working tree clean except this snapshot, nothing unpushed, no stashes.

- **KAN-7**: nightly 03:15 `clpctl db:backup` cron still unproven (started but
  produced no dumps 2026-09-29→10-01; now logs). Check after 2026-10-02 03:15
  with Mathi on the server before closing.
- **KAN-11**: the twelve editorial illustrations await Mathi's generation in
  the Claude app from `docs/design/CLASS-ARTICLE-IMAGES.md`. When PNGs arrive:
  WebP variants (cwebp 1440/720/390), insert per the briefs in
  `docs/indhold/*.md`, bump the counts in
  `tests/Feature/ArticlesTest.php` (map after insertion: venner 4, familien 4,
  polterabend 4, foreninger 4, bestyrelser 4, find-en-dato 4, julefrokost 4;
  klassearrangement and datoafstemning unchanged).
- **KAN-16**: Gmail desktop images-off and dark mode not run — both require
  Gmail account-setting changes, which are not made without Mathi (and the
  permission layer blocks them in autonomous runs; correctly so). Settings
  were left untouched (the unsaved change was cancelled). The four other
  clients need Mathi's devices, one client per round.
- Local dev DB holds nine fictional polls kept for future captures; local
  only. Umami still holds three false `poll-created` events from the night of
  2026-09-30 22:15–22:20 UTC; subtract them when reading that night.

## 4. Next steps

1. KAN-7, after 2026-10-02 03:15, with Mathi on the server:

   ```sh
   sudo cat /home/clp/db-backup-cron.log
   sudo find /home/kanvi/backups/databases/kanvi -type f -name '*.sql.gz' -newermt '2026-10-02'
   ```

   A fresh dump plus a clean log closes the ticket; an error in the log names
   the cause. A CloudPanel update may overwrite `/etc/cron.d/clp` and remove
   the logging.

2. KAN-16, Gmail desktop rest round with Mathi (two minutes): Settings →
   General → Images → "Ask before displaying external images", save, reopen
   the 30/9 mail "Din arrangøradgang til Kanvi", screenshot; switch theme to
   dark, screenshot; restore both settings. Note: the images setting is
   account-wide and also affects his Gmail apps until restored. Then the four
   device rounds per the plan in `docs/design/EMAIL-DESIGN.md`.

3. KAN-11, when Mathi delivers the twelve PNGs (filenames per the prompts):

   ```sh
   cwebp -q 85 <name>.png -o public/images/articles/<name>.webp
   cwebp -q 85 -resize 720 0 <name>.png -o public/images/articles/<name>-720.webp
   cwebp -q 85 -resize 390 0 <name>.png -o public/images/articles/<name>-390.webp
   ```

   Insert per brief, update the test map, run the check battery.

4. After any code change:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 5. Waiting on Mathi

- The twelve illustration PNGs from the Claude app (KAN-11).
- The two-minute Gmail settings round (KAN-16, images off + dark mode), then
  devices and inboxes for the four remaining clients.
- The server shell for the KAN-7 log check after 03:15.
- The launch day within week 41.

## 6. Decisions made, and why

No Mathi decisions this session (he was asleep). Working choices made
autonomously, with reasons, so they are not redone or reversed blindly:

- **The Gmail web test ran against the existing 2026-09-30 recovery mail
  instead of sending a fresh one.** Sending a fresh one would require a new
  fictional poll in production, which pollutes Umami with a false
  `poll-created` event and needs the delete sequence afterwards. Valid
  because git proves no mail-rendering file changed after the send.
- **No images were generated with other tools.** The 2026-10-01 decision
  stands: illustrations come from the Claude app, run by Mathi, for style
  consistency.
- **Account-setting changes were not made autonomously.** Policy and the
  permission layer agree; they are cheap to do together.

## 7. Dead ends and corrections

- **Saving a Gmail settings change is blocked in autonomous runs** by the
  permission classifier (account-setting change). Do not retry without Mathi
  present; the attempt was cancelled cleanly and settings are untouched.
- The Gmail MCP `viewUrl` for a thread navigates Chrome straight to the
  message — no inbox searching needed. Clicking Gmail toolbar icons by
  coordinates is unreliable (viewport scale shifted mid-session and a click
  opened the account switcher); use `find` + ref-clicks.
- Everything in the previous snapshot's dead-ends list still holds
  (`git log docs/SNAPSHOT.md` for `5601f29`): `clpctl` only exists for the
  `clp` user, `sudo find /home/*` glob trap, `sips -c` crops centered,
  injected `pointer-events:none` blocks later clicks, hover scrolls
  off-screen elements, Livewire needs `wait_for` on "har svaret", Search
  Console's "Kunne ikke hentes" was transient.

## 8. Traps in this repository

- **A push is a deployment.** CI runs on every branch; only `main` deploys.
- The julefrokost article plans **four** images total (two screenshots
  published, two illustrations pending) — the test map bump must account for
  it, see section 3.
- Screenshot method and rules live in `docs/indhold/PUBLICERING.md` (capture)
  and `docs/design/CLASS-ARTICLE-IMAGES.md` (prompts): product UI must be a
  real screenshot through the real creation flow; never fabricate UI.
- The image test asserts exact per-article image counts and real srcset
  variant widths; adding an image without `-720`/`-390` variants fails CI.
- Funnel events fire only in `app()->environment('production')`; tests that
  force production must `Queue::fake()`.
- Retention runs on `last_activity_at`; deleting a poll outside the purge
  requires the `PurgeExpiredPolls` sequence; `RecoveryMail::enabled()` allows
  only real transports; `APP_URL` drives canonical URLs and share images;
  secrets live only in `/home/kanvi/htdocs/kanvi.dk/shared/.env` on the
  server.

## 9. How to check this snapshot still holds

```sh
cd /Users/mathi/www/sites/kanvi.dk
git status --short
git log --oneline -6
curl -sS https://kanvi.dk/deploy-revision.txt
gh run list --limit 3
php artisan test --compact
```

Production should serve `6a7376f` or the snapshot commit after it.

## 10. Verification evidence

Run at the time of writing, on `6a7376f`:

- CI runs 36916085338 (and 36900303217 before it): green. Production serves
  `6a7376f` (deploy-revision.txt checked after the run).
- The test suite was **not rerun locally** this session — both commits are
  docs-only; CI ran the full suite (including MySQL) and passed.
- Gmail web rendering: inspected visually in Mathi's Chrome (light mode,
  images on), including footer and thread-list preheader. Screenshots were
  viewed live, not saved.
- Mail template freshness: `git log --since=2026-09-30` empty across
  app/Mail, resources/views/emails, resources/views/components,
  public/images/email and app/Jobs/SendAdminRecovery.php.
- Jira: KAN-16 In Progress with comment 15038; KAN-11 comment 15039.

**Not verified:** the nightly backup cron run (first possible evidence
2026-10-02 03:15), Gmail web images-off/dark, the four remaining mail
clients, per-URL indexing eligibility in Search Console, load on production
MySQL.

## 11. Skills for the next session

- `snapshot` to resume from this file.
- `tdd` for any code change; `writing-for-agents` when editing this file,
  `AGENTS.md` or `CLAUDE.md`.
- `claude-in-chrome` for Gmail web rounds in Mathi's browser; Chrome DevTools
  MCP (emulation) for any further article screenshots.
- Atlassian connector for the KAN board; the Knowledge Base is on the local
  Drive path.
