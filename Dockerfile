FROM php:8.2-fpm

# Zainstaluj zależności systemowe
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm

# Wyczyść cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Zainstaluj rozszerzenia PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Zainstaluj Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ustaw katalog roboczy
WORKDIR /var/www

# Skopiuj pliki projektu
COPY . /var/www

# Zainstaluj zależności PHP
RUN composer install --no-dev --optimize-autoloader

# Zainstaluj zależności Node.js i zbuduj assets
RUN npm ci && npm run build

# Ustaw uprawnienia
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Wyczyść cache i zoptymalizuj aplikację
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Zmień użytkownika na www-data
USER www-data

# Uruchom serwer PHP
CMD php artisan serve --host=0.0.0.0 --port=10000
