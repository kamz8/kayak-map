# Kayak Map

Kayak Map is a Laravel and Vue application for exploring kayak trails, managing river route data, and preparing geospatial information for paddlers. The public app focuses on maps, trail discovery, regions, route details, warnings, and weather/context data. 

## Main Features

- Interactive Leaflet map for kayak trails and points of interest.
- Laravel 11 API with versioned endpoints under `/api/v1`.
- Vue 3 frontend with Vuetify, Vuex, Vue Router, Axios, and Vite.
- Dashboard SPA for administration, trail editing, links, users, roles, permissions, and settings.
- GPX and river-route tooling for importing, editing, and snapping trail tracks.
- MySQL/MariaDB storage with spatial data support.
- Docker-based development and production deployment.

## Production Dashboard

The production dashboard is served on a dedicated subdomain:

```text
https://dashboard.wartkinurt.pl
```

The dashboard remains part of the Laravel application. Traefik routes the dashboard host to the production Nginx service, Laravel serves `resources/views/dashboard.blade.php`, and Vite provides the dashboard production bundle from `resources/js/dashboard/main.js`.

Admin setup commands:

```bash
php artisan db:seed --class=Database\\Seeders\\Dashboard\\AdminUserSeeder
php artisan check:admin-user
```

## Requirements

- PHP 8.3+
- Composer 2+
- Node.js 18+ recommended
- Docker and Docker Compose
- MariaDB/MySQL with spatial support
- Redis for cache, queues, and selected permission workflows

## Quick Setup

```bash
git clone <repo-url>
cd kayak-map
npm run setup
```

After setup, common local URLs are:

- Main app: `https://kayak-map.test/`
- Dashboard: `https://kayak-map.test/dashboard`
- Dashboard login: `https://kayak-map.test/dashboard/login`
- PhpMyAdmin: `http://localhost:8081`

## Development Commands

```bash
npm run dev              # Start Vite for the main app and dashboard
npm run build            # Build production assets
docker-compose up -d     # Start local containers
docker-compose down      # Stop local containers
npm run fresh            # Rebuild a clean local environment
npm run fresh:deep       # Clean environment including node_modules/vendor
```

Helper commands for developers without local PHP/Composer:

```bash
./dev-helper.sh composer install
./dev-helper.sh artisan migrate
./dev-helper.sh artisan tinker
./dev-helper.sh help
```

## Testing

```bash
php artisan test --compact
npm test
vendor/bin/pint --dirty
```

Use focused tests while developing, for example:

```bash
php artisan test --compact tests/Unit/DashboardProductionConfigTest.php
php artisan test --compact tests/Unit/ReadmeLanguageTest.php
```

## Production Deployment Notes

- `docker-compose.prod.yml` contains Traefik labels for `wartkinurt.pl`, `api.wartkinurt.pl`, and `dashboard.wartkinurt.pl`.
- Production Nginx config is mounted from `docker/nginx/laravel.conf`.
- Run `npm run build` before deploying an image that serves production assets.
- Run migrations and seed the required dashboard admin data before opening the dashboard to operators.

## Important Paths

- `routes/web.php` - public app and dashboard SPA entry routes.
- `routes/api.php` - API routes.
- `resources/js/app.js` - public frontend entry.
- `resources/js/dashboard/main.js` - dashboard frontend entry.
- `resources/views/dashboard.blade.php` - dashboard Blade shell.
- `docker-compose.prod.yml` - production Compose and Traefik labels.
- `docker/nginx/laravel.conf` - production Nginx server config.

## Documentation

- `devops/README.md` - backup, restore, and deployment tooling.
- `tests/README.md` - backend test notes.
- `tests/vitest/README.md` - frontend test notes.
- `resources/js/dashboard/README.md` - dashboard architecture.
