#!/bin/bash

echo "════════════════════════════════════════════════════════════"
echo "🚀 Running Performance Tests"
echo "════════════════════════════════════════════════════════════"
echo ""

echo "📊 Test 1: Quick Performance (50 links)"
php artisan test --filter=quick_performance_test
echo ""

echo "📊 Test 2: Optimization Comparison"
php artisan test --filter=compare_with_vs_without_optimization
echo ""

echo "════════════════════════════════════════════════════════════"
echo "✅ All performance tests completed!"
echo "════════════════════════════════════════════════════════════"
