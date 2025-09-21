<p align="center">
  <img src="https://raw.githubusercontent.com/kamz8/kayak-map/feature/api-trails/public/favicon-apple.png" width="100" alt="Kayak Map Logo">
</p>

# Kayak Map

Kayak Map is an interactive web application designed to help users explore and discover kayak trails in various regions. Built using **Vue.js (v3)**, **Vuetify**, and **Laravel (v11)**, the application offers a dynamic map interface, detailed trail information, and a seamless user experience for outdoor enthusiasts. It integrates geolocation services, trail visualization, and region-based searching, leveraging **OpenStreetMap** and **Overpass API** for geographical data.

## What We Aim to Achieve

We are working with a dataset of nearly 200 river trails across Poland. These are carefully prepared and verified routes. Our goal is to process GPX (GPS Exchange Format) files, allowing us to modify or add new trails based on this data. Additionally, we aim to enhance the experience by adding **key informational or warning points** along the trails to inform or alert users about hazardous conditions or helpful locations.

Our main objectives include:

- **Gathering comprehensive data** on Polish kayak trails and making it freely available to kayaking enthusiasts in Poland.
- Providing users with **safe and informed experiences** while exploring the beautiful landscapes of our rivers.
- **Processing GPX trails** for both modifying existing routes and adding new ones to our growing database.
- Adding **critical points** to warn users about obstacles, hazards, or other significant markers along their kayaking journey.
- Developing future **integration with devices** to enhance the kayaking experience, such as GPS trackers or water-level monitoring devices.

This project aspires to become the most comprehensive and user-friendly resource for exploring Polish rivers by kayak, providing detailed routes and information for outdoor enthusiasts.

## Objectives and Key Features

The main objectives and key aspects of this application are:

- :compass: **Interactive Map**: Provide users with a dynamic and interactive map experience.
- :world_map: **Region-Based Search**: Enable filtering by country, state, city, and geographic regions.
- :triangular_ruler: **Geospatial Data**: Store and process trails using geospatial data types in the database.
- :mag: **Search Optimization**: Implement URL-based routing and filters to enhance SEO performance.
- :bookmark_tabs: **Trail Information**: Present users with detailed information on each trail, including its difficulty, scenery, and region.
- :satellite: **API Integration**: Use **OpenStreetMap**, **Overpass API**, and **Nominatim** for real-time geospatial data and geocoding.
- :chart_with_upwards_trend: **Performance**: Ensure efficient data handling through asynchronous job queues for large datasets and trail imports.
- :iphone: **Mobile-First Design**: Implement a responsive design using Vuetify for a seamless experience across devices.

### Key Aspects Delivered

- :white_check_mark: Fully interactive map with leaflet integration.
- :white_check_mark: SEO-friendly URL routing for each trail and region.
- :white_check_mark: Real-time GPS location tracking.
- :white_check_mark: Integration with OpenStreetMap and Overpass API for real-time data.
- :white_check_mark: Detailed trail information, including start and end points, length, and difficulty.
- :white_check_mark: Modular Vue.js architecture with Vuex for state management.
- :white_check_mark: Geospatial data types for storing trail routes and geographic boundaries in the database.

## Technologies

- **Frontend**: Vue.js (v3), Vuetify, Vuex, Leaflet.js, @vue-leaflet/vue-leaflet
- **Backend**: Laravel (v11), MySQL with spatial extensions
- **APIs**:
  - **OpenStreetMap/Overpass API**: For fetching rivers and geographic features.
  - **Nominatim**: For geocoding and reverse geocoding locations.
- **Database**: MySQL with support for geographic data types (`GEOMETRY`, `GEOGRAPHY`).
- **Job Queue**: Asynchronous task handling for processing large datasets like trail imports and region mapping.

## 🚀 Quick Setup dla Developerów

