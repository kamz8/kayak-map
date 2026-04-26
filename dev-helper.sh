#!/bin/bash

# 🚀 Kayak Map - Helper dla developerów
# Skrypt ułatwiający pracę developerom bez lokalnego PHP/Composer

set -e

# Kolory
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Header
echo -e "${BLUE}🚀 Kayak Map - Developer Helper${NC}"
echo "=================================="

# Funkcja helper
show_help() {
    echo -e "${YELLOW}Dostępne komendy:${NC}"
    echo ""
    echo -e "${GREEN}Setup i instalacja:${NC}"
    echo "  setup              - Pełny setup projektu"
    echo "  fresh              - Świeża instalacja"
    echo "  fresh-deep         - Głęboka instalacja (usuwa node_modules)"
    echo ""
    echo -e "${GREEN}Development:${NC}"
    echo "  artisan [cmd]      - Uruchom komendę artisan w kontenerze"
    echo "  composer [cmd]     - Uruchom komendę composer w kontenerze"
    echo "  migrate            - Uruchom migracje"
    echo "  seed               - Uruchom seedy"
    echo "  tinker             - Laravel Tinker"
    echo ""
    echo -e "${GREEN}Database:${NC}"
    echo "  db-backup          - Backup bazy danych"
    echo "  db-restore         - Przywróć backup"
    echo "  db-fresh           - Świeże migracje + seedy"
    echo ""
    echo -e "${GREEN}Docker:${NC}"
    echo "  up                 - Uruchom kontenery"
    echo "  down               - Zatrzymaj kontenery"
    echo "  restart            - Restart kontenerów"
    echo "  logs               - Pokaż logi"
    echo "  shell              - Shell w kontenerze kayak-app"
    echo ""
    echo -e "${GREEN}Inne:${NC}"
    echo "  status             - Status projektu"
    echo "  help               - Ta pomoc"
    echo ""
    echo -e "${BLUE}Przykłady:${NC}"
    echo "  ./dev-helper.sh artisan make:model Post"
    echo "  ./dev-helper.sh composer require package/name"
    echo "  ./dev-helper.sh migrate"
}

# Sprawdź czy kontenery działają
check_containers() {
    if ! docker ps | grep -q kayak-app; then
        echo -e "${YELLOW}⚠️  Kontener kayak-app nie działa. Uruchamiam kontenery...${NC}"
        docker-compose up -d
        sleep 10
    fi
}

# Main switch
case $1 in
    "setup"|"fresh"|"fresh-deep")
        npm run ${1}
        ;;
    "artisan")
        check_containers
        shift
        echo -e "${BLUE}🔧 Uruchamiam: php artisan $@${NC}"
        if [[ "$*" == *"tinker"* ]]; then
            docker exec -it kayak-app php artisan "$@"
        else
            docker exec kayak-app php artisan "$@"
        fi
        ;;
    "composer")
        check_containers
        shift
        echo -e "${BLUE}📦 Uruchamiam: composer $@${NC}"
        docker exec kayak-app composer "$@"
        ;;
    "migrate")
        check_containers
        echo -e "${BLUE}🗄️  Uruchamiam migracje...${NC}"
        docker exec kayak-app php artisan migrate
        ;;
    "seed")
        check_containers
        echo -e "${BLUE}🌱 Uruchamiam seedy...${NC}"
        docker exec kayak-app php artisan db:seed
        ;;
    "tinker")
        check_containers
        echo -e "${BLUE}🛠️  Uruchamiam Laravel Tinker...${NC}"
        docker exec -it kayak-app php artisan tinker
        ;;
    "db-backup")
        npm run db:backup
        ;;
    "db-restore")
        npm run db:restore
        ;;
    "db-fresh")
        check_containers
        echo -e "${BLUE}🔄 Świeże migracje + seedy...${NC}"
        docker exec kayak-app php artisan migrate:fresh --seed
        ;;
    "up")
        echo -e "${BLUE}🐳 Uruchamiam kontenery...${NC}"
        docker-compose up -d
        ;;
    "down")
        echo -e "${BLUE}🛑 Zatrzymuję kontenery...${NC}"
        docker-compose down
        ;;
    "restart")
        echo -e "${BLUE}🔄 Restart kontenerów...${NC}"
        docker-compose restart
        ;;
    "logs")
        echo -e "${BLUE}📋 Logi kontenerów:${NC}"
        docker-compose logs -f --tail=50
        ;;
    "shell")
        check_containers
        echo -e "${BLUE}🐚 Shell w kontenerze kayak-app...${NC}"
        docker exec -it kayak-app bash
        ;;
    "status")
        make status 2>/dev/null || {
            echo -e "${BLUE}📊 Status projektu:${NC}"
            echo ""
            echo -e "${YELLOW}Docker kontenery:${NC}"
            docker-compose ps 2>/dev/null || echo "Docker nie działa"
            echo ""
            echo -e "${YELLOW}Porty:${NC}"
            echo "Frontend: http://localhost:5173"
            echo "Backend: http://localhost:8000"
            echo "PhpMyAdmin: http://localhost:8081"
        }
        ;;
    "help"|"--help"|"-h"|"")
        show_help
        ;;
    *)
        echo -e "${RED}❌ Nieznana komenda: $1${NC}"
        echo ""
        show_help
        exit 1
        ;;
esac