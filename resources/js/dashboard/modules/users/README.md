# Users Module - Dashboard

## 📋 Przegląd

Kompletny moduł zarządzania użytkownikami dla Kayak Map Dashboard. Obsługuje pełny CRUD users z systemem ról, filtrami, paginacją Laravel i zaawansowanymi funkcjami bezpieczeństwa.

**Status: ✅ PRODUCTION READY**

## 🏗️ Architektura

### Backend (Laravel)
```
app/
├── Http/Controllers/Api/V1/Dashboard/
│   └── UserController.php              # CRUD z Laravel Resources
├── Services/Dashboard/
│   └── UserService.php                 # Business logic + security
├── Http/Resources/Dashboard/
│   └── UserResource.php                # API response formatting
├── Http/Requests/Dashboard/User/
│   ├── CreateUserRequest.php           # Validation + error handling
│   └── UpdateUserRequest.php           # Update validation rules
```

### Frontend (Vue)
```
resources/js/dashboard/modules/users/
├── index.js                            # Module exports
├── Pages/
│   ├── UsersIndex.vue                  # Lista użytkowników + filtry
│   ├── UserCreate.vue                  # Formularz tworzenia
│   └── UserEdit.vue                    # Formularz edycji + actions
├── components/
│   ├── UserForm.vue                    # Reusable form component
│   ├── UserRoleManager.vue             # Role assignment modal
│   ├── UserStatusBadge.vue             # Status display component
│   └── UserFilters.vue                 # Filtry + search
├── router/index.js                     # Module routes
└── store/index.js                      # Vuex store module
```

## 🎯 Features

### ✅ Core Features
- **CRUD Operations** - Create, Read, Update, Delete users
- **Laravel Pagination** - Standard Laravel paginate() z meta
- **Advanced Search** - Po imieniu, nazwisku, email, telefon
- **Multi-Filter System** - Rola, status, data rejestracji
- **Role Management** - Assign/sync roles z security constraints
- **Status Management** - Active, inactive, verified states

### ✅ Security Features
- **Permission Gates** - `users.view`, `users.create`, `users.edit`, `users.delete`
- **Role Hierarchy** - Super Admin > Admin > Editor > User
- **Self-Protection** - Nie można edytować/usunąć siebie
- **Super Admin Protection** - Tylko Super Admin może zarządzać Super Admin
- **Last Admin Protection** - Nie można usunąć ostatniego Super Admin

### ✅ UI/UX Features
- **DataTable Integration** - Wykorzystuje dashboard UiDataTable
- **Real-time Filtering** - Debounced filters (300ms)
- **Role Badges** - Color-coded role display
- **Status Indicators** - Visual status badges z ikonami
- **Responsive Design** - Mobile-friendly forms
- **Loading States** - Proper loading indicators
- **Error Handling** - User-friendly error messages

## 🚀 Quick Start

### 1. API Endpoints

```bash
# Lista użytkowników
GET /api/v1/dashboard/users?page=1&per_page=15&search=john&role=Admin&status=active

# Szczegóły użytkownika
GET /api/v1/dashboard/users/123

# Utwórz użytkownika
POST /api/v1/dashboard/users
{
  "first_name": "Jan",
  "last_name": "Kowalski",
  "email": "jan@example.com",
  "roles": ["Editor"]
}

# Aktualizuj użytkownika
PUT /api/v1/dashboard/users/123
{
  "first_name": "Jan Updated",
  "is_active": false
}

# Usuń użytkownika (soft delete)
DELETE /api/v1/dashboard/users/123
```

### 2. Frontend Routes

```javascript
// Dostępne routes
/dashboard/users           # Lista użytkowników
/dashboard/users/create    # Dodaj użytkownika
/dashboard/users/123/edit  # Edytuj użytkownika
```

### 3. Vuex Store Usage

```javascript
// W komponencie
import { mapActions, mapGetters } from 'vuex'

export default {
  computed: {
    ...mapGetters('users', ['users', 'loading', 'pagination'])
  },
  methods: {
    ...mapActions('users', ['fetchUsers', 'createUser', 'updateUser'])
  }
}
```

