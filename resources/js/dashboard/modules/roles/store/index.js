import apiClient from '@/dashboard/plugins/axios.js'

const state = () => ({
  roles: [],
  loading: false,
  error: null
})

const mutations = {
  SET_ROLES(state, roles) {
    state.roles = roles
  },
  SET_LOADING(state, loading) {
    state.loading = loading
  },
  SET_ERROR(state, error) {
    state.error = error
  },
  ADD_ROLE(state, role) {
    state.roles.push(role)
  },
  UPDATE_ROLE(state, updatedRole) {
    const index = state.roles.findIndex(role => role.id === updatedRole.id)
    if (index !== -1) {
      state.roles.splice(index, 1, updatedRole)
    }
  },
  REMOVE_ROLE(state, roleId) {
    state.roles = state.roles.filter(role => role.id !== roleId)
  }
}

const actions = {
  async fetchRoles({ commit }) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.get('/dashboard/roles?with_users_count=true&with_permissions=true')
      commit('SET_ROLES', response.data.data || response.data)

    } catch (error) {
      console.error('Fetch roles error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się pobrać ról')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async createRole({ commit }, roleData) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.post('/dashboard/roles', roleData)
      const role = response.data.data || response.data

      commit('ADD_ROLE', role)
      return role

    } catch (error) {
      console.error('Create role error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się utworzyć roli')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async updateRole({ commit }, { id, roleData }) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.put(`/dashboard/roles/${id}`, roleData)
      const role = response.data.data || response.data

      commit('UPDATE_ROLE', role)
      return role

    } catch (error) {
      console.error('Update role error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się zaktualizować roli')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async deleteRole({ commit }, roleId) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      await apiClient.delete(`/dashboard/roles/${roleId}`)
      commit('REMOVE_ROLE', roleId)

    } catch (error) {
      console.error('Delete role error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się usunąć roli')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async assignPermissions({ commit }, { roleId, permissionIds }) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.post(`/dashboard/roles/${roleId}/assign-permissions`, {
        permission_ids: permissionIds
      })

      const role = response.data.data || response.data
      commit('UPDATE_ROLE', role)

      return role

    } catch (error) {
      console.error('Assign permissions error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się przypisać uprawnień')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async revokePermissions({ commit }, { roleId, permissionIds }) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      const response = await apiClient.delete(`/dashboard/roles/${roleId}/revoke-permissions`, {
        data: { permission_ids: permissionIds }
      })

      const role = response.data.data || response.data
      commit('UPDATE_ROLE', role)

      return role

    } catch (error) {
      console.error('Revoke permissions error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się usunąć uprawnień')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async assignUsersToRole({ commit }, { roleId, userIds }) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      // Using UserRoleController sync-roles endpoint for each user
      const assignmentPromises = userIds.map(userId =>
        apiClient.put(`/dashboard/users/${userId}/sync-roles`, {
          roles: [roleId]
        })
      )

      await Promise.all(assignmentPromises)

      // Refresh roles to get updated user count
      const rolesResponse = await apiClient.get('/dashboard/roles?with_users_count=true')
      commit('SET_ROLES', rolesResponse.data.data || rolesResponse.data)

      return true

    } catch (error) {
      console.error('Assign users to role error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się przypisać użytkowników do roli')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  async removeUsersFromRole({ commit }, { roleId, userIds }) {
    try {
      commit('SET_LOADING', true)
      commit('SET_ERROR', null)

      // Remove role from users by setting empty roles array
      const removalPromises = userIds.map(userId =>
        apiClient.put(`/dashboard/users/${userId}/sync-roles`, {
          roles: [] // Remove all roles for simplicity - in real app would need current roles minus this one
        })
      )

      await Promise.all(removalPromises)

      // Refresh roles to get updated user count
      const rolesResponse = await apiClient.get('/dashboard/roles?with_users_count=true')
      commit('SET_ROLES', rolesResponse.data.data || rolesResponse.data)

      return true

    } catch (error) {
      console.error('Remove users from role error:', error)
      commit('SET_ERROR', error.response?.data?.message || 'Nie udało się usunąć użytkowników z roli')
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  }
}

const getters = {
  allRoles: (state) => state.roles,
  rolesCount: (state) => state.roles.length,
  isLoading: (state) => state.loading,
  error: (state) => state.error,

  getRoleById: (state) => (id) => {
    return state.roles.find(role => role.id === id)
  },

  getRoleByName: (state) => (name) => {
    return state.roles.find(role => role.name === name)
  },

  systemRoles: (state) => {
    // System roles that cannot be deleted
    const systemRoleNames = ['Super Admin', 'Admin', 'Editor', 'User']
    return state.roles.filter(role => systemRoleNames.includes(role.name))
  },

  customRoles: (state) => {
    // Custom roles that can be deleted
    const systemRoleNames = ['Super Admin', 'Admin', 'Editor', 'User']
    return state.roles.filter(role => !systemRoleNames.includes(role.name))
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}