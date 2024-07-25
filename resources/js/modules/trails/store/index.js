import axios from 'axios';
import apiClient from "@/plugins/apiClient.js";

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
    async fetchTrails({ commit }, bounds) {
        commit('SET_LOADING', true);
        try {
            const response = await apiClient.get('https://kayak-map.test/api/v1/trails', {
                params: {
                    start_lat: bounds.startLat,
                    end_lat: bounds.endLat,
                    start_lng: bounds.startLng,
                    end_lng: bounds.endLng,
                }
            });
            commit('SET_TRAILS', response.data.data);
        } catch (error) {
            commit('SET_ERROR', error);
        } finally {
            commit('SET_LOADING', false);
        }
    }
};

const getters = {
    trails: state => state.trails,
    loading: state => state.loading,
    error: state => state.error,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};
