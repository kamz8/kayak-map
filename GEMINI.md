# GEMINI.md - Kayak Map Project Knowledge Base

This document provides a comprehensive overview of the Kayak Map project, its technical stack, architecture, and development conventions to be used as instructional context for future interactions.

## 1. Project Overview

**Kayak Map** is a full-stack, interactive web application for exploring and discovering kayak trails, with an initial focus on Poland. The project aims to be the most comprehensive and user-friendly resource for kayakers, providing detailed route data, hazard warnings, and a rich map interface.

### Core Objectives
- **Data Aggregation:** Collect and manage a large dataset of kayak trails (initially ~200 in Poland).
- **Safety:** Provide users with safety information and warnings about on-trail hazards or points of interest.
- **GPX Processing:** Process GPX files to add or modify trail data.
- **Future Integrations:** Plan for future integration with devices like GPS trackers and water-level monitors.

---

## 2. Technical Architecture

### 2.1. Technology Stack

*   **Backend:**
    *   **Framework:** Laravel 11 (PHP 8.2+)
    *   **Database:** MySQL/MariaDB with Spatial Extensions
    *   **Authentication:** Laravel Sanctum, JWT (`php-open-source-saver/jwt-auth`), Laravel Socialite (Google, Facebook)
    *   **API Documentation:** Swagger (`l5-swagger`)
    *   **Geospatial:** `matanyadaev/laravel-eloquent-spatial`, `kamz8/laravel-overpass`
    *   **File Processing:** `sibyx/phpgpx` for GPX parsing.

*   **Frontend:**
    *   **Framework:** Vue.js 3
    *   **UI:** Vuetify
    *   **State Management:** Vuex
    *   **Routing:** Vue Router 4
    *   **Maps:** Leaflet.js (`@vue-leaflet/vue-leaflet`)
    *   **HTTP Client:** Axios
    *   **Build Tool:** Vite (with multi-entry config for main app and dashboard)

*   **Development & DevOps:**
    *   **Containerization:** Docker & Docker Compose
    *   **Environments:** Separate configurations for development, staging, and production.
    *   **CI/CD:** Git workflow defined, with plans for automation.
    *   **Backups:** Scripts for creating and restoring encrypted database backups.

*   **External APIs:**
    *   **Geographic Data:** OpenStreetMap / Overpass API
    *   **Geocoding:** Nominatim
    *   **Weather:** A weather proxy service is integrated.

### 2.2. Building and Running the Project

The project is designed to be run within a Docker environment.

*   **First-Time Setup:** The `npm run setup` command is the primary entry point. It automates dependency installation, Docker environment startup, and database seeding with production data.
*   **Development Servers:**
    *   `docker-compose up -d` starts all necessary services (Nginx, PHP, Vite, DB, Redis).
    *   The main application is accessible via a local domain (e.g., `https://kayak-map.test`).
    *   The Vite HMR server runs within a container.
*   **Backend Commands:** Laravel Artisan commands should be executed inside the `app` container (e.g., `docker-compose exec app php artisan <command>`).
*   **Testing:**
    *   **Backend:** Pest for unit/feature tests (`composer test`).
    *   **Frontend:** Vitest for unit tests (`npm test`).

---

## 3. Database Schema

The database relies heavily on MySQL's spatial data types (`POINT`, `POLYGON`, `LINESTRING`) for geospatial queries.

### Key Models:
*   **`trails`**: The core model for kayak trails, including names, descriptions, length, difficulty, ratings, and start/end coordinates.
*   **`regions`**: A hierarchical structure for geographical areas (Country > State > City), using a `parent_id` for self-referencing. Stores `center_point` and `area` as spatial types.
*   **`points`**: Points of interest along a trail (e.g., warnings, campsites), linked to a `point_type`.
*   **`rivers`**: Stores the geographical path of a river as a `LINESTRING`.
*   **`river_tracks`**: Stores the specific GPS track for a `trail` as a JSON array of coordinates.
*   **`users`**: A comprehensive user model including profile information, preferences, and support for soft deletes.
*   **`social_accounts`**: Stores OAuth data for users logging in via social providers.
*   **`imageables` & `linkables`**: Polymorphic pivot tables allowing images and external links to be attached to various models like `Trail`, `Section`, and `Region`.

---

