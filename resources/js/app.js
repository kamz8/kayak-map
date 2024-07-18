import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import vuetify from './plugins/vuetify';
import 'leaflet/dist/leaflet.css'; // Import Leaflet CSS
import '../css/app.css'; // Import globalnych stylów

const app = createApp(App);

app.use(router);
app.use(store);
app.use(vuetify);

app.mount('#app');
