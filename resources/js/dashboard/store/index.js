import { createStore } from 'vuex'
import auth from '../modules/auth/store/auth.js'
import ui from './modules/ui.js'
import users from '../modules/users/store/index.js'
import roles from '../modules/roles/store/index.js'

const store = createStore({
  modules: {
    auth,
    ui,
    users,
    roles
  },
  strict: process.env.NODE_ENV !== 'production'
})

export default store
