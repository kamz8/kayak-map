#!/bin/bash
set -e

# Funkcja do odczytu wartości z pliku .env
get_env_value() {
    local key=$1
    local default=$2
    if [ -f ".env" ]; then
        local value=$(grep "^${key}=" ".env" 2>/dev/null | cut -d '=' -f2- | sed 's/^["'\'']\|["'\'']$//g')
        echo "${value:-$default}"
    else
        echo "$default"
    fi
}

# Konfiguracja z .env lub domyślne wartości
DB_CONTAINER=$(get_env_value "DB_CONTAINER" "kayak-mysql")
DB_NAME=$(get_env_value "DB_DATABASE" "kayak_map")
DB_USER=$(get_env_value "DB_USERNAME" "root")
DB_PASS=$(get_env_value "DB_PASSWORD" "root_password")
DB_HOST=$(get_env_value "DB_HOST" "localhost")
DB_PORT=$(get_env_value "DB_PORT" "3306")
BACKUP_DIR="database/backups"
ENCRYPTED_FILE="$BACKUP_DIR/production_data.sql.enc"
TEMP_FILE="$BACKUP_DIR/temp_restore_$(date +%Y%m%d_%H%M%S).sql"
BACKUP_PASSWORD=$(get_env_value "BACKUP_PASSWORD" "kayak2024!backup#secure")

echo "📖 Konfiguracja odczytana z .env:"
echo "   DB Container: $DB_CONTAINER"
echo "   DB Name: $DB_NAME"
echo "   DB Host: $DB_HOST:$DB_PORT"
echo "   DB User: $DB_USER"

echo "🔄 Przywracanie bazy danych z zaszyfrowanego backup..."

# Sprawdź czy plik backup istnieje
if [ ! -f "$ENCRYPTED_FILE" ]; then
    echo "❌ Plik backup nie istnieje: $ENCRYPTED_FILE"
    echo "💡 Utwórz backup uruchamiając: bash devops/database/db-backup.sh"
    exit 1
fi

# Sprawdź czy kontener działa
if ! docker ps | grep -q $DB_CONTAINER 2>/dev/null; then
    echo "🚀 Uruchamianie kontenerów..."
    docker-compose up -d mysql
    echo "⏳ Oczekiwanie na uruchomienie MySQL..."
    sleep 15
fi

# Odszyfrowanie backup
echo "🔓 Odszyfrowywanie backup..."
openssl enc -aes-256-cbc -d -in $ENCRYPTED_FILE -out $TEMP_FILE -k $BACKUP_PASSWORD

if [ ! -f "$TEMP_FILE" ]; then
    echo "❌ Błąd podczas odszyfrowywania. Sprawdź hasło!"
    exit 1
fi

echo "📥 Importowanie danych do bazy..."

# Sprawdź połączenie z bazą
echo "🔌 Sprawdzanie połączenia z bazą danych..."
docker exec $DB_CONTAINER mariadb -u $DB_USER -p$DB_PASS -e "SELECT 1;" > /dev/null

# Import danych
docker exec -i $DB_CONTAINER mariadb -u $DB_USER -p$DB_PASS $DB_NAME < $TEMP_FILE

if [ $? -eq 0 ]; then
    echo "✅ Import zakończony pomyślnie!"

    # Usuń tymczasowy plik
    rm $TEMP_FILE

    # Pokaż statystyki
    echo "📊 Statystyki bazy danych:"
    docker exec $DB_CONTAINER mariadb -u $DB_USER -p$DB_PASS $DB_NAME -e "
        SELECT
            (SELECT COUNT(*) FROM trails) as trails_count,
            (SELECT COUNT(*) FROM regions) as regions_count,
            (SELECT COUNT(*) FROM points) as points_count,
            (SELECT COUNT(*) FROM river_tracks) as river_tracks_count;
    "
else
    echo "❌ Błąd podczas importu!"
    rm -f $TEMP_FILE
    exit 1
fi
