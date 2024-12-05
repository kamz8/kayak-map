#!/bin/bash

# Aktualizacja zmiennych środowiskowych w istniejącym .env
sed -i "s|APP_ENV=.*|APP_ENV=${APP_ENV}|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=${APP_DEBUG}|" .env
sed -i "s|APP_URL=.*|APP_URL=${APP_URL}|" .env
sed -i "s|DB_HOST=.*|DB_HOST=${DB_HOST}|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

# Czekanie na bazę danych
until nc -z -v -w30 $DB_HOST 3306
do
    echo "Waiting for database connection..."
    sleep 5
done

# Cache dla produkcji
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php-fpm
