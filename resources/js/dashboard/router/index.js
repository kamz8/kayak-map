import { createRouter, createWebHistory } from 'vue-router'
import store from '../store/index.js'

// Auth module
import { authRoutes } from '../modules/auth'

// Trails module  
import { trailsRoutes } from '../modules/trails'

// Dashboard views - lazy loaded
const DashboardOverview = () => import('../views/dashboard/Overview.vue')

const routes = [
  // Auth module routes
  ...authRoutes,
  
  // Dashboard home
  {
    path: '/dashboard',
    name: 'DashboardHome',
    component: DashboardOverview,
    meta: {
      requiresAuth: true,
      title: 'Dashboard - Kayak Map'
    }
  },
  
  // Trails module routes
  ...trailsRoutes,
  
  // Catch all redirect
  {
    path: '/dashboard/:pathMatch(.*)*',
    redirect: '/dashboard'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Route guards
router.beforeEach(async (to, from, next) => {
  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title
  }

  const isAuthenticated = store.getters['auth/isAuthenticated']

  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!isAuthenticated) {
      try {
        await store.dispatch('auth/initialize')
        if (store.getters['auth/isAuthenticated']) {
          next()
        } else {
          next('/dashboard/login')
        }
      } catch (error) {
        next('/dashboard/login')
      }
    } else {
      next()
    }
  }
  // Check if route requires guest (like login page)
  else if (to.meta.requiresGuest) {
    if (isAuthenticated) {
      next('/dashboard')
    } else {
      next()
    }
  }
  // Public routes
  else {
    next()
  }
})

export default router
