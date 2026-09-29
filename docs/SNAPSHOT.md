# Kanvi session snapshot

Recorded: 2026-09-29 17:54 CEST, Europe/Copenhagen. This is a technical handover, not a
production release record. The Knowledge Base project overview holds the
nontechnical project status and decisions.

Verified shared overview:
https://drive.google.com/file/d/1Fe-URgOMgkIXbZ5v6xawiORAYvPkTUKQ/view

Current KB folder: `Knowledge Base/01-Indbakke/Kanvi`. No existing Kanvi folder
was found. Mathi was asked whether it belongs under Mathi ApS or Private
projekter; no answer had arrived when this was saved, so the KB inbox rule was
used instead of assuming ownership. Move the existing folder after confirmation;
keep the overview's file ID and the repo pointers intact.

## 1. Repository state

- Working directory: `/Users/mathi/www/sites/kanvi.dk`
- Branch: `main`
- HEAD: `3d90ecf90040aff0a140e5935aaf697072b80a01`
- Remote: `git@github.com:mkumarathurai/kanvi.dk.git`
- The local `origin/main` reference matches HEAD; no unpushed commits relative
  to that reference. No remote fetch was performed during this snapshot.
- No stashes. Substantial work is uncommitted, including untracked files.
- GitHub returned no workflow runs and no open GitHub issues on 2026-09-29.
  There is no checked-in `.github` workflow. No Jira project/key is established
  in this session. CI is not verified green.

## 2. Where we are

Kanvi is a Danish, account-free group date poll, focused on “Hvornår kan vi?”.
The MVP, supplied v2 logo, clipboard fallback, and creation/participant journeys
are already in the commit history. Later session work added the shared email
design, the rest of the homepage, spacing refinements, public-page analytics,
and nine public articles. The articles were initially only source documents;
the latest implementation makes them visible on the local site and homepage.
The local checks pass, but these latest changes have not been committed or
deployed by this session. Planned article imagery and production acceptance
checks remain outstanding.

## 3. Work completed

Committed foundation:

- `d595da6`: MVP, behavioral contract and Soft Nordic design.
- `e70edbb`: supplied v2 logo and brand assets.
- `3b17d39`: copy-button fallback for local HTTP / unavailable Clipboard API.
- `3d90ecf`: creation and participant journeys aligned with supplied references.

Uncommitted work from the subsequent conversation:

- Shared email layout/components, PNG logo, eight additional mail types and
  recovery migration. `docs/design/EMAIL-DESIGN.md` records integration status.
  Only recovery is wired to an existing live flow; mail classes do not implement
  accounts, login, invitations, participant reminders or event features.
- Email previews and actual local Mailpit delivery were checked earlier in the
  session. Mathi approved the mail design and the green primary CTA.
- Complete homepage sections: occasion cards with editable title prefill,
  sharing explanation, isolated interactive demo, FAQ and final CTA.
- Shared section spacing: 112 px desktop, 80 px mobile. The demo section has a
  subtle independent background. Personal footer signature is preserved.
- Self-hosted Umami on public production pages only. See `docs/analytics.md`.
- SEO strategy and nine source article documents, followed by publication of
  their edited text through a shared server-rendered article system.

Published local routes:

| Article | Route |
| --- | --- |
| Doodle alternative | `/doodle-alternativ` |
| Date polls | `/datoafstemning` |
| Finding a date | `/find-en-dato` |
| Christmas lunch | `/til/julefrokost` |
| Boards | `/til/bestyrelser` |
| Associations | `/til/foreninger` |
| Friends | `/til/venner` |
| Family | `/til/familien` |
| Bachelor/bachelorette parties | `/til/polterabend` |

`/guides` lists all nine, `/til` lists the six situations, and `/#guides` links
to all nine from the homepage. `/sitemap.xml` lists 12 public URLs. Metadata,
canonical URLs, WebPage/BreadcrumbList data, contextual links and creation CTAs
are implemented. See `docs/indhold/PUBLICERING.md` for the editing model.

## 4. Unfinished work

The working tree must be preserved. Its main change groups are:

