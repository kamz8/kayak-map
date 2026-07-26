/**
 * Vuex module for managing dynamic breadcrumb updates
 * Store only holds updates (patches) for breadcrumb items with keys
 * Base breadcrumbs always come from route.meta.breadcrumbs
 */

const state = () => ({
  // Only stores dynamic updates for breadcrumb items
  // Format: { trail: { text: 'Trail Name', to: '/path' }, section: { text: 'Section Name' } }
  updates: {}
})

const getters = {
  /**
   * Get dynamic updates for breadcrumb keys
   */
  updates: (state) => state.updates,

  /**
   * Check if there are any dynamic updates
   */
  hasUpdates: (state) => Object.keys(state.updates).length > 0
}

const mutations = {
  /**
   * Update a breadcrumb item by key
   * @param {Object} state
   * @param {Object} payload - { key, updates }
   */
  UPDATE_BREADCRUMB_BY_KEY(state, { key, updates }) {
    // Merge updates with existing values for this key
    state.updates = {
      ...state.updates,
      [key]: {
        ...state.updates[key],
        ...updates
      }
    }
  },

  /**
   * Clear updates for a specific key
   * @param {Object} state
   * @param {string} key
   */
  CLEAR_KEY(state, key) {
    const newUpdates = { ...state.updates }
    delete newUpdates[key]
    state.updates = newUpdates
  },

  /**
   * Clear all dynamic updates
   * @param {Object} state
   */
  CLEAR_UPDATES(state) {
    state.updates = {}
  }
}

const actions = {
  /**
   * Update breadcrumb by key (for dynamic updates)
   * @param {Object} context
   * @param {Object} payload - { key, updates }
   */
  updateBreadcrumbByKey({ commit }, payload) {
    commit('UPDATE_BREADCRUMB_BY_KEY', payload)
  },

  /**
   * Clear updates for a specific key
   * @param {Object} context
   * @param {string} key
   */
  clearKey({ commit }, key) {
    commit('CLEAR_KEY', key)
  },

  /**
   * Clear all dynamic updates
   * @param {Object} context
   */
  clearUpdates({ commit }) {
    commit('CLEAR_UPDATES')
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
