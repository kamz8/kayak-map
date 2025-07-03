import apiClient from "@/plugins/apiClient.js"

const state = {
    trails: [],
    loading: false,
    error: null,
    filters: {
        searchQuery: { value: null, touched: false },
        length: { min: 0, max: 100000, touched: false },
        scenery: { value: 0, touched: false },
        rating: { value: 0, touched: false },
        difficulty: { value: [], touched: false }
    },
    boundingBox: {
        start_lat: null,
        end_lat: null,
        start_lng: null,
        end_lng: null
    },
    activeTrail: null,
    highlightedTrail: null,
    currentTrail: null,
    currentTrailLoading: false,
    selectedPoint: null, // Currently selected point for bidirectional sync
    selectedPointId: null // ID of selected point for easier tracking

}

const mutations = {
    SET_TRAILS(state, trails) {
        state.trails = trails
    },
    SET_LOADING(state, loading) {
        state.loading = loading
    },
    SET_ERROR(state, error) {
        state.error = error
    },
    SET_FILTER(state, { filterName, value, touched }) {
        state.filters[filterName] = { value, touched }
    },
    SET_BOUNDING_BOX(state, boundingBox) {
        state.boundingBox = boundingBox
    },
    SET_ACTIVE_TRAIL(state, trail) {
        state.activeTrail = trail
    },
    SET_HIGHLIGHTED_TRAIL(state, trail) {
        state.highlightedTrail = trail
    },
    SET_CURRENT_TRAIL(state, trail) {
        state.currentTrail = trail
    },
    SET_CURRENT_TRAIL_LOADING(state, loading) {
        state.currentTrailLoading = loading
    },
    SET_SELECTED_POINT(state, point) {
        state.selectedPoint = point
        state.selectedPointId = point?.id || null
    },
    CLEAR_SELECTED_POINT(state) {
        state.selectedPoint = null
        state.selectedPointId = null
    }
}

const actions = {
    updateFilter({ commit, dispatch }, { filterName, value, touched = true }) {
        commit('SET_FILTER', { filterName, value, touched })
    },

    clearFilter({ commit, dispatch }, filterName) {
        const defaultValue = getDefaultFilterValue(filterName)
        commit('SET_FILTER', { filterName, value: defaultValue, touched: false })
        dispatch('fetchTrails')
    },

    applyFilters({ dispatch }) {
        dispatch('fetchTrails')
    },

    async fetchTrails({ commit, dispatch, state }) {
        commit('SET_LOADING', true)
        commit('SET_ERROR', null)
        const params = { ...state.boundingBox }

        Object.entries(state.filters).forEach(([key, filter]) => {
            if (filter.touched) {
                if (key === 'length') {
                    params.min_length = filter.value.min * 1000
                    params.max_length = filter.value.max * 1000
                } else {
                    const snake_case_key = key.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`)
                    params[snake_case_key] = filter.value
                }
            }
        })

        try {
            const response = await apiClient.get('/trails', { params })
            commit('SET_TRAILS', response.data.data)
        } catch (error) {
            commit('SET_ERROR', error)
            dispatch('system_messages/addMessage', {
                type: 'error',
                text: 'Wystąpił błąd podczas pobierania tras. Spróbuj ponownie później.'
            }, { root: true })
        } finally {
            commit('SET_LOADING', false)
        }
    },

    updateBoundingBox({ commit, dispatch }, boundingBox) {
        commit('SET_BOUNDING_BOX', boundingBox)
        dispatch('fetchTrails')
    },

    selectTrail({ commit }, trail) {
        commit('SET_ACTIVE_TRAIL', trail)
    },

    highlightTrail({ commit }, trail) {
        commit('SET_HIGHLIGHTED_TRAIL', trail)
    },

    clearHighlightTrail({ commit }) {
        commit('SET_HIGHLIGHTED_TRAIL', null)
    },

    clearActiveTrail({ commit }) {
        commit('SET_ACTIVE_TRAIL', null);
    },

    async fetchTrailDetails({ commit, dispatch }, slug) {
        commit('SET_CURRENT_TRAIL_LOADING', true)
        commit('SET_CURRENT_TRAIL', null)
        try {
            const response = await apiClient.get(`/trail/${slug}`)
            commit('SET_CURRENT_TRAIL', response.data.data)
        } catch (error) {

            if (error.response?.status === 404) {
                throw error; // Propagate 404 to the router
            } else {
                dispatch('system-messages/addMessage', {
                    type: 'error',
                    text: 'Nie udało się pobrać szczegółów trasy. Spróbuj ponownie później.'
                }, { root: true })
                console.error('Error fetching trail details:', error)
                throw error
            }

        } finally {
            commit('SET_CURRENT_TRAIL_LOADING', false)
        }
    },

    clearCurrentTrail({ commit }) {
        commit('SET_CURRENT_TRAIL', null)
    },

    selectPoint({ commit }, point) {
        commit('SET_SELECTED_POINT', point)
    },
    clearSelectedPoint({ commit }) {
        commit('CLEAR_SELECTED_POINT')
    }
}

const getters = {
    trails: state => state.trails,
    loading: state => state.loading,
    error: state => state.error,
    filters: state => state.filters,
    activeTrail: state => state.activeTrail,
    highlightedTrail: state => state.highlightedTrail,
    boundingBox: state => state.boundingBox,
    currentTrail: state => state.currentTrail,
    currentTrailLoading: state => state.currentTrailLoading,

    selectedPoint: state => state.selectedPoint,
    selectedPointId: state => state.selectedPointId
}

function getDefaultFilterValue(filterName) {
    switch (filterName) {
        case 'searchQuery': return null
        case 'length': return { min: 0, max: 100000 }
        case 'scenery': return 0
        case 'rating': return 0
        case 'difficulty': return []
        default: return null
    }
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}
