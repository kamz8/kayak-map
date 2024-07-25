import apiClient from '@/plugins/apiClient';

const state = {
    trails: [],
};

const mutations = {
    SET_TRAILS(state, trails) {
        state.trails = trails;
    }
};

const actions = {
    async fetchTrails({ commit, dispatch }, bounds) {
        try {
            const response = await apiClient.get('/trails', {
                params: {
                    start_lat: bounds._southWest.lat,
                    end_lat: bounds._northEast.lat,
                    start_lng: bounds._southWest.lng,
                    end_lng: bounds._northEast.lng,
                }
            });
            commit('SET_TRAILS', response.data.data);
            dispatch('system_messages/addMessage', { type: 'error', message: 'Failed to fetch trails.' }, { root: true });
        } catch (error) {
            console.error("Error fetching trails:", error);
        }
    }
};

const getters = {
    trails: state => state.trails,
};

export default {
    state,
    mutations,
    actions,
    getters
};
