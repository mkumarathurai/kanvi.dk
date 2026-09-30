# Kanvi session snapshot

Recorded: 2026-09-30 15:15 CEST, Europe/Copenhagen. Technical handover. The
Knowledge Base project overview holds the nontechnical status and decisions:
https://drive.google.com/file/d/1Fe-URgOMgkIXbZ5v6xawiORAYvPkTUKQ/view

Outstanding work is on the Jira board, not in this file:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Repository and production state

- Working directory: `/Users/mathi/www/sites/kanvi.dk`
- Branch `main`, clean working tree at the time of writing.
- Remote: `git@github.com:mkumarathurai/kanvi.dk.git`
- Three workflows are checked in: `ci.yml`, `deploy.yml`, `check-deploy-access.yml`.
- `DEPLOY_ENABLED=true`, `DEPLOY_PATH=/home/kanvi/htdocs/kanvi.dk`. **A push to
  `main` publishes to https://kanvi.dk once CI passes.** There is no separate
  release step.
- Production serves the revision named in `/deploy-revision.txt`. Check it
  against `git rev-parse HEAD` before assuming what is live.

## 2. Where we are

Kanvi is live and the core journey is verified in production: create, share,
respond, result, final date. On 2026-09-30 the site also gained a privacy page,
an FAQ, a help centre, twelve-month retention, sharing images and a MySQL CI job.

The sitemap lists 23 public URLs: the homepage, three listings, ten guides,
`/faq`, `/privatliv` and seven help pages. The SEO plan targets 25 to 30.

## 3. What changed on 2026-09-30, after the first deployment

Each commit carries its KAN key; the board holds the reasoning.

- `KAN-17` Jira convention, plus the decided product rules, in `CLAUDE.md`.
- `KAN-19` Homepage trial poll shows participants, totals and unanswered.
- `KAN-5` Retention: polls are deleted twelve months after last activity.
- `KAN-8` Result access after closure confirmed as decided, not provisional.
- `KAN-4` Privacy page at `/privatliv`, linked from every page including
  private poll pages.
- `KAN-9` FAQ at `/faq`; the homepage anchor stopgap is retired everywhere.
- `KAN-10` Help centre at `/hjaelp` with seven task pages.
- `KAN-15` CI runs the PHP suite on SQLite and on MySQL 8.4.
- `KAN-12` Every page has its own Open Graph image at `/deling/<key>.png`.
- `KAN-6` Resend transport installed; environment and DNS remain.
- `KAN-11` Two real product screenshots in the date poll article.

## 4. Verification evidence

Local, at the time of writing:

- `php artisan test --compact`: 141 passed, 1 skipped, 1,416 assertions.
  The skip is the driver guard, which only asserts when `EXPECTED_DB_DRIVER` is set.
- `npm test`: 37 passed. `vendor/bin/pint --test`: passed. `npm run build`: passed.
- `git diff --check`: passed.

CI, on every push to `main` today: green, including the MySQL job, which runs the
full PHP suite against MySQL 8.4 with the seven-process concurrency test.

Production, checked after the deploys: `/privatliv`, `/faq` and `/hjaelp` return
200, and `/sitemap.xml` lists 23 URLs. The privacy page and the FAQ were walked
through in Chrome with an empty console.

Not verified: mail delivery, backup restore, analytics ingestion, Search Console,
mail-client rendering, and behaviour under load on production MySQL.

## 5. The traps that will cost you time

- **A push is a deployment.** Nothing else gates it once CI is green.
- **Retention deletes data and nothing runs it yet.** `kanvi:purge-polls` is
  scheduled daily, but the server runs no scheduler, so the command never fires.
  That is KAN-20, and the privacy page promises the deletion.
- **Anything generated at request time is invisible locally and broken in
  production twice over.** The web server serves paths ending in an image, script
  or style extension itself, so they never reach PHP: that is why no route may end
  in one, and `RoutePathsTest` fails the build if one does. And the release
  archive names its root files explicitly, so `design-tokens.json` was missing
  until the deploy script started refusing a release without it. The poll link
  preview had both defects from the day it was written.
