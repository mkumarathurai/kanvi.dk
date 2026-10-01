# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `4aeba11` · Recorded: 2026-10-01 ~19:45 CEST.

The snapshot's own commit follows this one, so HEAD is one behind by design.

Technical handover only. Project knowledge is in the Knowledge Base overview at
`02-Projekter/Mathi ApS/Kanvi`. Outstanding work is on the KAN board:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi is live at https://kanvi.dk and launches in week 41 once the KAN board is
done. This session closed KAN-13 (Umami ingestion proven in the dashboard,
Search Console domain property verified via DNS TXT, sitemap accepted with 23
pages), verified the KAN-7 backup restore with CloudPanel's own dump and tooling,
and produced all nineteen product screenshots for KAN-11 across the nine
illustrated articles. Three tickets remain: KAN-7 (nightly backup cron still
unproven), KAN-11 (eleven editorial illustrations, prompts ready, Mathi
generates), KAN-16 (mail clients, not started, plan agreed). A real user
created the first organic poll in production on 2026-10-01.

## 2. What was done

- `63115e6` KAN-7 · Restore procedure documented after a verified restore of
  CloudPanel's own dump into a scratch DB (counts matched production exactly).
  On the server, outside the repo: the kanvi database was re-mapped from the
  wrong CloudPanel site (klogspot.dk) to kanvi.dk in `/home/clp/htdocs/app/data/db.sq3`
  (backup kept as `db.sq3.bak-20261001`), the stray dump in `/home/klogspot`
  was deleted, and the 03:15 cron line in `/etc/cron.d/clp` now logs to
  `/home/clp/db-backup-cron.log` (original kept as `clp.bak-20261001`).
- KAN-13 closed · My Chrome visit proven in the Umami dashboard; zero events
  from private paths in the all-time path list; www 301s to the apex; Search
  Console domain property `kanvi.dk` verified via a manual TXT record in
  Cloudflare (record must stay); `https://kanvi.dk/sitemap.xml` submitted:
  status Succes, 23 pages. Evidence in the ticket's comment.
- `4a4feee` KAN-11 · The three outstanding screenshots: the 390 px mobile
  answering view for the date poll article (DevTools device emulation — the
  missing capture method, now in `docs/indhold/PUBLICERING.md`), and the class
  article's hero and result.
- `b247e6b` KAN-11 · Eleven generation prompts for the remaining editorial
  illustrations written into `docs/design/CLASS-ARTICLE-IMAGES.md` (file
  generalized from class-only).
- `4aeba11` KAN-11 · Sixteen product screenshots for the seven remaining
  articles: seven mobile heroes, seven desktop result views, two mobile
  answering views. Image test now asserts all nine illustrated articles.
- CI green and deployed for every commit; production serves `4aeba11` and the
  new images return 200.

## 3. Unfinished

Working tree clean except this snapshot, nothing unpushed, no stashes.

- **KAN-7**: the nightly 03:15 `clpctl db:backup` cron starts (syslog shows it)
  but produced no dumps for any site at least 2026-09-29→10-01, while the same
  command run manually as `clp` succeeds — even under a cron-like minimal
  environment. Cause unknown; the job now logs. Check after 2026-10-02 03:15
  before closing the ticket.
- **KAN-11**: the eleven editorial illustrations await Mathi's generation in
  the Claude app from the prompts in `docs/design/CLASS-ARTICLE-IMAGES.md`.
  When the PNGs arrive: WebP variants (cwebp, 1440/720/390), insert per the
  briefs in `docs/indhold/*.md`, bump the counts in
  `tests/Feature/ArticlesTest.php::test_every_article_image_has_real_assets_dimensions_and_lazy_loading`.
- **KAN-16**: not started. Plan agreed: recovery mails to Mathi's Gmail, read
  in all five clients (Gmail web, Gmail app, Apple Mail, Outlook, iPhone Mail),
  one client per round with light/dark and images on/off screenshots, results
  into the table in `docs/design/EMAIL-DESIGN.md`.
- Local dev DB holds nine fictional polls created for the screenshots (plus
  older Sommerfest test polls). Deliberately kept for future captures; they are
  local only. Poll public_ids are in this session's Jira comments if needed.
- Umami still holds three false `poll-created` events from the night of
  2026-09-30 22:15–22:20 UTC; subtract them when reading that night.

## 4. Next steps

1. KAN-7, after 2026-10-02 03:15, with Mathi on the server:

   ```sh
   sudo cat /home/clp/db-backup-cron.log
   sudo find /home/kanvi/backups/databases/kanvi -type f -name '*.sql.gz' -newermt '2026-10-02'
   ```

   A fresh dump plus an empty/clean log closes the ticket; an error in the log
   names the cause. A CloudPanel update may overwrite `/etc/cron.d/clp` and
   remove the logging.

2. KAN-11, when Mathi says the PNGs are ready (folder of his choosing,
   filenames per the prompts):

   ```sh
   cwebp -q 85 <name>.png -o public/images/articles/<name>.webp
   cwebp -q 85 -resize 720 0 <name>.png -o public/images/articles/<name>-720.webp
   cwebp -q 85 -resize 390 0 <name>.png -o public/images/articles/<name>-390.webp
   ```

   Then insert per brief, update the test counts, and run the check battery.

3. KAN-16 when Mathi says "start mail-testen" with devices at hand.

