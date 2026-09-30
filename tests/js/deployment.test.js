import test from 'node:test';
import assert from 'node:assert/strict';
import { mkdtempSync, mkdirSync, writeFileSync, readFileSync, readlinkSync, symlinkSync, existsSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { spawnSync } from 'node:child_process';

const script = resolve('scripts/deploy-release.sh');
const php = spawnSync('which', ['php'], { encoding: 'utf8' }).stdout.trim();
const release = 'a'.repeat(40) + '-123-1';

function fixture(t, { previous = true, env = true } = {}) {
    const root = mkdtempSync(join(tmpdir(), 'kanvi-deploy-'));
    t.after(() => rmSync(root, { recursive: true, force: true }));
    const next = join(root, 'releases', release);
    for (const path of ['shared/storage', 'bin', `releases/${release}/bootstrap/cache`, `releases/${release}/public/build`, `releases/${release}/vendor/composer`, 'releases/old']) {
        mkdirSync(join(root, path), { recursive: true });
    }
    if (env) writeFileSync(join(root, 'shared/.env'), 'SERVER_ENV_MUST_SURVIVE');
    writeFileSync(join(root, 'shared/storage/persistent'), 'saved response');
    writeFileSync(join(next, 'artisan'), '');
    writeFileSync(join(next, 'vendor/composer/platform_check.php'), '');
    writeFileSync(join(next, 'public/build/manifest.json'), '{}');
    writeFileSync(join(next, 'public/deploy-revision.txt'), release + '\n');
    if (previous) symlinkSync(join(root, 'releases/old'), join(root, 'current'));
    writeFileSync(join(root, 'bin/php'), `#!/usr/bin/env bash
if [[ "$1" != artisan ]]; then exec "${php}" "$@"; fi
printf '%s\\n' "$*" >> "$DEPLOY_TEST_ROOT/commands"
if [[ "$*" == *"$FAIL_COMMAND"* && -n "$FAIL_COMMAND" ]]; then exit 42; fi
if [[ "$*" == *"vendor:publish --tag=livewire:assets"* ]]; then
    mkdir -p public/vendor/livewire
    printf 'window.Livewire={};\\n' > public/vendor/livewire/livewire.min.js
fi
`, { mode: 0o755 });
    writeFileSync(join(root, 'bin/curl'), `#!/usr/bin/env bash
if [[ -n "$FAIL_HTTP" ]]; then exit 28; fi
if [[ "$*" == *deploy-revision.txt* ]]; then printf '%s\\n' "$HEALTH_REVISION"; fi
if [[ "$*" == *vendor/livewire/livewire.min.js* ]]; then
    if [[ -n "$FAIL_ASSET" ]]; then exit 22; fi
    cat "$DEPLOY_TEST_ROOT/current/public/vendor/livewire/livewire.min.js"
fi
`, { mode: 0o755 });
    writeFileSync(join(root, 'bin/sudo'), `#!/usr/bin/env bash
printf '%s\\n' "sudo $*" >> "$DEPLOY_TEST_ROOT/commands"
if [[ -n "$FAIL_RELOAD" ]]; then exit 43; fi
`, { mode: 0o755 });
    return {
        root, next,
        run(extra = {}) {
            return spawnSync('bash', [script, root, release], {
                encoding: 'utf8',
                env: { ...process.env, PATH: join(root, 'bin') + ':' + process.env.PATH, DEPLOY_TEST_ROOT: root, FAIL_COMMAND: '', FAIL_HTTP: '', FAIL_RELOAD: '', FAIL_ASSET: '', HEALTH_REVISION: release, ...extra },
            });
        },
    };
}

test('release activation keeps environment and persistent data outside the release', t => {
    const f = fixture(t);
    const result = f.run();
    assert.equal(result.status, 0, result.stderr);
    assert.equal(readlinkSync(join(f.root, 'current')), f.next);
    assert.equal(readFileSync(join(f.next, '.env'), 'utf8'), 'SERVER_ENV_MUST_SURVIVE');
    assert.equal(readFileSync(join(f.next, 'storage/persistent'), 'utf8'), 'saved response');
    assert.ok(readFileSync(join(f.root, 'commands'), 'utf8').includes('artisan queue:restart'));
    assert.match(readFileSync(join(f.root, 'commands'), 'utf8'), /sudo -n \/usr\/bin\/systemctl reload php8.4-fpm/);
    assert.ok(existsSync(join(f.next, 'public/vendor/livewire/livewire.min.js')));
});

test('missing server environment stops before migration or activation', t => {
    const f = fixture(t, { env: false });
    const result = f.run();
    assert.notEqual(result.status, 0);
    assert.match(result.stderr, /must be provisioned first/);
    assert.equal(readlinkSync(join(f.root, 'current')), join(f.root, 'releases/old'));
    assert.equal(existsSync(join(f.root, 'commands')), false);
});

test('failed migration preserves the previous release', t => {
    const f = fixture(t);
    assert.equal(f.run({ FAIL_COMMAND: 'migrate --force' }).status, 42);
    assert.match(readFileSync(join(f.root, 'commands'), 'utf8'), /migrate --force/);
    assert.equal(readlinkSync(join(f.root, 'current')), join(f.root, 'releases/old'));
});

test('HTTP 200 from the wrong revision causes a code rollback', t => {
    const f = fixture(t);
    const result = f.run({ HEALTH_REVISION: 'Hello World :-)' });
    assert.notEqual(result.status, 0);
    assert.match(result.stderr, /different release/);
    assert.equal(readlinkSync(join(f.root, 'current')), join(f.root, 'releases/old'));
});

test('failed first deployment removes its pointer and preserves shared storage', t => {
    const f = fixture(t, { previous: false });
    assert.notEqual(f.run({ HEALTH_REVISION: 'wrong' }).status, 0);
    assert.equal(existsSync(join(f.root, 'current')), false);
    assert.equal(readFileSync(join(f.root, 'shared/storage/persistent'), 'utf8'), 'saved response');
});

test('an existing real current directory is never overwritten', t => {
    const f = fixture(t, { previous: false });
    mkdirSync(join(f.root, 'current'));
    writeFileSync(join(f.root, 'current/keep'), 'original site');
    const result = f.run();
    assert.notEqual(result.status, 0);
    assert.match(result.stderr, /never a real directory/);
    assert.equal(readFileSync(join(f.root, 'current/keep'), 'utf8'), 'original site');
});

test('an HTTP timeout restores the previous code and reports failure', t => {
    const f = fixture(t);
    const result = f.run({ FAIL_HTTP: 'yes' });
    assert.equal(result.status, 28);
    assert.match(result.stderr, /reverting code pointer/);
    assert.equal(readlinkSync(join(f.root, 'current')), join(f.root, 'releases/old'));
});

test('failed cache preparation never activates the candidate', t => {
    const f = fixture(t);
    assert.equal(f.run({ FAIL_COMMAND: 'config:cache' }).status, 42);
    assert.equal(readlinkSync(join(f.root, 'current')), join(f.root, 'releases/old'));
    assert.doesNotMatch(readFileSync(join(f.root, 'commands'), 'utf8'), /migrate/);
});

test('failed FPM reload restores the previous code pointer and reports failure', t => {
    const f = fixture(t);
    assert.equal(f.run({ FAIL_RELOAD: 'yes' }).status, 43);
    assert.equal(readlinkSync(join(f.root, 'current')), join(f.root, 'releases/old'));
});

test('a missing Livewire asset rolls back even when the PHP health check passes', t => {
    const f = fixture(t);
    assert.notEqual(f.run({ FAIL_ASSET: 'yes' }).status, 0);
    assert.equal(readlinkSync(join(f.root, 'current')), join(f.root, 'releases/old'));
});
