import apiClient from '@/dashboard/plugins/axios.js'

const state = () => ({
  users: [],
  user: null,
  loading: false,
  error: null,

  // Pagination
  pagination: {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0
  },

  // Filters
  filters: {
    search: '',
    role: '',
    status: '',
    created_from: '',
    created_to: '',
    sort_by: 'created_at',
    sort_direction: 'desc'
  },

  // Available options for filters (loaded from API)
  roles: [],

  statuses: [
    { value: 'active', label: 'Aktywny' },
    { value: 'inactive', label: 'Nieaktywny' },
    { value: 'email_verified', label: 'Email zweryfikowany' },
    { value: 'email_unverified', label: 'Email niezweryfikowany' },
    { value: 'phone_verified', label: 'Telefon zweryfikowany' },
    { value: 'phone_unverified', label: 'Telefon niezweryfikowany' }
  ]
})

const mutations = {
  SET_LOADING(state, loading) {
    state.loading = loading
  },

  SET_ERROR(state, error) {
    state.error = error
  },

  SET_USERS(state, data) {
    state.users = data.data
    state.pagination = data.meta
  },

  SET_USER(state, user) {
    state.user = user
  },

  SET_ROLES(state, roles) {
    state.roles = roles.map(role => ({
      id: role.id,
      value: role.name,
      label: role.name === 'Super Admin' ? 'Super Administrator' :
             role.name === 'Admin' ? 'Administrator' :
             role.name === 'Editor' ? 'Edytor' :
             role.name === 'User' ? 'Użytkownik' : role.name
    }))
  },

  ADD_USER(state, user) {
    state.users.unshift(user)
    state.pagination.total++
  },

  UPDATE_USER(state, updatedUser) {
    const index = state.users.findIndex(user => user.id === updatedUser.id)
    if (index !== -1) {
      state.users.splice(index, 1, updatedUser)
    }

    // Update current user if it's the same
    if (state.user && state.user.id === updatedUser.id) {
      state.user = updatedUser
    }
  },

  REMOVE_USER(state, userId) {
    const index = state.users.findIndex(user => user.id === userId)
    if (index !== -1) {
      state.users.splice(index, 1)
      state.pagination.total--
    }

    // Clear current user if it was deleted
    if (state.user && state.user.id === userId) {
      state.user = null
    }
  },

  SET_FILTERS(state, filters) {
    state.filters = { ...state.filters, ...filters }
  },

  RESET_FILTERS(state) {
    state.filters = {
      search: '',
      role: '',
      status: '',
      created_from: '',
      created_to: '',
      sort_by: 'created_at',
      sort_direction: 'desc'
    }
  },

  SET_PER_PAGE(state, perPage) {
    state.pagination.per_page = perPage
    state.pagination.current_page = 1
  },

  SET_CURRENT_PAGE(state, page) {
    state.pagination.current_page = page
  }
}

