
import { inject } from 'vue';

export default {
    install(app) {
        const store = app.config.globalProperties.$store;
        if (!store) {
            console.error('Vuex store is not initialized');
            return;
        }

        app.config.globalProperties.$alert = function ({ type, title, text, duration }) {
            store.dispatch('system-messages/addMessage', {
                type,
                title,
                text,
                duration: duration || 5000
            });
        }

        const createAlertHelper = (type) => {
            return function (text, title, duration) {
                const messageTypes = store.getters['system-messages/messageTypes'];
                if (!messageTypes) {
                    console.error('Message types are not defined in the store');
                    return;
                }
                const messageType = messageTypes[type.toUpperCase()];
                this.$alert({
                    type: messageType,
                    title: title || type.charAt(0).toUpperCase() + type.slice(1),
                    text,
                    duration
                });
            }
        }

        const config = (dotString) => {
            // Pobieramy konfigurację z inject
            const configObject = inject('config');
            if (!configObject) {
                console.error('Global config is not provided.');
                return undefined;
            }

            // Funkcja do nawigacji po obiekcie konfiguracyjnym
            return dotString.split('.').reduce((o, key) => (o && o[key] !== undefined ? o[key] : undefined), configObject);
        };

        app.config.globalProperties.$alertInfo = createAlertHelper('info');
        app.config.globalProperties.$alertSuccess = createAlertHelper('success');
        app.config.globalProperties.$alertWarning = createAlertHelper('warning');
        app.config.globalProperties.$alertError = createAlertHelper('error');
        app.config.globalProperties.$config = (dotString) => {
            // Użyj options.config jako domyślnego obiektu konfiguracyjnego
            // Funkcja do obsługi notacji kropkowej
            return dotString.split('.').reduce((o, key) => (o && o[key] !== undefined ? o[key] : undefined), configObject);
        };

    }
}
