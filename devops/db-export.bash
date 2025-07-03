#!/bin/bash

# Zmienne
DB_USER="admin"
DB_PASS="Poké!moon95"
DB_NAME="kayak_map"
DB_HOST="db"

# Utwórz folder
mkdir -p ./docker/mysql

# Data w formacie
DATE=$(date +%Y%m%d_%H%M%S)

echo "🔄 Eksport bazy danych $DB_NAME..."

# Sprawdź czy kontener mysqldump istnieje
if ! docker ps -a --format "table {{.Names}}" | grep -q "mysqldump-tool"; then
    echo "❌ Kontener mysqldump-tool nie istnieje. Uruchom: docker-compose up -d mysqldump"
    exit 1
fi

# Eksport z mysqldump
echo "📤 Eksport przez mysqldump..."
docker exec mysqldump-tool mysqldump \
    -h $DB_HOST \
    -u $DB_USER \
    -p"$DB_PASS" \
    --default-character-set=utf8mb4 \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    --comments \
    --dump-date \
    $DB_NAME > "./docker/mysql/backup_$DATE.sql"

if [ $? -eq 0 ]; then
    echo "✅ Eksport zakończony: backup_$DATE.sql"
    echo "📊 Rozmiar pliku: $(du -h ./docker/mysql/backup_$DATE.sql | cut -f1)"
    echo "📁 Pliki w ./docker/mysql/:"
    ls -la ./docker/mysql/
else
    echo "❌ Błąd podczas eksportu!"
    exit 1
fi

# Eksport bazy testowej
echo ""
echo "🔄 Eksport bazy testowej..."
docker exec mysqldump-tool mysqldump \
    -h wartkinurt_mariadb_testing \
    -u admin \
    -padmin123 \
    --default-character-set=utf8mb4 \
    --single-transaction \
    --routines \
    --triggers \
    --add-drop-table \
    wartkinurt_testing > "./docker/mysql/test_backup_$DATE.sql"

if [ $? -eq 0 ]; then
    echo "✅ Backup testowy zakończony: test_backup_$DATE.sql"
else
    echo "❌ Błąd podczas backup testowego!"
fi

echo ""
echo "🎉 Wszystkie eksporty zakończone!"
