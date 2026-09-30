# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `79a0301` · Recorded: 2026-09-30 17:30 CEST.

The snapshot's own commit follows this one, so HEAD is one behind by design.

Technical handover only. Project knowledge is in the Knowledge Base overview:
https://drive.google.com/file/d/1Fe-URgOMgkIXbZ5v6xawiORAYvPkTUKQ/view
Outstanding work is on the KAN board, not restated here:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi is live at https://kanvi.dk. The session began as a review of what Codex had
built, turned into a Jira board, and then into a day of building. Twelve tickets are
finished and deployed; six remain, and most of those need Mathi rather than code.

The site gained a privacy page, an FAQ, a help centre with seven guides,
twelve-month retention, a sharing image per page, and a MySQL job in CI. The sitemap
went from 13 public URLs to 23.

Retention is the one that took a full chain to finish: decided, built, tested,
deployed, and then proved to actually run on the server. Production mail is
configured with Resend and the organizer's recovery option now appears on the share
screen, but no mail has ever been sent, so delivery is unproven.

Two production-only defects surfaced along the way. Both had been live since the
features were written, both were invisible locally, and both are fixed.

## 2. What was done

Twenty-two commits, `0697a3a` through `79a0301`, each carrying its KAN key.

- `0697a3a` KAN-17 · Jira convention and the decided product rules in `CLAUDE.md`.
- `d8ea103` KAN-19 · Homepage trial poll shows participants, totals, unanswered.
- `f2c0ac3` KAN-5 · Retention: delete twelve months after last activity.
- `6cba0db` KAN-8 · Result access after closure recorded as decided, not provisional.
- `980c634`, `89f35b9` KAN-4 · Privacy page, and honest wording while mail was off.
- `4513bd3` KAN-9 · `/faq`; the homepage anchor stopgap retired everywhere.
- `97bb029` KAN-10 · `/hjaelp` with seven task pages.
- `9f511fb` KAN-15 · CI runs the PHP suite on SQLite and on MySQL 8.4.
- `a4280b9`, `5ec5673`, `6947140` KAN-12 · Sharing images, and the two defects below.
- `5f11548` KAN-6 · Resend transport installed and pinned by tests.
- `6620105` KAN-11 · Two real product screenshots in the date poll article.
- `fc368bb`, `aeab5bc` KAN-21 · Controller, processors, and the transfer to the US.
- `14863b2` KAN-20 · A failed purge is logged; a test keeps it on the schedule.
- `43e64a6`, `16119c0`, `5f4c95c`, `fc4c05a`, `79a0301` KAN-18 · This file.

Server changes Mathi made, outside the repository: the production environment file
now names Resend and `KANVI_RECOVERY_MAILER`, and a CloudPanel cron job runs
`schedule:run` every minute, truncating its log so it holds only the latest run.

## 3. Unfinished

The working tree is clean, nothing is unpushed, there are no stashes. What is
unfinished sits elsewhere:

- **Mail is configured but never delivered.** The share screen offers the recovery
  option, which proves the transport is accepted, but no real mail has been sent and
  no recovery link redeemed. Sending one needs Mathi's agreement: it is a real
  message to a real address. KAN-6.
- **Two fictional polls sit in production.** `Mailtest 30. september - fiktive data`,
  created this session to check that the mail field appeared, is open with no
  responses. `Deploymenttest 30. september - fiktive data` from the previous session
  is finalized. Both are clearly marked as fictional. Neither is cleaned up.
- **The local development database holds fictional polls** created for the article
  screenshots, including `Sommerfest med naboerne` with six participants. Harmless,
  but it is not a clean database.
- **Article images.** Eight of ten articles have none, and the mobile screenshot
  could not be captured at all. KAN-11, with the reason in section 7.
- **The Resend choice needs confirming** now that its US storage is known. Section 7.

## 4. Next steps

1. Send one real recovery mail and redeem it, with Mathi's agreement. Use the
   fictional production poll that already exists: open its share screen, register an
   address, then confirm the link grants organizer access once and is refused the
   second time. Closes KAN-6, the last launch blocker with code in it.

2. Confirm or change Resend, now that its US storage is known. See section 7.

3. Delete the two fictional production polls when they have served their purpose.
   There is no delete action in the product, so this is a database operation on the
   server. It is the only way to remove them before the retention window.

4. Run the checks after any change. The full suite is inexpensive:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 5. Waiting on Mathi

- Permission to send the test recovery mail.
- Whether Resend stays, given that it stores data in the United States.
- A tested backup restore on the server. KAN-7.
- Umami ingestion and Search Console, both of which need his accounts. KAN-13.
- The five mail clients. KAN-16.
- How funnel events should be counted without breaking the published promise that
  private pages send nothing. Three options are written up on KAN-14.
- Whether Kanvi moves out of the Knowledge Base inbox. He confirmed Mathi ApS as
  data controller, which points at the company area, but not the move itself.

He has shell access to the server as the `kanvi` site user, so the backup restore
and the poll cleanup are reachable without going through CloudPanel's interface.

## 6. Decisions made, and why

The two written up as ADRs are in `docs/adr/`. `CLAUDE.md` carries the short form.

- **Polls are deleted twelve months after last activity, with no archive step.**
  Chosen over six months and over a twelve-month archive with deletion at
  twenty-four. A year survives an annually recurring event; an archive step would
  have meant explaining two states and keeping data for two years. ADR 0001.
- **After closure the final date is public; totals and names are not.** A shared link
  has to be able to tell the group which day it is, because that is the moment the
  link matters most. Who answered what is a different question. ADR 0002.
- **Mail goes through Resend rather than the server.** The recovery mail is the
  organizer's only way back in, so deliverability beats having one less account.
