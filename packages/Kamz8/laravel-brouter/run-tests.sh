#!/bin/bash
# packages/kamz/laravel-brouter/run-tests.sh

echo "🧪 Running BRouter Package Tests..."

# Navigate to package directory
cd packages/kamz/laravel-brouter

# Install dependencies if needed
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install
fi

# Run tests
echo "🚀 Running PHPUnit..."
./vendor/bin/phpunit

echo "✅ Tests completed!"
