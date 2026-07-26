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

**Links (Polimorficzne linki)**
```php
- id, url, meta_data (JSON)
- Linki zewnętrzne dla Trail, Section lub Region
- Relacja: polymorphic many-to-many przez linkables
```

**Linkables (Pivot table)**
```php
- id, link_id, linkable_id, linkable_type
- Polimorficzna tabela pivot dla Links
- linkable_type: 'App\Models\Trail', 'App\Models\Section', 'App\Models\Region'
```

#### Relacje
- Trail ↔ Region (many-to-many przez trail_region)
- Trail → RiverTrack (one-to-one)
- Trail → Section (one-to-many)
- Trail → Point (one-to-many)
- Trail ↔ Link (polymorphic many-to-many przez linkables)
- Section ↔ Link (polymorphic many-to-many przez linkables)
- Region ↔ Link (polymorphic many-to-many przez linkables)
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
- **LinkController** - Zarządzanie linkami (Dashboard)

#### Serwisy
- **TrailService** - Logika biznesowa szlaków
- **RegionService** - Zarządzanie regionami
- **GeocodingService** - Usługi geolokalizacji
- **SearchService** - Wyszukiwarka
- **GpxProcessor** - Przetwarzanie plików GPS
- **LinkService** - Uniwersalny serwis dla linków (Trail/Section/Region)

#### Zasoby API (Resources)
- **TrailResource** - Serializacja szlaków
- **RegionResource** - Dane regionów
- **LinkResource** - Serializacja linków z parsowaniem meta_data
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

#### Dashboard API - Links Management
```
# Trail Links
GET    /api/v1/dashboard/trails/{id}/links              # Lista linków dla szlaku
POST   /api/v1/dashboard/trails/{id}/links              # Dodaj link do szlaku
PUT    /api/v1/dashboard/trails/{id}/links/{linkId}     # Aktualizuj link szlaku
DELETE /api/v1/dashboard/trails/{id}/links/{linkId}     # Usuń link szlaku

# Section Links
GET    /api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links              # Lista linków dla sekcji
POST   /api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links              # Dodaj link do sekcji
PUT    /api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links/{linkId}     # Aktualizuj link sekcji
DELETE /api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links/{linkId}     # Usuń link sekcji
```

**Cechy Links API:**
- ✅ **Polimorficzne relacje** - Link może należeć do wielu Trail/Section
- ✅ **Optymalizacja query** - Selective column fetching (tylko potrzebne kolumny)
- ✅ **Walidacja przynależności** - Link musi należeć do danego Trail/Section
- ✅ **Meta data parsing** - Automatyczne parsowanie JSON meta_data
- ✅ **Performance tested** - < 15 queries dla 100 linków, < 1000ms

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

## 🎨 **Frontend Coding Standards - Dashboard UI Kit**

### ⚠️ **WAŻNE: ZAWSZE używaj komponentów z UI Kit**

Podczas kodowania Dashboard **ZAWSZE** używaj komponentów z `resources/js/dashboard/components/ui/`. **NIE** używaj surowych komponentów Vuetify bezpośrednio.

### **Architektura UI**
```
resources/js/dashboard/
├── design-system/
│   ├── tokens.js          # Design tokens (kolory, spacing, variants)
│   ├── styles.css         # Global UI styles
│   └── theme/
│       └── vuetify.js     # Vuetify theme config
├── components/ui/
│   ├── UiButton.vue       # ✅ NOWE komponenty (shadcn/ui style)
│   ├── UiCard.vue
│   ├── UiInput.vue
│   ├── UiBadge.vue
│   ├── UiDataTable.vue
│   ├── DataTable.vue      # Legacy (compatibility)
│   ├── FormField.vue
│   ├── StatsCard.vue
│   ├── ConfirmDialog.vue
│   └── index.js           # Exports
```

### **Komponenty UI Kit - Przykłady Użycia**

