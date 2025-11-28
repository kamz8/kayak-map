import { inject } from 'vue'

/**
 * Composable for managing dynamic breadcrumb updates
 * Store only patches specific breadcrumb items by key
 * Base breadcrumbs always come from route.meta.breadcrumbs
 *
 * USAGE: Must be called inside setup()
 */
export function useBreadcrumbs() {
  // Try to get store via inject (works in lazy-loaded components)
  let store = null

  try {
    store = inject('store')
  } catch (e) {
    // Fallback - will be handled below
  }

  if (!store) {
    console.warn('useBreadcrumbs: Vuex store not available. Make sure to call this composable inside setup().')
    return {
      updateBreadcrumbByKey: () => {},
      clearKey: () => {},
      clearUpdates: () => {}
    }
  }

  /**
   * Update a breadcrumb item by its key (for dynamic content)
   * @param {string} key - Unique key of the breadcrumb item
   * @param {Object} updates - Object with properties to update (text, to, muted, etc.)
   */
  const updateBreadcrumbByKey = (key, updates) => {
    if (store) {
      store.dispatch('breadcrumbs/updateBreadcrumbByKey', { key, updates })
    }
  }

  /**
   * Clear updates for a specific key
   * @param {string} key - Key to clear
   */
  const clearKey = (key) => {
    if (store) {
      store.dispatch('breadcrumbs/clearKey', key)
    }
  }

  /**
   * Clear all dynamic updates (useful on route change)
   */
  const clearUpdates = () => {
    if (store) {
      store.dispatch('breadcrumbs/clearUpdates')
    }
  }

  return {
    updateBreadcrumbByKey,
    clearKey,
    clearUpdates
  }
}
