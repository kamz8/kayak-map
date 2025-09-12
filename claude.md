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
- id, name, path (geometria rzeki - GEOGRAPHY LINESTRING)
```

**RiverTrack (Ścieżki rzeczne)**
```php
- id, trail_id, track_points (JSON)
- Punkty GPS tworzące trasę w formacie [{"lat": 50.0, "lng": 19.0}]
```

**Section (Sekcje szlaków)**
```php
- id, trail_id, name, description
- polygon_coordinates (JSON), scenery
- Sekcje dzielą szlak na mniejsze odcinki
```

#### System Użytkowników

**Users (Użytkownicy)**
```php
- id, first_name, last_name, name, email (unique)
- password (nullable - dla OAuth), email_verified_at
- phone (unique, nullable), phone_verified
- bio, location, birth_date, gender
- preferences (JSON), notification_settings (JSON)  
- is_active, is_admin, last_login_at, last_login_ip
- remember_token, deleted_at (soft delete)
```

**Social Accounts (OAuth)**
```php
- id, user_id, provider, provider_id
- provider_token, provider_refresh_token, token_expires_at
- provider_nickname
- Unique index: [provider, provider_id]
```

**Verification Codes**
```php
- id, user_id, code, type, used, expires_at
- Typy: email (32 znaki, 24h), phone (6 cyfr, 10min)
- 2fa (6 cyfr, 5min), action (6 cyfr, 10min)
```

**User Devices**
```php
- id, user_id, device_id (unique), device_type
- device_name, push_token, last_used_at
- Typy: ios/android/web
```

#### Media i Powiązania

**Images**
```php
- id, path, created_at, updated_at
```

**Imageables (Polimorficzne relacje)**
```php
- id, image_id, imageable_id, imageable_type
- is_main, order
- Powiązania: Trail → Images, Section → Images
```

**Links**
```php  
- id, section_id, url, meta_data (JSON)
- Linki zewnętrzne dla sekcji
```

#### Relacje
- Trail ↔ Region (many-to-many przez trail_region)
- Trail → RiverTrack (one-to-one)
- Trail → Section (one-to-many)
- Trail → Point (one-to-many)
- Region → Region (self-referencing hierarchy)
- User → SocialAccount (one-to-many)
- User → VerificationCode (one-to-many)
- User → UserDevice (one-to-many)
- Trail/Section → Images (polymorphic many-to-many)

### Architektura Frontend

#### Struktura Modułowa - Main App
```
resources/js/modules/
├── auth/                    # Uwierzytelnianie JWT
├── main-page/              # Strona główna
├── regions/                # Regiony i nawigacja
├── system-messages/        # Powiadomienia systemowe
└── trails/                 # Szlaki i mapa
```

#### Dashboard SPA - Oddzielna Aplikacja
```
resources/js/modules/dashboard/
├── main.js                 # Vite entry point
├── App.vue                 # Root component  
├── components/
│   ├── layout/            # DashboardLayout, Sidebar, TopBar
│   └── ui/                # DataTable, FormField, StatsCard, ConfirmDialog
├── views/
│   ├── auth/              # LoginView.vue
│   ├── dashboard/         # Overview.vue
│   └── trails/            # TrailsList.vue, TrailsCreate.vue
├── store/                 # Separate Vuex store (auth + ui modules)
├── router/                # Vue Router config
├── plugins/               # Vuetify + Axios configuration
└── styles/                # Dashboard-specific styles
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

#### Cache System (Vue Plugin)
- **TTL Cache** - Cache z czasem wygaśnięcia
- **Tag-based Cache** - Grupowanie cache po tagach
- **Laravel-style API** - `remember()`, `setCacheWithTTL()`, `getCacheWithTTL()`
- **Auto-cleanup** - Automatyczne usuwanie przeterminowanych danych
- **LocalStorage backend** - Persistentne przechowywanie w przeglądarce

```javascript
// Przykład użycia cache plugin
this.$cache.remember('trails-data', 3600, async () => {
  const response = await axios.get('/api/v1/trails');
  return response.data;
}, ['trails', 'api']);
```

#### System Wiadomości
- **Global helpers** - `$alertInfo()`, `$alertWarning()`, `$alertError()`
- **Auto-timeout** - Automatyczne ukrywanie po 3 sekundach
- **Vuex integration** - Centralne zarządzanie komunikatami
- **Multiple types** - Info, Warning, Error messages

