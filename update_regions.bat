@echo off
echo Starting geocoding process...

for %%i in (7 10 13 14 15 18 20 25 26 27 28 30 32 33 43 46 52 53 54 55 56 61 62 63 64 65 66 69 70 72 74 75 76 83 85 86 91 93 94 95 96 98 99 100 101 105 106 107 109 110 113 114 115 120 121 122 123 124 126) do (
    echo Processing trail ID: %%i
    php artisan trails:geocode-to-regions %%i --api=public
    timeout /t 2 /nobreak > nul
)

echo Geocoding process completed!
pause
