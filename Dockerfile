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
    unzip \
    libmagickwand-dev \
    imagemagick \
    libwebp-dev \
    libxpm-dev \
    libgd-dev

# Instalacja rozszerzeń PHP
RUN docker-php-ext-install pdo_mysql zip

# Konfiguracja i instalacja GD z wszystkimi funkcjami
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-xpm \
    && docker-php-ext-install -j$(nproc) gd

# Konfiguracja polityki bezpieczeństwa ImageMagick
#COPY docker/imagemagick-policy.xml /etc/ImageMagick-6/policy.xml


# Instalacja Node.js i npm
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

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
RUN composer install --no-scripts --no-autoloader

# Instalacja zależności Node.js i budowanie assetów
RUN npm ci && npm run build

# Generowanie autoloadera Composer w trybie optymalizowanym
RUN composer dump-autoload --optimize

# Kopiowanie konfiguracji PHP
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Ustawienie limitu pamięci dla PHP CLI
RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/memory-limit-cli.ini

# Etap końcowy
FROM php:8.3-fpm

# Instalacja zależności systemowych i rozszerzeń PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    supervisor \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
        bcmath \
        intl \
        opcache

# Konfiguracja i instalacja GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Instalacja Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Kopiowanie zbudowanej aplikacji
COPY --from=builder /var/www/html /var/www/html

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
RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/storage/framework/{cache,views,sessions} \
    && chmod -R 775 /var/www/html/storage

# Upewnienie się, że katalog sesji istnieje i ma odpowiednie uprawnienia
RUN mkdir -p /var/www/html/storage/framework/sessions \
    && chown -R www-data:www-data /var/www/html/storage/framework/sessions \
    && chmod -R 775 /var/www/html/storage/framework/sessions

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
