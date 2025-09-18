#!/bin/bash

# Post-create script for DevContainer
set -e

echo "🚀 Kayak Map DevContainer - Post Create Setup"
echo "=============================================="

# Fix permissions
echo "📂 Fixing permissions..."
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html

# Install/update Composer dependencies if needed
echo "📦 Checking Composer dependencies..."
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
else
    echo "Composer dependencies already installed"
fi

# Install/update NPM dependencies if needed
echo "📦 Checking NPM dependencies..."
if [ ! -d "node_modules" ]; then
    echo "Installing NPM dependencies..."
    npm install
else
    echo "NPM dependencies already installed"
fi

# Setup environment file
echo "⚙️ Checking environment file..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "✅ .env created from .env.example"
    else
        echo "⚠️ No .env.example found"
    fi
else
    echo ".env already exists"
fi

# Generate app key if needed
echo "🔑 Checking application key..."
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
    echo "✅ Application key generated"
else
    echo "Application key already exists"
fi

# Create storage directories and fix permissions
echo "📁 Creating storage directories..."
mkdir -p storage/framework/{cache,views,sessions}
mkdir -p storage/logs
mkdir -p storage/app/public
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Create storage symlink
echo "🔗 Creating storage symlink..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo "✅ Storage symlink created"
else
    echo "Storage symlink already exists"
fi

# Install Git hooks (if exists)
if [ -f "scripts/install-hooks.sh" ]; then
    echo "🪝 Installing Git hooks..."
    bash scripts/install-hooks.sh
fi

echo "✅ DevContainer post-create setup completed!"