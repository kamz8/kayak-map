# Dashboard Modules - Epiki Funkcjonalności

## 📋 Przegląd

Dashboard wykorzystuje **modułową strukturę** z epikami funkcjonalności. Każdy moduł to oddzielny zakres funkcjonalności z własnymi komponentami, views, store i routami.

**Uwaga**: UI Kit (DataTable, FormField, etc.) to komponenty **globalne** w `components/ui/`, nie moduł!

## 🏗️ Struktura Modułowa

```
resources/js/dashboard/modules/
├── auth/                 # 🔐 Autentykacja i sesje
│   ├── views/           # Komponenty widoków (LoginView)
│   ├── store/           # Vuex store dla auth
│   └── index.js         # Export routes i store
│
├── trails/              # 🗺️ Zarządzanie szlakami
│   ├── views/           # Lista, tworzenie, edycja szlaków
│   ├── store/           # Store dla trails (przyszłościowe)
│   ├── components/      # Komponenty specyficzne dla trails
│   └── index.js         # Export routes i components
│
│
├── users/               # 👥 Zarządzanie użytkownikami (planowane)
│   └── index.js
│
└── settings/            # ⚙️ Ustawienia dashboard (planowane)
    └── index.js
```

## 🎯 Moduły

### ✅ Auth Module
**Status: Zaimplementowane**
- `LoginView.vue` - Formularz logowania
- `auth.js` store - JWT auth, localStorage
- Route guards i middleware

### ✅ Trails Module  
**Status: Zaimplementowane**
- `TrailsList.vue` - Lista szlaków z tabelą
- `TrailsCreate.vue` - Formularz dodawania szlaku
- Routes z lazy loading


### 🚧 Users Module
**Status: Planowane**
- Lista użytkowników
- Dodawanie/edycja użytkowników
- Zarządzanie uprawnieniami

### 🚧 Settings Module  
**Status: Planowane**
- Ustawienia dashboard
- Konfiguracja aplikacji
- Backup i przywracanie

## 📦 Import i Export

### Import z modułów:
```javascript
// Auth module
import { authRoutes } from '@dashboard-modules/auth'

// Trails module  
import { trailsRoutes } from '@dashboard-modules/trails'

// Global UI components
import { DataTable, FormField } from '@ui'
```

### Aliasy Vite:
```javascript
'@dashboard': 'resources/js/dashboard'
'@ui': 'resources/js/dashboard/modules/ui'
'@dashboard-modules': 'resources/js/dashboard/modules'
```

## 🔧 Dodawanie Nowych Modułów

### 1. Utwórz strukturę modułu:
```bash
mkdir -p resources/js/dashboard/modules/nazwa-modulu/{Pages,store,components}
```

### 2. Stwórz index.js z exportami:
```javascript
// modules/nazwa-modulu/index.js
export const nazwaModuluRoutes = [
  // routes definition
]

export const NazwaModuluComponent = () => import('./Pages/Component.vue')
```

### 3. Zintegruj z routerem:
```javascript
// router/index.js
import { nazwaModuluRoutes } from '../modules/nazwa-modulu'

const routes = [
  ...nazwaModuluRoutes,
  // other routes
]
```

### 4. Jeśli potrzebny store:
```javascript
// store/index.js
import nazwaModulu from '../modules/nazwa-modulu/store'

const store = createStore({
  modules: {
    nazwaModulu,
    // other modules
  }
})
```

## 🎨 Styleguide Modułów

### Nazewnictwo:
- **Foldery**: kebab-case (`user-management`)
- **Components**: PascalCase (`UsersList.vue`)
- **Routes**: camelCase (`usersRoutes`)
- **Store**: camelCase (`usersStore`)

### Struktura plików w module:
```
module-name/
├── index.js              # Main exports
├── views/                # Vue components dla stron
├── components/           # Vue components wewnętrzne dla modułu
├── store/                # Vuex store (jeśli potrzebny)
├── router/               # Routes config (opcjonalne)
└── utils/                # Utility functions (opcjonalne)
```

## 🔮 Roadmap

### Phase 1: Core Modules ✅
- [x] Auth module refactoring
- [x] Trails module extraction  
- [x] Global UI components structure
- [x] Router integration

### Phase 2: Extended Modules 🚧
- [ ] Users management module
- [ ] Settings module  
- [ ] Reports module
- [ ] Analytics module

### Phase 3: Advanced Features 🔮
- [ ] Plugin system for modules
- [ ] Dynamic module loading
- [ ] Module-specific stores
- [ ] Inter-module communication

---

**Dashboard Modules v2.0**  
*Modular Epic-Based Architecture*  
*Scalable & Maintainable Structure*
