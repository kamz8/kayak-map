// Trails module exports
export const TrailsList = () => import('./views/TrailsList.vue')
export const TrailsCreate = () => import('./views/TrailsCreate.vue')

// Trails module routes
export const trailsRoutes = [
  {
    path: '/dashboard/trails',
    name: 'DashboardTrails',
    component: () => import('./views/TrailsList.vue'),
    meta: {
      requiresAuth: true,
      title: 'Szlaki - Dashboard'
    }
  },
  {
    path: '/dashboard/trails/create',
    name: 'DashboardTrailsCreate',
    component: () => import('./views/TrailsCreate.vue'),
    meta: {
      requiresAuth: true,
      title: 'Dodaj szlak - Dashboard'
    }
  }
]