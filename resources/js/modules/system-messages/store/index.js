const state = {
    messages: [],
    messageTypes: {
        INFO: 'info',
        WARNING: 'warning',
        ERROR: 'error',
    }
};

const mutations = {
    ADD_MESSAGE(state, message) {
        state.messages.push(message);
    },
    REMOVE_MESSAGE(state, index) {
        state.messages.splice(index, 1);
    }
};

const actions = {
    addMessage({ commit }, message) {
        commit('ADD_MESSAGE', message);
        setTimeout(() => {
            commit('REMOVE_MESSAGE', state.messages.indexOf(message));
        }, 3000); // Usuwa po 3 sekundach
    }
};

const getters = {
    messages: (state) => state.messages,
    messageTypes: (state) => state.messageTypes,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};
