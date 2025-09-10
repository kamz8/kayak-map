import apiClient from '@/dashboard/plugins/axios.js'

const state = () => ({
  token: localStorage.getItem('dashboard_token') || null,
  user: null,
  loading: false,
  error: null
})

const mutations = {
  SET_TOKEN(state, token) {
    state.token = token
    if (token) {
      localStorage.setItem('dashboard_token', token)
    } else {
      localStorage.removeItem('dashboard_token')
    }
  },
  SET_USER(state, user) {
    state.user = user
  },
  SET_LOADING(state, status) {
    state.loading = status
  },
  SET_ERROR(state, error) {
    state.error = error
  },
  CLEAR_AUTH(state) {
    state.token = null
    state.user = null
    state.error = null
    localStorage.removeItem('dashboard_token')
  }
}

const actions = {
  async login({ commit }, credentials) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.post('/auth/login', credentials)
      const { token, user } = response.data

      commit('SET_TOKEN', token)
      commit('SET_USER', user)

      return response.data
    } catch (error) {
      const message = error.response?.data?.message || 'Błąd podczas logowania'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async logout({ commit }) {
    try {
      await apiClient.post('/auth/logout')
    } catch (error) {
      console.error('Błąd podczas wylogowywania:', error)
    } finally {
      commit('CLEAR_AUTH')
      window.location.href = '/dashboard/login'
    }
  },

  async fetchUser({ commit }) {
    try {
      commit('SET_LOADING', true)
      const response = await apiClient.get('/auth/user')
      commit('SET_USER', response.data)
      return response.data
    } catch (error) {
      const message = error.response?.data?.message || 'Błąd podczas pobierania danych użytkownika'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async initialize({ commit, dispatch }) {
    const token = localStorage.getItem('dashboard_token')
    if (token) {
      commit('SET_TOKEN', token)
      try {
        await dispatch('fetchUser')
      } catch (error) {
        commit('CLEAR_AUTH')
        throw error
      }
    }
  }
}

const getters = {
  isAuthenticated: (state) => !!state.token && !!state.user,
  user: (state) => state.user,
  loading: (state) => state.loading,
  error: (state) => state.error,
  token: (state) => state.token
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}
