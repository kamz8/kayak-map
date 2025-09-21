import apiClient from '@/dashboard/plugins/axios.js'
import { JwtUtils } from '@/dashboard/utils/jwt.js'
import { tokenManager } from '@/dashboard/utils/tokenManager.js'

const state = () => ({
  token: localStorage.getItem('token') || null,
  refreshToken: localStorage.getItem('refresh_token') || null,
  user: null,
  loading: false,
  error: null
})

const mutations = {
  SET_TOKEN(state, token) {
    state.token = token
    if (token) {
      localStorage.setItem('token', token)
    } else {
      localStorage.removeItem('token')
    }
  },
  SET_REFRESH_TOKEN(state, refreshToken) {
    state.refreshToken = refreshToken
    if (refreshToken) {
      localStorage.setItem('refresh_token', refreshToken)
    } else {
      localStorage.removeItem('refresh_token')
    }
  },
  SET_TOKENS(state, { accessToken, refreshToken }) {
    state.token = accessToken
    state.refreshToken = refreshToken

    if (accessToken) {
      localStorage.setItem('token', accessToken)
    } else {
      localStorage.removeItem('token')
    }

    if (refreshToken) {
      localStorage.setItem('refresh_token', refreshToken)
    } else {
      localStorage.removeItem('refresh_token')
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
    state.refreshToken = null
    state.user = null
    state.error = null
    localStorage.removeItem('token')
    localStorage.removeItem('refresh_token')
    tokenManager.clearTokens()
  }
}

const actions = {
  async login({ commit }, credentials) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      console.log('Attempting login...', credentials.email)
      const response = await apiClient.post('/auth/login', credentials)
      console.log('Login response:', response.data)

      const { access_token, refresh_token, expires_in } = response.data.data

      if (!JwtUtils.isValid(access_token)) {
        throw new Error('Otrzymano nieprawidłowy token JWT')
      }

      const user = JwtUtils.getUser(access_token)
      if (!user) {
        throw new Error('Nie można zdekodować danych użytkownika z tokena')
      }

      if (!JwtUtils.canAccessDashboard(access_token)) {
        const error = new Error('Brak uprawnień do panelu administracyjnego')
        error.response = { status: 403, data: { message: 'Brak uprawnień administratora' } }
        throw error
      }

      // Store tokens in Vuex and localStorage
      commit('SET_TOKENS', {
        accessToken: access_token,
        refreshToken: refresh_token
      })
      commit('SET_USER', user)

      // Initialize token manager with automatic refresh
      tokenManager.initialize(access_token, refresh_token)

      console.log('Login successful, user from JWT:', user)
      console.log('Token expires in:', expires_in, 'seconds')

      return {
        data: {
          access_token,
          refresh_token,
          expires_in,
          user
        }
      }
    } catch (error) {
      console.error('Login error:', error)
      const message = error.response?.data?.message || error.message || 'Błąd podczas logowania'
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

  async fetchUser({ commit, state }) {
    try {
      commit('SET_LOADING', true)

      if (!state.token) {
        throw new Error('Brak tokena uwierzytelnienia')
      }

      if (!JwtUtils.isValid(state.token)) {
        throw new Error('Token wygasł lub jest nieprawidłowy')
      }

      const user = JwtUtils.getUser(state.token)
      if (!user) {
        throw new Error('Nie można zdekodować danych użytkownika z tokena')
      }

      if (!JwtUtils.canAccessDashboard(state.token)) {
        const error = new Error('Brak uprawnień do panelu administracyjnego')
        error.response = { status: 403, data: { message: 'Brak uprawnień administratora' } }
        throw error
      }

      commit('SET_USER', user)
      return user
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Błąd podczas pobierania danych użytkownika'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async initialize({ commit, dispatch, state }) {
    let accessToken = localStorage.getItem('token')
    let refreshToken = localStorage.getItem('refresh_token')

    if (!accessToken) {
      // No token found - user is not authenticated
      return
    }

    // Check if access token is valid
    if (!JwtUtils.isValid(accessToken)) {
      console.warn('Access token is invalid or expired')

      // Try to refresh using refresh token
      if (refreshToken) {
        try {
          console.log('Attempting to refresh expired token...')
          await dispatch('refreshToken')
          return // Success, user is authenticated
        } catch (error) {
          console.warn('Failed to refresh token, clearing auth:', error)
          commit('CLEAR_AUTH')
          return
        }
      } else {
        console.warn('No refresh token available, clearing auth')
        commit('CLEAR_AUTH')
        return
      }
    }

    // Access token is valid, proceed with initialization
    commit('SET_TOKENS', {
      accessToken,
      refreshToken
    })

    // Initialize token manager
    tokenManager.initialize(accessToken, refreshToken)

    try {
      await dispatch('fetchUser')
    } catch (error) {
      // If fetchUser fails, token is probably invalid
      console.warn('Failed to fetch user data, clearing auth:', error)
      commit('CLEAR_AUTH')
      throw error
    }
  },

  async refreshToken({ commit, state }) {
    try {
      const refreshToken = state.refreshToken || localStorage.getItem('refresh_token')

      if (!refreshToken) {
        throw new Error('No refresh token available')
      }

      console.log('Refreshing access token...')
      const result = await tokenManager.refreshTokens()

      // Update tokens in store
      commit('SET_TOKENS', {
        accessToken: result.access_token,
        refreshToken: result.refresh_token
      })

      // Update user data from new token
      const user = JwtUtils.getUser(result.access_token)
      commit('SET_USER', user)

      console.log('Token refreshed successfully')
      return result

    } catch (error) {
      console.error('Token refresh failed:', error)
      commit('CLEAR_AUTH')
      throw error
    }
  },

}

const getters = {
  isAuthenticated: (state) => {
    return !!state.token && JwtUtils.isValid(state.token)
  },
  user: (state) => state.user,
  loading: (state) => state.loading,
  error: (state) => state.error,
  token: (state) => state.token,
  refreshToken: (state) => state.refreshToken,
  hasRole: (state) => (roleName) => {
    if (!state.token) return false
    return JwtUtils.hasRole(state.token, roleName)
  },
  hasPermission: (state) => (permissionName) => {
    if (!state.token) return false
    return JwtUtils.hasPermission(state.token, permissionName)
  },
  canAccessDashboard: (state) => {
    if (!state.token) return false
    return JwtUtils.canAccessDashboard(state.token)
  },
  tokenExpiration: (state) => {
    if (!state.token) return null
    return JwtUtils.getTokenExpiration(state.token)
  },
  timeToExpire: (state) => {
    if (!state.token) return 0
    return JwtUtils.getTimeToExpire(state.token)
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}
