#!/bin/bash
set -e

# Konfiguracja - automatycznie z .env lub domyślne wartości
DB_CONTAINER="mariadb"
DB_NAME=${DB_DATABASE:-"kayak_map"}
DB_USER=${DB_USERNAME:-"admin"}
DB_PASS=${DB_PASSWORD:-"Poké!moon95"}
BACKUP_DIR="database/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.sql"
ENCRYPTED_FILE="$BACKUP_DIR/production_data.sql.enc"
BACKUP_PASSWORD=${BACKUP_PASSWORD:-"kayak2024!backup#secure"}

echo "🔄 Tworzenie backup bazy danych..."

# Sprawdź czy kontener działa
if ! docker ps | grep -q $DB_CONTAINER 2>/dev/null; then
    echo "❌ Kontener MySQL nie działa. Uruchom: docker-compose up -d"
    exit 1
fi

# Stwórz katalog backupów
mkdir -p $BACKUP_DIR

# Export bazy danych
echo "📦 Eksportowanie bazy danych z kontenera..."
docker exec $DB_CONTAINER mariadb-dump \
    -u $DB_USER \
    -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    --create-options \
    --quick \
    --extended-insert \
    --default-character-set=utf8mb4 \
    $DB_NAME > $BACKUP_FILE

if [ ! -s "$BACKUP_FILE" ]; then
    echo "❌ Backup pusty lub nie został utworzony"
    exit 1
fi

echo "🔐 Szyfrowanie backup..."
# Szyfrowanie pliku za pomocą OpenSSL
openssl enc -aes-256-cbc -salt -in $BACKUP_FILE -out $ENCRYPTED_FILE -k $BACKUP_PASSWORD

if [ -f "$ENCRYPTED_FILE" ]; then
    echo "✅ Backup zaszyfrowany: $ENCRYPTED_FILE"
    
    # Usuń niezaszyfrowany plik
    rm $BACKUP_FILE
    
    # Pokaż rozmiar pliku
    FILESIZE=$(du -h "$ENCRYPTED_FILE" | cut -f1)
    echo "📊 Rozmiar zaszyfrowanego backup: $FILESIZE"
    
    echo "🔑 WAŻNE: Hasło do odszyfrowania: $BACKUP_PASSWORD"
    echo "💾 Backup gotowy do commitowania do repo!"
else
    echo "❌ Błąd podczas szyfrowania"
    exit 1
fi