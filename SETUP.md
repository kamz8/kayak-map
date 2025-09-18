# 🚀 Kayak Map - Quick Setup dla Developerów

Prosta instalacja dla każdego developera (nie trzeba mieć PHP ani Composer lokalnie!)

## Wymagania minimalne

- **Docker Desktop** - [pobierz tutaj](https://www.docker.com/products/docker-desktop/)
- **Node.js** (v16+) - [pobierz tutaj](https://nodejs.org/)
- **Git** - [pobierz tutaj](https://git-scm.com/)

## 🛠️ Szybka instalacja (3 kroki)

### 1. **Klonuj projekt**
```bash
git clone <repository-url>
cd kayak-map
```

### 2. **Uruchom automatyczny setup**
```bash
npm run setup
```

### 3. **Gotowe! 🎉**
Aplikacja będzie dostępna pod:
- **Frontend**: http://localhost:5173 (dev)
- **Backend**: http://localhost:8000
- **PhpMyAdmin**: http://localhost:8081

## ⚡ Inne przydatne komendy

```bash
# Świeża instalacja (czyści wszystko)
npm run fresh

# Development
npm run dev              # Frontend development server
php artisan serve        # Backend server (jeśli masz PHP)

# Backup i restore bazy danych
npm run db:backup        # Backup bazy
npm run db:restore       # Przywracanie z backup

# Docker
docker-compose up -d     # Uruchom kontenery
docker-compose down      # Zatrzymaj kontenery
docker-compose logs      # Zobacz logi
```

## 🎯 Dla developerów bez PHP/Composer

**Nie martw się!** Wszystko działa przez Docker:

- ✅ **Composer** - zainstalowany w kontenerze `kayak-app`
- ✅ **PHP** - zainstalowany w kontenerze `kayak-app`
- ✅ **Migracje** - uruchamiane automatycznie w kontenerze
- ✅ **Laravel commands** - dostępne przez `docker exec kayak-app php artisan`

### Przykłady komend w kontenerze:
```bash
# Migracje
docker exec kayak-app php artisan migrate

# Seedy
docker exec kayak-app php artisan db:seed

# Cache
docker exec kayak-app php artisan cache:clear

# Composer
docker exec kayak-app composer install
```

## 🔧 Konfiguracja

Wszystkie ustawienia są w pliku `.env`:

```env
# Database (automatycznie skonfigurowane)
DB_CONTAINER=mariadb
DB_HOST=mariadb
DB_DATABASE=kayak_map
DB_USERNAME=root
DB_PASSWORD=admin123

# URLs
APP_URL=https://kayak-map.test
VITE_DEV_SERVER_URL=https://kayak-map.test
```

## 📚 Struktura projektu

```
kayak-map/
├── devops/              # Skrypty automatyzacji
│   ├── setup/          # Setup i instalacja
│   └── database/       # Backup i restore
├── docker/             # Konfiguracja Docker
├── resources/js/       # Frontend Vue.js
├── app/               # Backend Laravel
└── database/          # Migracje i seedy
```

## 🆘 Rozwiązywanie problemów

### Docker nie odpowiada?
```bash
docker-compose down -v
npm run fresh
```

### Błędy z bazą danych?
```bash
npm run db:restore      # Przywróć dane z backup
```

### Problemy z cache?
```bash
docker exec kayak-app php artisan cache:clear
docker exec kayak-app php artisan config:clear
```

### Chcesz zacząć od zera?
```bash
npm run fresh:deep      # Usuwa też node_modules
```

## 💡 Wskazówki

1. **Pierwszy raz?** Użyj `npm run setup` - wszystko się skonfiguruje automatycznie
2. **Problemy?** Użyj `npm run fresh` - restart projektu
3. **Nowe zmiany?** Sprawdź czy `docker-compose up -d` działa
4. **Frontend development?** `npm run dev` uruchomi hot reload

---

**Pro tip**: Dodaj to repo do zakładek - będziesz tu wracać! 😉