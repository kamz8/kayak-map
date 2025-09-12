import { createRouter, createWebHistory } from 'vue-router'
import store from '../store/index.js'

// Auth module
import { authRoutes } from '../modules/auth'

// Dashboard views - lazy loaded
const DashboardLayout = () => import('../layouts/DashboardLayout.vue')
const DashboardOverview = () => import('../views/dashboard/Overview.vue')

// Autoloader for module routes using import.meta.glob
const moduleRoutes = []
const modules = import.meta.glob('../modules/**/router/*.js', { eager: true })

for (const path in modules) {
  const routes = modules[path].default
  if (Array.isArray(routes)) {
    moduleRoutes.push(...routes)
  }
}

const routes = [
  // Auth module routes (no layout)
  ...authRoutes,
  
  // Dashboard with layout
  {
    path: '/dashboard',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'DashboardHome',
        component: DashboardOverview,
        meta: {
          title: 'Dashboard - Kayak Map',
          breadcrumbs: []
        }
      },
      // Auto-loaded module routes as children
      ...moduleRoutes.map(route => ({
        ...route,
        path: route.path.replace('/dashboard/', '')
      }))
    ]
  }
]

// Export routes for navigation generation
export const navigationRoutes = moduleRoutes.filter(route => route.meta?.navigation)

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Router middleware
router.beforeEach(async (to, from, next) => {
  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title
  }

  // Prevent infinite redirect loops
  if (to.path === from.path) {
    return next(false)
  }

  // Temporary: Skip auth for dashboard development
  next()

  // try {
  //   // Initialize auth state once
  //   await store.dispatch('auth/initialize')
  //   const isAuthenticated = store.getters['auth/isAuthenticated']

  //   // Handle auth routes
  //   if (to.meta.requiresAuth && !isAuthenticated) {
  //     return next('/dashboard/login')
  //   }

  //   // Handle guest routes (redirect authenticated users)
  //   if (to.meta.requiresGuest && isAuthenticated) {
  //     return next('/dashboard')
  //   }

  //   // Allow route
  //   next()

  // } catch (error) {
  //   console.error('Router auth error:', error)
  //   // On auth error, redirect to login only if not already there
  //   if (to.path !== '/dashboard/login') {
  //     next('/dashboard/login')
  //   } else {
  //     next()
  //   }
  // }
})

export default router
