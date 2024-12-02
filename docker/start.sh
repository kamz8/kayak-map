#!/bin/bash

if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

fi

php artisan migrate --force

php artisan serve --host=0.0.0.0 --port=10000

