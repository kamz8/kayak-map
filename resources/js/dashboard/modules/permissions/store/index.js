import apiClient from '@/dashboard/plugins/axios.js'

const state = () => ({
  permissions: [],
  groupedPermissions: {},
  loading: false,
  error: null
})

const mutations = {
  SET_PERMISSIONS(state, permissions) {
    state.permissions = permissions
  },
  SET_GROUPED_PERMISSIONS(state, groupedPermissions) {
    state.groupedPermissions = groupedPermissions
  },
  SET_LOADING(state, loading) {
    state.loading = loading
  },
  SET_ERROR(state, error) {
    state.error = error
  }
}

const actions = {
  async fetchPermissions({ commit }) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.get('/dashboard/permissions')
      commit('SET_PERMISSIONS', response.data.data || response.data)

    } catch (error) {
      console.error('Fetch permissions error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się pobrać uprawnień')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async fetchGroupedPermissions({ commit }) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.get('/dashboard/permissions?grouped=true')
      commit('SET_GROUPED_PERMISSIONS', response.data.data || response.data)

    } catch (error) {
      console.error('Fetch grouped permissions error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się pobrać pogrupowanych uprawnień')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async fetchRolePermissions({ commit }, roleId) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.get(`/dashboard/roles/${roleId}`)
      const role = response.data.data || response.data

      return role.permissions || []

    } catch (error) {
      console.error('Fetch role permissions error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się pobrać uprawnień roli')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  }
}

const getters = {
  allPermissions: (state) => state.permissions,
  groupedPermissions: (state) => state.groupedPermissions,
  permissionsCount: (state) => state.permissions.length,
  isLoading: (state) => state.loading,
  error: (state) => state.error,

  getPermissionById: (state) => (id) => {
    return state.permissions.find(permission => permission.id === id)
  },

  getPermissionsByModule: (state) => (module) => {
    return state.permissions.filter(permission => permission.module === module)
  },

  availableModules: (state) => {
    const modules = new Set(state.permissions.map(p => p.module || 'general'))
    return Array.from(modules).sort()
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}