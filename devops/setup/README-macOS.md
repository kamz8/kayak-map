# 🍎 Kayak Map - Setup na macOS

## Kompatybilność z macOS

**TAK! System uruchomi się na MacBook** - zarówno na Intel jak i Apple Silicon (M1/M2/M3).

## 🚀 Quick Start dla macOS

```bash
# 1. Sprawdź kompatybilność systemu
npm run macos:check
# lub
make macos-check

# 2. Automatyczny setup (identyczny jak na innych systemach)
npm run setup
```

## 📋 Wymagania dla macOS

### Obowiązkowe:
- **Docker Desktop for Mac** - [Download](https://desktop.docker.com/mac/main/amd64/Docker.dmg)
- **PHP 8.2+** - `brew install php@8.2`
- **Composer** - `brew install composer` 
- **Node.js & NPM** - `brew install node`

### Opcjonalne (ale zalecane):
- **Homebrew** - `/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"`
- **OpenSSL** - `brew install openssl` (dla lepszej kompatybilności szyfrowania)

## 🔧 Różnice macOS vs Linux

### ✅ **Co działa identycznie:**
- Docker Compose
- Wszystkie komendy NPM i Make  
- Zaszyfrowane backupy bazy danych
- Laravel i Vue.js development
- Szyfrowanie/odszyfrowywanie OpenSSL

### ⚙️ **Automatyczne dostosowania:**
- **BSD date** vs GNU date - skrypty wykrywają i dostosowują się automatycznie
- **BSD sed/awk** vs GNU - używamy kompatybilnych składni
- **Uprawnienia plików** - automatyczne `chmod +x` w setup
- **Docker timeouts** - dłuższe czekanie na uruchomienie kontenerów

### 📱 **Apple Silicon (M1/M2/M3):**
- Docker używa natywnych obrazów ARM64 gdzie dostępne
- MariaDB, Redis, PHP działają natywnie na Apple Silicon
- Lepsze performance niż emulacja x86

## 🛠️ Instalacja krok po kroku

### 1. **Homebrew** (jeśli nie masz)
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### 2. **Docker Desktop**
- Pobierz: [Docker Desktop for Mac](https://www.docker.com/products/docker-desktop)
- Zainstaluj i uruchom aplikację
- Sprawdź: `docker --version && docker-compose --version`

### 3. **PHP, Composer, Node.js**
```bash
# PHP 8.2+
brew install php@8.2
echo 'export PATH="/opt/homebrew/opt/php@8.2/bin:$PATH"' >> ~/.zshrc

# Composer
brew install composer

# Node.js & NPM
brew install node

# OpenSSL (opcjonalnie)
brew install openssl
echo 'export PATH="/opt/homebrew/opt/openssl@3/bin:$PATH"' >> ~/.zshrc
```

### 4. **Projekt Kayak Map**
```bash
# Klonowanie
git clone <repo-url>
cd kayak-map

# Sprawdź kompatybilność
npm run macos:check

# Automatyczny setup
npm run setup

# Development
npm run dev          # Frontend (Vite)
php artisan serve    # Backend (Laravel)
```

## 🐳 Docker na macOS

### **Zalecane ustawienia Docker Desktop:**
- **Memory**: 4GB+ (dla Apple Silicon: 6GB+)
- **CPU**: 4+ cores
- **Disk**: 20GB+ free space
- **File sharing**: Włączone dla projektów

### **Apple Silicon specyficzne:**
```bash
# Sprawdź architekturę
uname -m  # arm64 = Apple Silicon, x86_64 = Intel

# Docker info
docker system info | grep Architecture
```

## ⚡ Performance na macOS

### **Apple Silicon (M1/M2/M3):**
- 🚀 **Natywna performance** - Docker działa w natywnej architekturze ARM64
- 🔥 **Szybsze buildy** - Vite i Composer korzystają z natywnego Silicon
- 💾 **Lepsza efektywność energetyczna**

### **Intel Mac:**
- ✅ **Pełna kompatybilność** - wszystko działa standardowo
- 🐳 **Docker przez wirtualizację** - może być nieco wolniejszy

## 🔍 Troubleshooting macOS

### **"Permission denied" przy skryptach bash:**
```bash
chmod +x devops/setup/*.sh
chmod +x devops/database/*.sh
# lub
make permissions
```

### **Docker nie startuje:**
```bash
# Sprawdź czy Docker Desktop działa
docker info

# Restart Docker Desktop przez GUI lub:
pkill Docker && open /Applications/Docker.app
```

### **PHP version conflicts:**
```bash
# Sprawdź aktywną wersję
php -v

# Przełącz na PHP 8.2
brew link --force php@8.2

# Dodaj do PATH w ~/.zshrc
echo 'export PATH="/opt/homebrew/opt/php@8.2/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

### **Port conflicts (3306, 80, etc.):**
```bash
# Sprawdź co używa portu
lsof -i :3306
lsof -i :80

# Kill process jeśli potrzeba
sudo kill -9 <PID>
```

### **OpenSSL/LibreSSL problems:**
```bash
# Sprawdź wersję
openssl version

# Jeśli używasz LibreSSL (domyślny macOS), zainstaluj OpenSSL
brew install openssl
export PATH="/opt/homebrew/opt/openssl@3/bin:$PATH"

# Test szyfrowania
echo "test" | openssl enc -aes-256-cbc -k "test" | openssl enc -aes-256-cbc -d -k "test"
```

## 🎯 macOS Specific Tips

### **Terminal zalecany:**
- **iTerm2** - lepsze niż domyślny Terminal.app
- **Oh My Zsh** - dla lepszego doświadczenia CLI

### **File system:**
- macOS używa **APFS/HFS+** - case-insensitive domyślnie
- Pliki `.env` i uprawnienia mogą się różnić od Linuxa

### **Homebrew paths:**
- **Intel Mac**: `/usr/local/`
- **Apple Silicon**: `/opt/homebrew/`

### **Memory management:**
- Docker Desktop może zużywać dużo RAM-u
- Zamykaj kontenery gdy nie używasz: `docker-compose down`

## ✅ Validation checklist

Po instalacji sprawdź:

```bash
# System
npm run macos:check

# Docker
docker --version
docker-compose --version  
docker info

# PHP & Composer
php -v                    # 8.2+
composer --version

# Node.js
node -v                   # 14+
npm -v

# Projekt
npm run setup             # Powinien przejść bez błędów
npm run dev              # Frontend development server
php artisan serve        # Backend development server
```

---

**🎉 macOS jest w pełni obsługiwany! Projekt działa identycznie jak na Linuxie.**