# Bazowy obraz PHP
FROM php:8.2-fpm

# Instalacja zależności
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    git \
    curl \
    libzip-dev \
    unzip \
    nodejs \
    npm

# Instalacja rozszerzeń PHP
RUN docker-php-ext-install pdo_mysql gd zip

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalacja Redis w PHP
RUN pecl install redis \
    && docker-php-ext-enable redis

# Ustawienie katalogu roboczego
WORKDIR /var/www/html

# Kopiowanie plików projektu
COPY . .

# Instalacja zależności PHP i Node.js
RUN composer install --optimize-autoloader --no-dev
RUN npm ci

# Budowanie front-endu w trybie produkcyjnym
RUN npm run build

# Uprawnienia do katalogów
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
