# 🐳 Docker Development z Hot Reload

## Problem rozwiązany

Wcześniej `docker-compose.yml` tylko **budował** frontend. Teraz **uruchamia Vite dev server z HMR** (Hot Module Replacement) w kontenerze Docker.

## 🚀 Rozwiązanie

### **Dodany serwis `vite` do docker-compose.yml:**
- **Kontener**: `kayak-vite-dev` 
- **Porty**: `5173` (dev server), `24678` (HMR WebSocket)
- **Volume mounting**: Live sync plików z hosta
- **File watching**: `CHOKIDAR_USEPOLLING=true` dla Docker
- **Auto-reload**: Natychmiastowe odświeżanie przy zmianach

## 📋 Nowe komendy

### **NPM Scripts:**
```bash
npm run docker:up       # Uruchom wszystkie kontenery
npm run docker:down     # Zatrzymaj kontenery
npm run docker:dev      # Uruchom Vite dev z logami
npm run docker:build    # Przebuduj kontener Vite
npm run docker:logs     # Pokaż logi Vite
```

### **Makefile:**
```bash
make docker-dev         # Uruchom Vite dev server w Docker
make docker-build       # Zbuduj kontener Vite  
make docker-logs        # Logi Vite dev server
make up                 # Uruchom wszystkie kontenery
make down               # Zatrzymaj kontenery
```

## 🔥 Development workflow

### **Opcja 1: Lokalne development (jak dotychczas)**
```bash
npm run dev             # Vite lokalnie (port 5173)
php artisan serve       # Laravel lokalnie (port 8000)
```

### **Opcja 2: Docker development z HMR przez nginx**
```bash
# Uruchom full stack z HMR przez nginx proxy
npm run docker:dev
# LUB  
make docker-dev

# Dodaj do /etc/hosts (lub C:\Windows\System32\drivers\etc\hosts):
# 127.0.0.1 kayak-map.test

# Aplikacja dostępna na: https://kayak-map.test
# HMR działa przez nginx proxy /_vite/ i /vite-hmr
# Zmiany w kodzie → automatyczne odświeżanie w przeglądarce
```

### **Opcja 3: Wszystko w Docker**
```bash
docker-compose up -d    # Wszystkie serwisy
npm run docker:logs     # Obserwuj logi Vite
```

## ⚙️ Konfiguracja

### **Vite config automatycznie wykrywa Docker:**
- `DOCKER_ENV=true` → Host `0.0.0.0`, polling `true`
- `DOCKER_ENV=false` → Host `kayak-map.test`, polling `false`

### **File watching:**
- **Docker**: `CHOKIDAR_USEPOLLING=true` (wymagane dla Windows/macOS)
- **Lokalnie**: Native file system events

### **HMR WebSocket:**
- **Port**: `24678` 
- **Host**: `localhost` w Docker, `kayak-map.test` lokalnie

## 🔧 Troubleshooting

### **HMR nie działa:**
```bash
# Sprawdź czy porty są otwarte
docker-compose ps
netstat -an | grep 5173
netstat -an | grep 24678

# Przebuduj kontener
npm run docker:build
```

### **Wolne odświeżanie:**
```bash
# Sprawdź czy polling jest włączony
docker exec kayak-vite-dev env | grep CHOKIDAR_USEPOLLING

# Zmień interwał polling w vite.config.js (domyślnie 300ms)
watch: {
    usePolling: true,
    interval: 100,  // Szybsze polling
}
```

### **Problemy z SSL:**
- Docker używa **HTTP** na `localhost:5173`
- Lokalnie używa **HTTPS** na `kayak-map.test:5173`

### **Proxy API nie działa:**
```bash
# Sprawdź czy nginx kontener działa
docker-compose ps nginx

# Test przez nginx proxy
curl -k https://kayak-map.test/api/v1/trails

# Test Vite proxy
curl -k https://kayak-map.test/_vite/
```

### **Hosts file nie ustawiony:**
```bash
# Linux/Mac
echo "127.0.0.1 kayak-map.test" | sudo tee -a /etc/hosts

# Windows (jako Administrator)
echo 127.0.0.1 kayak-map.test >> C:\Windows\System32\drivers\etc\hosts
```

## 📊 Porównanie

| Funkcja | Lokalnie | Docker |
|---------|----------|---------|
| **Setup** | `npm run dev` | `npm run docker:dev` |
| **URL** | `https://kayak-map.test:5173` | `https://kayak-map.test` |
| **HMR** | ✅ Natywny | ✅ Przez nginx proxy |
| **File watching** | ✅ Native FS events | ✅ Polling |
| **SSL** | ✅ Tak | ✅ Przez nginx |
| **Performance** | 🚀 Najszybszy | ⚡ Szybki |
| **Izolacja** | ❌ Brak | ✅ Pełna |
| **Production-like** | ❌ | ✅ Identyczny z prod |

## 🎯 Zalecenia

### **Kiedy używać Docker dev:**
- Problemy z lokalnymi dependencjami
- Testowanie deployment setup
- Zespół z różnymi OS
- Chcesz izolację środowiska

### **Kiedy używać lokalnie:**
- Szybki development
- Debugging z IDE
- Najlepsza performance
- Masz stabilne lokalne środowisko

---

**🎉 Teraz masz Docker development z pełnym hot reload!**