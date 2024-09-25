# Użyj oficjalnego obrazu PHP z Apache
FROM php:8.2-apache

# Ustaw zmienną środowiskową dla produkcji
ENV APP_ENV=production
ENV APP_DEBUG=false

# Zainstaluj zależności systemowe
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Zainstaluj Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Zainstaluj Node.js i npm
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs

# Ustaw katalog roboczy
WORKDIR /var/www/html

# Skopiuj pliki projektu
COPY . .

# Zainstaluj zależności PHP
RUN composer install --no-dev --optimize-autoloader

# Zainstaluj zależności Node.js i zbuduj assets
RUN npm install && npm run build

# Nadaj odpowiednie uprawnienia
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Skonfiguruj Apache
RUN a2enmod rewrite
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Oczyść cache
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port 80
EXPOSE 80

# Uruchom Apache
CMD ["apache2-foreground"]
