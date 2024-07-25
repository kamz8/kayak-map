import axios from 'axios';

const state = {
    trails: [],
    loading: false,
    error: null,
};

const mutations = {
    SET_TRAILS(state, trails) {
        state.trails = trails;
    },
    SET_LOADING(state, loading) {
        state.loading = loading;
    },
    SET_ERROR(state, error) {
        state.error = error;
    }
};

const actions = {
    async fetchTrails({ commit }, { startLat, endLat, startLng, endLng, difficulty, scenery }) {
        commit('SET_LOADING', true);
        commit('SET_ERROR', null);
        try {
            const response = await axios.get('http://your-app-url/api/v1/trails', {
                params: {
                    start_lat: startLat,
                    end_lat: endLat,
                    start_lng: startLng,
                    end_lng: endLng,
                    difficulty: difficulty,
                    scenery: scenery,
                },
            });
            commit('SET_TRAILS', response.data.data);
        } catch (error) {
            commit('SET_ERROR', error.message);
        } finally {
            commit('SET_LOADING', false);
        }
    },
    setTrails({ commit }, trails) {
        commit('SET_TRAILS', trails);
    }
};

const getters = {
    trails: state => state.trails,
    loading: state => state.loading,
    error: state => state.error,
};

export default {
    state,
    mutations,
    actions,
    getters
};
