#!/bin/bash

echo "════════════════════════════════════════════════════════════"
echo "🧪 Kayak Map - Running All Tests"
echo "════════════════════════════════════════════════════════════"
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BACKEND_PASSED=0
FRONTEND_PASSED=0

# Backend Tests (Laravel/PHPUnit)
echo "📦 Running Backend Tests (Laravel/PHPUnit)..."
echo "────────────────────────────────────────────────────────────"
php artisan test
if [ $? -eq 0 ]; then
    BACKEND_PASSED=1
    echo -e "${GREEN}✅ Backend tests passed!${NC}"
else
    echo -e "${RED}❌ Backend tests failed!${NC}"
fi
echo ""

# Frontend Tests (Vitest)
echo "📦 Running Frontend Tests (Vitest)..."
echo "────────────────────────────────────────────────────────────"
npm run test:run
if [ $? -eq 0 ]; then
    FRONTEND_PASSED=1
    echo -e "${GREEN}✅ Frontend tests passed!${NC}"
else
    echo -e "${RED}❌ Frontend tests failed!${NC}"
fi
echo ""

# Summary
echo "════════════════════════════════════════════════════════════"
echo "📊 Test Summary"
echo "════════════════════════════════════════════════════════════"

if [ $BACKEND_PASSED -eq 1 ]; then
    echo -e "Backend:  ${GREEN}✅ PASSED${NC}"
else
    echo -e "Backend:  ${RED}❌ FAILED${NC}"
fi

if [ $FRONTEND_PASSED -eq 1 ]; then
    echo -e "Frontend: ${GREEN}✅ PASSED${NC}"
else
    echo -e "Frontend: ${RED}❌ FAILED${NC}"
fi

echo "════════════════════════════════════════════════════════════"

# Exit with error if any test suite failed
if [ $BACKEND_PASSED -eq 1 ] && [ $FRONTEND_PASSED -eq 1 ]; then
    echo -e "${GREEN}✅ All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}❌ Some tests failed!${NC}"
    exit 1
fi
