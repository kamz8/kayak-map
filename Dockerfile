# Etap budowania
FROM php:8.3-fpm as builder

# Instalacja zależności systemowych i rozszerzeń PHP
RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxpm-dev \
    libmagickwand-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalacja Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Budowanie aplikacji
WORKDIR /var/www/html
COPY composer.* package*.json ./
COPY packages ./packages
RUN composer install --no-scripts --no-autoloader --no-dev \
    && npm ci
COPY . .
RUN npm run build \
    && composer dump-autoload --optimize --no-dev --no-scripts

# Etap produkcyjny
FROM php:8.3-fpm

# Instalacja zależności produkcyjnych
RUN apt-get update && apt-get install -y \
    supervisor \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxpm-dev \
    curl \
    unzip \
    chromium \
    chromium-driver \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        gd \
        bcmath \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install --global puppeteer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Konfiguracja Browsershot
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium \
    BROWSERSHOT_NODE_BINARY=/usr/bin/node \
    BROWSERSHOT_NPM_BINARY=/usr/bin/npm \
    BROWSERSHOT_CHROME_PATH=/usr/bin/chromium

# Kopiowanie konfiguracji
COPY docker/php/local.ini /usr/local/etc/php/conf.d/
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/
COPY docker/supervisord.conf /etc/supervisor/conf.d/

# Kopiowanie aplikacji
COPY --from=builder /var/www/html /var/www/html
WORKDIR /var/www/html

# Konfiguracja uprawnień
RUN mkdir -p storage/framework/{cache,views,sessions} storage/logs \
    && chown -R www-data:www-data . \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
