# 🐳 DevContainer dla PhpStorm/JetBrains

## 🚀 Quick Start

1. **Zainstaluj DevContainer plugin w PhpStorm**
   - File → Settings → Plugins
   - Zainstaluj "Dev Containers" plugin

2. **Otwórz projekt w DevContainer**
   - Otwórz projekt w PhpStorm
   - Kliknij ikonę DevContainer w prawym dolnym rogu
   - Lub: View → Tool Windows → Dev Containers
   - Wybierz "Reopen in Container"

3. **Poczekaj na setup** (1-2 minuty)
   - Budowanie kontenerów
   - Instalacja dependencji
   - Konfiguracja bazy danych
   - Uruchamianie serwerów dev

4. **Gotowe!** 🎉

## 📋 Co jest skonfigurowane automatycznie

### 🗄️ **Baza danych**
- ✅ MariaDB z danymi produkcyjnymi
- ✅ PhpMyAdmin (localhost:8081)
- ✅ Migracje i seedy
- ✅ Połączenie skonfigurowane w PhpStorm Database

### 🔧 **Development Tools**
- ✅ PHP 8.3 z wszystkimi rozszerzeniami
- ✅ Composer z wszystkimi dependencjami
- ✅ Node.js + NPM z wszystkimi pakietami
- ✅ Laravel Artisan commands
- ✅ Vite dev server (hot-reload)
- ✅ Storage symlink (`public/storage` → `storage/app/public`)

### 🐛 **Debugging**
- ✅ Xdebug 3 skonfigurowany dla PhpStorm
- ✅ Server name: `kayak-map-devcontainer`
- ✅ Port 9003
- ✅ Path mappings automatyczne

### 🌐 **Porty (forwarded)**
- `3306` - MariaDB
- `5173` - Vite dev server
- `8000` - Laravel serve
- `8081` - PhpMyAdmin
- `6379` - Redis
- `80/443` - Nginx

## 🛠️ Jak używać

### **Terminal w kontenerze**
- Wszystkie komendy uruchamiaj w terminalu PhpStorm
- `php artisan` - Laravel commands
- `composer` - Composer commands
- `npm` - Node.js commands
- `./dev-helper.sh` - Helper script

### **Debugging**
1. Ustaw breakpoint w kodzie PHP
2. Uruchom debug session w PhpStorm
3. Odpal stronę w przeglądarce
4. PhpStorm zatrzyma się na breakpoint

### **Database**
- PhpStorm automatycznie połączy się z bazą
- Widok: View → Tool Windows → Database
- Możesz przeglądać tabele, uruchamiać SQL itp.

### **Hot Reload**
- Frontend: `npm run dev` - automatyczny reload zmian
- Backend: każda zmiana w PHP jest od razu widoczna

## 🔧 Konfiguracja PhpStorm

### **PHP Interpreter**
- Automatycznie skonfigurowany Docker PHP
- Ścieżka: `/usr/local/bin/php`
- Version: PHP 8.3

### **Xdebug**
- Server name: `kayak-map-devcontainer`
- Host: `host.docker.internal`
- Port: `9003`

### **Database Connection**
```
Host: mariadb
Port: 3306
Database: kayak_map
User: root
Password: admin123
```

## ⚡ Performance Tips

### **Volume Mounts**
- `vendor/` i `node_modules/` są w Docker volumes (szybsze)
- Source code jest w bind mount (zmiany od razu widoczne)

### **Rebuild Container**
```bash
# Jeśli coś nie działa, rebuild:
Ctrl+Shift+P → "Dev Containers: Rebuild Container"
```

## 🆘 Troubleshooting

### **DevContainer nie startuje?**
```bash
# Sprawdź Docker:
docker ps
docker-compose ps

# Sprawdź logi:
docker-compose logs
```

### **Baza danych nie łączy?**
```bash
# W terminalu kontenera:
php artisan db:show

# Sprawdź czy MariaDB działa:
docker exec mariadb mariadb -u root -padmin123 -e "SELECT 1"
```

### **Xdebug nie działa?**
1. Sprawdź czy Xdebug jest włączony: `php -m | grep xdebug`
2. Sprawdź konfigurację: `php --ini`
3. Restart debug session w PhpStorm
4. Sprawdź firewall (port 9003)

### **Wolne performance?**
- Zwiększ RAM dla Docker Desktop (8GB+)
- Sprawdź czy volumes są używane dla vendor/node_modules
- Restart Docker Desktop

## 🎯 Korzyści dla zespołu

### **Dla Ciebie (PHP Developer)**
- ✅ Identyczne środowisko na każdej maszynie
- ✅ Nie musisz instalować PHP/Composer lokalnie
- ✅ Xdebug działa out-of-box
- ✅ Database już skonfigurowana

### **Dla kolegi (Kotlin/Android)**
- ✅ Nie musi znać PHP/Laravel
- ✅ Jeden klik = działające środowisko
- ✅ PhpStorm wszystko skonfiguruje automatycznie
- ✅ Focus na frontend/logice biznesowej

### **Dla zespołu**
- ✅ Identyczne wersje PHP, Node.js, bazy
- ✅ Brak "works on my machine"
- ✅ Nowi developerzy ready w minuty
- ✅ CI/CD ma identyczne środowisko

---

**Pro tip**: Dodaj ten projekt do JetBrains Toolbox → Recent Projects! 🚀