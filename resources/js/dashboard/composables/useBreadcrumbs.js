import { getCurrentInstance } from 'vue'

/**
 * Composable for managing dynamic breadcrumbs
 * Allows updating specific breadcrumb items without redefining the entire array
 */
export function useBreadcrumbs() {
  const instance = getCurrentInstance()
  const route = instance?.proxy?.$route

  if (!route) {
    console.warn('useBreadcrumbs: Route not available')
    return {
      updateBreadcrumb: () => {},
      updateBreadcrumbByKey: () => {},
      getBreadcrumbs: () => []
    }
  }

  /**
   * Update a breadcrumb item by its key
   * @param {string} key - Unique key of the breadcrumb item
   * @param {Object} updates - Object with properties to update (text, to, muted, etc.)
   */
  const updateBreadcrumbByKey = (key, updates) => {
    if (!route.meta.breadcrumbs) {
      console.warn('useBreadcrumbs: No breadcrumbs defined in route meta')
      return
    }

    const breadcrumbs = route.meta.breadcrumbs
    const index = breadcrumbs.findIndex(item => item.key === key)

    if (index === -1) {
      console.warn(`useBreadcrumbs: Breadcrumb with key "${key}" not found`)
      return
    }

    // Update only the specified properties
    breadcrumbs[index] = {
      ...breadcrumbs[index],
      ...updates
    }
  }

  /**
   * Update a breadcrumb item by its index
   * @param {number} index - Index of the breadcrumb item
   * @param {Object} updates - Object with properties to update (text, to, muted, etc.)
   */
  const updateBreadcrumb = (index, updates) => {
    if (!route.meta.breadcrumbs) {
      console.warn('useBreadcrumbs: No breadcrumbs defined in route meta')
      return
    }

    const breadcrumbs = route.meta.breadcrumbs

    if (index < 0 || index >= breadcrumbs.length) {
      console.warn(`useBreadcrumbs: Index ${index} out of bounds`)
      return
    }

    // Update only the specified properties
    breadcrumbs[index] = {
      ...breadcrumbs[index],
      ...updates
    }
  }

  /**
   * Get current breadcrumbs array
   * @returns {Array}
   */
  const getBreadcrumbs = () => {
    return route.meta.breadcrumbs || []
  }

  return {
    updateBreadcrumb,
    updateBreadcrumbByKey,
    getBreadcrumbs
  }
}
