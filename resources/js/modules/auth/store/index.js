import apiClient from "@/plugins/apiClient.js"

const state = {
    token: localStorage.getItem('token') || null,
    user: null,
    loading: false,
    error: null,
    authMessage: null
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
    },

    // ... existing mutations
    SET_AUTH_MESSAGE(state, message) {
        state.authMessage = message
    },
    CLEAR_AUTH_MESSAGE(state) {
        state.authMessage = null
    }
}

const actions = {
    // Standardowe logowanie
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
            commit('SET_ERROR', error.response?.data?.message || 'Błąd podczas logowania')
            throw error
        } finally {
            commit('SET_LOADING', false)
        }
    },

    // Logowanie przez social media
    async handleSocialLogin({ commit }, provider) {
        try {
            commit('SET_LOADING', true)
            commit('SET_ERROR', null)


            // Konfiguracja okna popup
            const width = 600
            const height = 600
            const left = window.screen.width / 2 - width / 2
            const top = window.screen.height / 2 - height / 2

            const response = await apiClient.get(`auth/social/${provider}/redirect`);

            if (!response.data?.data?.url) {
                throw new Error('Nie otrzymano URL autoryzacji');
            }

            // Otwieramy okno z otrzymanym URL
            const authWindow = window.open(
                response.data.data.url,
                'OAuth',
                `width=${width},height=${height},left=${left},top=${top}`
            );

            // Obsługa komunikacji między oknami
            const handleMessage = async (event) => {
                // Sprawdzamy czy wiadomość pochodzi z naszej domeny
                if (event.origin !== window.location.origin) return

                try {
                    if (event.data.code) {
                        const config = {
                            headers: event.data.headers
                        };

                        // Wysyłamy kod do naszego API
                        const response = await apiClient.post(`auth/social/${provider}/callback`, {
                            code: event.data.code
                        })

                        const { token, user } = response.data
                        commit('SET_TOKEN', token)
                        commit('SET_USER', user)
                        commit('SET_ERROR', null)

                        // Zamykamy okno i czyścimy listener
                        authWindow.close()
                        window.removeEventListener('message', handleMessage)
                        window.location.href = '/'
                    }

                    if (event.data.error) {
                        throw new Error(event.data.error)
                    }
                } catch (error) {
                    commit('SET_ERROR', error.message || 'Błąd podczas logowania')
                    throw error
                }
            }

            // Nasłuchujemy na wiadomość z okna OAuth
            window.addEventListener('message', handleMessage)
        } catch (error) {
            commit('SET_ERROR', error.message || `Błąd podczas logowania przez ${provider}`)
            throw error
        } finally {
            commit('SET_LOADING', false)
        }
    },

    // Obsługa callback z social login
    async handleAuthCallback({ commit }, { provider, code }) {
        try {
            commit('SET_LOADING', true)
            commit('SET_ERROR', null)
            console.log(apiClient)
            const response = (await apiClient.post(`auth/social/${provider}/callback`, {code}))
            const { token, user } = response.data

            commit('SET_TOKEN', token)
            commit('SET_USER', user)

            window.location.href = '/' // Przekierowanie na główną po udanym logowaniu
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
            await apiClient.post('/auth/logout')
        } catch (error) {
            console.error('Błąd podczas wylogowywania:', error)
        } finally {
            commit('CLEAR_AUTH')
            commit('SET_LOADING', false)
            window.location.href = '/login'
        }
    },

    // Pobranie danych użytkownika
    async fetchUser({ commit }) {
        try {
            commit('SET_LOADING', true)
            const response = await apiClient.get('/auth/user')
            commit('SET_USER', response.data)
            return response.data
        } catch (error) {
            commit('SET_ERROR', error.response?.data?.message || 'Błąd podczas pobierania danych użytkownika')
            throw error
        } finally {
            commit('SET_LOADING', false)
        }
    },

    // Inicjalizacja stanu przy starcie aplikacji
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
    },

    async sendResetLink({ commit, dispatch }) {

    },

    async resetPassword({ commit }, formData) {
        try {
            commit('SET_LOADING', true)
            const response = await apiClient.post('/auth/reset-password', formData)
            return response.data
        } catch (error) {
            throw error
        } finally {
            commit('SET_LOADING', false)
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
