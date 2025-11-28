# Migration Guide - Frontend Tests Location

## ✅ Completed Migration

Testy frontendowe zostały przeniesione z `resources/js/dashboard/__tests__/` do `tests/vitest/` aby wszystkie testy (backend + frontend) były w jednym miejscu.

## Zmiany

### Przed (stara struktura)
```
resources/js/dashboard/
├── __tests__/
│   ├── setup.js
│   └── README.md
├── store/modules/__tests__/
│   └── breadcrumbs.spec.js
├── composables/__tests__/
│   └── useBreadcrumbs.spec.js
└── components/ui/__tests__/
    └── UiBreadcrumb.spec.js
```

### Po (nowa struktura) ✅
```
tests/
├── Feature/              # Laravel tests
├── Unit/                 # Laravel tests
└── vitest/              # Frontend tests
    ├── setup.js
    ├── README.md
    ├── run-tests.sh     # Test runner (Linux/Mac)
    ├── run-tests.bat    # Test runner (Windows)
    ├── store/
    │   └── breadcrumbs.spec.js
    ├── composables/
    │   └── useBreadcrumbs.spec.js
    └── components/
        └── UiBreadcrumb.spec.js
```

## Aktualizacje konfiguracji

### vitest.config.js
```javascript
// Przed
setupFiles: ['./resources/js/dashboard/__tests__/setup.js']

// Po
setupFiles: ['./tests/vitest/setup.js']
include: ['tests/vitest/**/*.spec.js']
```

## Co się nie zmieniło

- ✅ Wszystkie aliasy (`@`, `@dashboard`, `@ui`) działają tak samo
- ✅ Komendy NPM pozostały takie same:
  - `npm test`
  - `npm run test:run`
  - `npm run test:ui`
  - `npm run test:coverage`
- ✅ Struktura testów i asercje pozostały identyczne

## Nowe funkcje

### 1. Dedykowane skrypty testowe

**Linux/Mac:**
```bash
cd tests/vitest
./run-tests.sh
```

**Windows:**
```bash
cd tests\vitest
run-tests.bat
```

### 2. Run all tests (Backend + Frontend)

**Root projektu:**
```bash
# Linux/Mac
./run-all-tests.sh

# Windows
run-all-tests.bat
```

## Migracja własnych testów

Jeśli masz własne testy do przeniesienia:

1. **Przenieś pliki testowe:**
```bash
# Przykład dla store tests
mv resources/js/dashboard/store/modules/__tests__/*.spec.js tests/vitest/store/

# Przykład dla composables
mv resources/js/dashboard/composables/__tests__/*.spec.js tests/vitest/composables/

# Przykład dla components
mv resources/js/dashboard/components/**/__tests__/*.spec.js tests/vitest/components/
```

2. **Zaktualizuj imports (jeśli są bezwzględne ścieżki):**
```javascript
// Nie trzeba zmieniać - aliasy działają tak samo
import { useBreadcrumbs } from '@/dashboard/composables/useBreadcrumbs'
import UiBreadcrumb from '@ui/UiBreadcrumb.vue'
```

3. **Uruchom testy:**
```bash
npm run test:run
```

## Troubleshooting

### Problem: "Cannot find module"
**Rozwiązanie:** Sprawdź czy vitest.config.js ma poprawne aliases:
```javascript
resolve: {
  alias: {
    '@': path.resolve(__dirname, './resources/js'),
    '@dashboard': path.resolve(__dirname, './resources/js/dashboard'),
    '@ui': path.resolve(__dirname, './resources/js/dashboard/components/ui')
  }
}
```

### Problem: Tests nie są wykrywane
**Rozwiązanie:** Sprawdź czy vitest.config.js ma:
```javascript
test: {
  include: ['tests/vitest/**/*.spec.js']
}
```

---

**Migration completed:** 2025-11-27
**Status:** ✅ Wszystkie testy działają w nowej lokalizacji
