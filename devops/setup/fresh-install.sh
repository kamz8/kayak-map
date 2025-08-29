#!/bin/bash
set -e

# Kolory dla output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔄 Kayak Map - Świeża Instalacja${NC}"
echo "===================================="

# Zatrzymanie i usunięcie kontenerów
echo -e "${YELLOW}🛑 Zatrzymywanie i usuwanie kontenerów...${NC}"
docker-compose down -v --remove-orphans 2>/dev/null || true

# Czyszczenie cache'ów Laravel
echo -e "${YELLOW}🧹 Czyszczenie cache'ów...${NC}"
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Usuwanie starych plików cache
rm -rf bootstrap/cache/*.php 2>/dev/null || true
rm -rf storage/framework/cache/data/* 2>/dev/null || true
rm -rf storage/framework/sessions/* 2>/dev/null || true
rm -rf storage/framework/views/* 2>/dev/null || true

# Czyszczenie node_modules i vendor (opcjonalnie)
if [ "$1" = "--deep" ]; then
    echo -e "${YELLOW}🗑️  Głębokie czyszczenie - usuwanie node_modules i vendor...${NC}"
    rm -rf node_modules 2>/dev/null || true
    rm -rf vendor 2>/dev/null || true
fi

echo -e "${GREEN}✅ Czyszczenie zakończone${NC}"

# Uruchomienie pełnego setup
echo -e "${YELLOW}🚀 Uruchamianie pełnego setup...${NC}"
bash devops/setup/project-setup.sh

echo -e "${GREEN}=================================="
echo -e "🎉 Świeża instalacja zakończona!"
echo -e "=================================="
echo -e "${NC}"