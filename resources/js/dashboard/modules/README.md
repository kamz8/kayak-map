# Dashboard Modules

Dashboard modules group feature-specific pages, routes, stores, components, and utilities. Shared UI belongs in `resources/js/dashboard/components/ui/`, not in feature modules.

## Structure

```text
resources/js/dashboard/modules/
├── auth/
├── trails/
├── users/
├── roles/
├── permissions/
├── settings/
└── security/
```

## Module Responsibilities

- `auth` - login, token handling, and auth store integration.
- `trails` - trail CRUD, links, section links, and map editor.
- `users` - user CRUD, filters, roles, and status UI.
- `roles` - role management.
- `permissions` - permission management.
- `settings` - profile and application settings.
- `security` - password and security-related pages.

## Imports

```javascript
import { trailsRoutes } from '@dashboard-modules/trails'
import { UiButton, UiDataTable } from '@/dashboard/components/ui'
```

## Adding A Module

```bash
mkdir -p resources/js/dashboard/modules/example/{Pages,components,router,store}
```

Create an `index.js` that exports routes or module registration values. Register routes in the dashboard router and store modules in the dashboard Vuex store only when the feature needs state.

## Naming

- Folders: kebab-case.
- Vue components and pages: PascalCase.
- Route exports: camelCase, for example `usersRoutes`.
- Store module names: camelCase.

## UI Rule

Use `resources/js/dashboard/components/ui/` for shared interface elements. Keep module components focused on feature behavior and data flow.
