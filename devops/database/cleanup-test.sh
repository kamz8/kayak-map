#!/bin/bash

# Kolory dla output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Konfiguracja
TEST_DB_CONTAINER="wartkinurt_mariadb_testing"
TEST_DB_NAME="wartkinurt_testing"
TEST_DB_USER="admin"
TEST_DB_PASS="admin123"
BACKUP_DIR="database/backups"

echo -e "${YELLOW}🧹 Czyszczenie po testach backup/restore${NC}"
echo "========================================="

# Wyczyść testową bazę danych (nie usuwaj całej bazy)
if docker ps | grep -q $TEST_DB_CONTAINER 2>/dev/null; then
    echo -e "${YELLOW}🗑️  Czyszczenie testowej bazy danych: $TEST_DB_NAME${NC}"
    docker exec $TEST_DB_CONTAINER mariadb -u $TEST_DB_USER -p$TEST_DB_PASS $TEST_DB_NAME -e "
        SET FOREIGN_KEY_CHECKS = 0;
        DROP TABLE IF EXISTS trails, regions, points, river_tracks, sections, images, imageables, links, point_types, rivers, users, personal_access_tokens, cache, jobs, trail_region;
        SET FOREIGN_KEY_CHECKS = 1;
    " 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Testowa baza danych wyczyszczona${NC}"
    else
        echo -e "${RED}❌ Błąd czyszczenia testowej bazy danych${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Kontener testowy MariaDB nie działa${NC}"
fi

# Lista plików do usunięcia
echo -e "${YELLOW}📁 Pliki backup do potencjalnego usunięcia:${NC}"
if ls $BACKUP_DIR/backup_*.sql 1> /dev/null 2>&1; then
    ls -la $BACKUP_DIR/backup_*.sql
    echo ""
    echo -e "${YELLOW}💡 Aby usunąć niezaszyfrowane backupy:${NC}"
    echo -e "${BLUE}rm $BACKUP_DIR/backup_*.sql${NC}"
else
    echo -e "${GREEN}✅ Brak niezaszyfrowanych plików backup${NC}"
fi

# Lista tymczasowych plików
echo -e "${YELLOW}📁 Tymczasowe pliki restore:${NC}"
if ls $BACKUP_DIR/temp_*.sql 1> /dev/null 2>&1; then
    ls -la $BACKUP_DIR/temp_*.sql
    echo ""
    echo -e "${YELLOW}💡 Aby usunąć tymczasowe pliki:${NC}"
    echo -e "${BLUE}rm $BACKUP_DIR/temp_*.sql${NC}"
else
    echo -e "${GREEN}✅ Brak tymczasowych plików${NC}"
fi

# Pokaż zaszyfrowane backupy
echo -e "${YELLOW}🔐 Zaszyfrowane backupy (ZACHOWANE):${NC}"
if ls $BACKUP_DIR/*.enc 1> /dev/null 2>&1; then
    ls -la $BACKUP_DIR/*.enc
else
    echo -e "${YELLOW}⚠️  Brak zaszyfrowanych backupów${NC}"
fi

echo -e "${GREEN}🎉 Czyszczenie zakończone!${NC}"