## 📡 API Reference

### UserController Methods

| Method | Endpoint | Description | Permissions |
|--------|----------|-------------|-------------|
| `index` | `GET /users` | Lista z paginacją | `users.view` |
| `show` | `GET /users/{id}` | Szczegóły użytkownika | `users.view` |
| `store` | `POST /users` | Utwórz użytkownika | `users.create` |
| `update` | `PUT /users/{id}` | Aktualizuj użytkownika | `users.edit` |
| `destroy` | `DELETE /users/{id}` | Usuń użytkownika | `users.delete` |

### Filtry Query Parameters

```javascript
{
  page: 1,              // Numer strony
  per_page: 15,         // Elementów na stronę (max 50)
  search: "jan",        // Szukaj w: first_name, last_name, email, phone
  role: "Admin",        // Filtruj po roli
  status: "active",     // active|inactive|email_verified|email_unverified|phone_verified|phone_unverified
  created_from: "2024-01-01",  // Od daty rejestracji
  created_to: "2024-12-31",    // Do daty rejestracji
  sort_by: "created_at",       // created_at|first_name|last_name|email|last_login_at
  sort_direction: "desc"       // asc|desc
}
```

### UserResource Response

```json
{
  "data": [
    {
      "id": 1,
      "first_name": "Jan",
      "last_name": "Kowalski",
      "full_name": "Jan Kowalski",
      "email": "jan@example.com",
      "phone": "+48123456789",
      "is_active": true,
      "status": "active",
      "email_verified_at": "2024-01-15 10:30:00",
      "last_login_at": "2024-09-12 08:15:30",
      "roles": [
        {
          "id": 2,
          "name": "Admin",
          "display_name": "Administrator",
          "color": "warning"
        }
      ],
      "avatar": {
        "id": 5,
        "url": "https://kayak-map.test/storage/avatars/user1.jpg"
      },
      "created_at": "2024-01-10 12:00:00"
    }
  ],
  "links": {
    "first": "http://localhost/api/v1/dashboard/users?page=1",
    "last": "http://localhost/api/v1/dashboard/users?page=10",
    "prev": null,
    "next": "http://localhost/api/v1/dashboard/users?page=2"
  },
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150,
    "from": 1,
    "to": 15
  }
}
```

## 🧩 Components API

### UserForm.vue

```vue
<UserForm
  :user="user"              <!-- User object dla edycji (null = create) -->
  :available-roles="roles"  <!-- Array dostępnych ról -->
  :loading="false"          <!-- Loading state -->
  @submit="handleSubmit"    <!-- Submit handler -->
/>
```

### UserRoleManager.vue

```vue
<UserRoleManager
  :user="user"              <!-- User object z rolami -->
  :available-roles="roles"  <!-- Array dostępnych ról -->
  @roles-updated="refresh"  <!-- Handler po aktualizacji ról -->
/>
```

### UserFilters.vue

```vue
<UserFilters
  :filters="currentFilters"    <!-- Obecne filtry -->
  :role-options="roleOptions"  <!-- Opcje ról -->
  :status-options="statusOpts" <!-- Opcje statusów -->
  @update:filters="setFilters" <!-- Handler zmiany filtrów -->
  @reset="resetFilters"        <!-- Handler reset filtrów -->
/>
```

### UserStatusBadge.vue

```vue
<UserStatusBadge
  status="active"          <!-- active|inactive|unverified|deleted -->
  size="sm"                <!-- sm|default|lg -->
  :show-icon="true"        <!-- Pokazuj ikonę -->
/>
```

## 🔒 Security Implementation

### Permission Middleware

```php
// UserController.php
$this->middleware('permission:users.view')->only(['index', 'show']);
$this->middleware('permission:users.create')->only(['store']);
$this->middleware('permission:users.edit')->only(['update']);
$this->middleware('permission:users.delete')->only(['destroy']);
```

### Business Logic Security

