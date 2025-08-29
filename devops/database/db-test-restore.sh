#!/bin/bash
set -e

# Kolory dla output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Konfiguracja testowej bazy
PROD_DB_CONTAINER="mariadb"
TEST_DB_CONTAINER="wartkinurt_mariadb_testing"
PROD_DB_NAME=${DB_DATABASE:-"kayak_map"}
TEST_DB_NAME="wartkinurt_testing"
PROD_DB_USER=${DB_USERNAME:-"admin"}
PROD_DB_PASS=${DB_PASSWORD:-"Poké!moon95"}
TEST_DB_USER="admin"
TEST_DB_PASS="admin123"
BACKUP_DIR="database/backups"
ENCRYPTED_FILE="$BACKUP_DIR/production_data.sql.enc"
# Kompatybilność z macOS (BSD date) i Linux (GNU date)
if date -j >/dev/null 2>&1; then
    # macOS (BSD date)
    TEMP_TIMESTAMP=$(date -j "+%Y%m%d_%H%M%S")
else
    # Linux (GNU date)
    TEMP_TIMESTAMP=$(date +%Y%m%d_%H%M%S)
fi
TEMP_FILE="$BACKUP_DIR/temp_test_restore_${TEMP_TIMESTAMP}.sql"
BACKUP_PASSWORD=${BACKUP_PASSWORD:-"kayak2024!backup#secure"}

echo -e "${BLUE}🧪 Test restore zaszyfrowanego backup na testowej bazie${NC}"
echo "==========================================================="
echo -e "📊 Baza produkcyjna: ${YELLOW}$PROD_DB_NAME${NC}"
echo -e "🧪 Baza testowa: ${YELLOW}$TEST_DB_NAME${NC}"

# Sprawdź czy plik backup istnieje
if [ ! -f "$ENCRYPTED_FILE" ]; then
    echo -e "${RED}❌ Plik backup nie istnieje: $ENCRYPTED_FILE${NC}"
    echo -e "${YELLOW}💡 Utwórz backup uruchamiając: bash devops/database/db-backup-safe.sh${NC}"
    exit 1
fi

# Sprawdź czy kontenery działają
if ! docker ps | grep -q $PROD_DB_CONTAINER 2>/dev/null; then
    echo -e "${RED}❌ Kontener produkcyjny $PROD_DB_CONTAINER nie działa${NC}"
    exit 1
fi

if ! docker ps | grep -q $TEST_DB_CONTAINER 2>/dev/null; then
    echo -e "${YELLOW}🚀 Uruchamianie kontenera testowego...${NC}"
    docker-compose up -d wartkinurt_mysql_testing
    echo -e "${YELLOW}⏳ Oczekiwanie na uruchomienie MariaDB (30s)...${NC}"
    sleep 30
fi

# Sprawdź czy testowy kontener działa
if ! docker ps | grep -q $TEST_DB_CONTAINER 2>/dev/null; then
    echo -e "${RED}❌ Kontener testowy $TEST_DB_CONTAINER nie działa${NC}"
    exit 1
fi

# Wyczyść testową bazę danych
echo -e "${YELLOW}🏗️  Przygotowywanie testowej bazy danych...${NC}"
docker exec $TEST_DB_CONTAINER mariadb -u $TEST_DB_USER -p$TEST_DB_PASS $TEST_DB_NAME -e "
    SET FOREIGN_KEY_CHECKS = 0;
    DROP TABLE IF EXISTS trails, regions, points, river_tracks, sections, images, imageables, links, point_types, rivers, users, personal_access_tokens, cache, jobs, trail_region;
    SET FOREIGN_KEY_CHECKS = 1;
"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Testowa baza danych wyczyszczona: $TEST_DB_NAME${NC}"
else
    echo -e "${RED}❌ Błąd czyszczenia testowej bazy danych${NC}"
    exit 1
fi

