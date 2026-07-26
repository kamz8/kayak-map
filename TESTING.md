# Testing Guide - Kayak Map Dashboard

## 🚀 Quick Start - All Tests

Uruchom wszystkie testy (Backend + Frontend) jednym skryptem:

```bash
# Linux/Mac
./run-all-tests.sh

# Windows
run-all-tests.bat
```

---

## Frontend Unit Tests (Vitest)

### Quick Start

1. **Instalacja dependencies testowych:**
```bash
npm install --save-dev @vue/test-utils@^2.4.6 @vitest/ui@^2.1.8 @vitest/coverage-v8@^2.1.8 vitest@^2.1.8 jsdom@^25.0.1
```

2. **Dodanie skryptów do package.json:**
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

3. **Uruchomienie testów:**

```bash
# Opcja 1: NPM scripts (zalecane)
npm test              # Watch mode
npm run test:run      # Single run
npm run test:ui       # Z interfejsem UI
npm run test:coverage # Z coverage

# Opcja 2: Dedykowane skrypty w tests/vitest/
cd tests/vitest
./run-tests.sh        # Linux/Mac
run-tests.bat         # Windows
```

### Struktura testów

```
tests/
├── Feature/                    # Laravel Feature tests (API endpoints)
├── Unit/                       # Laravel Unit tests (Services, Models)
│
└── vitest/                     # Frontend Vitest tests
    ├── setup.js               # Global test setup
    ├── README.md              # Szczegółowa dokumentacja
    │
    ├── store/
    │   └── breadcrumbs.spec.js           # ✅ 100% coverage
    │
    ├── composables/
    │   └── useBreadcrumbs.spec.js        # ✅ 100% coverage
    │
    └── components/
        └── UiBreadcrumb.spec.js          # ✅ 95%+ coverage
```

### Co jest testowane?

#### 1. Vuex Store (`breadcrumbs.spec.js`) - 100% coverage
- State initialization
- Getters: `updates`, `hasUpdates`
- Mutations: `UPDATE_BREADCRUMB_BY_KEY`, `CLEAR_KEY`, `CLEAR_UPDATES`
- Actions dispatching
- Integration scenarios

#### 2. Composable (`useBreadcrumbs.spec.js`) - 100% coverage
- `updateBreadcrumbByKey()` - update dynamicznych breadcrumbs
- `clearKey()` - czyszczenie pojedynczego klucza
- `clearUpdates()` - czyszczenie wszystkich updates
- Fallback behavior gdy brak store
- Real-world scenarios (trail links, section links)

#### 3. Component (`UiBreadcrumb.spec.js`) - 95%+ coverage
- Rendering z props i route.meta
- Clickable links vs static spans
- Muted styling (`opacity: 0.6`)
- `v-show` logic dla pustych breadcrumbs
- Separators między itemami
- Home icon display
- Variants i sizes (subtle, sm, lg)
- Dynamic updates integration
- Edge cases handling

### Przykładowe testy

#### Store Test
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

#### Composable Test
```javascript
it('should update store state', () => {
  breadcrumbs.updateBreadcrumbByKey('trail', {
    text: 'Test Trail',
    to: '/test'
  })

  const storeUpdates = store.getters['breadcrumbs/updates']
  expect(storeUpdates.trail.text).toBe('Test Trail')
})
```

#### Component Test
```javascript
it('should apply muted class to items with muted property', () => {
  const items = [
    { text: 'Dashboard', to: '/dashboard' },
    { text: 'Trail', to: '/trail/123', muted: true }
  ]

  const wrapper = mount(UiBreadcrumb, { props: { items } })
  expect(wrapper.find('.ui-breadcrumb-link--muted').exists()).toBe(true)
})
```

### Coverage Report

```bash
npm run test:coverage
```

Expected output:
```
 ✓ store/modules/__tests__/breadcrumbs.spec.js (24 tests)
 ✓ composables/__tests__/useBreadcrumbs.spec.js (18 tests)
 ✓ components/ui/__tests__/UiBreadcrumb.spec.js (28 tests)

 Test Files  3 passed (3)
      Tests  70 passed (70)

-----------------------------------------
File                              | % Stmts | % Branch | % Funcs | % Lines
----------------------------------|---------|----------|---------|--------
store/modules/breadcrumbs.js      |   100   |   100    |   100   |   100
composables/useBreadcrumbs.js     |   100   |   100    |   100   |   100
components/ui/UiBreadcrumb.vue    |   95.2  |   92.3   |   100   |   95.2
-----------------------------------------
```

### Troubleshooting

#### Problem: Tests fail z "Cannot find module"
**Solution:**
```bash
npm install --save-dev @vue/test-utils vitest jsdom
```

#### Problem: "vi is not defined"
**Solution:** Dodaj `globals: true` w `vitest.config.js`:
```javascript
export default defineConfig({
  test: {
    globals: true
  }
})
```

#### Problem: Path aliases nie działają
**Solution:** Skonfiguruj aliases w `vitest.config.js`:
```javascript
resolve: {
  alias: {
    '@': path.resolve(__dirname, './resources/js'),
    '@dashboard': path.resolve(__dirname, './resources/js/dashboard'),
    '@ui': path.resolve(__dirname, './resources/js/dashboard/components/ui')
  }
}
```

### CI/CD Integration

Przykładowy workflow znajduje się w `.github/workflows/frontend-tests.yml.example`.

Skopiuj i dostosuj:
```bash
cp .github/workflows/frontend-tests.yml.example .github/workflows/frontend-tests.yml
```

---

## Backend Tests (PHPUnit/Pest)

### Uruchomienie backend tests

```bash
# Wszystkie testy
php artisan test

# Konkretny test file
php artisan test --filter=LinkServiceTest

# Z coverage
php artisan test --coverage

# Performance tests
./run-performance-tests.bat   # Windows
./run-performance-tests.sh    # Linux/Mac
```

### Performance Tests

Dla Links API mamy dedykowane performance tests:

```bash
# Quick performance test (50 links)
php artisan test --filter=quick_performance_test

# Optimization comparison
php artisan test --filter=compare_with_vs_without_optimization
```

Expected metrics:
- Query Count: < 15 zapytań
- Execution Time: < 1000ms dla 100 linków
- Memory Usage: < 80 MB

---

## Best Practices

### Frontend Testing

1. **Jedna asercja na test** - łatwiejsze debugowanie
2. **Opisowe nazwy** - `should update breadcrumb text` > `test1`
3. **AAA Pattern** - Arrange, Act, Assert
4. **Fresh state** - `beforeEach()` dla czystego state
5. **Mock external dependencies** - API calls, router, etc.
6. **Test user scenarios** - nie tylko pojedyncze funkcje

### Backend Testing

1. **Database transactions** - rollback po każdym teście
2. **Factory classes** - do generowania test data
3. **Test happy path + edge cases**
4. **Performance benchmarks** - dla krytycznych endpointów
5. **Integration tests** - test full request/response cycle

---

## Documentation

- **Frontend Tests:** `tests/vitest/README.md`
- **Backend Tests:** `tests/Feature/Api/V1/Dashboard/`
- **Performance Results:** `PERFORMANCE_RESULTS.md`

---

**Last Updated:** 2025-11-27
