# Kayak Map Dashboard UI Kit

## Overview

A comprehensive UI component library built on **Vuetify 3** with **shadcn/ui** design patterns. This UI Kit provides a consistent, accessible, and beautiful design system for the Kayak Map Dashboard.

## 🎨 Design Philosophy

- **shadcn/ui aesthetics** with Vuetify's robust functionality
- **Consistent variant system** across all components
- **Design tokens** as single source of truth
- **Accessible by default** with WCAG compliance
- **Type-safe** with comprehensive prop validation

## 🧱 New UI Kit Components

### Core Components

#### UiButton
Modern button component with multiple variants and sizes.

```vue
<UiButton variant="default" size="sm">Default Button</UiButton>
<UiButton variant="destructive">Delete Item</UiButton>
<UiButton variant="outline" size="lg">Large Outline</UiButton>
<UiButton variant="ghost">Ghost Button</UiButton>
<UiButton variant="link">Link Style</UiButton>
```

**Variants:** `default` | `destructive` | `outline` | `secondary` | `ghost` | `link`  
**Sizes:** `sm` | `default` | `lg` | `icon`

#### UiCard
Flexible card component with multiple layouts.

```vue
<UiCard title="Card Title" variant="elevated">
  <template #subtitle>Card subtitle</template>
  <p>Card content goes here</p>
  <template #actions>
    <UiButton>Action</UiButton>
  </template>
</UiCard>
```

**Variants:** `default` | `outlined` | `elevated`

#### UiInput
Enhanced input field with consistent styling.

```vue
<UiInput
  v-model="value"
  placeholder="Enter text..."
  variant="default"
  :error-message="validation.error"
/>
```

**Variants:** `default` | `filled` | `underlined`  
**Sizes:** `sm` | `default` | `lg`

#### UiBadge
Status and label badges with semantic variants.

```vue
<UiBadge variant="default">Default</UiBadge>
<UiBadge variant="success">Success</UiBadge>
<UiBadge variant="destructive">Error</UiBadge>
<UiBadge variant="warning">Warning</UiBadge>
<UiBadge variant="outline">Outlined</UiBadge>
```

**Variants:** `default` | `secondary` | `destructive` | `success` | `warning` | `outline`  
**Sizes:** `sm` | `default` | `lg`

#### UiDataTable
Advanced data table with integrated search, actions, and styling.

```vue
<UiDataTable
  title="Data Table"
  :headers="headers"
  :items="items"
  :actions="{ view: true, edit: true, delete: true }"
  @view="handleView"
  @edit="handleEdit"
  @delete="handleDelete"
>
  <template #actions>
    <UiButton variant="default" size="sm">
      <v-icon start>mdi-plus</v-icon>
      Add Item
    </UiButton>
  </template>
  
  <template #item.status="{ value }">
    <UiBadge :variant="getStatusVariant(value)">
      {{ value }}
    </UiBadge>
  </template>
</UiDataTable>
```

## 🎯 Usage Patterns

### Import Components

```javascript
// Individual imports
import UiButton from '@/dashboard/components/ui/UiButton.vue'
import UiCard from '@/dashboard/components/ui/UiCard.vue'

// Batch import
import { UiButton, UiCard, UiInput, UiBadge } from '@/dashboard/components/ui'

// Global registration (main.js)
import { registerUiComponents } from '@/dashboard/components/ui'
registerUiComponents(app)
```

### Design Tokens

```javascript
import { designTokens } from '@/dashboard/design-system/tokens'

// Access color values
const primaryColor = designTokens.colors.primary
const spacing = designTokens.spacing[4]

// Get variant props for custom components
const buttonProps = designTokens.variants.button.destructive
```

## 🏗️ Architecture

```
resources/js/dashboard/
├── design-system/
│   ├── tokens.js          # Design tokens
│   ├── styles.css         # Global UI styles
│   └── theme/
│       └── vuetify.js     # Vuetify theme config
├── components/ui/
│   ├── UiButton.vue       # New UI Kit components
│   ├── UiCard.vue
│   ├── UiInput.vue
│   ├── UiBadge.vue
│   ├── UiDataTable.vue
│   ├── DataTable.vue      # Legacy components
│   ├── Logo.vue
│   └── index.js           # Exports
└── lib/
    └── utils.js           # Utilities
```

---

## 📚 Legacy Components (Compatibility)

## Komponenty

### DataTable.vue
Uniwersalna tabela danych z wbudowaną funkcjonalnością sortowania, filtrowania i akcji CRUD.

#### Props
```js
{
  title: String,                    // Tytuł tabeli
  headers: Array,                   // Nagłówki kolumn (required)  
  items: Array,                     // Dane do wyświetlenia
  loading: Boolean,                 // Stan ładowania
  searchable: Boolean,              // Czy włączyć wyszukiwanie
  searchLabel: String,              // Label dla pola wyszukiwania
  actions: Object,                  // Konfiguracja akcji (view, edit, delete)
  itemsPerPageOptions: Array,       // Opcje ilości elementów na stronie
  noDataIcon: String,               // Ikona gdy brak danych
  noDataTitle: String,              // Tytuł gdy brak danych
  noDataText: String                // Tekst gdy brak danych
}
```

#### Events
```js
{
  'view': (item) => {},             // Podgląd elementu
  'edit': (item) => {},             // Edycja elementu
  'delete': (item) => {}            // Usuwanie elementu
}
```

#### Slots
```js
{
  'toolbar': {},                    // Narzędzia w header
  'filters': {},                    // Filtry
  'header.{key}': { column },       // Custom nagłówki
  'item.{key}': { item, value },    // Custom komórki
  'actions': { item }               // Custom akcje
}
```

