<p align="center">
  <img src="https://raw.githubusercontent.com/kamz8/kayak-map/feature/api-trails/public/favicon-apple.png" width="100" alt="Kayak Map Logo">
</p>

# Kayak Map

Kayak Map is an interactive web application designed to help users explore and discover kayak trails in various regions. Built using **Vue.js (v3)**, **Vuetify**, and **Laravel (v11)**, the application offers a dynamic map interface, detailed trail information, and a seamless user experience for outdoor enthusiasts. It integrates geolocation services, trail visualization, and region-based searching, leveraging **OpenStreetMap** and **Overpass API** for geographical data.

## Features

- **Interactive Map**: Explore kayak trails on a dynamic map powered by **Leaflet.js**.
- **Trail Information**: Each trail provides detailed information, including difficulty level, scenery rating, and geographic regions.
- **Region-Based Search**: Filter and search trails based on country, state, and city.
- **Geolocation**: Locate the user's position on the map with GPS support.
- **Vuex State Management**: Centralized state management for trails, regions, and user preferences.
- **Vuetify Integration**: Beautiful and responsive UI components for a smooth user experience.
- **SEO Friendly**: The map and trail details are accessible via URL routing, making it SEO-friendly for better search engine indexing.
- **Geographic Data Storage**: Trails are linked to geographic regions (country/state/city) with spatial data stored in the database.

## Technologies

- **Frontend**: Vue.js (v3), Vuetify, Vuex, Leaflet.js, @vue-leaflet/vue-leaflet
- **Backend**: Laravel (v11), MySQL with spatial extensions
- **APIs**:
  - **OpenStreetMap/Overpass API**: For fetching rivers and geographic features.
  - **Nominatim**: For geocoding and reverse geocoding locations.
- **Database**: MySQL with support for geographic data types (`GEOMETRY`, `GEOGRAPHY`).
- **Job Queue**: Asynchronous task handling for processing large datasets like trail imports and region mapping.

## Installation

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
   cp .env.example .env
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
