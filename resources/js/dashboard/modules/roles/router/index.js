// Roles Module Routes
const RolesIndex = () => import('../Pages/RolesIndex.vue')

const rolesRoutes = [
  {
    path: '/dashboard/roles',
    name: 'RolesIndex',
    component: RolesIndex,
    meta: {
      title: 'Zarządzanie rolami - Kayak Map Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Role', to: '/dashboard/roles' }
      ],
      navigation: {
        section: 'Administracja',
        title: 'Role',
        icon: 'mdi-shield-account',
        order: 120,
        disabled: false
      },
      permissions: ['roles.view']
    }
  },
]

export default rolesRoutes