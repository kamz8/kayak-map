@echo off
echo ════════════════════════════════════════════════════════════
echo 🧪 Running Frontend Tests (Vitest)
echo ════════════════════════════════════════════════════════════
echo.

REM Check if node_modules exists
if not exist "node_modules" (
    echo ❌ node_modules not found. Running npm install...
    call npm install
)

REM Check if test dependencies are installed
call npm list vitest >nul 2>&1
if errorlevel 1 (
    echo ❌ Vitest not found. Installing test dependencies...
    call npm install --save-dev @vue/test-utils@^2.4.6 @vitest/ui@^2.1.8 @vitest/coverage-v8@^2.1.8 vitest@^2.1.8 jsdom@^25.0.1
)

echo 📦 Running tests...
echo.

REM Run tests
call npm run test:run

echo.
echo ════════════════════════════════════════════════════════════
echo ✅ Tests completed!
echo ════════════════════════════════════════════════════════════
pause
