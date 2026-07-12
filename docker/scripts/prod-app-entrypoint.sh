#!/bin/sh
set -e

# Production entrypoint.
#
# The app image ships a pristine copy of the built application at
# /var/www/html-dist. In production the /var/www/html path is a shared named
# volume (so nginx can serve the built public/ assets that php-fpm executes).
# Docker only auto-populates a named volume from the image the first time the
# volume is empty, which means new images would otherwise never update the code
# already living in that volume ("volume shadowing").
#
# To fix that, on every container start we sync the pristine image code into
# /var/www/html, excluding runtime state that lives on its own volume/bind
# (storage and .env). This keeps the shared volume in lock-step with the image
# on each deploy while preserving persistent data.

DIST=/var/www/html-dist
APP=/var/www/html

if [ -d "$DIST" ]; then
    mkdir -p "$APP"
    # Copy everything except runtime state. Using tar keeps it dependency-free
    # (no rsync needed) and only overwrites/adds files, never touching storage.
    tar -C "$DIST" \
        --exclude=./storage \
        --exclude=./.env \
        -cf - . | tar -C "$APP" -xpf -

    # php-fpm runs as www-data and must be able to write framework caches.
    chown -R www-data:www-data "$APP/bootstrap/cache" 2>/dev/null || true
    mkdir -p "$APP/storage/framework/cache" \
             "$APP/storage/framework/views" \
             "$APP/storage/framework/sessions" \
             "$APP/storage/logs"
    chown -R www-data:www-data "$APP/storage" 2>/dev/null || true

    # Keep Laravel's public storage link available after syncing into the
    # shared application volume; otherwise /storage/assets/* returns 404.
    rm -rf "$APP/public/storage"
    ln -s ../storage/app/public "$APP/public/storage"
fi

exec "$@"