#### Multi-Entry Vite Build
- **Main App** - `resources/js/app.js` 
- **Dashboard SPA** - `resources/js/modules/dashboard/main.js`
- **Separate bundles** - Niezależne aplikacje z własnym cache
- **Hot Module Replacement** - HMR dla obu aplikacji
- **Shared dependencies** - Wspólne biblioteki (Vue, Vuetify)

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

#### Kluczowe Zależności NPM
```json
"vue": "^3.4.0",
"vuetify": "^3.6.13", 
"vuex": "^4.1.0",
"vue-router": "^4.3.0",
"leaflet": "^1.9.0",
"@vue-leaflet/vue-leaflet": "^0.10.0",
"axios": "^1.6.0",
"vite": "^5.0.0"
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

## Dashboard Administration Panel

### ✅ **Status: GOTOWY DO UŻYTKU**

Dashboard to w pełni funkcjonalna aplikacja SPA zintegrowana z głównym projektem:

#### **Funkcje Dashboard**
- **Admin Authentication** - Separate login system z JWT
- **Trails Management** - CRUD operations dla szlaków
- **Users Management** - Zarządzanie użytkownikami (planowane)
- **Analytics Overview** - Statystyki i metryki
- **System Settings** - Konfiguracja aplikacji

#### **Dostęp do Dashboard**
- **URL**: `https://kayak-map.test/dashboard`
- **Login Page**: `https://kayak-map.test/dashboard/login`
- **Development**: `npm run dev` → Dashboard dostępny natychmiast
- **Production**: `npm run build` → Separate bundle dla dashboard

#### **Vite Configuration**
```javascript
// vite.config.js - Multi-entry setup
input: [
  'resources/css/app.css',
  'resources/js/app.js',                          // Main app
  'resources/js/modules/dashboard/main.js'        // Dashboard SPA
]
```

#### **Laravel Routes**
```php
// routes/web.php
Route::get('/dashboard/{any?}', function () {
    return view('dashboard');
})->where('any', '.*');
```

#### **UI Components (shadcn-vue style)**
- **DataTable** - Advanced CRUD tables z paginacją/sortowaniem
- **FormField** - Universal form fields z validation
- **StatsCard** - Dashboard metrics cards
- **ConfirmDialog** - Action confirmations

### **Admin User Setup**
```bash
# Automatyczne utworzenie admin user
php artisan db:seed AdminUserSeeder

# Sprawdzenie admin users
php artisan check:admin-user
```

## Użytkowanie

### Dla Deweloperów
1. **Setup lokalny**: `npm run setup` (automatyczny setup z danymi produkcyjnymi)
2. **Development**: `npm run dev` (uruchamia oba: main app + dashboard)
3. **Świeży start**: `npm run fresh` (w razie problemów)
4. **Dashboard dev**: Navigate to `http://localhost/dashboard`

### Dla Administratorów
1. **Dashboard Login**: `/dashboard/login`
2. **Trails Management**: CRUD operations na szlakach
3. **User Management**: Zarządzanie użytkownikami
4. **Analytics**: Overview statystyk i metryk
5. **System Settings**: Konfiguracja aplikacji

### Dla Użytkowników
1. **Eksploracja map** - Przeglądanie interaktywnej mapy szlaków
2. **Filtry zaawansowane** - Wyszukiwanie według trudności i regionów  
3. **Szczegóły szlaków** - Punkty ostrzeżenia i informacje
4. **Planowanie tras** - Wybór optymalnych szlaków

## Status Projektu

### ✅ **Główne Komponenty - UKOŃCZONE**
- **Backend API** - Laravel 11 z spatial extensions
- **Frontend SPA** - Vue 3 + Vuetify z Leaflet maps
- **Database** - MySQL z pełną strukturą spatial
- **Authentication** - JWT + OAuth (Google/Facebook)
- **Dashboard Panel** - Separate SPA dla administracji
- **Docker Setup** - Multi-container development environment

### 🚧 **W Trakcie Rozwoju**
- **Mobile Apps** - React Native/Flutter (planowane)
- **Advanced Analytics** - Dashboard charts i raporty
- **Real-time Features** - WebSocket notifications
- **Offline Maps** - Progressive Web App features

### 🔮 **Przyszłe Funkcje**
- **Device Integration** - GPS trackers, water level monitoring
- **Multi-country Support** - Expansion beyond Poland
- **Advanced ML** - Trail recommendations, difficulty prediction
- **Community Features** - User reviews, trail sharing

---

*Dokumentacja aktualizowana: 12.09.2025*  
*Wersja projektu: Laravel 11 + Vue 3*  
*Dashboard Status: **PRODUCTION READY** ✅*