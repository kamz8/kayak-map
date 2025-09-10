import { createApp } from 'vue'
import App from './Dashboard.vue'
import router from './router/index.js'
import store from './store/index.js'
import vuetify from './plugins/vuetify.js'
import axios from './plugins/axios.js'

// CSS imports
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import './styles/main.css'

const app = createApp(App)

app.use(router)
app.use(store)
app.use(vuetify)

// Global properties
app.config.globalProperties.$http = axios

// Initialize auth
store.dispatch('auth/initialize')

app.mount('#dashboard-app')
