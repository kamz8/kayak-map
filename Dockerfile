# Etap budowania
FROM php:8.3-fpm as builder

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
    unzip

# Instalacja Node.js i npm
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs

# Instalacja rozszerzeń PHP
RUN docker-php-ext-install pdo_mysql zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalacja Redis w PHP
RUN pecl install redis \
    && docker-php-ext-enable redis

# Ustawienie katalogu roboczego
WORKDIR /var/www/html

# Kopiowanie plików projektu
COPY . .

# Instalacja zależności PHP
RUN composer install --optimize-autoloader --no-scripts

# Instalacja zależności Node.js i budowanie assetów
RUN npm ci && npm run build

# Etap końcowy
FROM php:8.3-fpm

# Kopiowanie zbudowanej aplikacji
COPY --from=builder /var/www/html /var/www/html

# Instalacja supervisor
RUN apt-get update && apt-get install -y supervisor

# Konfiguracja supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Optymalizacja PHP
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=60" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Uprawnienia do katalogów
RUN chown -R www-data:www-data /var/www/html

# Tworzenie linku symbolicznego
RUN php artisan storage:link

# Ustawienie odpowiednich uprawnień
RUN chown -R www-data:www-data /var/www/html/storage

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