#### 1. UiButton (PRIORYTET: Zawsze używaj zamiast v-btn)
```vue
<!-- ✅ DOBRZE -->
<UiButton variant="default" size="sm">Zapisz</UiButton>
<UiButton variant="destructive" @click="deleteItem">Usuń</UiButton>
<UiButton variant="outline">Anuluj</UiButton>
<UiButton variant="ghost">Opcje</UiButton>

<!-- ❌ ŹLE - nie używaj bezpośrednio -->
<v-btn color="primary">Zapisz</v-btn>
```

**Dostępne varianty**: `default`, `destructive`, `outline`, `secondary`, `ghost`, `link`
**Dostępne rozmiary**: `sm`, `default`, `lg`, `icon`

#### 2. UiCard (zamiast v-card)
```vue
<!-- ✅ DOBRZE -->
<UiCard title="Szczegóły Trasy" variant="elevated">
  <template #subtitle>Informacje podstawowe</template>
  <p>Zawartość karty...</p>
  <template #actions>
    <UiButton variant="default">Edytuj</UiButton>
  </template>
</UiCard>

<!-- ❌ ŹLE -->
<v-card>
  <v-card-title>Szczegóły Trasy</v-card-title>
  ...
</v-card>
```

#### 3. UiInput (zamiast v-text-field)
```vue
<!-- ✅ DOBRZE -->
<UiInput
  v-model="trail.name"
  placeholder="Nazwa szlaku"
  :error-message="errors.name"
/>

<!-- ❌ ŹLE -->
<v-text-field
  v-model="trail.name"
  label="Nazwa szlaku"
/>
```

#### 4. UiBadge (statusy, etykiety)
```vue
<!-- ✅ DOBRZE -->
<UiBadge variant="success">Aktywny</UiBadge>
<UiBadge variant="destructive">Błąd</UiBadge>
<UiBadge variant="warning">Ostrzeżenie</UiBadge>

<!-- ❌ ŹLE -->
<v-chip color="success">Aktywny</v-chip>
```

#### 5. UiDataTable (dla tabel CRUD)
```vue
<!-- ✅ DOBRZE -->
<UiDataTable
  title="Lista Tras"
  :headers="headers"
  :items="trails"
  :actions="{ view: true, edit: true, delete: true }"
  @edit="handleEdit"
  @delete="handleDelete"
>
  <template #actions>
    <UiButton variant="default" size="sm">
      <v-icon start>mdi-plus</v-icon>
      Dodaj Trasę
    </UiButton>
  </template>

  <template #item.status="{ value }">
    <UiBadge :variant="getStatusVariant(value)">
      {{ value }}
    </UiBadge>
  </template>
</UiDataTable>
```

### **Import Pattern**

```vue
<script>
// Importuj komponenty UI
import { UiButton, UiCard, UiInput, UiBadge, UiDataTable } from '@/dashboard/components/ui'

export default {
  name: 'TrailsManagement',
  components: {
    UiButton,
    UiCard,
    UiInput,
    UiBadge,
    UiDataTable
  },
  // ...
}
</script>
```

### **Design Tokens - Spójna Stylizacja**

```javascript
// Użyj design tokens dla custom stylów
import { designTokens } from '@/dashboard/design-system/tokens'

// Dostęp do wartości
const primaryColor = designTokens.colors.primary
const spacing = designTokens.spacing[4]
const buttonProps = designTokens.variants.button.destructive
```

### **Wzorce Kodowania**

#### Nazewnictwo
- **Komponenty**: PascalCase (`UiButton`, `TrailsList`)
- **Props**: camelCase (`modelValue`, `errorMessage`)
- **Events**: kebab-case (`@update:model-value`)
- **Slots**: kebab-case (`#actions`, `#item.status`)

#### Props Validation
```vue
<script>
export default {
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'outline', 'ghost'].includes(value)
    },
    size: {
      type: String,
      default: 'default',
      validator: (value) => ['sm', 'default', 'lg'].includes(value)
    }
  }
}
</script>
```

#### Emits Validation
```vue
<script>
export default {
  emits: {
    'update:modelValue': (value) => value !== undefined,
    'submit': (data) => data && typeof data === 'object'
  }
}
</script>
```

