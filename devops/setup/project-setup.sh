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

# Funkcja do odczytu wartości z pliku .env
get_env_value() {
    local key=$1
    local default=$2
    if [ -f "$ENV_FILE" ]; then
        local value=$(grep "^${key}=" "$ENV_FILE" 2>/dev/null | cut -d '=' -f2- | sed 's/^["'\'']\|["'\'']$//g')
        echo "${value:-$default}"
    else
        echo "$default"
    fi
}

# Domyślne wartości (fallback)
DEFAULT_DB_CONTAINER="mariadb"
DEFAULT_DB_HOST="mariadb"
DEFAULT_DB_PORT="3306"
DEFAULT_DB_DATABASE="kayak_map"
DEFAULT_DB_USERNAME="root"
DEFAULT_DB_PASSWORD="admin123"
DEFAULT_APP_URL="http://localhost:8000"
DEFAULT_VITE_URL="http://localhost:5173"

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

# Sprawdzenie czy composer jest dostępny lokalnie
echo -e "${YELLOW}📦 Instalowanie dependencji PHP...${NC}"
if command -v composer >/dev/null 2>&1; then
    echo -e "${BLUE}   Używam lokalnego Composer${NC}"
    composer install --no-interaction --prefer-dist --optimize-autoloader
else
    echo -e "${BLUE}   Lokalny Composer niedostępny - kontynuuję bez instalacji dependencji${NC}"
    echo -e "${YELLOW}   ⚠️  Dependencje PHP będą zainstalowane w kontenerze Docker${NC}"
fi

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

# Odczyt konfiguracji z .env
echo -e "${YELLOW}📖 Odczytywanie konfiguracji z .env...${NC}"
DB_CONTAINER=$(get_env_value "DB_CONTAINER" "$DEFAULT_DB_CONTAINER")
DB_HOST=$(get_env_value "DB_HOST" "$DEFAULT_DB_HOST")
DB_PORT=$(get_env_value "DB_PORT" "$DEFAULT_DB_PORT")
DB_DATABASE=$(get_env_value "DB_DATABASE" "$DEFAULT_DB_DATABASE")
DB_USERNAME=$(get_env_value "DB_USERNAME" "$DEFAULT_DB_USERNAME")
DB_PASSWORD=$(get_env_value "DB_PASSWORD" "$DEFAULT_DB_PASSWORD")
APP_URL=$(get_env_value "APP_URL" "$DEFAULT_APP_URL")
VITE_URL=$(get_env_value "VITE_DEV_SERVER_URL" "$DEFAULT_VITE_URL")

echo -e "${BLUE}📋 Konfiguracja projektu:${NC}"
echo -e "   Database Container: ${YELLOW}$DB_CONTAINER${NC}"
echo -e "   Database Host: ${YELLOW}$DB_HOST${NC}"
echo -e "   Database Port: ${YELLOW}$DB_PORT${NC}"
echo -e "   Database Name: ${YELLOW}$DB_DATABASE${NC}"
echo -e "   App URL: ${YELLOW}$APP_URL${NC}"
echo -e "   Vite URL: ${YELLOW}$VITE_URL${NC}"

# Generowanie klucza aplikacji
echo -e "${YELLOW}🔑 Generowanie klucza aplikacji...${NC}"
if command -v php >/dev/null 2>&1; then
    php artisan key:generate --force
else
    docker exec kayak-app php artisan key:generate --force
fi

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
    if docker exec $DB_CONTAINER mariadb -u root -p$DB_PASSWORD -e "SELECT 1;" >/dev/null 2>&1; then
        echo -e "${GREEN}✅ MySQL jest gotowy${NC}"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${RED}❌ Timeout - MySQL nie odpowiada${NC}"
        echo -e "${YELLOW}⚠️  Sprawdź czy kontener $DB_CONTAINER działa i czy hasło $DB_PASSWORD jest poprawne${NC}"
        exit 1
    fi
    sleep 2
done

# Instalacja dependencji PHP w kontenerze (jeśli nie były zainstalowane lokalnie)
if ! command -v composer >/dev/null 2>&1; then
    echo -e "${YELLOW}📦 Instalowanie dependencji PHP w kontenerze kayak-app...${NC}"
    docker exec kayak-app composer install --no-interaction --prefer-dist --optimize-autoloader --working-dir=/var/www/html
    echo -e "${GREEN}✅ Dependencje PHP zainstalowane w kontenerze${NC}"
fi

# Migracje bazy danych
echo -e "${YELLOW}🗄️  Uruchamianie migracji bazy danych...${NC}"
if command -v php >/dev/null 2>&1; then
    echo -e "${BLUE}   Używam lokalnego PHP${NC}"
    php artisan migrate:fresh --force
else
    echo -e "${BLUE}   Używam PHP w kontenerze kayak-app${NC}"
    docker exec kayak-app php artisan migrate:fresh --force
fi

# Przywracanie danych produkcyjnych
echo -e "${YELLOW}📥 Przywracanie danych produkcyjnych...${NC}"
if [ -f "database/backups/production_data.sql.enc" ]; then
    bash devops/database/db-restore.sh
else
    echo -e "${YELLOW}⚠️  Brak zaszyfrowanego backup - uruchamianie seeders...${NC}"
    if command -v php >/dev/null 2>&1; then
        php artisan db:seed --force
    else
        docker exec kayak-app php artisan db:seed --force
    fi
fi

# Tworzenie storage symlink
echo -e "${YELLOW}🔗 Tworzenie storage symlink...${NC}"
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo -e "${GREEN}✅ Storage symlink utworzony${NC}"
else
    echo -e "${YELLOW}Storage symlink już istnieje${NC}"
fi

# Cache konfiguracji (opcjonalnie)
echo -e "${YELLOW}💾 Optymalizacja konfiguracji...${NC}"
if command -v php >/dev/null 2>&1; then
    php artisan config:cache
    php artisan route:cache
else
    docker exec kayak-app php artisan config:cache
    docker exec kayak-app php artisan route:cache
fi

# Budowanie frontend
echo -e "${YELLOW}🏗️  Budowanie aplikacji frontend...${NC}"
npm run build

# Podsumowanie
echo -e "${GREEN}=================================="
echo -e "🎉 Setup zakończony pomyślnie!"
echo -e "=================================="
echo -e "Aplikacja dostępna pod adresem:"
echo -e "Frontend: ${BLUE}${VITE_URL}${NC} (dev)"
echo -e "Backend:  ${BLUE}${APP_URL}${NC}"
echo -e "MySQL:    ${BLUE}${DB_HOST}:${DB_PORT}${NC}"
echo ""
echo -e "Komendy do zarządzania:"
echo -e "• ${YELLOW}npm run dev${NC}     - Development server"
echo -e "• ${YELLOW}php artisan serve${NC} - Laravel server"
echo -e "• ${YELLOW}docker-compose logs${NC} - Logi kontenerów"
echo ""
echo -e "Hasło do backup: ${YELLOW}kayak2024!backup#secure${NC}"
echo -e "${NC}"