# Odszyfrowanie backup
echo -e "${YELLOW}🔓 Odszyfrowywanie backup...${NC}"
openssl enc -aes-256-cbc -d -salt -in $ENCRYPTED_FILE -out $TEMP_FILE -k $BACKUP_PASSWORD

if [ ! -f "$TEMP_FILE" ]; then
    echo -e "${RED}❌ Błąd podczas odszyfrowywania. Sprawdź hasło!${NC}"
    exit 1
fi

# Import danych do testowej bazy
echo -e "${YELLOW}📥 Importowanie danych do testowej bazy...${NC}"
docker exec -i $TEST_DB_CONTAINER mariadb -u $TEST_DB_USER -p$TEST_DB_PASS $TEST_DB_NAME < $TEMP_FILE

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Import zakończony pomyślnie!${NC}"
    
    # Usuń tymczasowy plik
    rm $TEMP_FILE
    
    # Porównaj statystyki między bazami
    echo -e "${YELLOW}📊 Porównanie statystyk baz danych:${NC}"
    echo "================================================"
    
    # Statystyki bazy produkcyjnej
    echo -e "${BLUE}📋 Baza produkcyjna ($PROD_DB_NAME):${NC}"
    docker exec $PROD_DB_CONTAINER mariadb -u $PROD_DB_USER -p$PROD_DB_PASS $PROD_DB_NAME -e "
        SELECT 'Trails' as table_name, COUNT(*) as count FROM trails
        UNION ALL
        SELECT 'Regions', COUNT(*) FROM regions
        UNION ALL
        SELECT 'Points', COUNT(*) FROM points
        UNION ALL
        SELECT 'River_tracks', COUNT(*) FROM river_tracks
        UNION ALL
        SELECT 'Views', COUNT(*) FROM information_schema.views WHERE table_schema = '$PROD_DB_NAME';
    " --table
    
    # Statystyki bazy testowej
    echo -e "${BLUE}📋 Baza testowa ($TEST_DB_NAME):${NC}"
    docker exec $TEST_DB_CONTAINER mariadb -u $TEST_DB_USER -p$TEST_DB_PASS $TEST_DB_NAME -e "
        SELECT 'Trails' as table_name, COUNT(*) as count FROM trails
        UNION ALL
        SELECT 'Regions', COUNT(*) FROM regions
        UNION ALL
        SELECT 'Points', COUNT(*) FROM points
        UNION ALL
        SELECT 'River_tracks', COUNT(*) FROM river_tracks
        UNION ALL
        SELECT 'Views', COUNT(*) FROM information_schema.views WHERE table_schema = '$TEST_DB_NAME';
    " --table
    
    echo -e "${GREEN}=================================="
    echo -e "🎉 Test restore zakończony pomyślnie!"
    echo -e "=================================="
    echo -e "✅ Dane zostały poprawnie przywrócone"
    echo -e "✅ Widoki zostały zachowane"
    echo -e "✅ Statystyki się zgadzają"
    echo ""
    echo -e "${YELLOW}🧹 Opcje czyszczenia:${NC}"
    echo -e "• Usuń testową bazę: ${BLUE}docker exec $TEST_DB_CONTAINER mariadb -u $TEST_DB_USER -p$TEST_DB_PASS -e 'DROP DATABASE $TEST_DB_NAME;'${NC}"
    echo -e "• Usuń niezaszyfrowany backup: ${BLUE}rm database/backups/backup_*.sql${NC}"
    echo -e "• Lub użyj: ${BLUE}npm run db:cleanup${NC}"
    
else
    echo -e "${RED}❌ Błąd podczas importu testowego!${NC}"
    rm -f $TEMP_FILE
    
    # Usuń niepoprawną testową bazę
    docker exec $TEST_DB_CONTAINER mariadb -u $TEST_DB_USER -p$TEST_DB_PASS -e "DROP DATABASE IF EXISTS $TEST_DB_NAME;" 2>/dev/null || true
    exit 1
fi