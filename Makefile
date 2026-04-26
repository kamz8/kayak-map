.PHONY: setup fresh fresh-deep db-backup db-restore help clean

# Funkcja do odczytu .env
define get_env_value
$(shell if [ -f .env ]; then grep "^$(1)=" .env 2>/dev/null | cut -d '=' -f2- | sed 's/^["'\'']\|["'\'']$$//g'; else echo "$(2)"; fi)
endef

# Zmienne z .env lub domyślne
DB_CONTAINER = $(call get_env_value,DB_CONTAINER,kayak-mysql)
DB_HOST = $(call get_env_value,DB_HOST,localhost)
DB_PORT = $(call get_env_value,DB_PORT,3306)
DB_DATABASE = $(call get_env_value,DB_DATABASE,kayak_map)
APP_URL = $(call get_env_value,APP_URL,http://localhost:8000)
BACKUP_DIR = database/backups
ENCRYPTED_BACKUP = $(BACKUP_DIR)/production_data.sql.enc

## Główne komendy
setup: ## Pełny setup projektu z danymi produkcyjnymi
	@echo "🚀 Uruchamianie setup projektu..."
	@bash devops/setup/project-setup.sh

fresh: ## Świeża instalacja (usuwa kontenery i cache)
	@echo "🔄 Świeża instalacja..."
	@bash devops/setup/fresh-install.sh

fresh-deep: ## Głęboka świeża instalacja (usuwa też node_modules i vendor)
	@echo "🔄 Głęboka świeża instalacja..."
	@bash devops/setup/fresh-install.sh --deep

## Zarządzanie bazą danych
db-backup: ## Utwórz bezpieczny zaszyfrowany backup bazy danych (z widokami)
	@echo "📦 Tworzenie bezpiecznego backup bazy danych..."
	@bash devops/database/db-backup-safe.sh

db-restore: ## Przywróć dane z zaszyfrowanego backup
	@echo "📥 Przywracanie danych z backup..."
	@bash devops/database/db-restore.sh

db-test: ## Test restore na testowej bazie danych
	@echo "🧪 Test restore na testowej bazie..."
	@bash devops/database/db-test-restore.sh

db-cleanup: ## Wyczyść testowe bazy i tymczasowe pliki
	@echo "🧹 Czyszczenie po testach..."
	@bash devops/database/cleanup-test.sh

macos-check: ## Sprawdź kompatybilność z macOS
	@echo "🍎 Sprawdzanie kompatybilności macOS..."
	@bash devops/setup/macos-compatibility.sh

## Docker
up: ## Uruchom kontenery Docker
	@docker-compose up -d

down: ## Zatrzymaj kontenery Docker
	@docker-compose down

logs: ## Pokaż logi kontenerów
	@docker-compose logs -f

docker-dev: ## Uruchom Vite dev server w Docker z HMR przez nginx
	@echo "🚀 Uruchamianie full stack w Docker z HMR..."
	@docker-compose up nginx vite -d
	@echo "🌐 Aplikacja dostępna na: https://kayak-map.test"
	@echo "🔥 HMR działa przez nginx proxy"
	@echo "📱 Dodaj do /etc/hosts: 127.0.0.1 kayak-map.test"
	@docker-compose logs -f vite

docker-build: ## Zbuduj kontener Vite
	@docker-compose build vite

docker-logs: ## Pokaż logi Vite dev server
	@docker-compose logs -f vite

## Development
dev: ## Uruchom development serwer
	@npm run dev

serve: ## Uruchom Laravel development serwer
	@php artisan serve

## Utilities
clean: ## Wyczyść cache i temporary files
	@echo "🧹 Czyszczenie cache..."
	@php artisan cache:clear || true
	@php artisan config:clear || true
	@php artisan route:clear || true
	@php artisan view:clear || true
	@rm -rf bootstrap/cache/*.php || true
	@rm -rf storage/framework/cache/data/* || true
	@echo "✅ Cache wyczyszczony"

permissions: ## Napraw uprawnienia (Linux/Mac)
	@echo "🔧 Naprawianie uprawnień..."
	@chmod +x devops/setup/*.sh
	@chmod +x devops/database/*.sh
	@chmod -R 775 storage bootstrap/cache || true
	@echo "✅ Uprawnienia naprawione"

status: ## Pokaż status projektu
	@echo "📊 Status projektu Kayak Map"
	@echo "================================"
	@echo "Docker kontenery:"
	@docker-compose ps || echo "Docker nie działa"
	@echo ""
	@echo "Backup bazy:"
	@if [ -f "$(ENCRYPTED_BACKUP)" ]; then \
		echo "✅ Zaszyfrowany backup istnieje: $(shell du -h $(ENCRYPTED_BACKUP) | cut -f1)"; \
	else \
		echo "❌ Brak zaszyfrowanego backup"; \
	fi
	@echo ""
	@echo "Laravel:"
	@php artisan --version || echo "PHP/Laravel nie dostępne"
	@echo ""
	@echo "Storage:"
	@if [ -L "public/storage" ]; then \
		echo "✅ Storage symlink istnieje"; \
	else \
		echo "❌ Brak storage symlink (uruchom: php artisan storage:link)"; \
	fi

help: ## Pokaż tę pomoc
	@echo "Kayak Map - Dostępne komendy:"
	@echo "================================"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo "Przykłady użycia:"
	@echo "  make setup       # Pierwszy setup projektu"
	@echo "  make fresh       # Czysta reinstalacja"
	@echo "  make db-backup   # Backup bazy do repo"
	@echo "  make status      # Sprawdź status"

# Domyślna komenda
.DEFAULT_GOAL := help