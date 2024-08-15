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
    highlightedTrail: null
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

    async fetchTrails({ commit, state }) {
        commit('SET_LOADING', true)
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
}

const getters = {
    trails: state => state.trails,
    loading: state => state.loading,
    error: state => state.error,
    filters: state => state.filters,
    activeTrail: state => state.activeTrail,
    highlightedTrail: state => state.highlightedTrail,
    boundingBox: state => state.boundingBox
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
