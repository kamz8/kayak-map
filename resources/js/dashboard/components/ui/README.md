# Dashboard UI Kit

The dashboard UI Kit is a Vuetify 3 based component layer with shadcn-style variants and consistent design tokens. It is the default choice for dashboard UI work.

## Required Usage

Use UI Kit components instead of raw Vuetify components when a matching component exists.

```vue
<UiButton variant="default" size="sm">Save</UiButton>
<UiCard title="Trail Details">Content</UiCard>
<UiInput v-model="name" placeholder="Trail name" />
<UiBadge variant="success">Active</UiBadge>
```

Avoid this for new dashboard UI when a UI Kit component exists:

```vue
<v-btn color="primary">Save</v-btn>
<v-card>Content</v-card>
```

## Core Components

### UiButton

Variants: `default`, `destructive`, `outline`, `secondary`, `ghost`, `link`.

Sizes: `sm`, `default`, `lg`, `icon`.

### UiCard

Use for dashboard content panels and sections. Supports title, subtitle, content, and actions slots.

### UiInput

Use for text inputs with consistent validation and error display.

### UiBadge

Use for statuses, labels, roles, and small semantic markers.

### UiDataTable

Use for CRUD tables with actions, slots, search, and dashboard styling.

## Imports

```javascript
import { UiButton, UiCard, UiInput, UiBadge, UiDataTable } from '@/dashboard/components/ui'
```

## Design Tokens

Design tokens live in `resources/js/dashboard/design-system/tokens.js` and provide colors, spacing, typography, and variant values.

```javascript
import { designTokens } from '@/dashboard/design-system/tokens'
```

## Component Standards

- Component names use PascalCase.
- Props use camelCase.
- Events use kebab-case.
- Slots use kebab-case.
- Props must have validation when values are constrained.
- Emits should be declared with validation when practical.
- Components must support keyboard and screen-reader usage where applicable.

## Legacy Components

`DataTable.vue`, `FormField.vue`, `StatsCard.vue`, and `ConfirmDialog.vue` remain available for compatibility. Prefer the newer `Ui*` components for new dashboard work unless the legacy component is already the established pattern in the file being edited.