- `polls.last_activity_at` drives retention. Every write path that locks the poll
  must call `markActive()`. Saving a response never touched the poll row's
  `updated_at`, which is why retention does not use it.
- `admin_recovery_links` does not cascade from `poll_admin_access`, so the purge
  clears it explicitly before deleting a poll.
- Articles, informational pages and help pages share one renderer, one route loop
  and one sitemap, separated by the `kind` key in `config/articles.php`. The guide
  tests demand three creation CTAs and nine headings; pages are not held to that.
- `@js()` renders a JavaScript string literal, not JSON. Assert on `viewData()`
  rather than parsing it out of the HTML.
- `iterator_to_array($xml->url)` collapses same-named elements into one. Pass
  `false` for the second argument, or the assertion is hollow.
- The site footer carries a personal name, so `assertDontSee` on a participant
  name passes for the wrong reason.
- Mail is off in production. `RecoveryMail::enabled()` refuses the log transport
  by design, so nothing sends until `KANVI_RECOVERY_MAILER` names a real one.
- Vite build output is ignored; rebuild assets when CSS or JS changes.
- `APP_URL` drives canonical URLs, the sitemap, sharing images and email assets.
- Secrets live in the server environment file. Never print or copy `.env`, raw
  admin or recovery links, credentials or real participant data.

## 6. Decisions to preserve

`docs/adr/` holds the two written this session. `CLAUDE.md` summarises the rules.
Authoritative behavioural detail is in `docs/kanvi-product-solution-spec-v1.1.md`,
`docs/autosave.md` and `docs/administration.md`. Section 38 of the specification
no longer has open clarifications.

- Polls are deleted twelve months after last activity, with no archive step.
  The participant cookie expires on the same schedule. (ADR 0001)
- After closure the final date is public to anyone with the link; totals, names
  and individual answers still require participant or organizer access. (ADR 0002)
- Mail goes through Resend, not the server.
- Name plus one server-confirmed answer counts as responded. Unanswered is a
  separate state, never "cannot". First saved answer unlocks totals and names.
- Optimistic local UI and server-confirmed state are distinct. Saved means an ACK
  of the latest revision; retries retain local input.
- Organizer access uses browser access, the admin link and optional email
  recovery. Losing all three cannot be silently repaired.
- Reopening clears the final date and finalization timestamp, preserving responses.
- The written design system is authoritative. Keep the supplied v2 logo.
- Email uses one reusable system and the green primary CTA `#138448`.
- Preserve "Made with ❤️" and the current-year copyright.
- Analytics uses the self-hosted Umami. Private pages load no tracker, and the
  privacy page and FAQ now state that in public.

Implementation limits, not product decisions: single calendar dates only, no time
slots, no date ranges, no automatic deadlines. Public copy is written to match.

## 7. What is left, and who it needs

On the KAN board. The ones that need Mathi rather than code:

- `KAN-20` the scheduler, without which retention does not happen.
- `KAN-6` the Resend key and the DNS records.
- `KAN-7` a tested backup restore.
- `KAN-13` analytics ingestion and Search Console.
- `KAN-16` the five mail clients.
- `KAN-21` CVR, hosting provider and country for the privacy page.
- `KAN-14` a decision on how to count funnel events without breaking the promise
  that private pages send nothing. The options are written up on the ticket.

Code work still open: `KAN-11` images for the remaining articles.

## 8. Next session

Read the Knowledge Base rules, the whole project overview including decisions,
this file and the KAN board. Then confirm reality before changing anything:

```sh
cd /Users/mathi/www/sites/kanvi.dk
git status --short
git log --oneline -10
curl -sS https://kanvi.dk/deploy-revision.txt
```

Run the checks after changes. The full suite is inexpensive:

```sh
php artisan test --compact
vendor/bin/pint --test
npm test
npm run build
git diff --check
```
