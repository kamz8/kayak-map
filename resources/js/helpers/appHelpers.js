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

        app.config.globalProperties.$alertInfo = createAlertHelper('info');
        app.config.globalProperties.$alertWarning = createAlertHelper('warning');
        app.config.globalProperties.$alertError = createAlertHelper('error');

        // rejestruj pozosyałe helpery według schematów
    }
}
