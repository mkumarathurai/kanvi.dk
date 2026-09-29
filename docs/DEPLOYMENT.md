# Deployment record — 2026-09-29

The user requested deployment of the current work, followed by continued article
image work. This record separates repository delivery from production evidence.

## Release scope

- Shared mail templates and recovery-mail presentation.
- Complete homepage, editable occasion titles and isolated demo.
- Public-page Umami integration and ten server-rendered articles, including
  `/til/klassearrangement`, with navigation, metadata and sitemap.
- No database migration changes relative to `3d90ecf`.
- No new Composer packages. The existing uncommitted npm additions are
  `@resvg/resvg-js` 2.6.2 and `fontkit` 2.0.4, development tools for converting
  the approved logo to the checked-in email PNG; neither is a browser dependency.

## Verification

- PHP: 108 tests, 1,063 assertions; JavaScript: 25 tests.
- Pint, Vite build and diff whitespace checks passed locally.
- Bounded secret-pattern scan: 221 nonignored project files; no matches.
- Class article walkthrough in Safari: guide index → article → creation CTA.
  Desktop and 390 px iframe preview inspected. Browser console not captured.
- CI added for PHP 8.4, SQLite and Node 22 with read-only repository permissions.
  Actions are pinned to resolved upstream commits:
  [checkout](https://github.com/actions/checkout),
  [setup-node](https://github.com/actions/setup-node), and
  [setup-php](https://github.com/shivammathur/setup-php).

The original snapshot remains a historical handover. Its uncommitted-file and
nine-article counts predate this release preparation. No Jira project/key is
established; these commits follow the existing descriptive-subject convention
and do not claim compliance with the shared Jira-key requirement.

## Production prerequisites and rollback

The hosting panel/SSH target and deployment trigger were requested from Mathi;
none is documented in the repository. Do not infer that a GitHub push deploys.
Before changing production, capture its current revision and deployment script.
The remote main branch was `3d90ecf90040aff0a140e5935aaf697072b80a01` at the start;
this is not proof of the revision served in production.

Use the hosting system's existing release procedure. Preserve production `.env`,
`APP_KEY`, database and persistent storage. Verify HTTPS `APP_URL`, production
environment, disabled debug, PHP 8.4, built Vite assets and existing mail/queue
configuration. Do not rotate keys or reset/migrate-fresh the database.

Rollback means redeploying the recorded prior production revision with its
matching assets and refreshing application caches/workers using the existing
procedure. This release introduces no schema rollback requirement. Do not
restore a database backup over new participant responses.

After deployment, check the homepage, guide index, all ten article routes,
sitemap/canonicals, static assets and creation screen. A production poll smoke
test must use clearly fictional data and must not send unsolicited mail.
Monitor HTTP errors, queue failures, recovery delivery and analytics ingestion
over the first 24 hours. Retention decisions, real mail-client acceptance and
article images remain outstanding; a successful deploy does not complete them.

## Current status

The three preparation commits were pushed to `main` through `0e84c74`.
[Release CI passed](https://github.com/mkumarathurai/kanvi.dk/actions/runs/36603985842).
An HTTP 200 from the production article URL returned only `Hello World :-)`, not
Kanvi. Mathi confirmed that Kanvi has never been installed on this server yet.

SSH alias `silanthi` connects as `mathi` on port 2222. The intended site user is
`kanvi`, home `/home/kanvi`, without direct SSH access. PHP CLI is 8.4.24. Read-only
inspection succeeded, but `sudo -n -u kanvi id` requires a password and the Kanvi
directory is inaccessible to `mathi`. No site files, users, permissions, database
or server configuration were changed.

Mathi explicitly deferred server installation on 2026-09-29. Do not resume server
installation or change access until asked. Resolve site-user execution access,
site root, database choice, environment and launch requirements on resumption.

Two class-article illustrations were subsequently implemented locally with WebP
variants, lazy loading and intrinsic dimensions. The hero/product result images
and social preview remain outstanding. Their new test failed before the change
and then passed; the full PHP suite passed with 109 tests and 1,086 assertions.
Pint, Vite and whitespace checks passed; both illustrations were inspected in the
390 px Safari preview. These are editorial illustrations, not product screenshots.