- **The work is deployed continuously.** Every finished ticket goes to `main` and
  publishes after green CI, with the browser check afterwards.
- **Outstanding work lives on the KAN board, not in documents.** This file points at
  it rather than restating it, so the two cannot drift apart.
- **The privacy page states the US transfer plainly** rather than softening it,
  because a page whose purpose is accuracy is the wrong place to be vague.
- **The scheduler's cron job truncates its log rather than appending.** `/dev/null`
  was rejected: it would also hide a future `php: command not found` after a PHP
  upgrade, which is the failure this log proved useful for today.

## 7. Dead ends and corrections

The expensive half. Each of these cost time today.

- **A route path ending in an image, script or style extension never reaches PHP.**
  The host serves those itself, so `/p/{poll}/preview.png` returned nginx's own 404.
  The poll link preview had been broken in production since it was written, and the
  new article sharing images broke the same way. Both paths dropped the extension.
  `RoutePathsTest` now fails the build on any such route.
- **The release archive names its root files one by one.** With the path fixed, the
  renderer reached PHP and threw a 500, because `design-tokens.json` was never sent.
  The archive now includes it and the deploy script refuses a release without it.
- **Resend stores data in the United States.** Its own DPA names Plus Five Five, Inc.
  in San Francisco and states that processing takes place in the US, under the
  standard contractual clauses and the EU-U.S. Data Privacy Framework. Choosing a
  European sending region only changes where mail is dispatched from. This was not
  known when Resend was chosen, so the choice is worth revisiting rather than
  assuming.
- **`@js()` renders a JavaScript string literal, not JSON.** Parsing it out of the
  HTML fails. Assert on `viewData()` instead.
- **`iterator_to_array($xml->url)` collapses same-named elements into one.** Pass
  `false` for the second argument, or the assertion silently checks one row. It
  passed for the wrong reason until it was caught.
- **The site footer carries a personal name**, so `assertDontSee` on a participant
  called Mathi passes for the wrong reason. Assert on the state the page was given.
- **Chrome refuses a window narrower than about 500 px**, so the mobile screenshot
  could not be captured by resizing. It needs another method.
- **CloudPanel's cron form has separate schedule fields**, so the command box takes
  the command alone; pasting a full crontab line duplicates the five asterisks and
  breaks the job. The list has no edit action either, only Delete, so changing a
  command means deleting the row and adding it again.
- **Piping test output to `tail` hides the exit code**, so a chained `&& git push`
  ran on a failing suite. CI caught it and the deploy never ran. Check the suite's
  own exit code, not the pipeline's.

## 8. Traps in this repository

- **A push is a deployment.** Nothing else gates it once CI is green.
- Retention is driven by `polls.last_activity_at`, not `updated_at`. Saving a
  response never touched the poll row. Every write path that locks the poll must call
  `markActive()`, or a poll people are using will be deleted.
- A poll with no `last_activity_at` is never purged. That is deliberate: for an
  irreversible delete, "do not guess" is the safe direction.
- `admin_recovery_links` does not cascade from `poll_admin_access`, so the purge
  clears it explicitly before deleting a poll.
- Guides, informational pages and help pages share one renderer, one route loop and
  one sitemap, separated by the `kind` key in `config/articles.php`. The guide tests
  demand three creation CTAs and nine headings; pages are not held to that.
- `RecoveryMail::enabled()` refuses the `log` transport by design, so
  `KANVI_RECOVERY_MAILER` must name a real one or mail stays off however the rest is
  configured.
- Vite build output is gitignored; rebuild assets when CSS or JS changes.
- `APP_URL` drives canonical URLs, the sitemap, sharing images and email assets.
- Secrets live only in the server environment file. Never print or copy `.env`, raw
  admin or recovery links, credentials, or real participant data.

## 9. How to check this snapshot still holds

```sh
cd /Users/mathi/www/sites/kanvi.dk
git status --short
git log --oneline -5
curl -sS https://kanvi.dk/deploy-revision.txt
gh run list --limit 3
php artisan test --compact
```

Production should serve the revision this file names, or a later one. If it serves
something older, a deployment failed and the Actions log says why.

## 10. Verification evidence

Run at the time of writing, on `79a0301`:

- `php artisan test --compact`: 143 passed, 1 skipped, 1,467 assertions. The skip is
  the driver guard, which only asserts when `EXPECTED_DB_DRIVER` is set.
- `npm test`: 38 passed. `vendor/bin/pint --test`: passed. `git diff --check`: clean.
- CI has been green on every push today, including the MySQL job, which runs the full
  PHP suite against MySQL 8.4 with the seven-process concurrency test.
- Production serves the current revision. `/privatliv`, `/faq` and `/hjaelp` return
  200, the sitemap lists 23 URLs, and the generated sharing images return
  `image/png`.
- The production share screen offers the recovery mail field, which only renders when
  a real mail transport is configured.
- The scheduler runs. Its log repeats `No scheduled commands are ready to run.`, and
  `php artisan schedule:list` on the server returns the purge with its next due time.
  That covers cron firing, `php` resolving on cron's PATH, the app booting, and the
  command being registered.

**Not verified:** mail actually being delivered, backup restore, analytics ingestion,
Search Console, mail-client rendering, and behaviour under load on production MySQL.
The cron job has not been observed surviving a deployment or a reboot; it runs from
the `current` symlink, so it should follow a release untouched, but that is
reasoning, not a test.

## 11. Skills for the next session

- The user's `snapshot` skill for writing and resuming this file.
- `writing-for-agents` when editing this file, `AGENTS.md` or `CLAUDE.md`.
- Google Drive for the Knowledge Base overview; Atlassian for the KAN board.
- Chrome browser automation for production walkthroughs. Note the window-width limit
  in section 7 before planning a mobile capture.