const actions = {
  // Fetch users list with filters and pagination
  async fetchUsers({ commit, state }, params = {}) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      const queryParams = {
        page: params.page || state.pagination.current_page,
        per_page: params.per_page || state.pagination.per_page,
        ...state.filters,
        ...params
      }

      // Remove empty filter values
      Object.keys(queryParams).forEach(key => {
        if (queryParams[key] === '' || queryParams[key] === null) {
          delete queryParams[key]
        }
      })

      const response = await apiClient.get('/dashboard/users', {
        params: queryParams
      })

      commit('SET_USERS', response.data)
      return response.data
    } catch (error) {
      const message = error.response?.data?.message || 'Nie udało się pobrać listy użytkowników'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Fetch single user details
  async fetchUser({ commit }, userId) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      const response = await apiClient.get(`/dashboard/users/${userId}`)
      commit('SET_USER', response.data.data)
      return response.data.data
    } catch (error) {
      const message = error.response?.data?.message || 'Nie udało się pobrać danych użytkownika'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Fetch available roles for filters and forms
  async fetchRoles({ commit }) {
    try {
      const response = await apiClient.get('/dashboard/roles')
      commit('SET_ROLES', response.data.data || response.data)
      return response.data
    } catch (error) {
      console.error('Failed to fetch roles:', error)
      // Fallback to default roles if API fails
      const fallbackRoles = [
        { id: 1, name: 'Super Admin' },
        { id: 2, name: 'Admin' },
        { id: 3, name: 'Editor' },
        { id: 4, name: 'User' }
      ]
      commit('SET_ROLES', fallbackRoles)
      return fallbackRoles
    }
  },

  // Create new user
  async createUser({ commit }, userData) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      const response = await apiClient.post('/dashboard/users', userData)
      commit('ADD_USER', response.data.data)
      return response.data.data
    } catch (error) {
      const message = error.response?.data?.message || 'Nie udało się utworzyć użytkownika'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Update user
  async updateUser({ commit }, { userId, userData }) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      const response = await apiClient.put(`/dashboard/users/${userId}`, userData)
      commit('UPDATE_USER', response.data.data)
      return response.data.data
    } catch (error) {
      const message = error.response?.data?.message || 'Nie udało się zaktualizować użytkownika'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Delete user
  async deleteUser({ commit }, userId) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      await apiClient.delete(`/dashboard/users/${userId}`)
      commit('REMOVE_USER', userId)
      return true
    } catch (error) {
      const message = error.response?.data?.message || 'Nie udało się usunąć użytkownika'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Assign roles to user
  async assignRoles({ commit }, { userId, roles }) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      const response = await apiClient.post(`/dashboard/users/${userId}/assign-roles`, {
        roles
      })
      commit('UPDATE_USER', response.data.data.user)
      return response.data.data.user
    } catch (error) {
      const message = error.response?.data?.message || 'Nie udało się przypisać ról'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Sync user roles (replace all roles)
  async syncRoles({ commit }, { userId, roles }) {
    commit('SET_LOADING', true)
    commit('SET_ERROR', null)

    try {
      const response = await apiClient.put(`/dashboard/users/${userId}/sync-roles`, {
        roles
      })
      commit('UPDATE_USER', response.data.data.user)
      return response.data.data.user
    } catch (error) {
      const message = error.response?.data?.message || 'Nie udało się zsynchronizować ról'
      commit('SET_ERROR', message)
      throw error
    } finally {
      commit('SET_LOADING', false)
    }
  },

  // Set filters and fetch users
  async setFilters({ commit, dispatch }, filters) {
    commit('SET_FILTERS', filters)
    // Reset to first page when filters change
    return await dispatch('fetchUsers', { page: 1 })
  },

  // Reset filters and fetch users
  async resetFilters({ commit, dispatch }) {
    commit('RESET_FILTERS')
    return await dispatch('fetchUsers', { page: 1 })
  },

  // Change items per page
  async changeItemsPerPage({ commit, dispatch }, perPage) {
    commit('SET_PER_PAGE', perPage)
    return await dispatch('fetchUsers', { page: 1, per_page: perPage })
  },

  // Change current page
  async changePage({ commit, dispatch }, page) {
    commit('SET_CURRENT_PAGE', page)
    return await dispatch('fetchUsers', { page })
  }
}

const getters = {
  // Get users list
  users: (state) => state.users,

  // Get current user details
  currentUser: (state) => state.user,

  // Get loading state
  loading: (state) => state.loading,

  // Get error message
  error: (state) => state.error,

  // Get pagination info
  pagination: (state) => state.pagination,

  // Get current filters
  filters: (state) => state.filters,

  // Get filter options
  roleOptions: (state) => state.roles,
  statusOptions: (state) => state.statuses,

  // Get filtered users by role
  getUsersByRole: (state) => (role) => {
    return state.users.filter(user =>
      user.roles && user.roles.some(userRole => userRole.name === role)
    )
  },

  // Get users by status
  getUsersByStatus: (state) => (status) => {
    return state.users.filter(user => user.status === status)
  },

  // Check if there are more pages
  hasMorePages: (state) => {
    return state.pagination.current_page < state.pagination.last_page
  },

  // Get total users count
  totalUsers: (state) => state.pagination.total,

  // Check if any filters are active
  hasActiveFilters: (state) => {
    return Object.values(state.filters).some(value =>
      value !== '' && value !== null && value !== undefined
    )
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}
