#!/usr/bin/env bash

echo "Running composer"

composer install --no-dev --working-dir=/var/www/html --prefer-dist --no-scripts --no-progress --no-interaction

echo "Caching config..."
php artisan config:cache
echo "Caching routes..."
php artisan route:cache

# Upewnienie się, że po buildzie mamy odpowiednie uprawnienia
echo "Setting permissions for storage and cache"
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

npm ci && npm run build

