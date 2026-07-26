# Dashboard Frontend Tests

Unit tests dla systemu breadcrumbs w Dashboard używając **Vitest** + **Vue Test Utils**.

## 🚀 Quick Start

### Instalacja dependencies

```bash
npm install --save-dev @vue/test-utils@^2.4.6 @vitest/ui@^2.1.8 @vitest/coverage-v8@^2.1.8 vitest@^2.1.8 jsdom@^25.0.1
```

Lub użyj pliku z dependencies:
```bash
npm install $(cat test-dependencies.json | jq -r '.devDependencies | to_entries[] | "\(.key)@\(.value)"')
```

### Dodanie skryptów do package.json

```json
{
  "scripts": {
    "test": "vitest",
    "test:ui": "vitest --ui",
    "test:coverage": "vitest --coverage",
    "test:run": "vitest run"
  }
}
```

## 📋 Uruchamianie testów

```bash
# Watch mode (domyślnie)
npm test

# Jednorazowe uruchomienie
npm run test:run

# Z interfejsem UI
npm run test:ui

# Z coverage reportem
npm run test:coverage
```

## 📁 Struktura testów

```
tests/
├── Feature/                    # Laravel Feature tests
├── Unit/                       # Laravel Unit tests
│
└── vitest/                     # Frontend Vitest tests
    ├── setup.js               # Setup file (mocks, globals)
    ├── README.md              # Ten plik
    │
    ├── store/
    │   └── breadcrumbs.spec.js           # Vuex store tests
    │
    ├── composables/
    │   └── useBreadcrumbs.spec.js        # Composable tests
    │
    └── components/
        └── UiBreadcrumb.spec.js          # Component tests
```

## 🧪 Co jest testowane?

### 1. **Vuex Store Module** (`breadcrumbs.spec.js`)
- ✅ State initialization
- ✅ Getters (`updates`, `hasUpdates`)
- ✅ Mutations (`UPDATE_BREADCRUMB_BY_KEY`, `CLEAR_KEY`, `CLEAR_UPDATES`)
- ✅ Actions dispatching
- ✅ Integration scenarios

**Przykład:**
```javascript
it('should add new update for a key', () => {
  breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
    key: 'trail',
    updates: { text: 'Trail Name', to: '/trail/123' }
  })

  expect(state.updates).toEqual({
    trail: { text: 'Trail Name', to: '/trail/123' }
  })
})
```

### 2. **Composable** (`useBreadcrumbs.spec.js`)
- ✅ Function availability
- ✅ `updateBreadcrumbByKey()` - dispatch i state update
- ✅ `clearKey()` - czyszczenie konkretnego klucza
- ✅ `clearUpdates()` - czyszczenie wszystkich
- ✅ Fallback gdy brak store
- ✅ Integration scenarios (trail links, section links)

**Przykład:**
```javascript
it('should update store state', () => {
  breadcrumbs.updateBreadcrumbByKey('trail', {
    text: 'Test Trail',
    to: '/test'
  })

  const storeUpdates = store.getters['breadcrumbs/updates']
  expect(storeUpdates).toEqual({
    trail: { text: 'Test Trail', to: '/test' }
  })
})
```

### 3. **Component** (`UiBreadcrumb.spec.js`)
- ✅ Rendering breadcrumbs z props
- ✅ Clickable links vs static text
- ✅ Muted styling (`opacity: 0.6`)
- ✅ `v-show` dla pustych breadcrumbs
- ✅ Separators rendering
- ✅ Home icon
- ✅ Variants (default, subtle) i sizes (sm, default, lg)
- ✅ Fallback do `route.meta.breadcrumbs`
- ✅ Dynamic updates integration
- ✅ Edge cases (empty array, single item)

**Przykład:**
```javascript
it('should apply muted class to items with muted property', () => {
  const items = [
    { text: 'Dashboard', to: '/dashboard' },
    { text: 'Trail Name', to: '/trail/123', muted: true },
    { text: 'Linki' }
  ]

  const wrapper = mount(UiBreadcrumb, {
    props: { items },
    global: { plugins: [router] }
  })

  const mutedLink = wrapper.find('a.ui-breadcrumb-link--muted')
  expect(mutedLink.exists()).toBe(true)
  expect(mutedLink.text()).toBe('Trail Name')
})
```

## 📊 Coverage

Po uruchomieniu `npm run test:coverage` zobaczysz raport:

```
File                              | % Stmts | % Branch | % Funcs | % Lines
----------------------------------|---------|----------|---------|--------
store/modules/breadcrumbs.js      |   100   |   100    |   100   |   100
composables/useBreadcrumbs.js     |   100   |   100    |   100   |   100
components/ui/UiBreadcrumb.vue    |   95+   |   90+    |   100   |   95+
```

## 🔧 Konfiguracja

### vitest.config.js
```javascript
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./resources/js/dashboard/__tests__/setup.js']
  },
  resolve: {
    alias: {
      '@': './resources/js',
      '@dashboard': './resources/js/dashboard',
      '@ui': './resources/js/dashboard/components/ui'
    }
  }
})
```

### setup.js
- Mockuje `$route` i `$router`
- Mockuje `window.matchMedia` dla Vuetify
- Globalne konfiguracje Vue Test Utils

## 🎯 Best Practices

1. **Jedna asercja na test** - testy są bardziej czytelne
2. **Opisowe nazwy testów** - `should update store state` zamiast `test1`
3. **AAA Pattern** - Arrange, Act, Assert
4. **Fresh state** - używaj `beforeEach()` dla czystego state
5. **Test edge cases** - puste tablice, null values, etc.
6. **Integration tests** - testuj rzeczywiste scenariusze użytkownika

## 📚 Dokumentacja

- [Vitest](https://vitest.dev/)
- [Vue Test Utils](https://test-utils.vuejs.org/)
- [Testing Best Practices](https://vitest.dev/guide/best-practices.html)

## 🐛 Troubleshooting

### Problem: `Cannot find module '@vue/test-utils'`
**Rozwiązanie:** Zainstaluj dependencies:
```bash
npm install --save-dev @vue/test-utils@^2.4.6
```

### Problem: `ReferenceError: vi is not defined`
**Rozwiązanie:** Dodaj `globals: true` w `vitest.config.js`:
```javascript
test: {
  globals: true
}
```

### Problem: Testy nie widzą aliasów (`@`, `@dashboard`, `@ui`)
**Rozwiązanie:** Dodaj `resolve.alias` w `vitest.config.js`.

## ✅ Checklist przed commitem

- [ ] Wszystkie testy przechodzą: `npm run test:run`
- [ ] Coverage > 90%: `npm run test:coverage`
- [ ] Brak console.log w kodzie produkcyjnym
- [ ] Brak skipped testów (`it.skip`)
- [ ] Brak focused testów (`it.only`)

---

**Ostatnia aktualizacja:** 2025-11-27
**Maintainer:** Development Team
