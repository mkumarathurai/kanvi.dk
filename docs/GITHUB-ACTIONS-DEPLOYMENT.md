# Kanvi deployment through GitHub Actions

Prepared 2026-09-30 at Mathi's request, using Invity as a reference. **Server
installation is still deferred.** This describes the prepared workflow, not an
installed or verified production system. No server access or GitHub secrets
have been created by this work.

Mathi subsequently authorized help with SSH access specifically: CloudPanel's
site user is `kanvi` and must be added to the SSH allow list. Read-only inspection
found `AllowUsers` in the main config and a Klogspot drop-in, both missing Kanvi.
A separate `/etc/ssh/sshd_config.d/60-kanvi.conf` containing `AllowUsers kanvi`
was proposed, followed by `sshd -t` and a reload of `ssh`. Adding an AllowUsers
directive extends the list rather than replacing it. Applying it requires
Mathi's interactive sudo authentication; application was not yet verified when
this note was written. This limited access task does not authorize installation.

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
