import apiClient from "@/plugins/apiClient.js"

const state = {
    token: localStorage.getItem('token') || null,
    user: null,
    loading: false,
    error: null
}

const mutations = {
    SET_TOKEN(state, token) {
        state.token = token
        if (token) {
            localStorage.setItem('token', token)
        } else {
            localStorage.removeItem('token')
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
        localStorage.removeItem('token')
    }
}

const actions = {
    // Logowanie standardowe
    async login({ commit }, credentials) {
        try {
            commit('SET_LOADING', true)
            commit('SET_ERROR', null)

            const response = await apiClient.post('auth/login', credentials)
            const { token, user } = response.data

            commit('SET_TOKEN', token)
            commit('SET_USER', user)

            return response.data
        } catch (error) {
            commit('SET_ERROR', error.response?.data?.message || 'Błąd podczas logowania')
            throw error
        } finally {
            commit('SET_LOADING', false)
        }
    },

    // Inicjacja logowania przez social media
    async initSocialLogin({ commit }, provider) {
        try {
            commit('SET_LOADING', true)
            commit('SET_ERROR', null)

            const response = await apiClient.get(`/auth/${provider}/redirect`)
            return response.data
        } catch (error) {
            commit('SET_ERROR', error.response?.data?.message || `Błąd podczas logowania przez ${provider}`)
            throw error
        } finally {
            commit('SET_LOADING', false)
        }
    },

    // Obsługa callback z social login
    async handleSocialCallback({ commit }, { provider, code }) {
        try {
            commit('SET_LOADING', true)
            commit('SET_ERROR', null)

            const response = await apiClient.post(`/api/v1/auth/${provider}/callback`, { code })
            const { token, user } = response.data

            commit('SET_TOKEN', token)
            commit('SET_USER', user)

            return response.data
        } catch (error) {
            commit('SET_ERROR', error.response?.data?.message || 'Błąd podczas autoryzacji')
            throw error
        } finally {
            commit('SET_LOADING', false)
        }
    },

    // Wylogowanie
    async logout({ commit }) {
        try {
            commit('SET_LOADING', true)
            await apiClient.post('/api/v1/auth/logout')
        } catch (error) {
            console.error('Błąd podczas wylogowywania:', error)
        } finally {
            commit('CLEAR_AUTH')
            commit('SET_LOADING', false)
        }
    },

    // Pobranie danych użytkownika
    async fetchUser({ commit }) {
        try {
            commit('SET_LOADING', true)
            const response = await apiClient.get('/api/v1/auth/user')
            commit('SET_USER', response.data)
            return response.data
        } catch (error) {
            commit('SET_ERROR', error.response?.data?.message || 'Błąd podczas pobierania danych użytkownika')
            throw error
        } finally {
            commit('SET_LOADING', false)
        }
    },

    // Inicjalizacja stanu auth przy starcie aplikacji
    async initialize({ commit, dispatch }) {
        const token = localStorage.getItem('token')
        if (token) {
            commit('SET_TOKEN', token)
            try {
                await dispatch('fetchUser')
            } catch (error) {
                commit('CLEAR_AUTH')
            }
        }
    }
}

const getters = {
    isAuthenticated: state => !!state.token,
    user: state => state.user,
    loading: state => state.loading,
    error: state => state.error,
    token: state => state.token
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}
