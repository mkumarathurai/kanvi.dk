#!/usr/bin/env bash
# Invoked by Actions as the site user, after uploading a complete release.
set -Eeuo pipefail
umask 027

fail() { printf '%s\n' "$*" >&2; exit 1; }
root="${1:?Deployment root required}"
release="${2:?Release identifier required}"
[[ "$root" =~ ^/[a-zA-Z0-9_./-]+$ && "$root" != / && "$root" != *..* ]] || fail 'Invalid deployment root'
[[ "$release" =~ ^[a-f0-9]{40}-[0-9]+-[0-9]+$ ]] || fail 'Invalid release identifier'
next="$root/releases/$release"
[[ -f "$root/shared/.env" && -d "$root/shared/storage" ]] || fail 'Server environment and storage must be provisioned first'
[[ ! -e "$root/current" || -L "$root/current" ]] || fail 'Current must be a symlink, never a real directory'
[[ -f "$next/artisan" && -f "$next/vendor/composer/platform_check.php" && -f "$next/public/build/manifest.json" ]] || fail 'Incomplete release'
[[ "$(cat "$next/public/deploy-revision.txt")" == "$release" ]] || fail 'Release identity mismatch'
[[ ! -e "$next/.env" && ! -L "$next/.env" && ! -e "$next/storage" && ! -L "$next/storage" ]] || fail 'Release already prepared; use a new run attempt'

previous=''
if [[ -L "$root/current" ]]; then
    previous="$(readlink "$root/current")"
    [[ "$previous" == "$root/releases/"* && -d "$previous" ]] || fail 'Previous release must exist beneath releases'
fi
activated=false
temporary="$root/.current-$release"

switch_to() {
    ln -s "$1" "$temporary"
    # rename(2) replaces the symlink itself atomically on the same filesystem.
    php -r 'if (!rename($argv[1], $argv[2])) { exit(1); }' "$temporary" "$root/current"
}

on_exit() {
    status=$?
    trap - EXIT
    if [[ "$status" -ne 0 && "$activated" == true ]]; then
        printf '%s\n' 'Activation failed; reverting code pointer. Database changes are not reverted.' >&2
        if [[ -n "$previous" ]]; then
            switch_to "$previous"
            (cd "$previous" && php artisan queue:restart) || true
        else
            unlink "$root/current"
        fi
    fi
    [[ ! -L "$temporary" ]] || unlink "$temporary"
    exit "$status"
}
trap on_exit EXIT

cd "$next"
ln -s "$root/shared/.env" .env
ln -s "$root/shared/storage" storage
php vendor/composer/platform_check.php
php artisan package:discover --ansi
php artisan config:cache
# A verified database backup and backward-compatible migrations are deployment
# prerequisites. Never migrate:fresh, roll back schema, or regenerate APP_KEY.
php artisan migrate --force
php artisan route:cache
php artisan storage:link
# Blade caches are shared; let each request compile the views it needs. Avoid
# view:cache/view:clear while the previous release still serves requests.
switch_to "$next"
activated=true

curl --fail --silent --show-error --max-time 20 --retry 3 "https://kanvi.dk/up?release=$release" > /dev/null
served="$(curl --fail --silent --show-error --max-time 20 --retry 3 "https://kanvi.dk/deploy-revision.txt?release=$release")"
[[ "$served" == "$release" ]] || fail 'Production returned a different release'
php artisan queue:restart
printf 'Activated and checked release %s\n' "$release"
