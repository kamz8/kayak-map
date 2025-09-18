# DevOps - Kayak Map

## Zaszyfrowany Backup Bazy Danych w Repozytorium

Projekt używa zaszyfrowanego backup bazy danych znajdującego się w repozytorium dla łatwego współdzielenia danych produkcyjnych między deweloperami.

### 🔐 Bezpieczeństwo

- **Backup jest zaszyfrowany** AES-256-CBC z solą
- **Hasło domyślne**: `kayak2024!backup#secure`
- **Brak danych użytkowników** - tylko dane geograficzne i szlaki
- Niezaszyfrowane pliki SQL są **ignorowane przez Git**

### 🚀 Quick Start

```bash
# Klonowanie i setup projektu
git clone <repo-url>
cd kayak-map

# Automatyczny setup z danymi produkcyjnymi
npm run setup
# LUB
make setup
```

## Dostępne Komendy

### NPM Scripts

```bash
npm run setup        # Pełny setup projektu
npm run fresh        # Świeża instalacja
npm run fresh:deep   # Głęboka instalacja (usuwa node_modules/vendor)
npm run db:backup    # Stwórz zaszyfrowany backup
npm run db:restore   # Przywróć dane z backup
```

### Makefile

```bash
make setup          # Pełny setup projektu
make fresh          # Świeża instalacja
make fresh-deep     # Głęboka świeża instalacja
make db-backup      # Backup bazy danych
make db-restore     # Restore bazy danych
make status         # Status projektu
make help           # Pokaż wszystkie komendy
```

## Struktura Plików DevOps

```
devops/
├── database/
│   ├── db-backup.sh     # Tworzenie zaszyfrowanego backup
│   └── db-restore.sh    # Przywracanie z backup
├── setup/
│   ├── project-setup.sh # Pełny setup projektu
│   └── fresh-install.sh # Świeża instalacja
└── README.md           # Ta dokumentacja
```

## Zarządzanie Bazą Danych

### Tworzenie Backup

```bash
# Automatycznie tworzy zaszyfrowany plik w database/backups/
npm run db:backup

# Plik production_data.sql.enc może być commitowany do repo
git add database/backups/production_data.sql.enc
git commit -m "Update database backup"
```

### Przywracanie Danych

```bash
# Z zaszyfrowanego pliku w repo
npm run db:restore

# Automatycznie:
# 1. Odszyfruje backup
# 2. Zaimportuje do bazy MySQL w kontenerze  
# 3. Pokaże statystyki
```

## Workflow dla Nowego Dewelopera

1. **Klonowanie repo**
   ```bash
   git clone <repo-url>
   cd kayak-map
   ```

2. **Automatyczny setup**
   ```bash
   npm run setup
   ```
   
   To wykona:
   - ✅ Sprawdzenie wymagań (Docker, PHP, Composer, NPM)
   - ✅ Instalację dependencji (composer install, npm install)  
   - ✅ Konfigurację .env
   - ✅ Generowanie klucza Laravel
   - ✅ Uruchomienie kontenerów Docker
   - ✅ Migracje bazy danych
   - ✅ Import danych produkcyjnych z zaszyfrowanego backup
   - ✅ Cache konfiguracji
   - ✅ Build frontend

3. **Gotowe!**
   ```bash
   npm run dev          # Frontend development server
   php artisan serve    # Backend development server
   ```

## Wymagania Systemowe

- **Docker** + **Docker Compose**
- **PHP** 8.2+
- **Composer** 2.x+
- **Node.js** 14.x+ + **NPM**
- **OpenSSL** (do szyfrowania/odszyfrowywania)

### Linux/Mac
```bash
# Sprawdź uprawnienia plików
make permissions
```

### Windows
- Używaj **Git Bash** lub **WSL** do uruchamiania skryptów
- Upewnij się, że Docker Desktop działa

## Troubleshooting

### MySQL nie startuje
```bash
# Sprawdź logi
docker-compose logs mysql

# Restart kontenerów
docker-compose down
docker-compose up -d
```

### Błąd odszyfrowywania backup
```bash
# Sprawdź czy plik istnieje
ls -la database/backups/production_data.sql.enc

# Sprawdź hasło w skrypcie
grep BACKUP_PASSWORD devops/database/db-restore.sh
```

### Uprawnienia (Linux/Mac)
```bash
# Napraw uprawnienia
make permissions
# LUB
chmod +x devops/setup/*.sh
chmod +x devops/database/*.sh
```

## Konfiguracja

### Zmiana hasła backup
Edytuj zmienną `BACKUP_PASSWORD` w:
- `devops/database/db-backup.sh`
- `devops/database/db-restore.sh`

### Zmienne środowiskowe
Sprawdź `.env.example` dla wszystkich dostępnych opcji konfiguracji.

---

**💡 Tip**: Użyj `make help` aby zobaczyć wszystkie dostępne komendy!