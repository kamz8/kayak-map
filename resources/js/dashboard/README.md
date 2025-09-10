# Dashboard Module - Kayak Map

## 📋 Przegląd

**Modułowy dashboard** zintegrowany z główną aplikacją Kayak Map. Zbudowany jako **oddzielny entry point Vite** z własnym routingiem i komponentami.

## 🏗️ Architektura

### Struktura modułowa
```
resources/js/modules/dashboard/
├── main.js                    # Entry point dla Vite
├── App.vue                    # Root component
├── plugins/                   # Vuetify, Axios config
├── store/                     # Vuex modules (auth, ui)  
├── router/                    # Vue Router config
├── components/
│   └── ui/                    # Vuetify UI Kit components
├── views/                     # Page components
│   ├── auth/                  # Login page
│   └── dashboard/             # Dashboard pages
└── styles/                    # Dashboard-specific styles
```

### Multi-entry Vite Build
```javascript
// vite.config.js
rollupOptions: {
  input: {
    app: 'resources/js/app.js',           // Main app
    dashboard: 'resources/js/modules/dashboard/main.js'  // Dashboard
  }
}
```

## 🎯 Features

### ✅ Zaimplementowane
- **🔐 Authentication** - JWT auth z localStorage
- **📊 Dashboard Overview** - stats i quick actions
- **🗺️ Trails Management** - lista i dodawanie szlaków
- **🎨 Vuetify UI Kit** - reusable components
- **📱 Responsive Design** - mobile-friendly
- **🔄 Route Guards** - protected routes

### 🚧 W rozwoju
- **👥 Users Management**
- **🗺️ Regions Management** 
- **📁 File Upload** (GPX import)
- **⚙️ Settings Panel**

## 🛠️ Stack Technologiczny

- **Vue 3** (Options API)
- **Vuetify 3.6.13** (Material Design)
- **Vuex** (State management)
- **Vue Router 4** (SPA routing)
- **Axios** (HTTP client)
- **Vite** (Build tool)

## 🚀 Uruchamianie

### Development
```bash
# Główna aplikacja + Dashboard w jednym kontenerze
npm run dev

# Lub w kontenerze
npm run docker:dev
```

### URLs
- **Main App**: `https://kayak-map.test/`
- **Dashboard**: `https://kayak-map.test/dashboard`
- **Dashboard Login**: `https://kayak-map.test/dashboard/login`

## 🎨 Vuetify UI Kit

### DataTable Component
```vue
<DataTable
  title="Lista szlaków"
  :headers="headers"
  :items="trails"
  :actions="{ view: true, edit: true, delete: true }"
  @edit="editTrail"
  @delete="deleteTrail"
/>
```

### FormField Component
```vue
<FormField
  v-model="form.email"
  type="email"
  label="Adres email"
  required
  :rules="emailRules"
/>
```

### Import UI Components
```javascript
import { DataTable, FormField, StatsCard } from '@ui'
```

## 📡 API Integration

### Axios Configuration
```javascript
// Dashboard używa tego samego API co main app
baseURL: '/api/v1'

// Auto-add auth headers
Authorization: `Bearer ${token}`
```

### Auth Store
```javascript
// Separate token storage dla dashboard
localStorage: 'dashboard_token'
```

## 🛣️ Routing

### Route Structure
```
/dashboard                  → Overview
/dashboard/login           → Login page
/dashboard/trails          → Trails list
/dashboard/trails/create   → Create trail
/dashboard/trails/:id/edit → Edit trail
```

### Route Guards
```javascript
// Protected routes require authentication
meta: { requiresAuth: true }

// Guest routes (login) redirect if authenticated  
meta: { requiresGuest: true }
```

## 🎭 Theme & Styling

### Material Design
- **Light/Dark mode** support
- **Material Design Icons** (MDI)
- **Roboto font** family
- **Vuetify color palette**

### Custom Styles
```css
/* Dashboard-specific styles */
.dashboard-main { background: #f5f5f5; }
.stats-card:hover { transform: translateY(-2px); }
```

## 🔧 Development

### Adding New Pages
1. Create Vue component in `views/dashboard/`
2. Add route in `router/index.js`
3. Add navigation item (if needed)

### Adding New UI Components
1. Create component in `components/ui/`
2. Export in `components/ui/index.js`
3. Use alias `@ui` for imports

### State Management
```javascript
// Dashboard store modules
store/modules/auth.js  // Authentication
store/modules/ui.js    // UI state (loading, snackbar)
```

## 📦 Build Process

### Development
- **Vite HMR** - hot module replacement
- **Multi-entry** - app.js + dashboard/main.js
- **Shared dependencies** - Vuetify, Vue, etc.

### Production
```bash
npm run build
# Generates:
# public/build/assets/app-[hash].js
# public/build/assets/dashboard-[hash].js
```

## 🌐 Laravel Integration

### Blade Template
```blade
{{-- resources/views/dashboard.blade.php --}}
@vite(['resources/js/modules/dashboard/main.js'])
<div id="dashboard-app"></div>
```

### Route Configuration
```php
// routes/web.php
Route::get('/dashboard/{any?}', function () {
    return view('dashboard');
})->where('any', '.*');
```

## 📱 Container Support

### Docker Integration
- **Same container** as main app
- **Shared Vite dev server**
- **Hot reload** works for both apps
- **Nginx proxy** handles routing

### Environment
- **Development**: Vite dev server
- **Production**: Static build files

## 🔮 Roadmap

### Phase 1: Core Features ✅
- [x] Dashboard layout & navigation
- [x] Authentication flow
- [x] Trails management basic CRUD
- [x] UI Kit components

### Phase 2: Extended Features 🚧
- [ ] Users management
- [ ] Regions management
- [ ] File upload (GPX)
- [ ] Settings panel
- [ ] Advanced filters

### Phase 3: Advanced Features 🔮
- [ ] Analytics dashboard
- [ ] System logs
- [ ] Backup management
- [ ] Permission system

---

**Dashboard Module v1.0**  
*Integrated with Kayak Map*  
*Built with Vuetify UI Kit*