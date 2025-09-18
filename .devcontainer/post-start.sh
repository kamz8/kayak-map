#!/bin/bash

# Post-start script for DevContainer
set -e

echo "▶️ Kayak Map DevContainer - Post Start Setup"
echo "============================================="

# Wait for database to be ready
echo "🔌 Waiting for database connection..."
until php artisan db:show --database=mysql >/dev/null 2>&1; do
    echo "Waiting for database..."
    sleep 2
done
echo "✅ Database is ready"

# Run migrations if needed
echo "🗄️ Checking database migrations..."
if ! php artisan migrate:status >/dev/null 2>&1; then
    echo "Running fresh migrations..."
    php artisan migrate:fresh --force

    # Check if we have backup data to restore
    if [ -f "database/backups/production_data.sql.enc" ]; then
        echo "📥 Restoring production data..."
        bash devops/database/db-restore.sh
    else
        echo "🌱 Running database seeders..."
        php artisan db:seed --force
    fi
else
    echo "Migrations already up to date"
fi

# Clear and cache config
echo "💾 Optimizing configuration..."
php artisan config:cache
php artisan route:cache

# Build frontend assets if not exists
if [ ! -d "public/build" ]; then
    echo "🏗️ Building frontend assets..."
    npm run build
else
    echo "Frontend assets already built"
fi

# Start development servers in background
echo "🖥️ Starting development servers..."

# Start Laravel development server
if ! pgrep -f "php artisan serve" > /dev/null; then
    nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-serve.log 2>&1 &
    echo "✅ Laravel dev server started on port 8000"
fi

# Start Vite dev server
if ! pgrep -f "vite" > /dev/null; then
    nohup npm run dev > /tmp/vite-dev.log 2>&1 &
    echo "✅ Vite dev server starting on port 5173"
fi

echo "🎉 DevContainer is ready for development!"
echo ""
echo "🌐 Available services:"
echo "  • Frontend: http://localhost:5173"
echo "  • Backend: http://localhost:8000"
echo "  • PhpMyAdmin: http://localhost:8081"
echo "  • Database: mariadb:3306"
echo ""
echo "🛠️ Useful commands:"
echo "  • ./dev-helper.sh help"
echo "  • php artisan tinker"
echo "  • npm run dev"