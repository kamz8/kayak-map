# Używamy oficjalnego obrazu PHP z Apache
FROM php:8.1-apache

# Instalacja niezbędnych narzędzi, rozszerzeń PHP, Node.js i npm dla Vue.js
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Instalacja Node.js i npm (dla Vue.js)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Instalacja kompozytora (Composer) dla zarządzania pakietami PHP
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ustawienie katalogu roboczego
WORKDIR /var/www/html

# Kopiowanie plików aplikacji do kontenera
COPY . .

# Instalacja zależności PHP (backend)
RUN composer install --no-dev --optimize-autoloader

# Instalacja zależności npm (frontend - Vue.js)
RUN npm install && npm run build

# Ustawienia uprawnień dla katalogu storage i bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Wystawienie portu
EXPOSE 80

# Domyślne polecenie uruchamiające Apache
CMD ["apache2-foreground"]