- Mail: `app/Mail/`, `app/Console/Commands/PreviewEmails.php`,
  `resources/views/emails/`, `resources/views/components/email/`,
  `public/images/email/`, `scripts/prepare-email-logo.mjs`, dependencies/tests.
  The deleted old `resources/views/mail/admin-recovery*.blade.php` files are
  replaced by the shared templates; this is intentional.
- Homepage: `CreatePoll`, Blade components/views, `resources/css/home-sections.css`,
  `resources/js/demo-poll.js`, `public/images/home/`, layout/icons and tests.
- Articles: `app/Content/Articles.php`, `ArticleController`, `config/articles.php`,
  `resources/content/articles/`, article views/CSS, homepage links, routes,
  robots.txt and `ArticlesTest`.
- Documentation: source articles, SEO strategy, design/email/analytics notes,
  this snapshot and startup pointers.

Known missing work, not reported as complete:

- Article screenshots, illustrations, responsive image variants and article
  `og:image`. Published pages currently contain complete edited text without
  image-brief placeholders.
- Standalone `/faq`, help center and further SEO pages. Current FAQ links use
  `/#spoergsmaal` so they resolve.
- Real Gmail desktop/mobile, Apple Mail, Outlook and iPhone Mail acceptance.
  Browser previews and Mailpit delivery are not substitutes for these clients.
- Production deploy, production mail transport/queue verification, analytics
  ingestion and Search Console submission. Mathi selected PHP 8.4 on the server;
  no current production configuration or deployed revision was verified here.
- Retention/archive policy and operational hardening; product funnel events.
- Full shared Definition of Done evidence: no CI run, Jira-linked commits,
  captured browser-console output, or demonstrated test-first history for all
  existing changes. Passing local tests do not satisfy these missing checks.

## 5. Next steps

1. Read the Knowledge Base rules, the whole project overview (especially
   decisions), this file and the files relevant to the next requested task.
   Confirm the current working tree before changing anything:

   ```sh
   cd /Users/mathi/www/sites/kanvi.dk
   git status --short
   git log --oneline -10
   git diff --stat
   ```

2. If continuing launch work, inventory the image briefs and capture the real
   product UI for article screenshots. Generate illustrations separately:

   ```sh
   rg -n 'BILLEDE|Filnavn|Billedplan' docs/indhold
   ```

3. Review the existing uncommitted work in coherent groups, establish ticket/CI
   conventions, and prepare a release only when asked. This snapshot request
   does not authorize bundling everything into a commit or deploying it.

4. Run the relevant checks after changes. The full suite is inexpensive:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 6. Waiting on Mathi / external acceptance

- Launch scope and production rollout timing are not established here.
- Production mail delivery and actual mail-client checks need an appropriate
  test destination/client environment.
- Retention/privacy wording and archival behavior need final product decisions.
- Production database choice and concurrency verification on it remain open.
- Specwise/Jira links are not known. Product decisions currently reside in the
  repo specification supplied/approved in this conversation; do not invent links.

These items do not block ordinary local content or design work.

## 7. Decisions to preserve

Authoritative behavioral detail is in `docs/kanvi-product-solution-spec-v1.1.md`,
`docs/autosave.md` and `docs/administration.md`. Mathi explicitly decided:

- Name plus one server-confirmed answer counts as responded. Unanswered is a
  separate state, never “cannot”. First saved answer unlocks totals and names.
- Optimistic local UI and server-confirmed state are distinct. Saved means ACK
  of the latest revision; retries retain local input. Same-poll integrity,
  authorization and minimum two active options belong in the foundation.
- Organizer access uses browser access, the admin link and optional email
  recovery. Losing all three cannot be silently repaired.
- Status permissions are explicit. Reopening clears the final date and
  finalization timestamp while preserving responses.
- The written design system is authoritative; images explain visual intent.
  Keep the final supplied v2 logo. Do not revert to an earlier logo reference.
- Email uses one reusable system and a green primary CTA (`#138448`), approved
  after the initial dark-neutral CTA proposal.
- Preserve “Made with ❤️” and the current-year Mathi Kumarathurai copyright.
- SEO centers on group date finding, with useful situation pages rather than
  mass-generated articles. Marketing is indexable; user polls remain noindex.
