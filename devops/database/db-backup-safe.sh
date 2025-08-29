#!/bin/bash
set -e

# Kolory dla output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Konfiguracja - automatycznie z .env lub domyślne wartości
DB_CONTAINER="mariadb"
DB_NAME=${DB_DATABASE:-"kayak_map"}
DB_USER=${DB_USERNAME:-"admin"}
DB_PASS=${DB_PASSWORD:-"Poké!moon95"}
BACKUP_DIR="database/backups"
# Kompatybilność z macOS (BSD date) i Linux (GNU date)
if date -j >/dev/null 2>&1; then
    # macOS (BSD date)
    TIMESTAMP=$(date -j "+%Y%m%d_%H%M%S")
else
    # Linux (GNU date)
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
fi
BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.sql"
ENCRYPTED_FILE="$BACKUP_DIR/production_data.sql.enc"
BACKUP_PASSWORD=${BACKUP_PASSWORD:-"kayak2024!backup#secure"}

echo -e "${BLUE}🔄 Bezpieczny backup bazy danych z widokami${NC}"
echo "================================================="
echo -e "📊 Baza danych: ${YELLOW}$DB_NAME${NC}"
echo -e "🐳 Kontener: ${YELLOW}$DB_CONTAINER${NC}"
echo -e "👤 Użytkownik: ${YELLOW}$DB_USER${NC}"

# Sprawdź czy kontener działa
if ! docker ps | grep -q $DB_CONTAINER 2>/dev/null; then
    echo -e "${RED}❌ Kontener $DB_CONTAINER nie działa${NC}"
    exit 1
fi

# Stwórz katalog backupów
mkdir -p $BACKUP_DIR

# Sprawdź czy istnieją widoki
echo -e "${YELLOW}🔍 Sprawdzanie widoków w bazie danych...${NC}"
VIEW_COUNT=$(docker exec $DB_CONTAINER mariadb -u $DB_USER -p$DB_PASS $DB_NAME -e "
SELECT COUNT(*) FROM information_schema.views WHERE table_schema = '$DB_NAME';
" --batch --skip-column-names 2>/dev/null || echo "0")

echo -e "📋 Znaleziono ${GREEN}$VIEW_COUNT${NC} widoków"

# Export bazy danych z widokami i procedurami
echo -e "${YELLOW}📦 Eksportowanie bazy danych (tabele + widoki + procedury)...${NC}"
docker exec $DB_CONTAINER mariadb-dump \
    -u $DB_USER \
    -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-table \
    --add-drop-view \
    --create-options \
    --quick \
    --extended-insert \
    --complete-insert \
    --default-character-set=utf8mb4 \
    --hex-blob \
    --set-gtid-purged=OFF \
    $DB_NAME > $BACKUP_FILE

if [ ! -s "$BACKUP_FILE" ]; then
    echo -e "${RED}❌ Backup pusty lub nie został utworzony${NC}"
    exit 1
fi

# Pokaż statystyki backup - kompatybilność macOS/Linux
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    LINE_COUNT=$(wc -l "$BACKUP_FILE" | awk '{print $1}')
else
    # Linux
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    LINE_COUNT=$(wc -l < "$BACKUP_FILE")
fi
echo -e "${GREEN}✅ Backup utworzony: ${BACKUP_SIZE}, ${LINE_COUNT} linii${NC}"

# Szyfrowanie backup
echo -e "${YELLOW}🔐 Szyfrowanie backup z ulepszonymi parametrami...${NC}"
openssl enc -aes-256-cbc -pbkdf2 -iter 100000 -salt -in $BACKUP_FILE -out $ENCRYPTED_FILE -k $BACKUP_PASSWORD

if [ -f "$ENCRYPTED_FILE" ]; then
    # Kompatybilność macOS/Linux dla du
    if [[ "$OSTYPE" == "darwin"* ]]; then
        ENCRYPTED_SIZE=$(du -h "$ENCRYPTED_FILE" | cut -f1)
    else
        ENCRYPTED_SIZE=$(du -h "$ENCRYPTED_FILE" | cut -f1)
    fi
    echo -e "${GREEN}✅ Backup zaszyfrowany: $ENCRYPTED_FILE (${ENCRYPTED_SIZE})${NC}"
    
    echo -e "${YELLOW}⚠️  BEZPIECZEŃSTWO:${NC}"
    echo -e "• Niezaszyfrowany backup zachowany: ${YELLOW}$BACKUP_FILE${NC}"
    echo -e "• Zaszyfrowany backup: ${GREEN}$ENCRYPTED_FILE${NC}"
    echo -e "• Hasło: ${BLUE}$BACKUP_PASSWORD${NC}"
    
    echo -e "${YELLOW}📋 Co dalej:${NC}"
    echo -e "1. Przetestuj restore na testowej bazie"
    echo -e "2. Po pozytywnym teście, usuń: ${YELLOW}$BACKUP_FILE${NC}"
    echo -e "3. Commituj zaszyfrowany plik: ${GREEN}$ENCRYPTED_FILE${NC}"
else
    echo -e "${RED}❌ Błąd podczas szyfrowania${NC}"
    exit 1
fi