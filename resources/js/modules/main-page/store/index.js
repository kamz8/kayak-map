const state = {
    trails: [],
};

const mutations = {
    SET_TRAILS(state, trails) {
        state.trails = trails;
    }
};

const actions = {
    setTrails({ commit }, trails) {
        commit('SET_TRAILS', trails);
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
