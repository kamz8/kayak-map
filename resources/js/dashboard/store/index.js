import { createStore } from 'vuex'
import auth from '../modules/auth/store/auth.js'
import ui from './modules/ui.js'
import breadcrumbs from './modules/breadcrumbs.js'
import users from '../modules/users/store/index.js'
import roles from '../modules/roles/store/index.js'
import permissions from '../modules/permissions/store/index.js'

const store = createStore({
  modules: {
    auth,
    ui,
    breadcrumbs,
    users,
    roles,
    permissions
  },
  strict: process.env.NODE_ENV !== 'production'
})

export default store
