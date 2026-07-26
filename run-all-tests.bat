@echo off
setlocal enabledelayedexpansion

echo ════════════════════════════════════════════════════════════
echo 🧪 Kayak Map - Running All Tests
echo ════════════════════════════════════════════════════════════
echo.

set BACKEND_PASSED=0
set FRONTEND_PASSED=0

REM Backend Tests (Laravel/PHPUnit)
echo 📦 Running Backend Tests (Laravel/PHPUnit)...
echo ────────────────────────────────────────────────────────────
call php artisan test
if !errorlevel! equ 0 (
    set BACKEND_PASSED=1
    echo ✅ Backend tests passed!
) else (
    echo ❌ Backend tests failed!
)
echo.

REM Frontend Tests (Vitest)
echo 📦 Running Frontend Tests (Vitest)...
echo ────────────────────────────────────────────────────────────
call npm run test:run
if !errorlevel! equ 0 (
    set FRONTEND_PASSED=1
    echo ✅ Frontend tests passed!
) else (
    echo ❌ Frontend tests failed!
)
echo.

REM Summary
echo ════════════════════════════════════════════════════════════
echo 📊 Test Summary
echo ════════════════════════════════════════════════════════════

if !BACKEND_PASSED! equ 1 (
    echo Backend:  ✅ PASSED
) else (
    echo Backend:  ❌ FAILED
)

if !FRONTEND_PASSED! equ 1 (
    echo Frontend: ✅ PASSED
) else (
    echo Frontend: ❌ FAILED
)

echo ════════════════════════════════════════════════════════════

REM Exit with error if any test suite failed
if !BACKEND_PASSED! equ 1 if !FRONTEND_PASSED! equ 1 (
    echo ✅ All tests passed!
    pause
    exit /b 0
) else (
    echo ❌ Some tests failed!
    pause
    exit /b 1
)