### **Accessibility (A11y)**
- Wszystkie komponenty UI mają odpowiednie **ARIA attributes**
- **Keyboard navigation** support out-of-the-box
- **Screen reader** compatibility
- Automatyczne **focus management**

### **Performance Best Practices**
- Używaj `computed` z cache dla danych transformacji
- `v-memo` dla dużych list
- Lazy loading dla heavy components
- Debounced search inputs (automatycznie w UiDataTable)

### **Pełna Dokumentacja**

Szczegółowa dokumentacja wszystkich komponentów:
- **Lokalizacja**: `resources/js/dashboard/components/ui/README.md`
- **Design Tokens**: `resources/js/dashboard/design-system/tokens.js`
- **Przykłady**: Zobacz istniejące widoki w `resources/js/dashboard/views/trails/`

### **Checklist przed PR**
- [ ] Używam komponentów UI Kit zamiast surowych Vuetify
- [ ] Props są poprawnie walidowane
- [ ] Emits są zdefiniowane z walidacją
- [ ] Komponenty są responsywne
- [ ] Accessibility attributes są dodane
- [ ] Stylowanie zgodne z design tokens
- [ ] Nazewnictwo zgodne z konwencją

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
- **Authentication** - JWT + OAuth (Google/Facebook) + **RFC 6749 Refresh Token Flow**
- **Dashboard Panel** - Separate SPA dla administracji
- **Dashboard UI Kit** - Komponenty UI w stylu shadcn/ui (UiButton, UiCard, UiInput, UiBadge, UiDataTable)
- **Links API** - Polimorficzny system zarządzania linkami (Trail/Section/Region)
- **Docker Setup** - Multi-container development environment

## 🔐 **OAuth 2.0 Refresh Token System (RFC 6749)**

### **Status: ✅ PRODUCTION READY**

Zaimplementowany kompletny system refresh tokenów zgodny ze standardem RFC 6749 OAuth 2.0.

#### **Backend Implementation**

**AuthService** - RFC 6749 Compliant:
```php
// Dual token generation
public function login(array $credentials): array
{
    $tokens = $this->generateTokens($user);
    return [
        'access_token' => $tokens['access_token'],    // Short TTL + full claims
        'refresh_token' => $tokens['refresh_token'],  // Long TTL + minimal claims
        'token_type' => 'Bearer',
        'expires_in' => config('jwt.ttl') * 60
    ];
}

// Fresh ACL on refresh
public function refresh(string $refreshToken): array
{
    $user = User::with('roles.permissions')->findOrFail($userId);
    $tokens = $this->generateTokens($user); // Fresh permissions
    return $tokens;
}
```

**Token Structure**:
- **Access Token**: Full user claims (roles, permissions, profile)
- **Refresh Token**: Minimal claims (user_id, token_type)
- **Token Rotation**: New refresh token on each refresh
- **Security**: Fresh ACL data, user status validation

#### **Frontend Implementation**

**TokenManager** - Automatic Token Management:
```javascript
class TokenManager {
  // Proactive refresh (5 minutes before expiry)
  startRefreshTimer() {
    const timeToRefresh = timeToExpire - this.REFRESH_THRESHOLD
    this.refreshTimer = setTimeout(() => this.refreshTokens(), timeToRefresh)
  }

  // Reactive refresh (on 401 errors with retry)
  async handleResponse(error) {
    if (error.response?.status === 401 && !originalRequest._retry) {
      const result = await this.refreshTokens()
      originalRequest.headers.Authorization = `Bearer ${result.access_token}`
      return axios(originalRequest) // Retry with new token
    }
  }

  // Request queueing during refresh
  addToQueue(resolve, reject) {
    this.failedQueue.push({ resolve, reject })
  }
}
```

**Axios Integration**:
- **Request Interceptor**: Auto-refresh before expiry
- **Response Interceptor**: Handle 401 with automatic retry
- **Queue Management**: Multiple concurrent requests during refresh