4. After any code change:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 5. Waiting on Mathi

- The eleven illustration PNGs from the Claude app (KAN-11).
- Devices and inboxes for the five mail clients (KAN-16); his Gmail is the
  agreed recipient.
- The server shell for the KAN-7 log check after 03:15.
- The launch day within week 41.

## 6. Decisions made, and why

All dated in the KB overview; the session's are:

- **Search Console verified via manual DNS TXT, not the automated Cloudflare
  flow.** The automated flow grants Google access to the Cloudflare DNS
  account; the manual record does not, and is trivially removable. The TXT
  record must remain for the verification to hold.
- **Illustrations are generated in the Claude app** (Mathi runs the prompts),
  not via another generator, because the two existing illustrations came from
  it and style consistency across articles matters.
- **Mail-client testing uses Mathi's one Gmail address** read via IMAP in all
  five clients, so no extra accounts are needed.
- **Screenshot dates with named weekdays that fell in the past were moved to
  matching weekdays in 2027** (polterabend May, foreninger April, find-en-dato
  June) — the product renders real weekdays, and the briefs' weekday/date pairs
  only exist in those months.
- **The bestyrelser brief's "kl. 19.00" is omitted from screenshots.** The
  product has no time options, and screenshots must not show features that do
  not exist.

## 7. Dead ends and corrections

- **`clpctl db:backup` does not exist when run as root** — Symfony reports
  "Command not defined". It is registered only for the `clp` user:
  `sudo -u clp clpctl db:backup ...`.
- **`sudo find /home/*/backups` fails**: the glob expands in the caller's
  shell, which cannot read `/home`. Wrap it: `sudo bash -c "find /home/*/backups ..."`.
- **`sips -c` crops centered even with `--cropOffset`** (ignored in testing).
  Use `cwebp -crop x y w h` for top-anchored crops.
- **Injected `pointer-events:none` CSS blocks subsequent tool clicks** on the
  page. Inject it only after the last click, or remove it before clicking.
- **Hovering an off-screen element scrolls the page** (DevTools hover scrolls
  into view) — don't use hover to park the pointer; kill hover states with CSS.
- **Livewire results view loads after a button click**; `wait_for` on
  "har svaret" is the reliable gate before measuring or shooting.
- **The initial Search Console sitemap status "Kunne ikke hentes" is
  transient** — it turned to Succes with 23 pages on reload a minute later.

## 8. Traps in this repository

- **A push is a deployment.** CI runs on every branch; only `main` deploys.
- Screenshot method and rules live in `docs/indhold/PUBLICering.md` (capture)
  — note the file is `PUBLICERING.md` — and `docs/design/CLASS-ARTICLE-IMAGES.md`
  (prompts): product UI must be a real screenshot through the real creation
  flow; fictional participants are seeded locally; never fabricate UI.
- Funnel events fire only in `app()->environment('production')`; tests that
  force production must `Queue::fake()` (`tests/TestCase.php` has
  `Http::preventStrayRequests()` as the backstop).
- The image test asserts the exact per-article image counts; adding or
  removing an article image fails the build until the map in
  `tests/Feature/ArticlesTest.php` is updated.
- Everything in the previous snapshot's trap list still holds: retention runs
  on `last_activity_at`; deleting a poll outside the purge requires the
  `PurgeExpiredPolls` sequence; `RecoveryMail::enabled()` allows only real
  transports; `APP_URL` drives canonical URLs and share images; secrets live
  only in `/home/kanvi/htdocs/kanvi.dk/shared/.env` on the server.

## 9. How to check this snapshot still holds

```sh
cd /Users/mathi/www/sites/kanvi.dk
git status --short
git log --oneline -6
curl -sS https://kanvi.dk/deploy-revision.txt
gh run list --limit 3
php artisan test --compact
```

Production should serve `4aeba11` or the snapshot commit after it.

## 10. Verification evidence

Run at the time of writing, on `4aeba11`:

- `php artisan test --compact`: 151 passed, 1 skipped, 1,669 assertions.
- `npm test`: passed. `vendor/bin/pint --test`: passed. `git diff --check`: clean.
- CI runs 36884038716, 36889725921 and 36899706863: green. Production serves
  `4aeba11`; `find-dato-med-venner.webp` returns 200 with `image/webp`.
- Umami dashboard showed the session's visit live and only public paths
  all-time. Search Console shows Succes / 23 pages for the sitemap.
- Backup restore: scratch DB matched production (17 tables, 8 migrations,
  identical row counts) before deletion.
- All sixteen new screenshots and both articles were inspected visually at
  390 px and desktop; Danish in-image labels checked.

**Not verified:** the nightly backup cron run (logs from 2026-10-02 03:15),
per-URL indexing eligibility in Search Console (report populates over days,
covered by the post-launch task), mail clients other than Gmail inbox
placement, load on production MySQL.

## 11. Skills for the next session

- `snapshot` to resume from this file.
- `tdd` for any code change; `writing-for-agents` when editing this file,
  `AGENTS.md` or `CLAUDE.md`.
- Chrome DevTools MCP (emulation) for article screenshots; `claude-in-chrome`
  for walkthroughs in Mathi's own browser (Umami, Search Console, Cloudflare).
- Atlassian connector for the KAN board; the Knowledge Base is on the local
  Drive path.
