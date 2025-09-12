import apiClient from '@/dashboard/plugins/axios.js'

const state = () => ({
  token: localStorage.getItem('dashboard_token') || null,
  user: {
    id: 1,
    first_name: 'Jan',
    last_name: 'Kowalski',
    name: 'Jan Kowalski',
    email: 'jan.kowalski@example.com',
    phone: '+48 123 456 789',
    phone_verified: true,
    email_verified_at: '2024-01-15T10:30:00Z',
    bio: 'Pasjonat kajakowania i eksploracji wodnych szlaków Polski. Od ponad 10 lat organizuję wyprawy kajakowe dla grup początkujących i zaawansowanych.',
    location: 'Kraków, Polska', 
    birth_date: '1990-05-15',
    gender: 'male',
    is_active: true,
    is_admin: true,
    last_login_at: '2024-09-12T08:15:30Z',
    avatar: null,
    preferences: {
      email_notifications: true,
      language: 'pl'
    },
    notification_settings: {
      enabled: true,
      email: true,
      push: false
    },
    created_at: '2024-01-10T12:00:00Z',
    updated_at: '2024-09-12T08:15:30Z'
  },
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
  },

  async updateProfile({ commit, state }, profileData) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      // Symulacja API call - w rzeczywistości to byłby POST do /auth/profile
      // const response = await apiClient.put('/auth/profile', profileData)
      
      // Mock update - aktualizujemy stan bezpośrednio
      const updatedUser = { ...state.user, ...profileData, updated_at: new Date().toISOString() }
      commit('SET_USER', updatedUser)

      return updatedUser
    } catch (error) {
      const message = error.response?.data?.message || 'Błąd podczas aktualizacji profilu'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async changePassword({ commit }, passwordData) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      // Symulacja API call
      // const response = await apiClient.post('/auth/change-password', passwordData)
      
      // Mock success
      return { success: true, message: 'Hasło zostało zmienione pomyślnie' }
    } catch (error) {
      const message = error.response?.data?.message || 'Błąd podczas zmiany hasła'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async deleteAccount({ commit }, confirmation) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      // Symulacja API call
      // const response = await apiClient.delete('/auth/account', { data: { confirmation } })
      
      // Mock success - wylogowanie po usunięciu
      commit('CLEAR_AUTH')
      window.location.href = '/dashboard/login'
      
      return { success: true, message: 'Konto zostało usunięte' }
    } catch (error) {
      const message = error.response?.data?.message || 'Błąd podczas usuwania konta'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  }
}

const getters = {
  isAuthenticated: (state) => !!state.token,
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
