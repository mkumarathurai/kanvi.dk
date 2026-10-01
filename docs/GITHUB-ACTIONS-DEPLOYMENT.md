# Kanvi deployment through GitHub Actions

## Live deployment — 2026-09-30

Kanvi is installed and served over HTTPS. `DEPLOY_ENABLED=true`; pushes to
`main` deploy automatically after CI passes. Environment restrictions allow
only `main`. The PHP reload sudo rule and `Linger=yes` were verified, all seven
migrations ran against MySQL, and the systemd user queue worker is running.
CloudPanel's server-wide cron in `/etc/cron.d/clp` backs up every database
except `db1` and `db2` at 03:15 with seven-day retention, into
`/home/kanvi/backups/databases`. A restore was verified on 2026-10-01; the
nightly cron run is not yet proven to produce dumps. See
"Database backup and restore" below and KAN-7.

The [first installation passed](https://github.com/mkumarathurai/kanvi.dk/actions/runs/36690370454)
but browser verification found that CloudPanel returned 404 for Livewire's
dynamic JavaScript URL. Pages rendered, but the creation form did not advance.
The deploy script now publishes matching Livewire static assets on every
release and verifies the served file's SHA-256 against the deployed file.
Failure restores the previous code pointer. Two new regression assertions
failed before the change; all 35 JavaScript tests then passed. The
[automatic corrective deployment passed](https://github.com/mkumarathurai/kanvi.dk/actions/runs/36691308688)
for application commit `b76ae9e197db13e7505c69ff86c6db4ce4e35ded`.

Observed production evidence:

- All 13 sitemap URLs returned 200 with real Kanvi content and HTTPS origins.
- The class article has one H1 and the correct canonical URL.
- CSS, application JavaScript and published Livewire JavaScript load correctly.
- Chrome walkthrough: homepage title → two future dates → review → create →
  share page → participant name → Kan/Måske → saved confirmation → correct
  results → administration → select final date. The console was empty after
  the fix, including on the participant/result/admin pages. A screenshot of
  results with the empty console was captured in the conversation.
- The fictional poll titled `Deploymenttest 30. september - fiktive data`
  remains finalized in production, with one fictional participant and two
  responses. No email was sent. No real user data was deleted or changed.
- Poll responses include `X-Robots-Tag: noindex, nofollow` and
  `Cache-Control: no-store, private`.
- Queue worker remains active after automatic restart; no failed jobs reported.
- CI passed the full PHP suite (109 tests / 1,086 assertions), JavaScript tests,
  Pint and asset build. No new dependencies were added.

State on 2026-09-30, end of day: recovery mail goes through Resend
(`KANVI_RECOVERY_MAILER=resend`). A real recovery mail was delivered, and its
one-time link granted access once and was refused the second time. Gmail placed
it in the inbox, not spam; other providers are untested. The Laravel scheduler runs
every minute from `/etc/cron.d/kanvi`, which drives the retention purge. CI runs
the PHP suite on SQLite and MySQL 8.4. Work is tracked in Jira project KAN.

Remaining acceptance, each on its KAN ticket: backup restore (KAN-7), analytics
ingestion and Search Console (KAN-13), and mail-client acceptance (KAN-16). Load
on production MySQL is untested. This is not a claim of full shared Definition
of Done compliance.

Monitor HTTP errors, failed queues and database backups over the first 24 hours.
For rollback use a verified compatible release, reload PHP and restart workers;
do not restore an old database over newer responses. The initial pre-fix release
has the Livewire asset defect, so prefer the corrected release for later rollback.

The sections below retain the chronology of installation and earlier deferral.

## First installation preparation — 2026-09-30

After asking to proceed toward deployment, Mathi created the database in
CloudPanel. Server preparation then created `releases` and the shared storage
directories as `kanvi`, plus `shared/.env` with mode 600. A fresh production
application key was generated on the server without displaying it. Existing
files were not overwritten. The placeholder site and web-root configuration
were not changed.

The environment uses the proposed database/user `kanvi` on local MySQL, but
the password is intentionally empty pending Mathi's direct entry. These names
and database access are not yet verified. Recovery mail remains disabled until
SMTP is configured and checked. PHP 8.4 FPM is active. First deployment still
requires verified database access, CloudPanel web-root/FPM setup and workers;
`DEPLOY_ENABLED` has not been enabled. Earlier deferral notes below are historical.

Follow-up verification: the user entered the password directly on the server.
The repository's phpdotenv parser loaded it on-server, PDO connected successfully,
`SELECT 1` passed, and the MySQL server reported version `8.4.7-7` with zero
tables in `kanvi`. No credentials were printed. The temporary checker is under
`/home/kanvi/tmp/kanvi-preflight-20260930`, outside the web root.

An initial `releases/bootstrap/public` copy preserves the placeholder and
`current` now points to it. The original `public` directory remains untouched.
`kanvi.dk/current/public` is ready to select as Root Directory in CloudPanel.
`/kanvi-setup-check.txt` will return `kanvi-release-root-ready` when that setting
has taken effect; the marker exists only in the placeholder release.
Mathi changed this setting in CloudPanel and the marker was verified over HTTPS.

A systemd user unit was prepared at
`/home/kanvi/.config/systemd/user/kanvi-worker.service`, enabled but not started.
It runs `current/artisan queue:work` with a 60-second timeout, automatic restart,
and a 90-second shutdown grace period. It will be started after installation.
`loginctl enable-linger kanvi` is still required so it survives SSH logout.

Deployment now reloads `php8.4-fpm` after switching the code pointer and on
rollback. The dedicated sudoers rule must permit only
`/usr/bin/systemctl reload php8.4-fpm`; it has not yet been installed. Tests for
reload and failure rollback failed before implementation and now pass. This
service is shared with other PHP 8.4 sites: reload is graceful, not a restart.

## Earlier access preparation

Prepared 2026-09-30 at Mathi's request, using Invity as a reference. **Server
installation is still deferred.** This describes the prepared workflow, not an
installed or verified production system. Mathi has now provisioned the dedicated
SSH key and GitHub secrets using the setup guide; application installation is
still deferred.

Mathi subsequently authorized help with SSH access specifically: CloudPanel's
site user is `kanvi` and must be added to the SSH allow list. Read-only inspection
found `AllowUsers` in the main config and a Klogspot drop-in, both missing Kanvi.
A separate `/etc/ssh/sshd_config.d/60-kanvi.conf` containing `AllowUsers kanvi`
was applied by Mathi, followed by a successful `sshd -t` and reload of `ssh`.
The file and active SSH service were subsequently verified read-only. A login
test as `kanvi` reached authentication but the existing key was rejected.
The dedicated deployment key was subsequently installed by Mathi. This limited
access task does not authorize installation.

Follow [the Danish SSH and GitHub setup guide](DEPLOY-ADGANG.md) for the manual
steps. The guide leaves deployment disabled.

Access verification on 2026-09-30: login with the dedicated key succeeded as
`kanvi`; the site directory exists and is writable; PHP CLI is 8.4.24. GitHub's
`production` environment has the five expected secrets and allows only `main`.
`DEPLOY_PATH` is `/home/kanvi/htdocs/kanvi.dk`; `DEPLOY_ENABLED` remains `false`.
The first Actions connectivity test failed host-key verification. Replacing
`SSH_KNOWN_HOSTS` with the public host key retrieved over the existing trusted
SSH connection fixed it. The [read-only Actions test passed](https://github.com/mkumarathurai/kanvi.dk/actions/runs/36688253842),
verifying the secrets together from a GitHub runner. [CI also passed](https://github.com/mkumarathurai/kanvi.dk/actions/runs/36684416009).
No site files, database, services or production release were changed by these
tests. Actual installation, FPM configuration, mail and queue checks remain open.

## Trigger and release flow

`.github/workflows/ci.yml` runs PHP tests, Pint, JavaScript tests and the asset
build. Only after verification passes can it call `deploy.yml`, and only on
`main` with repository variable `DEPLOY_ENABLED` set to the exact value `true`.
Leave that variable absent or `false` until installation is explicitly resumed.
Pull requests cannot deploy. A manual CI run on `main` uses the same checks and
gate. Deployment jobs are serialized and running deployments are not canceled.

The deployment job builds production Composer dependencies and Vite assets on
the runner, then transfers an archive over verified SSH. Node and Composer are
not required on the server. Each run gets a new directory beneath `releases`.
The archive excludes environment files, persistent storage, SQLite databases,
development dependencies and generated local caches.

The remote script attaches the existing environment and storage, checks the PHP
platform, discovers packages, caches configuration/routes, runs migrations and
creates the public storage link. It atomically switches `current`, requests the
Laravel health endpoint and checks an exact release marker over HTTPS. A 200
response containing the previous `Hello World :-)` placeholder cannot pass.
It then signals queue workers to restart; a process supervisor must already be
configured to start replacement workers.

Invity's checked-in workflow currently uses in-place rsync, although its guide
describes atomic releases. Kanvi deliberately uses separate releases so a failed
upload or preparation cannot replace the running code. Invity was only read.

## GitHub configuration when installation resumes

Create a GitHub environment named `production`, restricted to deployments from
`main`. Store these **environment secrets** there:

| Secret | Value to provision |
| --- | --- |
| `SSH_HOST` | Server IP or DNS name, not the local `silanthi` alias |
| `SSH_PORT` | SSH port; read-only inspection found `2222` |
| `SSH_USERNAME` | Site user `kanvi`, once deployment SSH access exists |
| `SSH_PRIVATE_KEY` | Dedicated Actions private key; not Mathi's personal key |
| `SSH_KNOWN_HOSTS` | Independently verified OpenSSH host-key entries, including `[host]:2222` for the nondefault port |

Configure these **repository variables** under Actions settings:

| Variable | Value |
| --- | --- |
| `DEPLOY_PATH` | Confirmed site root; proposed `/home/kanvi/htdocs/kanvi.dk` |
| `DEPLOY_ENABLED` | Absent/`false` during preparation; `true` only after installation and launch prerequisites are approved and checked |

Known-host entries must match `SSH_HOST` and the port. Do not automatically trust
an unverified `ssh-keyscan` result. Secrets are passed through environment
variables and never interpolated directly into shell source.

The existing `mathi` SSH session cannot run as `kanvi` without a password.
GitHub Actions does not remove the need for one-time site-user SSH provisioning.
Do not use root or copy credentials from Invity. No secret values belong in the
repository or Knowledge Base.

## Server prerequisites — do not execute while installation is deferred

Expected layout, owned by the site user:

```text
<DEPLOY_PATH>/
  releases/
  shared/
    .env
    storage/
      app/private/
      app/public/
      framework/cache/data/
      framework/sessions/
      framework/views/
      logs/
  current -> releases/<commit>-<run>-<attempt>
```

- Confirm the site path in CloudPanel. Set the web root to `current/public`
  during the coordinated first installation; the initial symlink does not
  exist until activation. Keep HTTPS and the `kanvi.dk` origin working.
- Provide PHP 8.4 CLI/FPM, required Composer platform extensions (including GD
  and the chosen database driver), Bash, tar and curl. FPM and the web server
  must be able to traverse release directories and read static files; the
  script uses restrictive default permissions. Verify this with the actual
  site user/groups instead of changing permissions globally.
- Provision `shared/.env` securely with `APP_ENV=production`, `APP_DEBUG=false`,
  `APP_URL=https://kanvi.dk`, a stable `APP_KEY`, database, mail, session and
  queue configuration. Never copy the development environment. The workflow
  never creates or rotates the application key.
- Decide the production database and verify concurrency on it. If SQLite is
  chosen, use an **absolute path beneath `shared`**, outside every release,
  with the appropriate write access. MySQL/MariaDB data remains in its service.
- Establish database backups and verify a restore before enabling deployment.
  Take a fresh backup before releases with schema changes. This workflow does
  not create or restore database backups. Existing data is migrated in place;
  automatic schema rollback is intentionally absent.
- Use backward-compatible migrations: the old release and queue workers may
  remain active while the new schema is applied. Breaking schema changes need
  an explicit maintenance procedure instead of this automatic flow.
- Configure supervised queue workers against `current/artisan` and verify
  restart behavior, PHP-FPM/opcache behavior and production recovery mail.
- Create `releases` and the shared storage directories in advance. Preserve
  the existing placeholder/site configuration until the first cutover is
  planned. The deploy script refuses a real directory at `current`.

Only after these checks: create the deployment credentials, enable the gate and
run CI on the intended `main` revision. Do not rerun obsolete workflow runs:
the workflow deploys the revision that passed that run's tests, not whatever
revision happens to be newest later.

## Failure and rollback

An SSH, host-key, upload or preparation failure leaves the prior release active.
Migrations may already have made database changes even when activation fails.
A health-check or queue-restart failure after switching attempts to restore
the previous code pointer and restart its workers. The Actions run stays red.
On first installation with no previous release, a failed activation removes
only the new `current` pointer; shared data is preserved. The pre-existing
placeholder is not automatically restored and may require reverting CloudPanel's
web-root setting.

If the connection dies or the process is forcibly killed, automatic rollback is
not guaranteed. Inspect `current`, Actions output and server logs before retrying.
A retry uses a new directory. Failed and previous releases are retained; review
disk space and prune only inactive releases once the new release is accepted.

For manual rollback, identify the previously verified release, atomically
replace `current` with a symlink to it, restart its queue workers and repeat
HTTPS/functional checks. First confirm schema compatibility. Never restore an
old database over new participant responses as an automatic code rollback.

## Database backup and restore — verified 2026-10-01

A CloudPanel-produced dump of the production database was restored into a
scratch database and matched production exactly: 17 tables, 8 migration rows,
and identical row counts in `polls`, `poll_options`, `participants` and
`responses`. The scratch database was deleted afterwards.

Restore procedure, run as a sudo-capable user on the server:

1. Find the newest dump:

   ```sh
   sudo find /home/kanvi/backups/databases/kanvi -type f -name '*.sql.gz' | sort | tail -5
   ```

2. Create a scratch database through CloudPanel. Never restore over `kanvi`.
   Use a throwaway password; the command line lands in shell history:

   ```sh
   sudo clpctl db:add --domainName=kanvi.dk --databaseName=kanvi-restore-test \
     --databaseUserName=kanvi-restore-test --databaseUserPassword='<throwaway>'
   ```

3. Import the dump:

   ```sh
   sudo clpctl db:import --databaseName=kanvi-restore-test --file=<dump>.sql.gz
   ```

4. Inspect: `SHOW TABLES` and row counts as the scratch user, then compare with
   production via `php artisan db:show --counts` as `kanvi` in
   `~/htdocs/kanvi.dk/current`. Query counts only; never select participant
   data into a terminal or transcript.

5. Delete the scratch database:

   ```sh
   sudo clpctl db:delete --databaseName=kanvi-restore-test
   ```

Findings from the verification, both discovered 2026-10-01:

- The `kanvi` database was registered under the wrong CloudPanel site
  (`klogspot.dk`), so dumps went to `/home/klogspot/backups`. Fixed by setting
  the database's `site_id` to the `kanvi.dk` site in CloudPanel's SQLite
  database (`/home/clp/htdocs/app/data/db.sq3`; pre-change copy kept as
  `db.sq3.bak-20261001` beside it). The stray dump directory was deleted.
- The nightly 03:15 cron run started on 2026-09-30 and 2026-10-01 but produced
  no dumps for any site, while the same command run manually as the `clp` user
  succeeds — including with a cron-like minimal environment. The cron line's
  `&> /dev/null` was replaced with `>> /home/clp/db-backup-cron.log 2>&1`
  (pre-change copy kept as `/etc/cron.d/clp.bak-20261001`) so the next run
  records its output. Check the log and the dump directory after the next
  03:15 run before closing KAN-7. A CloudPanel update may overwrite
  `/etc/cron.d/clp` and remove the logging.

Note that `clpctl db:backup` is only defined when run as the `clp` user
(`sudo -u clp clpctl db:backup ...`); as root the command does not exist.

## Verification and remaining acceptance

Local deploy tests use temporary directories and mock Artisan/HTTP commands.
They cover successful activation, persistent environment/storage, missing
provisioning, migration failure, cache failure, wrong served revision, HTTP
timeout, first-deploy failure and protection of a real `current` directory.
The successful-activation test failed before implementation.

They do not prove SSH credentials/host identity, CloudPanel/FPM permissions, or
production database/mail/worker operation. These are the three main launch
risks. No UI change is included; an actual production browser walkthrough and
console capture remain pending, as does a Kanvi Jira project/key.

After the first live release, follow the functional and 24-hour monitoring
checklist in `DEPLOYMENT.md`. Check the homepage, article, create/respond/admin
flows, recovery delivery, HTTPS, assets, errors and queues before declaring
production verified.

References checked while preparing this workflow:
[GitHub reusable workflows and environment secrets](https://docs.github.com/en/actions/how-tos/reuse-automations/reuse-workflows),
[Laravel 12 deployment](https://laravel.com/framework/docs/12.x/deployment).
