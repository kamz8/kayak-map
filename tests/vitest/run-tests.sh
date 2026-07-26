#!/bin/bash

echo "════════════════════════════════════════════════════════════"
echo "🧪 Running Frontend Tests (Vitest)"
echo "════════════════════════════════════════════════════════════"
echo ""

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "❌ node_modules not found. Running npm install..."
    npm install
fi

# Check if test dependencies are installed
if ! npm list vitest > /dev/null 2>&1; then
    echo "❌ Vitest not found. Installing test dependencies..."
    npm install --save-dev @vue/test-utils@^2.4.6 @vitest/ui@^2.1.8 @vitest/coverage-v8@^2.1.8 vitest@^2.1.8 jsdom@^25.0.1
fi

echo "📦 Running tests..."
echo ""

# Run tests
npm run test:run

echo ""
echo "════════════════════════════════════════════════════════════"
echo "✅ Tests completed!"
echo "════════════════════════════════════════════════════════════"