### Wymagania minimalne
- **Docker Desktop** - [pobierz tutaj](https://www.docker.com/products/docker-desktop/)
- **Node.js** (v16+) - [pobierz tutaj](https://nodejs.org/)
- **Git** - [pobierz tutaj](https://git-scm.com/)

### ⚡ Instalacja w 3 krokach

```bash
# 1. Klonuj projekt
git clone https://github.com/yourusername/kayak-map.git
cd kayak-map

# 2. Automatyczny setup (wszystko się skonfiguruje)
npm run setup

# 3. Gotowe! 🎉
```

**Po setup aplikacja dostępna pod:**
- **Frontend**: http://localhost:5173 (development)
- **Backend**: http://localhost:8000
- **PhpMyAdmin**: http://localhost:8081 (baza danych)

### 🗄️ Baza danych - automatycznie skonfigurowana!

Setup automatycznie:
- ✅ **Uruchamia MariaDB** w kontenerze Docker
- ✅ **Tworzy wszystkie tabele** (migrate:fresh)
- ✅ **Importuje dane produkcyjne** z backup (164 szlaki, 232 regiony, 5206 punktów)
- ✅ **Konfiguruje PhpMyAdmin** do zarządzania bazą
- ✅ **Tworzy storage symlink** (`public/storage` → `storage/app/public`)

**Dane po instalacji:**
```bash
# Sprawdź status projektu i bazy
make status

# Sprawdź dane w bazie
docker exec mariadb mariadb -u root -padmin123 kayak_map -e "
SELECT COUNT(*) as trails FROM trails;
SELECT COUNT(*) as regions FROM regions;"

# Sprawdź czy storage symlink działa
ls -la public/storage  # Powinien wskazywać na ../storage/app/public
```

### 🛠️ Dla developerów bez PHP/Composer

**Nie martw się!** Wszystko działa przez Docker - nie musisz instalować PHP ani Composer lokalnie:

```bash
# Wszystkie komendy przez helper script:
./dev-helper.sh composer install          # Composer w kontenerze
./dev-helper.sh artisan make:model Post   # Laravel commands
./dev-helper.sh migrate                   # Migracje
./dev-helper.sh tinker                    # Laravel Tinker
./dev-helper.sh help                      # Zobacz wszystkie dostępne komendy
```

### 📋 Przydatne komendy

```bash
# Development
npm run dev              # Frontend hot-reload
./dev-helper.sh artisan serve  # Backend server (w kontenerze)

# Zarządzanie bazą danych
npm run db:backup        # Backup bazy danych
npm run db:restore       # Przywróć z backup
./dev-helper.sh db-fresh # Świeże migracje + dane testowe

# Docker
docker-compose up -d     # Uruchom kontenery
docker-compose down      # Zatrzymaj kontenery
docker-compose logs -f   # Zobacz logi

# Problemy? Reset wszystkiego:
npm run fresh            # Świeża instalacja (czyści kontenery + volumny)
npm run fresh:deep       # + usuwa node_modules i vendor
```

### 🆘 Rozwiązywanie problemów

| Problem | Rozwiązanie |
|---------|-------------|
| **Docker nie odpowiada?** | `docker-compose down -v && npm run fresh` |
| **Błędy z bazą danych?** | `npm run db:restore` (przywróć dane z backup) |
| **Problemy z cache?** | `./dev-helper.sh artisan cache:clear` |
| **Chcesz zacząć od zera?** | `npm run fresh:deep` (usuwa wszystko) |
| **Brakuje Composer/PHP?** | Używaj `./dev-helper.sh` - wszystko działa w kontenerze |

### 🎯 Struktura projektu po setup

```
kayak-map/
├── 🗄️  Baza danych       → MariaDB (port 3306) z danymi produkcyjnymi
├── 🐳  Docker           → Wszystkie serwisy (nginx, php, redis, mariadb)
├── 🎨  Frontend         → Vue.js + Vuetify (port 5173)
├── ⚙️   Backend          → Laravel 11 (port 8000)
├── 📊  PhpMyAdmin       → Zarządzanie bazą (port 8081)
└── 🛠️  Dev Tools        → dev-helper.sh dla wygody
```

---

## 💻 DevContainer dla PhpStorm/JetBrains

### **Idealne dla zespołów używających PhpStorm!**

1. **Zainstaluj plugin Dev Containers w PhpStorm**
2. **Otwórz projekt → kliknij "Reopen in Container"**
3. **Poczekaj 2 minuty na automatyczny setup**
4. **Gotowe!** - pełne środowisko z debuggerem

### ✅ **Co dostajecie automatycznie:**
- 🐛 **Xdebug** skonfigurowany dla PhpStorm (breakpoints działają!)
- 🗄️ **Database connection** w PhpStorm Database tool
- 🔧 **PHP Interpreter** skonfigurowany (Docker PHP 8.3)
- 🚀 **Hot reload** dla frontend (Vite) i backend
- 📦 **Wszystkie dependencje** zainstalowane automatycznie
- 🌐 **Port forwarding** - wszystkie serwisy dostępne lokalnie
- ⚡ **Launch configurations** - gotowe konfiguracje debug/artisan/npm w `.devcontainer/phpstorm-launch.xml`

### 🎯 **Perfect for your team:**
- **Developer PHP** → pełne debugging + database tools
- **Developer Kotlin** → nie musi znać PHP, wszystko gotowe w PhpStorm
- **Identyczne środowisko** na każdej maszynie
- **Zero "works on my machine"** problems

**Szczegóły**: Zobacz `.devcontainer/README.md`

---

## Instalacja Legacy (bez Docker)

<details>
<summary>Kliknij tutaj jeśli chcesz tradycyjną instalację z lokalnym PHP</summary>

### Prerequisites

- **Node.js** >= 14.x
- **Composer** >= 2.x
- **PHP** >= 8.x
- **MySQL** with spatial extensions enabled

### Clone the Repository

```bash
git clone https://github.com/yourusername/kayak-map.git
cd kayak-map
```

### Backend Setup

1. Install backend dependencies:
   ```bash
   composer install
   ```

2. Set up environment variables:
   ```bash
   cp .env.local.example .env.local
   ```

3. Generate application key:
   ```bash
   php artisan key:generate
   ```

4. Run database migrations:
   ```bash
   php artisan migrate
   ```

5. Seed the database:
   ```bash
   php artisan db:seed
   ```

6. Start the Laravel development server:
   ```bash
   php artisan serve
   ```

### Frontend Setup

1. Install frontend dependencies:
   ```bash
   npm install
   ```

2. Start the frontend development server:
   ```bash
   npm run dev
   ```

## API Endpoints

The application provides a series of API endpoints to manage and fetch trails, regions, and geographic data.

- **GET /api/v1/trails**: Fetch trails based on bounding box and filters (difficulty, scenery, etc.).
- **GET /api/v1/regions**: Retrieve regions such as countries, states, cities.
- **POST /api/v1/geocode**: Geocode addresses and locations.

For detailed API documentation, refer to the `routes/api.php` file and the built-in **Laravel API Resource Collections** used in the project.

## Architecture

### Modular Structure

The application follows a **modular structure** with separate modules for managing trails, maps, and regions. Each module is organized under the `src/modules/` directory with a dedicated state, actions, and components.

### Key Components

- **MapView.vue**: Main map interface to explore and interact with trails.
- **TrailPopup.vue**: Displays detailed information about a selected trail.
- **RiverTrack.vue**: Displays the river's path and trail route.
- **SidebarTrails.vue**: Sidebar filter and trail list view.
- **GeocodingService**: Handles geolocation and region-based trail lookups.
- **TrailSeeder**: Seeds the database with trails and connects them to geographic regions.

### State Management

Vuex is used for state management, where we handle:
- **Trails**: Fetch, filter, and manage kayak trails.
- **Regions**: Geographic region hierarchy and filtering.
- **User Preferences**: Customizable UI and map preferences.

### Database

The project leverages **spatial extensions** in MySQL to store and query geographic data. Each trail is associated with regions, and its route is stored in the form of spatial data points.

### Region Management

Regions (Country/State/City/Geographic Area) are stored in a hierarchical structure, allowing trails to belong to multiple regions. Slugs are generated for SEO-friendly URLs like `/poland/dolnoslaskie/wroclaw`.

## Contributing

We welcome contributions! Please follow the guidelines below:
- Fork the repository.
- Create a new branch (`feature/your-feature`).
- Commit your changes.
- Open a pull request with a detailed description.

## License

This project is open-source and available under the [MIT License](LICENSE).