- Analytics uses Mathi's self-hosted Umami. Private pages and local development
  do not load the tracker; query strings and fragments are excluded.

Implementation limits, not newly approved product decisions: single calendar
dates only, no time slots/date ranges/automatic deadlines. Article wording was
adapted to those limits and recorded in `docs/indhold/PUBLICERING.md`.

## 8. Dead ends and corrections

- Keeping article text only in `docs/indhold/` did not publish it. The runtime
  uses explicit metadata/routes and clean Markdown under `resources/content/`.
- Publishing source documents directly would expose image briefs and editorial
  notes. Keep source drafts and public copy separate.
- Raw `<?xml` in a compiled Blade sitemap caused a parse error; the current
  view emits the XML declaration safely.
- Bare numeric dates inside Markdown bullets were parsed as nested ordered
  lists; polterabend dates now escape the period.
- Clipboard API alone failed on local HTTP. Preserve the tested fallback and
  truthful failure UI.
- Do not treat a mail class or a concept screenshot as evidence that the
  corresponding product feature exists.

## 9. Repository traps and useful entry points

- Local URL: `http://kanvi.dk.test/`, served by Herd; PHP CLI is 8.4.25.
- Vite build output is ignored; rebuild assets when CSS/JS changes.
- `APP_URL` drives canonical URLs, sitemap and email assets. Production must
  use the intended HTTPS origin. Do not copy the local `.test` origin to prod.
- Poll/admin/recovery routes have existing privacy controls. Marketing layout
  changes must preserve private-page handling and token secrecy.
- `docs/design/EMAIL-DESIGN.md` contains older milestone test totals; the current
  aggregate is below. Its untested mail-client list remains accurate.
- Recovery mail uses queued jobs. Local Mailpit is not production mail delivery.
- Temporary previews under `storage/app/private/mail-previews/` are ignored.
  The session's PHP preview server on port 8787 was stopped during this snapshot.
  Herd and Mailpit were left running; they are the user's development services.
- Secrets remain in local environment configuration. Do not print or copy
  `.env`, raw admin/recovery links, credentials or real participant data into
  snapshots or the Knowledge Base.

## 10. Verification evidence and limits

Re-run for this snapshot on 2026-09-29:

- `php artisan test --compact`: 107 passed, 1,012 assertions.
- `vendor/bin/pint --test`: passed.
- `npm test`: 25 passed, zero failures.
- `npm run build`: Vite 7.3.6 build passed.
- `php artisan migrate:status`: all seven listed local migrations ran.
- GitHub run list: empty; GitHub open issue list: empty.
- `git diff --check`: passed. The temporary preview listener on 8787 is stopped.
- Limited credential-pattern scan: 219 nonignored files checked for private-key
  headers and common AWS/GitHub/Slack token patterns; no matches. This is not a
  comprehensive secret audit and excluded ignored environment configuration.
- The Drive overview was fetched after upload and matched the local prepared
  Markdown exactly. Its file ID is linked above and in `AGENTS.md`/`CLAUDE.md`.

Earlier in this same session, Safari showed the nine-card `/guides` page,
opened the polterabend article from its card, and showed all nine homepage links.
Responsive previews at 390 px (article/home) and 320 px (guide index) were
visually checked. The desktop page and article were also checked. These checks
were observed in the conversation; no exported screenshot files or browser
console log were stored. Production behavior was not verified.

Three remaining failure risks: production origin/queue/mail configuration;
email-client rendering differences; deployment losing uncommitted or untracked
files. The largest launch concern is declaring the local working copy released
without a reproducible commit, CI and production acceptance evidence.

## 11. Skills for the next session

- Google Drive: read/update the Knowledge Base project overview as Markdown.
- The user's snapshot skill: `/Users/mathi/.claude/skills/snapshot/SKILL.md`.
  Writing a snapshot and resuming from it are different operations.
- `writing-for-agents` when editing startup instructions or this handover.
- `imagegen` for requested article illustrations, with real screenshots used
  for product UI. Re-read its instructions before generation.

Read relevant skills on demand. Preserve the current uncommitted implementation
and reconcile this snapshot with the repository before continuing.