## 4. Frontend Architecture

The frontend is divided into two separate Single Page Applications (SPAs) but shares the same backend and Vite build process.

### 4.1. Main Application (`resources/js/app.js`)
*   **Modular Structure:** Code is organized into modules under `resources/js/modules/` (e.g., `auth`, `trails`, `regions`).
*   **Key Components:**
    *   `MapView.vue`: The main interactive map interface using Leaflet.
    *   `TrailPopup.vue`: Displays detailed information for a selected trail.
    *   `SidebarTrails.vue`: A panel with filters and a list of trail results.
*   **Custom Caching Plugin:** A Laravel-style caching system (`this.$cache.remember(...)`) is implemented for Vue. It supports Time-to-Live (TTL) and tag-based invalidation, using `localStorage` as the backend.
*   **Global Helpers:** Global helper functions are available (e.g., `$alertInfo`, `$formatDate`).

### 4.2. Dashboard SPA (`resources/js/modules/dashboard/main.js`)
*   **Separate Entry Point:** The dashboard is a distinct Vue application, loaded on the `/dashboard` route.
*   **Purpose:** Provides admin-only functionality for managing trails, users, and system settings (CRUD operations).
*   **Custom UI Kit:** The dashboard uses a bespoke UI component kit found in `resources/js/dashboard/components/ui/`. **Development standard requires using these components (e.g., `<UiButton>`, `<UiDataTable>`) instead of raw Vuetify components.** This kit is inspired by `shadcn/ui`.
*   **Authentication:** The dashboard has its own JWT authentication flow and token storage, but it communicates with the same backend API.

---

## 5. Backend Architecture

### 5.1. API Design
*   The API is versioned under `/api/v1/`.
*   It follows RESTful principles, using Laravel API Resources for consistent JSON responses.
*   **Key Endpoints:**
    *   `GET /api/v1/trails`: Fetches trails with bounding-box and attribute-based filtering.
    *   `GET /api/v1/trails/{slug}`: Retrieves a single trail.
    *   `GET /api/v1/regions`: Fetches the region hierarchy.
    *   `POST /api/v1/upload-gpx`: Endpoint for uploading GPX files.
    *   `POST /api/v1/geocoding/reverse`: Converts coordinates to region data.
    *   `GET /api/v1/search`: A full-text search endpoint.
*   **Dashboard API:** Protected endpoints under `/api/v1/dashboard/` for managing content, including polymorphic link management for different models.

### 5.2. Service Layer & Async Jobs
*   Business logic is abstracted into Service classes (e.g., `TrailService`, `GeocodingService`, `GpxProcessor`).
*   Long-running tasks, such as processing large GPX files or associating trails with regions, are handled asynchronously using Laravel's queue system with Redis as the driver.

---

## 6. Authentication System

The project features a robust, RFC 6749 compliant OAuth 2.0 refresh token system.

*   **Tokens:** Upon login, the client receives a short-lived `access_token` and a long-lived `refresh_token`.
*   **Token Rotation:** The refresh token is rotated with each use to enhance security.
*   **Automatic Refresh:** The frontend features a `TokenManager` integrated with Axios interceptors to handle token management automatically:
    1.  **Proactive Refresh:** A timer refreshes the token 5 minutes before it expires.
    2.  **Reactive Refresh:** If an API call returns a 401 Unauthorized error, the manager attempts to refresh the token and retries the original request.
    3.  **Request Queuing:** Concurrent requests are queued while a token refresh is in progress to prevent race conditions.
*   **Security:** On token refresh, user status (active/inactive) and permissions (ACL) are re-validated from the database.

---

## 7. Development Workflow & Standards

*   **Git Workflow:** The project follows a `GitFlow`-like branching model:
    *   `main`: Production-ready code.
    *   `develop`: Main development branch where features are merged.
    *   Branches: `feature/*`, `bugfix/*`, `release/*`, `hotfix/*`.
*   **Dashboard Coding Standard:** It is mandatory to use the custom UI kit from `resources/js/dashboard/components/ui/` for all new UI in the dashboard. This ensures visual consistency and encapsulates complex logic.
*   **DevOps:** A `Makefile` and `npm scripts` provide helpers for common tasks like setup (`npm run setup`), backups (`npm run db:backup`), and fresh installs (`npm run fresh`).
