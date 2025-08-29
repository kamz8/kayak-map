# Kayak Map - Baza Wiedzy Projektu

## Przegląd Projektu

**Kayak Map** to interaktywna aplikacja webowa służąca do eksploracji i odkrywania szlaków kajakowych w różnych regionach. Projekt ma na celu stworzenie najbardziej kompleksowego i przyjaznego użytkownikowi źródła informacji o polskich rzekach do kajakowania.

### Główne Cele
- 📍 Gromadzenie kompleksowych danych o polskich szlakach kajakowych (blisko 200 tras)
- 🛡️ Zapewnienie bezpiecznych doświadczeń poprzez ostrzeżenia o zagrożeniach
- 🗺️ Przetwarzanie plików GPX dla modyfikacji i dodawania nowych tras
- ⚠️ Dodawanie kluczowych punktów ostrzeżenia i informacji wzdłuż szlaków
- 🔌 Przyszła integracja z urządzeniami (trackery GPS, monitoring poziomu wody)

## Architektura Techniczna

### Stack Technologiczny

#### Frontend
- **Vue.js v3** - Framework JavaScript
- **Vuetify** - Material Design framework dla Vue
- **Vuex** - Zarządzanie stanem aplikacji
- **Vue Router v4** - Routing SPA
- **Leaflet.js** + **@vue-leaflet/vue-leaflet** - Mapy interaktywne
- **Axios** - Komunikacja HTTP

#### Backend
- **Laravel v11** - Framework PHP
- **PHP 8.2+** - Język programowania
- **MySQL z rozszerzeniami przestrzennymi** - Baza danych
- **Laravel Sanctum** - Uwierzytelnianie API
- **JWT Auth** - Tokeny dostępu
- **Laravel Socialite** - Logowanie społecznościowe
- **L5 Swagger** - Dokumentacja API

#### Zewnętrzne API
- **OpenStreetMap/Overpass API** - Dane geograficzne rzek
- **Nominatim** - Geokodowanie i odwrotne geokodowanie
- **Weather Proxy** - Dane pogodowe

### Struktura Bazy Danych

#### Główne Modele

**Trail (Szlaki)**
```php
- id, river_name, trail_name, slug, description
- start_lat, start_lng, end_lat, end_lng
- trail_length, author, difficulty, scenery, rating
- difficulty_detailed (szczegółowy opis trudności)
```

**Region (Regiony)**
```php
- id, name, slug, type, parent_id, is_root
- center_point (Point), area (Polygon)
- Hierarchiczna struktura: Kraj > Województwo > Miasto > Obszar
```

**Point (Punkty na szlaku)**
```php
- id, trail_id, point_type_id, at_length
- name, description, lat, lng, order
- Punkty ostrzeżenia, informacje, przystanki
```

**River (Rzeki)**
```php
- id, name, path (geometria rzeki)
```

**RiverTrack (Ścieżki rzeczne)**
```php
- track_points (JSON) - punkty GPS tworzące trasę
```

#### Relacje
- Trail ↔ Region (many-to-many)
- Trail → RiverTrack (one-to-one)
- Trail → Section (one-to-many)
- Trail → Point (one-to-many)
- Region → Region (self-referencing hierarchy)

### Architektura Frontend

#### Struktura Modułowa
```
resources/js/modules/
├── auth/                    # Uwierzytelnianie
├── main-page/              # Strona główna
├── regions/                # Regiony i nawigacja
├── system-messages/        # Powiadomienia systemowe
└── trails/                 # Szlaki i mapa
```

#### Kluczowe Komponenty

**MapView.vue** - Główny interfejs mapy
- Integracja z Leaflet
- Wyświetlanie szlaków i punktów
- Filtry i wyszukiwanie
- Popup z detalami szlaku

**TrailPopup.vue** - Szczegółowe informacje o szlaku
- Dane podstawowe (długość, trudność, ocena)
- Punkty na trasie
- Galeria zdjęć
- Linki zewnętrzne

**SidebarTrails.vue** - Panel boczny z listą szlaków
- Filtry (trudność, ocena krajobrazu)
- Lista wyników
- Paginacja

**RegionCard.vue** - Karty regionów
- Hierarchia regionów
- Statystyki (liczba szlaków, miast)
- Obrazy główne

#### System Layoutów
```javascript
layouts: [
  { name: 'MainLayout' },      # Strona główna
  { name: 'BasicLayout' },     # Podstawowy layout
  { name: 'ExploreLayout' },   # Eksploracja map
  { name: 'AuthLayout' }       # Uwierzytelnianie
]
```

