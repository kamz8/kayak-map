/**
 * Dashboard composable for shared dashboard logic
 * Follows Vue.js master patterns for Options API composables
 */

import { reactive, computed } from 'vue'

// Global state for dashboard
const dashboardState = reactive({
  stats: {
    trails: 0,
    users: 0,
    regions: 0,
    activity: 0
  },
  loading: false,
  error: null,
  lastFetch: null
})

/**
 * Dashboard configuration constants
 */
export const DASHBOARD_CONFIG = {
  STATS: [
    {
      key: 'trails',
      title: 'Szlaki',
      icon: 'mdi-map-marker-path',
      variant: 'primary',
      subtitle: 'Aktywne szlaki w systemie'
    },
    {
      key: 'users',
      title: 'Użytkownicy',
      icon: 'mdi-account-group',
      variant: 'success',
      subtitle: 'Zarejestrowani użytkownicy'
    },
    {
      key: 'regions',
      title: 'Regiony',
      icon: 'mdi-map',
      variant: 'secondary',
      subtitle: 'Obszary geograficzne'
    },
    {
      key: 'activity',
      title: 'Aktywność',
      icon: 'mdi-chart-line',
      variant: 'warning',
      subtitle: 'Sesje w tym miesiącu'
    }
  ],
  
  MANAGEMENT_ITEMS: [
    {
      key: 'trails',
      title: 'Szlaki',
      icon: 'mdi-map-marker-path',
      route: '/dashboard/trails',
      enabled: true
    },
    {
      key: 'regions',
      title: 'Regiony',
      icon: 'mdi-map',
      route: '/dashboard/regions',
      enabled: false
    },
    {
      key: 'users',
      title: 'Użytkownicy',
      icon: 'mdi-account-group',
      route: '/dashboard/users',
      enabled: false
    }
  ],
  
  QUICK_ACTIONS: [
    {
      key: 'new-trail',
      title: 'Nowy szlak',
      icon: 'mdi-plus',
      color: 'primary',
      route: '/dashboard/trails/create',
      enabled: true
    },
    {
      key: 'import-gpx',
      title: 'Import GPX',
      icon: 'mdi-upload',
      color: 'success',
      route: null,
      enabled: false
    },
    {
      key: 'settings',
      title: 'Ustawienia',
      icon: 'mdi-cog',
      color: 'info',
      route: '/dashboard/settings',
      enabled: false
    }
  ]
}

/**
 * Dashboard composable mixin for Options API components
 * Use with mixins: [useDashboard]
 */
export const useDashboard = {
  data() {
    return {
      dashboardLoading: false
    }
  },
  
  computed: {
    dashboardStats() {
      return dashboardState.stats
    },
    
    dashboardError() {
      return dashboardState.error
    },
    
    isDashboardLoading() {
      return dashboardState.loading || this.dashboardLoading
    },
    
    statsConfig() {
      return DASHBOARD_CONFIG.STATS
    },
    
    managementItems() {
      return DASHBOARD_CONFIG.MANAGEMENT_ITEMS
    },
    
    quickActions() {
      return DASHBOARD_CONFIG.QUICK_ACTIONS
    },
    
    enabledManagementItems() {
      return this.managementItems.filter(item => item.enabled)
    },
    
    enabledQuickActions() {
      return this.quickActions.filter(action => action.enabled)
    },
    
    shouldRefreshStats() {
      if (!dashboardState.lastFetch) return true
      
      const CACHE_DURATION = 5 * 60 * 1000 // 5 minutes
      return Date.now() - dashboardState.lastFetch > CACHE_DURATION
    }
  },
  
  methods: {
    async fetchDashboardStats(force = false) {
      if (!force && !this.shouldRefreshStats) {
        return dashboardState.stats
      }
      
      dashboardState.loading = true
      dashboardState.error = null
      
      try {
        // TODO: Replace with actual API call
        await new Promise(resolve => setTimeout(resolve, 500))
        
        // Mock data - replace with actual API response
        const mockStats = {
          trails: 187,
          users: 1234,
          regions: 89,
          activity: 2543
        }
        
        // const response = await this.$http.get('/api/dashboard/stats')
        // dashboardState.stats = response.data
        
        Object.assign(dashboardState.stats, mockStats)
        dashboardState.lastFetch = Date.now()
        
        return dashboardState.stats
      } catch (error) {
        console.error('Failed to fetch dashboard stats:', error)
        dashboardState.error = error.message || 'Failed to fetch stats'
        throw error
      } finally {
        dashboardState.loading = false
      }
    },
    
    getStatValue(key) {
      return this.dashboardStats[key] || 0
    },
    
    navigateToRoute(route) {
      if (!route) return
      this.$router.push(route)
    },
    
    handleQuickAction(action) {
      if (!action.enabled) {
        console.warn(`Action ${action.key} is disabled`)
        return
      }
      
      if (action.route) {
        this.navigateToRoute(action.route)
      } else {
        this.handleCustomAction(action.key)
      }
    },
    
    handleManagementClick(item) {
      if (!item.enabled) {
        console.warn(`Management item ${item.key} is disabled`)
        return
      }
      
      this.navigateToRoute(item.route)
    },
    
    handleCustomAction(actionKey) {
      // Override this method in components to handle custom actions
      console.log(`Custom action: ${actionKey}`)
    },
    
    refreshDashboardStats() {
      return this.fetchDashboardStats(true)
    }
  }
}

/**
 * Utility functions for dashboard
 */
export const dashboardUtils = {
  /**
   * Format number with locale-aware formatting
   */
  formatNumber(value) {
    if (typeof value !== 'number') return '0'
    return new Intl.NumberFormat('pl-PL').format(value)
  },
  
  /**
   * Get color variant for stat value
   */
  getStatColorVariant(key, value) {
    const thresholds = {
      trails: { low: 50, medium: 100 },
      users: { low: 500, medium: 1000 },
      regions: { low: 20, medium: 50 },
      activity: { low: 1000, medium: 2000 }
    }
    
    const threshold = thresholds[key]
    if (!threshold) return 'primary'
    
    if (value < threshold.low) return 'error'
    if (value < threshold.medium) return 'warning'
    return 'success'
  },
  
  /**
   * Get icon for management item
   */
  getManagementItemIcon(key) {
    const icons = {
      trails: 'mdi-map-marker-path',
      regions: 'mdi-map',
      users: 'mdi-account-group',
      settings: 'mdi-cog',
      analytics: 'mdi-chart-line'
    }
    
    return icons[key] || 'mdi-circle'
  }
}

// Export for direct use (not as mixin)
export { dashboardState }