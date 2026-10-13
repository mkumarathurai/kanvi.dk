# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `54a03cc` · Recorded: 2026-10-13 12:55 CDT.

This file describes `54a03cc`; its own commit follows, so HEAD is one ahead by
design.

Technical handover only. Project knowledge is in the Knowledge Base overview at
`02-Projekter/Mathi ApS/Kanvi`. The KAN board:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi is live and the KAN board has no open tickets. This session made one code
change: the footer now ends with the company details (Mathi ApS, CVR, address,
mail@kanvi.dk, phone) in place of the old copyright line (KAN-28). KAN-27, the
async analytics-script fix from 2026-10-06, was closed after confirming the
`async` tag is live. Most of the session went to moving the self-hosted Umami
instance at `stats.mathi.dev` from the home server blackcoffee, which Mathi shut
down for security reasons, to silanthi. Umami has counted visits again since
2026-10-11. Visits from 2026-10-05 to 2026-10-11 were not counted. Kanvi's
`data-website-id` and server-side funnel config did not change.

## 2. What was done

- `54a03cc` Footer shows the company details at the very bottom
  (`resources/views/components/layout.blade.php`). The year stays dynamic. The
  mail and phone are links. KAN-28 created and closed.
- KAN-27 moved to Done. Fix `0acbdca` verified live: kanvi.dk serves
  `<script async src="https://stats.mathi.dev/script.js" …>`.
- Umami moved to silanthi. This was server work with no repo change. Knowledge
  for it is in the KB note `04-Faste omraader/Server og hosting/Umami statistik.md`.
  - Data moved with the database, so website IDs, including Kanvi's
    `fa2c9fe6-…`, are unchanged.
  - Runs as Umami v3.4.0. The database was upgraded to migration
    `26_add_api_key`.
- KB updated on 2026-10-11: the new Umami note, and two dated entries at the end
  of `Status lige nu` in the Kanvi overview.

## 3. Unfinished

- Working tree: only this file, committed with this snapshot. Nothing is
  unpushed. There are no stashes.
- No open KAN tickets.
- Outside this repo, open items are recorded in the KB Umami note:
  - an off-server backup for Umami
  - activityhub.dk's repo still loads Umami with Timeslot's website ID
  - deleting the old Umami volume on blackcoffee
- Search Console indexing check from the previous snapshot: not verified this
  session.

## 4. Next steps

1. Check indexing in Search Console (Pages report) for the sitemap URLs. This
   happens in Mathi's browser and needs no command.
2. For any new work, create a KAN ticket first for the commit trailer. Use the
   Atlassian MCP connector, not Rovo.
3. Run the check battery before pushing code:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 5. Waiting on Mathi

- The Search Console indexing check.
- Decisions on the three Umami open questions in the KB note. They live outside
  this repo.

## 6. Decisions made, and why

- **Footer uses mail@kanvi.dk, not hej@invity.dk** (Mathi). The request
  contained the Invity address. Every other Kanvi page uses mail@kanvi.dk, so
  Mathi chose consistency.
- **The company line replaces the old "© Mathi Kumarathurai" line** (Mathi), so
  the footer has a single copyright line. The new line sits outside the
  `lang="en"` block because it is Danish.
- **Umami data was moved, not reinstalled empty.** Website IDs stay the same, so
  none of the ten sites needed a code change or a deploy.
- Earlier decisions: see `git show 51519ef:docs/SNAPSHOT.md` section 6.

## 7. Dead ends and corrections

- The Atlassian **Rovo** connector again returned 403 "The app is not installed
  on this instance", both for search and for create. The **Atlassian MCP**
  connector with cloudId `5f410f19-f91f-4037-934a-c04305b2e3ff` works.
- Umami on blackcoffee ran in Docker. Docker is stopped there on purpose.
  Searching the native Postgres or the site directory finds nothing. The data
  is in the Docker volume `umami_umami-db-data`. Do not start Docker on
  blackcoffee to reach it.
- Earlier dead ends: `git show 51519ef:docs/SNAPSHOT.md` section 7.

## 8. Traps in this repository

- **A push is a deployment.** CI runs on every branch; only `main` deploys. The
  site is public: treat every push as user-facing.
- Every commit needs a `Refs: KAN-n` trailer. With the board empty, create a
  ticket before new work.
- The footer has a `lang="en"` block ("Made with ❤️"). Danish text goes outside
  it.
- `/deling/{article}` accepts only `ArticleController::shareKeys()`. Static
  `.png` paths are served by the host and never reach PHP.
- Inline SVG logos contain `<title>Kanvi?</title>`, so a grep for `<title>`
  finds them as well as the page title.
- Retention, recovery-mail, funnel and secrets rules: see `AGENTS.md`.

## 9. How to check this snapshot still holds

```sh
cd /Users/mathi/www/sites/kanvi.dk
git status --short
git log --oneline -4
git log --oneline @{u}..HEAD
curl -sS https://kanvi.dk/deploy-revision.txt
gh run list --limit 2
curl -s -o /dev/null -w '%{http_code}\n' https://stats.mathi.dev/script.js
```

## 10. Verification evidence

Run 2026-10-13 12:55 CDT on `54a03cc`:

- `php artisan test` 156 passed, 1 skipped. `npm test` 38 passed.
  `vendor/bin/pint --test` passed.
- CI run 37848208271 (`54a03cc`) green. Production `deploy-revision.txt` shows
  `54a03cc…-37848208271-1`.
- The live footer shows "Mathi ApS, CVR 32890032, Nygårdsvænget 25, 8370
  Hadsten · …".
- `https://stats.mathi.dev/script.js` returns 200. Mathi confirmed on
  2026-10-11 that a kanvi.dk visit appeared in Umami Realtime.
- Jira JQL `project = KAN AND statusCategory != Done` returns no issues.
- Remote `main` equals local `main`, so nothing is unpushed.

**Not verified:** Google indexing; Outlook rendering (by Mathi's earlier choice).

## 11. Skills for the next session

- `snapshot` to resume from this file.
- `tdd` for any code change. Use the Atlassian MCP connector for KAN tickets.
  Use Chrome DevTools MCP for production walkthroughs.
