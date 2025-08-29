#!/bin/bash
set -e

# Kolory dla output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Konfiguracja
ENV_FILE=".env"
ENV_EXAMPLE=".env.example"
DB_CONTAINER="kayak-mysql"

echo -e "${BLUE}🚀 Kayak Map - Setup Projektu${NC}"
echo "=================================="

# Funkcja sprawdzania czy komenda istnieje
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Wykryj system operacyjny
OS="$(uname -s)"
case "${OS}" in
    Darwin*)    MACHINE="Mac";;
    Linux*)     MACHINE="Linux";;
    CYGWIN*)    MACHINE="Cygwin";;
    MINGW*)     MACHINE="Windows";;
    *)          MACHINE="UNKNOWN:${OS}"
esac

echo -e "${BLUE}🖥️  System operacyjny: ${MACHINE}${NC}"

# Sprawdź wymagania systemowe
echo -e "${YELLOW}📋 Sprawdzanie wymagań systemowych...${NC}"

if ! command_exists docker; then
    echo -e "${RED}❌ Docker nie jest zainstalowany${NC}"
    exit 1
fi

if ! command_exists docker-compose; then
    echo -e "${RED}❌ Docker Compose nie jest zainstalowany${NC}"
    exit 1
fi

if ! command_exists composer; then
    echo -e "${RED}❌ Composer nie jest zainstalowany${NC}"
    exit 1
fi

if ! command_exists npm; then
    echo -e "${RED}❌ NPM nie jest zainstalowany${NC}"
    exit 1
fi

if ! command_exists php; then
    echo -e "${RED}❌ PHP nie jest zainstalowany${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Wszystkie wymagania spełnione${NC}"

# Instalacja dependencji backend
echo -e "${YELLOW}📦 Instalowanie dependencji PHP...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

# Instalacja dependencji frontend
echo -e "${YELLOW}📦 Instalowanie dependencji NPM...${NC}"
npm install

# Konfiguracja środowiska
echo -e "${YELLOW}⚙️  Konfigurowanie środowiska...${NC}"
if [ ! -f "$ENV_FILE" ]; then
    if [ -f "$ENV_EXAMPLE" ]; then
        cp "$ENV_EXAMPLE" "$ENV_FILE"
        echo -e "${GREEN}✅ Plik .env utworzony z .env.example${NC}"
    else
        echo -e "${RED}❌ Brak pliku .env.example${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}⚠️  Plik .env już istnieje${NC}"
fi

# Generowanie klucza aplikacji
echo -e "${YELLOW}🔑 Generowanie klucza aplikacji...${NC}"
php artisan key:generate --force

# Uruchamianie kontenerów Docker
echo -e "${YELLOW}🐳 Uruchamianie kontenerów Docker...${NC}"
docker-compose up -d

# Oczekiwanie na MySQL - dostosowane dla różnych systemów
echo -e "${YELLOW}⏳ Oczekiwanie na uruchomienie MySQL...${NC}"
if [ "$MACHINE" == "Mac" ]; then
    # macOS może potrzebować więcej czasu
    sleep 25
else
    sleep 20
fi

# Sprawdzenie czy MySQL działa
echo -e "${YELLOW}🔌 Sprawdzanie połączenia z bazą danych...${NC}"
for i in {1..30}; do
    if docker exec $DB_CONTAINER mysqladmin ping -h localhost --silent; then
        echo -e "${GREEN}✅ MySQL jest gotowy${NC}"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${RED}❌ Timeout - MySQL nie odpowiada${NC}"
        exit 1
    fi
    sleep 2
done

# Migracje bazy danych
echo -e "${YELLOW}🗄️  Uruchamianie migracji bazy danych...${NC}"
php artisan migrate:fresh --force

# Przywracanie danych produkcyjnych
echo -e "${YELLOW}📥 Przywracanie danych produkcyjnych...${NC}"
if [ -f "database/backups/production_data.sql.enc" ]; then
    bash devops/database/db-restore.sh
else
    echo -e "${YELLOW}⚠️  Brak zaszyfrowanego backup - uruchamianie seeders...${NC}"
    php artisan db:seed --force
fi

# Cache konfiguracji (opcjonalnie)
echo -e "${YELLOW}💾 Optymalizacja konfiguracji...${NC}"
php artisan config:cache
php artisan route:cache

# Budowanie frontend
echo -e "${YELLOW}🏗️  Budowanie aplikacji frontend...${NC}"
npm run build

# Podsumowanie
echo -e "${GREEN}=================================="
echo -e "🎉 Setup zakończony pomyślnie!"
echo -e "=================================="
echo -e "Aplikacja dostępna pod adresem:"
echo -e "Frontend: ${BLUE}http://localhost:5173${NC} (dev)"
echo -e "Backend:  ${BLUE}http://localhost:8000${NC}"
echo -e "MySQL:    ${BLUE}localhost:3306${NC}"
echo ""
echo -e "Komendy do zarządzania:"
echo -e "• ${YELLOW}npm run dev${NC}     - Development server"
echo -e "• ${YELLOW}php artisan serve${NC} - Laravel server"
echo -e "• ${YELLOW}docker-compose logs${NC} - Logi kontenerów"
echo ""
echo -e "Hasło do backup: ${YELLOW}kayak2024!backup#secure${NC}"
echo -e "${NC}"