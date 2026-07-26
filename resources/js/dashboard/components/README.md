# Dashboard Components

This directory contains shared dashboard components. These are not feature modules; they are reusable UI building blocks used by dashboard modules such as auth, trails, users, settings, roles, and permissions.

## Structure

```text
resources/js/dashboard/components/
└── ui/
    ├── UiButton.vue
    ├── UiCard.vue
    ├── UiInput.vue
    ├── UiBadge.vue
    ├── UiDataTable.vue
    ├── DataTable.vue
    ├── FormField.vue
    ├── StatsCard.vue
    ├── ConfirmDialog.vue
    └── index.js
```

## Usage

```javascript
import { UiButton, UiCard, UiInput, UiBadge, UiDataTable } from '@/dashboard/components/ui'
```

## Rules

- Use UI Kit components instead of raw Vuetify components when a matching component exists.
- Keep shared components generic and reusable across modules.
- Keep business logic inside feature modules or services, not shared UI components.
- Export shared components from `components/ui/index.js`.

## Adding A Shared Component

1. Create the component in `resources/js/dashboard/components/ui/`.
2. Add prop and emit validation.
3. Export it from `resources/js/dashboard/components/ui/index.js`.
4. Use existing design tokens and UI Kit variants.
5. Add focused tests when behavior is non-trivial.
