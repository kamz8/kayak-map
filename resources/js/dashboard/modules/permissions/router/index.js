// Permissions Module Routes
const PermissionsIndex = () => import('../Pages/PermissionsIndex.vue')

const permissionsRoutes = [
  {
    path: '/dashboard/permissions',
    name: 'PermissionsIndex',
    component: PermissionsIndex,
    meta: {
      title: 'Zarządzanie uprawnieniami - Kayak Map Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Uprawnienia', to: '/dashboard/permissions' }
      ],
      navigation: {
        section: 'Administracja',
        title: 'Uprawnienia',
        icon: 'mdi-key',
        order: 130,
        disabled: false
      },
      permissions: ['permissions.view']
    }
  },
]

export default permissionsRoutes
