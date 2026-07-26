# Dashboard Application

The dashboard is a separate Vue 3 SPA inside the Laravel application. It has its own Vite entry point, router, store modules, UI kit, layouts, and feature modules.

## Entry Points

- Vue entry: `resources/js/dashboard/main.js`
- Blade shell: `resources/views/dashboard.blade.php`
- Laravel route: `routes/web.php`
- Production URL: `https://dashboard.wartkinurt.pl`
- Local URL: `https://kayak-map.test/dashboard`

## Architecture

```text
resources/js/dashboard/
├── main.js
├── Dashboard.vue
├── router/
├── store/
├── plugins/
├── layouts/
├── components/ui/
├── modules/
├── composables/
├── services/
├── styles/
└── design-system/
```

## Production Build

The dashboard is included in the multi-entry Vite build:

```javascript
dashboard: 'resources/js/dashboard/main.js'
```

Build assets with:

```bash
npm run build
```

## Authentication

The dashboard uses JWT-based authentication with access and refresh tokens. Token refresh is handled by the dashboard token manager and Axios interceptors.

Admin setup:

```bash
php artisan db:seed --class=Database\\Seeders\\Dashboard\\AdminUserSeeder
php artisan check:admin-user
```

## Modules

- `auth` - dashboard login and auth store.
- `trails` - trail CRUD, links, sections, and map editor.
- `users` - user management.
- `roles` and `permissions` - access control management.
- `settings` and `security` - profile, settings, and password workflows.

## UI Kit Rule

Dashboard code must use components from `resources/js/dashboard/components/ui/` instead of raw Vuetify components when a UI Kit component exists. Use `UiButton`, `UiCard`, `UiInput`, `UiBadge`, and `UiDataTable` for new dashboard UI.

## Development

```bash
npm run dev
```

Use aliases from `vite.config.js`:

```javascript
import { UiButton, UiCard } from '@/dashboard/components/ui'
```

## Testing

```bash
npm test
php artisan test --compact tests/Unit/DashboardProductionConfigTest.php
```
