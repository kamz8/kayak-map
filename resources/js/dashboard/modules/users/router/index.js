// Users Module Routes
const UsersIndex = () => import('../Pages/UsersIndex.vue')
const UserCreate = () => import('../Pages/UserCreate.vue')
const UserEdit = () => import('../Pages/UserEdit.vue')

const usersRoutes = [
  {
    path: '/dashboard/users',
    name: 'UsersIndex',
    component: UsersIndex,
    meta: {
      title: 'Zarządzanie użytkownikami - WartiNurt Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Użytkownicy', to: '/dashboard/users' }
      ],
      navigation: {
        section: 'Administracja',
        title: 'Użytkownicy',
        icon: 'mdi-account-group',
        order: 110,
        disabled: false
      },
      permissions: ['users.view']
    }
  },
  {
    path: '/dashboard/users/create',
    name: 'UserCreate',
    component: UserCreate,
    meta: {
      title: 'Dodaj użytkownika - WartiNurt Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Użytkownicy', to: '/dashboard/users' },
        { text: 'Dodaj użytkownika', to: '/dashboard/users/create' }
      ],
      permissions: ['users.create']
    }
  },
  {
    path: '/dashboard/users/:id/edit',
    name: 'UserEdit',
    component: UserEdit,
    meta: {
      title: 'Edytuj użytkownika - WartiNurt Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Użytkownicy', to: '/dashboard/users' },
        { text: 'Edytuj użytkownika' }
      ],
      permissions: ['users.edit']
    }
  }
]

export default usersRoutes
