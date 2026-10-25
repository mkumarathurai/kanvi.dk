# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `50cad97` · Recorded: 2026-10-25 20:51 CET.

This file describes `50cad97`; its own commit follows, so HEAD is one ahead by
design.

Technical handover only. Project knowledge is in the Knowledge Base overview at
`02-Projekter/Mathi ApS/Kanvi`. The KAN board:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi is live and the KAN board has no open tickets. This session made no code
change. It resumed from the 2026-10-15 snapshot and found that snapshot's own
commit, `50cad97`, had never been pushed. With Mathi's approval it was pushed
and deployed; CI passed and production now serves `50cad97`. Nothing else moved
since 2026-10-15: the Search Console recheck is still due around 2026-10-29, and
the activityhub.dk Umami removal is still unpushed in that repo.

## 2. What was done

- Pushed `50cad97` (snapshot of 2026-10-15) to `main`. CI run 40227797014
  passed in 1m28s. Production `deploy-revision.txt` shows
  `50cad97…-40227797014-1`.
- No code, KB or Jira changes.

## 3. Unfinished

- This repo: the working tree holds only this file, committed with this
  snapshot. **That snapshot commit is not pushed unless the session reply says
  so** — check with `git log --oneline @{u}..HEAD`. It changes only
  `docs/SNAPSHOT.md`, but a push still deploys.
- **activityhub.dk has two unpushed commits on `main`: `9bb43fe`** (drops the
  Umami script, Jira TIME-530) **and `d7176df`** (old snapshot). Not pushed,
  because a push there deploys to production and Mathi has not said go for that
  repo. Live activityhub.dk pages still report to Umami under Timeslot's
  website ID. TIME-530 is still in Backlog. That repo also has ~90 uncommitted
  files from unrelated work; none was touched.
- Umami: off-server backup and deleting the old volume on blackcoffee are both
  postponed by Mathi.

## 4. Next steps

1. Around 2026-10-29, open Search Console → Indexing → Pages and check that
   "Registreret – endnu ikke indekseret" has dropped from 8. The eight URLs are
   listed in `git show 50cad97:docs/SNAPSHOT.md` section 2. This happens in
   Mathi's browser (Claude in Chrome).
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

- Go-ahead to push activityhub.dk `9bb43fe` and `d7176df`.
- Umami decisions: off-server backup, and when the blackcoffee volume may go.
- Whether the activityhub.dk handover to Indexed goes into the Timeslot KB
  overview (`02-Projekter/Indexed/Timeslot`). Not written there yet.

## 6. Decisions made, and why

- **Push the stranded Kanvi snapshot commit** (Mathi, 2026-10-25: "Commit og
  push alle ændringer"). Read as Kanvi only; activityhub.dk was left alone
  because a push there is a separate production deployment.
- Earlier decisions: `git show 50cad97:docs/SNAPSHOT.md` section 6.

## 7. Dead ends and corrections

- A `/snapshot commit` that ends with a commit but no push leaves the snapshot
  stranded locally — that is what happened to `50cad97` for ten days. After
  committing, either push (with Mathi's go) or say clearly that it is unpushed.
- Earlier dead ends (Search Console deep links 404, inspect bar ignoring first
  click, symlinked `vendor` in activityhub.dk worktrees):
  `git show 50cad97:docs/SNAPSHOT.md` section 7.

## 8. Traps in this repository

- **A push is a deployment.** Only `main` deploys. Treat every push as
  user-facing.
- Every commit needs a `Refs: KAN-n` trailer. Snapshot commits use `KAN-28`.
  With the board empty, create a ticket before new work. Use the Atlassian MCP
  connector (cloudId `5f410f19-f91f-4037-934a-c04305b2e3ff`), not Rovo.
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

Run 2026-10-25 on `50cad97`:

- CI run 40227797014 (`50cad97`) green. Production `deploy-revision.txt` shows
  `50cad97…-40227797014-1`.
- Remote `main` equals local `main` at `50cad97`.
- Jira JQL `project = KAN AND statusCategory != Done` returns no issues.
  TIME-530 is in Backlog.
- activityhub.dk `git log @{u}..HEAD` lists `9bb43fe` and `d7176df`.
- Kanvi tests were not re-run, because no Kanvi code changed. Last run:
  2026-10-13, 156 passed, 1 skipped.

**Not verified:** whether Google has indexed the eight pages; Outlook rendering
(by Mathi's earlier choice).

## 11. Skills for the next session

- `snapshot` to resume from this file.
- `tdd` for any code change. Use the Atlassian MCP connector for tickets.
  Use Claude in Chrome for Search Console checks.