**Vuex Integration**:
```javascript
// Store dual tokens
const state = () => ({
  token: localStorage.getItem('token') || null,           // Access token
  refreshToken: localStorage.getItem('refresh_token') || null, // Refresh token
  user: null
})

// Automatic initialization with refresh fallback
async initialize({ commit, dispatch, state }) {
  if (!JwtUtils.isValid(accessToken) && refreshToken) {
    await dispatch('refreshToken') // Auto-refresh on startup
  }
}
```

#### **Security Features**

1. **Token Rotation** - New refresh token on each refresh (prevents replay attacks)
2. **Fresh ACL** - Permissions/roles reloaded on refresh (handles permission changes)
3. **Minimal Claims** - Refresh tokens contain only essential data
4. **Automatic Cleanup** - Invalid tokens immediately cleared
5. **Queue Protection** - Prevents multiple concurrent refresh attempts
6. **User Status Check** - Validates user is still active on refresh

#### **OAuth 2.0 Flow Implementation**

```
┌─────────────┐                                 ┌──────────────────┐
│   Client    │                                 │ Authorization    │
│ (Dashboard) │                                 │     Server       │
│             │                                 │   (Laravel)      │
└─────────────┘                                 └──────────────────┘
      │                                                    │
      │ (A) Authorization Grant (login credentials)        │
      │───────────────────────────────────────────────────>│
      │                                                    │
      │ (B) Access Token + Refresh Token                   │
      │<───────────────────────────────────────────────────│
      │                                                    │
      │ (C) API Request + Access Token                     │
      │───────────────────────────────────────────────────>│
      │                                                    │
      │ (D) Protected Resource                             │
      │<───────────────────────────────────────────────────│
      │                                                    │
      │ (E) API Request + Expired Access Token             │
      │───────────────────────────────────────────────────>│
      │                                                    │
      │ (F) 401 Unauthorized                               │
      │<───────────────────────────────────────────────────│
      │                                                    │
      │ (G) Refresh Token Request                          │
      │───────────────────────────────────────────────────>│
      │                                                    │
      │ (H) New Access Token + New Refresh Token           │
      │<───────────────────────────────────────────────────│
```

#### **Automatic Behaviors**

- **5-Minute Rule**: Token refresh 5 minutes before expiry
- **401 Retry**: Automatic refresh and retry on unauthorized errors
- **Startup Refresh**: Auto-refresh expired tokens on app initialization
- **Background Timer**: Proactive refresh runs in background
- **Queue Management**: Failed requests queued during refresh process
- **Graceful Fallback**: Redirect to login only after refresh fails

#### **Configuration**

```php
// config/jwt.php
'ttl' => 60,                    // Access token: 1 hour
'refresh_ttl' => 20160,         // Refresh token: 2 weeks
'refresh_token_rotation' => true // Rotate refresh tokens
```

```javascript
// Frontend configuration
const REFRESH_THRESHOLD = 5 * 60 * 1000 // 5 minutes before expiry
```

**System jest w pełni zgodny z RFC 6749 OAuth 2.0 i automatycznie zarządza tokenami bez ingerencji użytkownika.** 🚀

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

*Dokumentacja aktualizowana: 26.11.2025*
*Wersja projektu: Laravel 11 + Vue 3*
*Dashboard Status: **PRODUCTION READY** ✅*
*Dashboard UI Kit: **shadcn/ui STYLE** ✅*
*Links API: **POLYMORPHIC + OPTIMIZED** ✅*
*OAuth 2.0 Refresh Token: **RFC 6749 COMPLIANT** ✅*

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.13
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/socialite (SOCIALITE) - v5
- laravel/dusk (DUSK) - v8
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v2
- phpunit/phpunit (PHPUNIT) - v10
- vue (VUE) - v3

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version-specific documentation.
- Laravel 11 brought a new streamlined file structure which this project now uses.

### Laravel 11 Structure
- In Laravel 11, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands
- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>
</laravel-boost-guidelines>