### Architektura Backend

#### Kontrolery API (V1)
- **TrailController** - Zarządzanie szlakami
- **RegionController** - Operacje na regionach
- **SearchController** - Wyszukiwanie
- **ReverseGeocodingController** - Geokodowanie
- **WeatherProxyController** - Dane pogodowe
- **GPXController** - Przetwarzanie plików GPX

#### Serwisy
- **TrailService** - Logika biznesowa szlaków
- **RegionService** - Zarządzanie regionami
- **GeocodingService** - Usługi geolokalizacji
- **SearchService** - Wyszukiwarka
- **GpxProcessor** - Przetwarzanie plików GPS

#### Zasoby API (Resources)
- **TrailResource** - Serializacja szlaków
- **RegionResource** - Dane regionów
- **NearbyTrailsCollection** - Szlaki w pobliżu
- **RecommendedTrailsCollection** - Rekomendacje

### Endpointy API

#### Główne Endpointy
```
GET /api/v1/trails              # Lista szlaków z filtrami
GET /api/v1/trails/{slug}       # Szczegóły szlaku
GET /api/v1/regions             # Lista regionów
POST /api/v1/geocoding/reverse  # Odwrotne geokodowanie
GET /api/v1/search              # Wyszukiwanie
```

#### Filtry dla szlaków
- `start_lat`, `end_lat`, `start_lng`, `end_lng` - Bounding box
- `difficulty` - Poziom trudności (łatwy, umiarkowany, trudny)
- `scenery` - Minimalna ocena krajobrazu (0-10)

### Przetwarzanie Danych

#### Pliki GPX
- Import tras z plików GPX
- Parsowanie punktów GPS
- Automatyczne tworzenie geometrii w bazie
- Zadania kolejkowe dla dużych importów

#### Geocodowanie
- Integracja z Nominatim
- Automatyczne przypisywanie szlaków do regionów
- Odwrotne geokodowanie współrzędnych

#### Zadania Asynchroniczne (Jobs)
```php
- ProcessGpxFileJob          # Przetwarzanie plików GPX
- AssociateTrailWithRegionJob # Przypisywanie do regionów  
- FetchRiverTrackJob         # Pobieranie danych rzek
- ImportTrailFileJob         # Import szlaków
```

### Deployment i DevOps

#### Docker
- **Dockerfile.vite** - Build frontend
- **Dockerfile.prod** - Wersja produkcyjna
- **docker-compose.yml** - Orchestracja kontenerów

#### Środowiska
- **Development** - `docker-compose.dev.yml`
- **Production** - `docker-compose.prod.yml`
- **Staging** - `docker-compose.staging.yml`

#### Skrypty NPM
```json
"dev": "vite",
"build": "vite build && npm run move-manifest",
"render": "vite build --config=vite.config.render.js",
"docker:dev": "docker-compose -f docker-compose.dev.yml up --build -d",
"docker:prod": "docker-compose -f docker-compose.prod.yml up --build"
```

### Uwierzytelnianie i Autoryzacja

#### System JWT
- Access token + Refresh token
- Secure HTTP-only cookies dla refresh tokenów
- Integracja z Laravel Sanctum

#### Logowanie społecznościowe  
- Google OAuth
- Facebook OAuth
- Konfiguracja przez Laravel Socialite

### Funkcje Specjalne

#### Wyszukiwanie i Filtry
- **Geospatial queries** - Wyszukiwanie w obszarze
- **Full-text search** - Nazwy szlaków i opisów  
- **Filtry trudności i ocen** - Personalizacja wyników

#### Mapy i Wizualizacja
- **Leaflet clustering** - Grupowanie punktów
- **GPX track display** - Wyświetlanie tras
- **Weather integration** - Dane pogodowe dla regionów
- **Static map generation** - Generowanie map statycznych

#### Import i Eksport
- **XML/GPX parsing** - Parsowanie tras
- **Batch imports** - Masowy import danych
- **Region association** - Automatyczne przypisywanie

### Konfiguracja Środowiska

#### Wymagania Systemowe
- Node.js >= 14.x
- Composer >= 2.x  
- PHP >= 8.2
- MySQL z rozszerzeniami przestrzennymi
- Docker + Docker Compose
- OpenSSL (do szyfrowania backupów)

#### Kompatybilność Platform
- ✅ **Linux** - Pełna obsługa (Ubuntu, CentOS, Debian)
- ✅ **macOS** - Pełna obsługa (Intel + Apple Silicon M1/M2/M3)
- ✅ **Windows** - Via Docker Desktop + WSL2

