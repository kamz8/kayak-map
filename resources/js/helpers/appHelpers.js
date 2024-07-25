import { useStore } from 'vuex'

export default {
    install(app) {
        app.config.globalProperties.$alert = function ({ type, text }) {
            const store = useStore()
            store.dispatch('system_messages/addMessage', { type, text })
        }
        app.config.globalProperties.$alertInfo = function (text) {
            const store = useStore()
            const messageType = store.getters['system_messages/messageTypes'].INFO
            this.$alert({ type: messageType, text })
        }
        app.config.globalProperties.$alertWarning = function (text) {
            const store = useStore()
            const messageType = store.getters['system_messages/messageTypes'].WARNING
            this.$alert({ type: messageType, text })
        }
        app.config.globalProperties.$alertError = function (text) {
            const store = useStore()
            const messageType = store.getters['system_messages/messageTypes'].ERROR
            this.$alert({ type: messageType, text })
        }
        app.config.globalProperties.$logMessage = function (message) {
            console.log(message)
        }
        app.config.globalProperties.$formatDate = function (date) {
            return this.$moment(date).format('LL') // Użyj polskiego formatu daty
        }
    }
}
