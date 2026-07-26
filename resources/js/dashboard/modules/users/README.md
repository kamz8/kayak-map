# Users Module

The users module provides dashboard user management for administrators. It includes list, create, edit, filtering, role assignment, status display, and security constraints.

## Status

Production ready.

## Backend

```text
app/Http/Controllers/Api/V1/Dashboard/UserController.php
app/Services/Dashboard/UserService.php
app/Http/Resources/Dashboard/UserResource.php
app/Http/Requests/Dashboard/User/CreateUserRequest.php
app/Http/Requests/Dashboard/User/UpdateUserRequest.php
```

## Frontend

```text
resources/js/dashboard/modules/users/
├── index.js
├── Pages/
│   ├── UsersIndex.vue
│   ├── UserCreate.vue
│   └── UserEdit.vue
├── components/
│   ├── UserForm.vue
│   ├── UserRoleManager.vue
│   ├── UserStatusBadge.vue
│   └── UserFilters.vue
├── router/index.js
└── store/index.js
```

## Features

- User CRUD operations.
- Laravel pagination.
- Search by name, email, and phone.
- Filters for role, status, and registration date.
- Role assignment with hierarchy rules.
- Active, inactive, email verified, and phone verified states.
- Protection against editing or deleting the current user.
- Protection against removing the last super admin.

## API Endpoints

```http
GET    /api/v1/dashboard/users
GET    /api/v1/dashboard/users/{id}
POST   /api/v1/dashboard/users
PUT    /api/v1/dashboard/users/{id}
DELETE /api/v1/dashboard/users/{id}
```

## Frontend Routes

```text
/dashboard/users
/dashboard/users/create
/dashboard/users/:id/edit
```

## Store Usage

```javascript
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

## UI Guidelines

- Use `UiDataTable` for user lists.
- Use `UiButton`, `UiInput`, `UiBadge`, and `UiCard` for new UI.
- Keep role/status presentation inside user-specific components.
- Keep reusable layout and controls in the dashboard UI Kit.