#### Quick Setup
```bash
# Klonowanie i automatyczny setup
git clone <repo-url>
cd kayak-map

# macOS: sprawdź kompatybilność (opcjonalnie)
npm run macos:check

# Automatyczny setup (wszystkie platformy)
npm run setup  # lub make setup
```

#### Główne Zależności Composer
```json
"matanyadaev/laravel-eloquent-spatial": "^4.3",
"kamz8/laravel-overpass": "0.1.0-alpha", 
"sibyx/phpgpx": "1.3.0",
"spatie/browsershot": "^4.3"
```

### SEO i Performance

#### URL Structure
- **SEO-friendly slugs** - `/poland/dolnoslaskie/wroclaw`
- **Region hierarchy** - Hierarchiczne URL regionów
- **Trail permalinks** - Stałe linki do szlaków

#### Optymalizacje
- **Database indexing** - Indeksy przestrzenne
- **Query optimization** - Optymalizacja zapytań
- **Caching layers** - Warstwy cache'owania
- **Lazy loading** - Ładowanie na żądanie

### Testy i Jakość Kodu

#### Framework Testowy
- **Pest PHP** - Nowoczesne testy PHP
- **Laravel Dusk** - Testy przeglądarki
- **Factory classes** - Generowanie danych testowych

#### Narzędzia Jakości
- **Laravel Pint** - Formatowanie kodu
- **PHPStan** - Analiza statyczna
- **Swagger/OpenAPI** - Dokumentacja API

### Dokumentacja i Zasoby

#### Dokumentacja API
- **Swagger UI** - Interaktywna dokumentacja
- **OpenAPI specs** - Specyfikacje API
- **Postman collections** - Kolekcje testowe

#### Przewodniki
- **Docs/API/** - Dokumentacja endpointów
- **Docs/Frontend/** - Przewodniki frontend
- **README.md** - Instalacja i konfiguracja

### Plany Rozwoju

#### Najbliższe Funkcje
- 📱 **Mobile apps** - Aplikacje Android/iOS
- 🌦️ **Weather integration** - Rozszerzona integracja pogody  
- 📊 **Analytics dashboard** - Panel analityczny
- 🔔 **Push notifications** - Powiadomienia push

#### Długoterminowe
- 🛰️ **Device integration** - Integracja z urządzeniami GPS
- 📈 **Advanced analytics** - Zaawansowane analizy
- 🗺️ **Offline maps** - Mapy offline
- 🌍 **Multi-country support** - Wsparcie dla innych krajów

### DevOps i Automatyzacja

#### Zaszyfrowany Backup Bazy Danych
- **Lokalizacja**: `database/backups/production_data.sql.enc`
- **Szyfrowanie**: AES-256-CBC z solą
- **Hasło**: `kayak2024!backup#secure`
- **Bezpieczeństwo**: Brak danych użytkowników, tylko dane geograficzne

#### Komendy DevOps
```bash
# NPM Scripts
npm run setup        # Pełny setup projektu z danymi produkcyjnymi
npm run fresh        # Świeża instalacja (czyszczenie cache + setup)
npm run fresh:deep   # Głęboka instalacja (usuwa node_modules/vendor)
npm run db:backup    # Tworzenie zaszyfrowanego backup (z widokami)
npm run db:restore   # Przywracanie danych z backup
npm run db:test      # Test restore na izolowanej bazie testowej
npm run db:cleanup   # Czyszczenie po testach
npm run macos:check  # Sprawdzenie kompatybilności macOS

# Makefile
make setup          # Pełny setup projektu
make fresh          # Świeża instalacja
make db-backup      # Backup bazy danych z widokami
make db-test        # Test restore na testowej bazie
make macos-check    # Kompatybilność macOS
make status         # Status projektu i kontenerów
make help           # Wszystkie dostępne komendy
```

## Użytkowanie

### Dla Deweloperów
1. **Setup lokalny**: `npm run setup` (automatyczny setup z danymi produkcyjnymi)
2. **Development**: `npm run dev` + `php artisan serve`
3. **Świeży start**: `npm run fresh` (w razie problemów)

### Dla Użytkowników
1. **Eksploracja map** - Przeglądanie interaktywnej mapy szlaków
2. **Filtry zaawansowane** - Wyszukiwanie według trudności i regionów  
3. **Szczegóły szlaków** - Punkty ostrzeżenia i informacje
4. **Planowanie tras** - Wybór optymalnych szlaków

---

*Dokumentacja aktualizowana: 29.08.2025*
*Wersja projektu: Laravel 11 + Vue 3*