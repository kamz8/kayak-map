import { createApp } from 'vue'
import App from './Dashboard.vue'
import router from './router/index.js'
import store from './store/index.js'
import vuetify from './plugins/vuetify.js'
import axios from './plugins/axios.js'
import { createPermissionService } from './services/PermissionService.js'

// CSS imports
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import './styles/main.css'
import './design-system/styles.css'

const app = createApp(App)

app.use(router)
app.use(store)
app.use(vuetify)

// Provide store for composables (allows inject in lazy-loaded components)
app.provide('store', store)

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-Client-Type'] = 'web';

// Initialize permission service
const permissionService = createPermissionService(store)

// Global properties
app.config.globalProperties.$http = axios
app.config.globalProperties.$permissions = permissionService
app.config.globalProperties.$can = (permission) => permissionService.can(permission)
app.config.globalProperties.$canAny = (permissions) => permissionService.canAny(permissions)
app.config.globalProperties.$canAll = (permissions) => permissionService.canAll(permissions)
app.config.globalProperties.$hasRole = (role) => permissionService.hasRole(role)
app.config.globalProperties.$hasAnyRole = (roles) => permissionService.hasAnyRole(roles)
app.config.globalProperties.$isAdmin = () => permissionService.isAdmin()
app.config.globalProperties.$isSuperAdmin = () => permissionService.isSuperAdmin()

// Initialize auth
store.dispatch('auth/initialize')

// Global error handlers
window.addEventListener('unhandledrejection', (event) => {
  console.error('Unhandled promise rejection:', event.reason)

  // Handle session expiration errors
  if (event.reason?.code === 'REFRESH_TOKEN_EXPIRED' ||
      event.reason?.message?.includes('Refresh token expired') ||
      event.reason?.message?.includes('Session expired')) {
    // Prevent default error logging
    event.preventDefault()

    // Show user-friendly message if not already shown
    if (!window.location.href.includes('session_expired=true')) {
      store.dispatch('ui/showError', 'Twoja sesja wygasła. Zaloguj się ponownie.')
    }
  }
})

// Handle global errors
app.config.errorHandler = (err, instance, info) => {
  console.error('Global error:', err, info)

  // Handle session expiration
  if (err?.code === 'REFRESH_TOKEN_EXPIRED' ||
      err?.message?.includes('Refresh token expired') ||
      err?.message?.includes('Session expired')) {
    store.dispatch('ui/showError', 'Twoja sesja wygasła. Zaloguj się ponownie.')
  }
}

app.mount('#dashboard-app')
