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
    path: '/dashboard/trails/:id/links',
    name: 'DashboardTrailLinks',
    component: () => import('../Pages/TrailLinks.vue'),
    meta: {
      requiresAuth: true,
      title: 'Zarządzanie linkami - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { key: 'trail', text: '', to: '', muted: true }, // Dynamic - updated by component
        { text: 'Linki' }
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
  },
  {
    path: '/dashboard/trails/:id/sections/:sectionId/links',
    name: 'DashboardSectionLinks',
    component: () => import('../Pages/SectionLinks.vue'),
    meta: {
      requiresAuth: true,
      title: 'Zarządzanie linkami - Dashboard',
      breadcrumbs: [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { key: 'trail', text: '', to: '', muted: true }, // Dynamic - updated by component
        { key: 'section', text: '', muted: true }, // Dynamic - updated by component
        { text: 'Linki' }
      ]
    }
  }
]