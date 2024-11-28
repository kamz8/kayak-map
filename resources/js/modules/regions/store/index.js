import apiClient from "@/plugins/apiClient.js"

const state = {
    loading: false,
    error: null,
}


const mutations = {

    SET_LOADING(state, loading) {
        state.loading = loading
    },
    SET_ERROR(state, error) {
        state.error = error
    },
}

const actions = {

}

const getters = {
    loading: state => state.loading,
    error: state => state.error,
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}
