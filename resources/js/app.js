import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import vuetify from './plugins/vuetify';
import '../css/app.css'; // Import globalnych stylów
import 'leaflet/dist/leaflet.css';
import appHelpers from "@/helpers/appHelpers.js";
import VueMoment from 'vue-moment';
import moment from 'moment-timezone';

// Ustawienie domyślnej strefy czasowej na Polskę
moment.tz.setDefault('Europe/Warsaw');

// Ustawienie domyślnego języka na polski
moment.locale('pl');

const app = createApp(App);

app.use(router);
app.use(store);
app.use(vuetify);
// Use the app helper
app.use(appHelpers);

app.mount('#app');
