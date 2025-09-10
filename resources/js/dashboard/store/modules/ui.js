const state = () => ({
  loading: false,
  snackbar: {
    show: false,
    message: '',
    color: 'info',
    timeout: 4000
  }
})

const mutations = {
  SET_LOADING(state, status) {
    state.loading = status
  },
  SHOW_SNACKBAR(state, { message, color = 'info', timeout = 4000 }) {
    state.snackbar = {
      show: true,
      message,
      color,
      timeout
    }
  },
  HIDE_SNACKBAR(state) {
    state.snackbar.show = false
  }
}

const actions = {
  showLoading({ commit }) {
    commit('SET_LOADING', true)
  },
  hideLoading({ commit }) {
    commit('SET_LOADING', false)
  },
  showSnackbar({ commit }, payload) {
    commit('SHOW_SNACKBAR', payload)
  },
  showSuccess({ commit }, message) {
    commit('SHOW_SNACKBAR', {
      message,
      color: 'success'
    })
  },
  showError({ commit }, message) {
    commit('SHOW_SNACKBAR', {
      message,
      color: 'error',
      timeout: 6000
    })
  },
  showWarning({ commit }, message) {
    commit('SHOW_SNACKBAR', {
      message,
      color: 'warning'
    })
  },
  showInfo({ commit }, message) {
    commit('SHOW_SNACKBAR', {
      message,
      color: 'info'
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}