#### Przykład użycia
```vue
<template>
  <DataTable
    title="Lista użytkowników"
    :headers="headers"
    :items="users"
    :loading="loading"
    :actions="{ view: true, edit: true, delete: true }"
    @view="handleView"
    @edit="handleEdit"
    @delete="handleDelete"
  >
    <template #toolbar>
      <v-btn color="primary" @click="addUser">Dodaj</v-btn>
    </template>
    
    <template #item.status="{ item, value }">
      <v-chip :color="getStatusColor(value)">
        {{ value }}
      </v-chip>
    </template>
  </DataTable>
</template>
```

### Logo.vue
Komponent logo z automatycznym dostosowaniem do motywu (dark/light).

#### Props
```js
{
  size: [String, Number],           // Rozmiar logo (default: 36)
  showText: Boolean,                // Czy pokazać tekst (default: true)
  text: String,                     // Tekst logo (default: 'Wartki Nurt')
  src: String                       // Ścieżka do pliku logo
}
```

#### Funkcje
- Automatyczny filtr `invert` w dark theme
- Responsive sizing
- Opcjonalny tekst
- Smooth transitions

#### Przykład użycia
```vue
<template>
  <Logo :size="48" :show-text="true" />
  <Logo :size="24" :show-text="false" />
</template>
```

### StatsCard.vue
Karta do wyświetlania statystyk i metryk.

#### Props
```js
{
  title: String,                    // Tytuł karty
  value: [String, Number],          // Wartość główna
  subtitle: String,                 // Podtytuł/opis
  icon: String,                     // Ikona MDI
  color: String,                    // Kolor (primary, success, warning, error)
  loading: Boolean,                 // Stan ładowania
  trend: Object                     // Trend { value, direction, color }
}
```

#### Przykład użycia
```vue
<template>
  <StatsCard
    title="Użytkownicy"
    :value="1234"
    subtitle="Aktywni użytkownicy"
    icon="mdi-account-group"
    color="success"
    :trend="{ value: '+12%', direction: 'up', color: 'success' }"
  />
</template>
```

### FormField.vue
Uniwersalne pole formularza z walidacją.

#### Props
```js
{
  label: String,                    // Label pola
  type: String,                     // Typ pola (text, email, password, etc.)
  modelValue: Any,                  // v-model value
  rules: Array,                     // Reguły walidacji
  required: Boolean,                // Czy pole wymagane
  disabled: Boolean,                // Czy pole wyłączone
  placeholder: String,              // Placeholder
  hint: String                      // Podpowiedź
}
```

#### Events
```js
{
  'update:modelValue': (value) => {}
}
```

### ConfirmDialog.vue
Dialog potwierdzenia akcji.

#### Props
```js
{
  modelValue: Boolean,              // v-model dla widoczności
  title: String,                    // Tytuł dialogu
  message: String,                  // Wiadomość
  confirmText: String,              // Tekst przycisku potwierdzenia
  cancelText: String,               // Tekst przycisku anulowania
  confirmColor: String,             // Kolor przycisku potwierdzenia
  dangerous: Boolean                // Czy akcja jest niebezpieczna
}
```

#### Events
```js
{
  'update:modelValue': (value) => {},
  'confirm': () => {},
  'cancel': () => {}
}
```

## Wzorce i Konwencje

### Nazewnictwo
- Komponenty: PascalCase (`DataTable`, `StatsCard`)
- Props: camelCase (`showText`, `itemsPerPage`)
- Events: kebab-case (`update:model-value`)
- Slots: kebab-case (`toolbar`, `no-data`)

### Props Validation
Wszystkie komponenty mają pełną walidację props z:
- Typami danych
- Wartościami domyślnymi
- Custom validatorami
- Wymaganymi polami

### Emits Validation
Wszystkie eventy są zdefiniowane z walidacją argumentów:
```js
emits: {
  'update:modelValue': (value) => value !== undefined,
  'submit': (data) => data && typeof data === 'object'
}
```

### Accessibility
- Wszystkie komponenty używają odpowiednich ARIA attributes
- Keyboard navigation support
- Screen reader compatibility
- Focus management

### Performance
- Computed properties z cache
- v-memo dla dużych list
- Lazy loading dla heavy components
- Debounced search inputs

### Error Handling
- Try-catch bloki w async metodach
- User-friendly error messages
- Graceful degradation
- Loading states

## Style Guide

### CSS Conventions
- Scoped styles
- BEM methodology dla klas
- CSS custom properties dla theming
- Mobile-first responsive design

### Vue 3 Best Practices
- Options API consistency
- Proper reactivity patterns
- Component composition
- Slot-first design
- Prop drilling avoidance

## Testowanie

### Unit Tests
```bash
# Uruchom testy komponentów
npm run test:unit

# Testy z coverage
npm run test:coverage
```

### E2E Tests
```bash
# Testy end-to-end
npm run test:e2e
```

## Development

### Adding New Components

1. Utwórz komponent w `components/ui/`
2. Dodaj do `components/ui/index.js`
3. Dodaj dokumentację do README
4. Napisz testy
5. Dodaj przykłady użycia

### Component Template
```vue
<template>
  <!-- Template content -->
</template>

<script>
export default {
  name: 'ComponentName',
  emits: {
    // Define emits with validation
  },
  props: {
    // Define props with validation
  },
  data() {
    return {
      // Component state
    }
  },
  computed: {
    // Computed properties
  },
  watch: {
    // Watchers
  },
  methods: {
    // Component methods
  }
}
</script>

<style scoped>
/* Scoped styles */
</style>
```

## Deployment

Komponenty są automatycznie buildowane jako część aplikacji dashboard:

```bash
npm run build
```

Bundle jest dostępny w `public/build/assets/dashboard-*.js`