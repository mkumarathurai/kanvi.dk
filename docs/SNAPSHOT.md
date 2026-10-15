# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `c1b5c16` · Recorded: 2026-10-15 11:31 CDT.

This file describes `c1b5c16`; its own commit follows, so HEAD is one ahead by
design.

Technical handover only. Project knowledge is in the Knowledge Base overview at
`02-Projekter/Mathi ApS/Kanvi`. The KAN board:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi is live and the KAN board has no open tickets. This session made no code
change in this repo. It checked Google indexing in Search Console: 20 pages are
indexed, eight sitemap pages are "discovered, not yet indexed", and the one
redirect is `www.kanvi.dk/`. With Mathi's approval, indexing was requested for
all eight. The rest of the session closed out Umami follow-ups. One of them, the
activityhub.dk script removal, is committed in that repo but not pushed.

## 2. What was done

- Search Console check (data from 2026-10-11). The eight pages are
  `/artikler/hvor-mange-datoer-skal-man-foreslaa`,
  `/artikler/ingen-dato-passer-alle`, `/datoafstemning`,
  `/hjaelp/rediger-afstemning`, `/hjaelp/vaelg-dato`, `/til/bestyrelser`,
  `/til/foreninger` and `/til/venner`. Each returns 200, has no `noindex` and
  has a self-referencing canonical. "Request indexing" was confirmed for each.
- Outside this repo: activityhub.dk commit `9bb43fe` removes the Umami script
  from `welcome`, `modules` and `contact` (Jira TIME-530). Its full test suite
  passed in a clean worktree: 3381 passed, 17 skipped.
- KB updated: a dated status entry and a new next step in the Kanvi overview,
  and the Umami note (`04-Faste omraader/Server og hosting/Umami statistik.md`).

## 3. Unfinished

- This repo: working tree holds only this file, committed with this snapshot.
  Nothing unpushed. No stashes. No open KAN tickets.
- **activityhub.dk has an unpushed commit, `9bb43fe`, on `main`.** Mathi chose
  not to push it yet, because a push to that repo's `main` deploys to
  production. Until it ships, the live activityhub.dk pages still report to
  Umami under Timeslot's website ID. TIME-530 stays open until it is deployed.
  An older commit, `d7176df` ("docs: snapshot 2026-10-05"), is also unpushed
  there and would ship with it. That repo also has 90 uncommitted files from
  other, unrelated work. None of them was touched.
- Umami: off-server backup and deleting the old volume on blackcoffee are both
  postponed by Mathi.

## 4. Next steps

1. Around 2026-10-29, open Search Console → Indexing → Pages and check that
   "Registreret – endnu ikke indekseret" has dropped from 8. This happens in
   Mathi's browser.
2. Ship activityhub.dk `9bb43fe` from a session in that repo, when Mathi says
   so. Then close TIME-530.

   ```sh
   cd /Users/mathi/www/sites/activityhub.dk && git log --oneline @{u}..HEAD
   ```
3. For new Kanvi work, create a KAN ticket first, then run the check battery:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 5. Waiting on Mathi

- The push of activityhub.dk `9bb43fe`.
- Umami decisions: off-server backup, and when the blackcoffee volume may go.
- Whether the activityhub.dk handover to Indexed goes into the Timeslot KB
  overview (`02-Projekter/Indexed/Timeslot`). It is not written there yet.

## 6. Decisions made, and why

- **Request indexing for the eight pages instead of waiting** (Mathi). Nothing
  on the site blocks them, so a recrawl request is the only lever.
- **Remove Umami from activityhub.dk** (Mathi). The project is handed over to
  Indexed, who will add their own analytics.
- **Do not push activityhub.dk yet** (Mathi).
- **Umami backup and blackcoffee cleanup postponed** (Mathi).
- Earlier decisions: `git show c1b5c16:docs/SNAPSHOT.md` section 6.

## 7. Dead ends and corrections

- Search Console deep links of the form `/search-console/inspect?…&id=<url>`
  return a 404. Use the search bar at the top instead.
- In Search Console the inspect search bar sometimes ignores the first click.
  Click it, press cmd+a, then type.
- Running activityhub.dk tests in a worktree with a **symlinked** `vendor`
  fails: the autoloader resolves to the original repo. Run `composer install`
  in the worktree instead.
- Earlier dead ends: `git show c1b5c16:docs/SNAPSHOT.md` section 7.

## 8. Traps in this repository

- **A push is a deployment.** Only `main` deploys. Treat every push as
  user-facing.
- Every commit needs a `Refs: KAN-n` trailer. With the board empty, create a
  ticket before new work. Use the Atlassian MCP connector (cloudId
  `5f410f19-f91f-4037-934a-c04305b2e3ff`), not Rovo.
- The footer has a `lang="en"` block ("Made with ❤️"). Danish text goes outside
  it.
- `/deling/{article}` accepts only `ArticleController::shareKeys()`.
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
cd /Users/mathi/www/sites/activityhub.dk && git log --oneline @{u}..HEAD
```

## 10. Verification evidence

Run 2026-10-15 on `c1b5c16`:

- CI run 38442732783 (`c1b5c16`) green. Production `deploy-revision.txt` shows
  `c1b5c16…-38442732783-1`.
- Jira JQL `project = KAN AND statusCategory != Done` returns no issues.
- Remote `main` equals local `main`.
- The eight pages: `curl` shows 200, no `noindex`, self canonical.
- `https://stats.mathi.dev/script.js` returns 200.
- Kanvi tests were not re-run, because no Kanvi code changed. Last run:
  2026-10-13, 156 passed, 1 skipped.

**Not verified:** whether Google actually indexes the eight pages; Outlook
rendering (by Mathi's earlier choice).

## 11. Skills for the next session

- `snapshot` to resume from this file.
- `tdd` for any code change. Use the Atlassian MCP connector for tickets.
  Use Claude in Chrome for Search Console checks.
