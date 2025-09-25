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

app.mount('#dashboard-app')