```php
// UserService.php

// Nie można edytować siebie
if ($user->id === $updater->id) {
    throw new \Exception('Cannot modify yourself through user management');
}

// Tylko Super Admin może edytować Super Admin
if ($user->isSuperAdmin() && !$updater->isSuperAdmin()) {
    throw new \Exception('Only Super Admin can modify other Super Admin users');
}

// Nie można usunąć ostatniego Super Admin
$superAdminCount = User::role('Super Admin')->count();
if ($superAdminCount <= 1) {
    throw new \Exception('Cannot delete the last Super Admin');
}
```

### Frontend Security

```javascript
// UserRoleManager.vue
computed: {
  canManageRoles() {
    // Super Admin może zarządzać wszystkimi
    if (this.currentUser.is_super_admin) return true

    // Nie można zarządzać swoimi rolami
    if (this.user.id === this.currentUser.id) return false

    // Jeśli target user to Super Admin, tylko Super Admin może
    if (this.user.is_super_admin) return false

    return true
  }
}
```

## 🎨 UI Components

### Status Colors & Icons

```javascript
const STATUS_CONFIG = {
  active: { variant: 'success', icon: 'mdi-check-circle' },
  inactive: { variant: 'secondary', icon: 'mdi-pause-circle' },
  unverified: { variant: 'warning', icon: 'mdi-email-alert' },
  deleted: { variant: 'destructive', icon: 'mdi-delete' }
}
```

### Role Colors

```javascript
const ROLE_COLORS = {
  'Super Admin': 'destructive',  // Red
  'Admin': 'warning',            // Orange
  'Editor': 'secondary',         // Blue
  'User': 'default'              // Gray
}
```

## 🔧 Customization

### Dodanie nowego filtru

```javascript
// W UserFilters.vue
<v-select
  v-model="localFilters.department"
  :items="departmentOptions"
  label="Dział"
  @update:model-value="emitFilters"
/>

// W UserService.php
if (!empty($filters['department'])) {
    $query->where('department', $filters['department']);
}
```

### Rozszerzenie UserResource

```php
// UserResource.php
public function toArray(Request $request): array
{
    return [
        // ... existing fields
        'custom_field' => $this->custom_field,
        'computed_value' => $this->getComputedValue(),
    ];
}
```

## 🐛 Troubleshooting

### Częste problemy

**1. Permission Denied**
```bash
# Sprawdź uprawnienia użytkownika
php artisan permission:show user@example.com

# Przypisz brakujące uprawnienia
php artisan permission:assign user@example.com users.view
```

**2. Role nie ładują się**
```javascript
// Sprawdź czy users store jest zarejestrowany
console.log(this.$store.state.users)

// Sprawdź czy roleOptions są dostępne
console.log(this.$store.getters['users/roleOptions'])
```

**3. Validation errors**
```php
// CreateUserRequest.php - sprawdź rules
'email' => ['required', 'email', 'max:255', 'unique:users,email']
```

## 📈 Performance

### Optymalizacje

- **Eager Loading**: `User::with(['roles', 'avatar'])`
- **Pagination**: Maksymalnie 50 elementów na stronę
- **Debounced Filters**: 300ms delay na filtry
- **Lazy Loading**: Pages ładowane on-demand

### Monitoring

```bash
# Sprawdź query performance
php artisan telescope

# Monitor API calls
/api/v1/dashboard/users - avg response time: ~150ms
```

## 🧪 Testing

### Backend Tests
```bash
# Uruchom testy UserController
php artisan test tests/Feature/Dashboard/UserControllerTest.php

# Test permissions
php artisan test tests/Feature/Dashboard/UserPermissionsTest.php
```

### Frontend Testing
```bash
# Test komponenty
npm run test:unit -- UserForm.spec.js

# E2E testy
npm run test:e2e -- users-module.spec.js
```

## 📝 Changelog

### v1.0.0 - 2025-09-14
- ✅ Initial release
- ✅ Full CRUD functionality
- ✅ Laravel pagination integration
- ✅ Role management system
- ✅ Advanced filtering
- ✅ Security constraints
- ✅ Responsive UI components

---

**Users Module v1.0.0**
*Production Ready - Full Featured User Management*
*Modular Architecture - Security First*