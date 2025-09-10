// Auth module exports
export { default as store } from './store'
export { default as LoginView } from './views/LoginView.vue'

// Auth module routes
export const authRoutes = [
  {
    path: '/dashboard/login',
    name: 'DashboardLogin',
    component: () => import('./views/LoginView.vue'),
    meta: {
      requiresGuest: true,
      title: 'Dashboard - Logowanie'
    }
  }
]