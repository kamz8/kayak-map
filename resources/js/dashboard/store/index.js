import { createStore } from 'vuex'
import auth from '../modules/auth/store/auth.js'
import ui from './modules/ui.js'

const store = createStore({
  modules: {
    auth,
    ui
  },
  strict: process.env.NODE_ENV !== 'production'
})

export default store
