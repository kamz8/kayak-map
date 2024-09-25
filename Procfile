web: vendor/bin/heroku-php-apache2 public/

echo "$ENV_FILE" > .env && php artisan config:cache && php artisan serve
