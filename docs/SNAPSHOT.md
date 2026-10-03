# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `51519ef` · Recorded: 2026-10-03 13:24 CEST.

This file describes `51519ef`; its own commit follows, so HEAD is one ahead by
design.

Technical handover only. Project knowledge is in the Knowledge Base overview at
`02-Projekter/Mathi ApS/Kanvi`. The KAN board:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi launched on 2026-10-03, a Saturday in week 40, one week ahead of the
week-41 plan. Mathi chose to launch today and announced Kanvi in their network.
To make that possible, Mathi closed KAN-16 without the Outlook round and kept the
logo as it is in the Gmail app's dark theme. KAN-3, its epic, closed with it,
so the KAN board has no open tickets. No code changed this session: production
still serves `51519ef`. The sitemap was already submitted on 2026-10-01; the
Knowledge Base had recorded that in its status but never ticked the next step,
which this session fixed. The one open item is checking indexing in Search
Console after launch.

## 2. What was done

- No code commits. HEAD `51519ef` is the previous snapshot.
- Jira: KAN-16 → Done with an evidence comment (four clients passed 2026-10-02,
  Outlook not tested, logo unchanged); KAN-3 → Done.
- Knowledge Base overview: 2026-10-03 status entries (launch decision,
  pre-launch check, launch announced), two dated decisions, two open questions
  answered, next steps ticked (sitemap, mail clients, launch).
- Pre-launch production check, 2026-10-03: `/`, `/sitemap.xml`, `/robots.txt`,
  `/guides`, `/artikler`, `/hjaelp`, `/privatliv` and `/til/venner` return 200
  in under 0.2 s; sitemap lists 26 URLs; `robots.txt` disallows nothing; no
  `noindex` meta on the homepage.

## 3. Unfinished

- Working tree: only this file, committed with this snapshot. Nothing
  unpushed after the push; no stashes.
- No open KAN tickets.
- Outlook rendering of the mails is not verified, by Mathi's choice.
- The core poll flow was not exercised in production today, to avoid real
  data; it was verified in earlier sessions.

## 4. Next steps

1. In a few days: check indexing in Search Console (Pages report) for the 26
   sitemap URLs. Mathi's browser; no command. Create a KAN ticket first if it
   turns into work.
2. Continue FAQ, help centre and content per the SEO plan, when Mathi asks.
   New work needs a KAN ticket for the commit trailer.
3. Check battery before any code change is pushed:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 5. Waiting on Mathi

- The Search Console indexing check (Mathi's Google account).
- Any new content or feature requests after launch.

## 6. Decisions made, and why

- **Launch on 2026-10-03 instead of week 41** (Mathi). The 2026-09-30 decision
  stays in the KB as history.
- **KAN-16 closed without Outlook; dark-mode logo unchanged** (Mathi), so the
  launch did not wait on installing Outlook. Supersedes the 2026-10-02
  decision to park both.
- Earlier decisions still hold: see `git show 51519ef:docs/SNAPSHOT.md`
  section 6 (ChatGPT illustrations, homepage share screenshot, homepage title,
  `fb:app_id` warning ignored, Livewire PageSpeed warning left alone, no text
  in images).

## 7. Dead ends and corrections

- The previous snapshot listed "submit the sitemap" as a launch step because
  the KB next-step item bundled it with the launch. It had been done on
  2026-10-01. When reading a snapshot, cross-check each next step against the
  KB's dated status entries.
- The Atlassian **Rovo** connector returns 403 "The app is not installed on
  this instance"; use the **Atlassian MCP** connector with cloudId
  `https://mkumarathurai.atlassian.net`.
- SSH to GitHub fails when the Mac's keychain is locked ("communication with
  agent failed"); fetch and push over HTTPS with the gh credential helper (see
  the auto-memory note `ssh-push-when-mac-locked`).
- Earlier dead ends: `git show 51519ef:docs/SNAPSHOT.md` section 7.

## 8. Traps in this repository

- **A push is a deployment.** CI runs on every branch; only `main` deploys.
  The site is now public and announced: treat every push as user-facing.
- Every commit needs a `Refs: KAN-n` trailer; with the board empty, create a
  ticket before new work.
- `/deling/{article}` accepts only `ArticleController::shareKeys()`; static
  `.png` paths are served by the host and never reach PHP.
- The image test asserts exact per-article counts and real 390/720 variants.
- Inline SVG logos contain `<title>Kanvi?</title>`; a grep for `<title>` finds
  them besides the page title.
- Product UI must be a real screenshot (`docs/indhold/PUBLICERING.md`).
- Retention, recovery-mail, funnel and secrets rules: see `AGENTS.md`.

## 9. How to check this snapshot still holds

```sh
cd /Users/mathi/www/sites/kanvi.dk
git status --short
git log --oneline -4
git log --oneline @{u}..HEAD
curl -sS https://kanvi.dk/deploy-revision.txt
gh run list --limit 2
```

## 10. Verification evidence

Run 2026-10-03 13:24 CEST on `51519ef`:

- CI run 37069952895 (`51519ef`) green; production serves `51519ef`.
- Remote `main` fetched over HTTPS equals `51519ef`.
- Jira JQL `project = KAN AND statusCategory != Done` returned KAN-3 and
  KAN-16 before this session; both transitioned to Done.
- Tests were not rerun: no code changed since the last run on 2026-10-02
  (`php artisan test --compact` 156 passed, 1 skipped; `npm test` 38 passed).

**Not verified:** Outlook rendering; Google indexing of the sitemap URLs.

## 11. Skills for the next session

- `snapshot` to resume from this file.
- `tdd` for any code change; Chrome DevTools MCP for production walkthroughs;
  Atlassian MCP connector for new KAN tickets.
