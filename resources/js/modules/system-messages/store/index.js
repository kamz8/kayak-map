const state = () => ({
    messages: [],
    messageTypes: {
        INFO: 'info',
        WARNING: 'warning',
        ERROR: 'error',
        SUCCESS: 'Success'
    },
    defaultDuration: 3000 // domyślny czas wyświetlania w milisekundach
});

const mutations = {
    ADD_MESSAGE(state, message) {
        state.messages.push(message);
    },
    REMOVE_MESSAGE(state, messageId) {
        const index = state.messages.findIndex(m => m.id === messageId);
        if (index !== -1) {
            state.messages.splice(index, 1);
        }
    }
};

const actions = {
    addMessage({ commit, dispatch, state }, { type, title, text, duration }) {
        const message = {
            id: Date.now(),
            type,
            title,
            text,
            duration: duration || state.defaultDuration
        };
        commit('ADD_MESSAGE', message);
        dispatch('setMessageTimer', message);
    },
    setMessageTimer({ commit }, message) {
        setTimeout(() => {
            commit('REMOVE_MESSAGE', message.id);
        }, message.duration);
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
