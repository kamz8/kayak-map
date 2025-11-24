// Trails module routes
export default [
  {
    path: '/dashboard/trails',
    name: 'DashboardTrails',
    component: () => import('../Pages/TrailsList.vue'),
    meta: {
      requiresAuth: true,
      title: 'Szlaki - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki' }
      ],
      // Navigation metadata for dropdown
      navigation: {
        section: 'Zarządzanie',
        icon: 'mdi-map-marker-path',
        title: 'Szlaki',
        order: 10
      }
    }
  },
  {
    path: '/dashboard/trails/create',
    name: 'DashboardTrailsCreate',
    component: () => import('../Pages/TrailsCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Dodaj szlak - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { text: 'Dodaj szlak' }
      ]
    }
  },
  {
    path: '/dashboard/trails/:id/edit',
    name: 'DashboardTrailsEdit',
    component: () => import('../Pages/TrailsEdit.vue'),
    meta: {
      requiresAuth: true,
      title: 'Edytuj szlak - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { text: 'Edytuj szlak' }
      ]
    }
  }
]