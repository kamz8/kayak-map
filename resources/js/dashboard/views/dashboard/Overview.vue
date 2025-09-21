<template>
  <div class="dashboard-overview">
    <!-- Loading State -->
    <div v-if="loading" class="d-flex justify-center py-8">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <!-- Stats Grid -->
    <div v-else class="stats-grid">
      <!-- Stats Cards -->
      <PatternCard
        v-for="config in statsConfig"
        :key="config.key"
        :variant="config.variant"
        contentPosition="top"
        rounded="xl"
        :class="`stats-card`"
      >
        <div class="stats-card-content">
          <div class="card-header">
            <h3 class="card-title">{{ config.title }}</h3>
            <v-icon class="card-icon">{{ config.icon }}</v-icon>
          </div>
          <div class="card-number">{{ getStatValue(config.key) }}</div>
          <div class="card-subtitle">{{ config.subtitle }}</div>
        </div>
      </PatternCard>

      <!-- Quick Actions Card -->
      <UiCard title="Szybkie akcje" class="action-card">
        <div class="action-buttons">
          <UiButton
            v-for="action in enabledQuickActions"
            :key="action.key"
            variant="secondary"
            size="sm"
            :disabled="!action.enabled"
            class="action-btn"
            @click="handleQuickAction(action)"
          >
            <v-icon start size="small">{{ action.icon }}</v-icon>
            {{ action.title }}
          </UiButton>
        </div>
      </UiCard>

      <!-- Management Card -->
      <UiCard title="Zarządzanie" class="management-card">
        <div class="management-links">
          <div
            v-for="item in managementItems"
            :key="item.key"
            class="management-item"
            :class="{ disabled: !item.enabled }"
            @click="handleManagementClick(item)"
          >
            <v-icon size="16" class="me-2">{{ item.icon }}</v-icon>
            <span>{{ item.title }}</span>
            <v-spacer />
            <UiBadge variant="secondary" size="sm">{{ getStatValue(item.key) }}</UiBadge>
          </div>
        </div>
      </UiCard>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { UiCard, UiButton, UiBadge } from '@/dashboard/components/ui'
import PatternCard from "@ui/PatternCard.vue";

const STATS_CONFIG = [
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
]

const MANAGEMENT_ITEMS = [
  {
    key: 'trails',
    title: 'Szlaki',
    icon: 'mdi-map-marker-path',
    route: '/dashboard/trails',
    enabled: true
  },
  {
    key: 'users',
    title: 'Użytkownicy',
    icon: 'mdi-account-group',
    route: '/dashboard/users',
    enabled: true
  },
  {
    key: 'regions',
    title: 'Regiony',
    icon: 'mdi-map',
    route: '/dashboard/regions',
    enabled: false
  }
]

const QUICK_ACTIONS = [
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

export default {
  name: 'DashboardOverview',
  components: {
      PatternCard,
    UiCard,
    UiButton,
    UiBadge
  },
  data() {
    return {
      loading: false,
      stats: {
        trails: 187,
        users: 1234,
        regions: 89,
        activity: 2543
      }
    }
  },
  computed: {
    ...mapGetters('auth', ['user']),

    statsConfig() {
      return STATS_CONFIG
    },

    managementItems() {
      return MANAGEMENT_ITEMS
    },

    quickActions() {
      return QUICK_ACTIONS
    },

    enabledManagementItems() {
      return this.managementItems.filter(item => item.enabled)
    },

    enabledQuickActions() {
      return this.quickActions.filter(action => action.enabled)
    }
  },
  async created() {
    await this.fetchStats()
  },
  methods: {
    async fetchStats() {
      this.loading = true
      try {
        // TODO: Replace with actual API call
        await new Promise(resolve => setTimeout(resolve, 500))
        // const response = await this.$http.get('/api/dashboard/stats')
        // this.stats = response.data
      } catch (error) {
        console.error('Failed to fetch dashboard stats:', error)
      } finally {
        this.loading = false
      }
    },

    navigateToRoute(route) {
      if (!route) return
      this.$router.push(route)
    },

    goToMainApp() {
      window.location.href = '/'
    },

    handleQuickAction(action) {
      if (!action.enabled) return

      if (action.route) {
        this.navigateToRoute(action.route)
      } else {
        console.log(`Quick action: ${action.key}`)
      }
    },

    handleManagementClick(item) {
      if (!item.enabled) return
      this.navigateToRoute(item.route)
    },

    getStatValue(key) {
      return this.stats[key] || 0
    }
  }
}
</script>

<style scoped>
.dashboard-overview {
  padding: 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  width: 100%;
}


/* Stats Cards Styling */
.stats-card {
  position: relative;
  overflow: hidden;
}

.stats-card-content {
  padding: 20px;
  height: 120px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.stats-card--primary .stats-card-content {
  background: linear-gradient(135deg, hsl(var(--v-theme-primary)), hsl(var(--v-theme-primary-darken-1)));
}

.stats-card--success .stats-card-content {
  background: linear-gradient(135deg, hsl(var(--v-theme-success)), #047857);
}

.stats-card--secondary .stats-card-content {
  background: linear-gradient(135deg, hsl(var(--v-theme-secondary)), hsl(var(--v-theme-secondary-darken-1)));
}

.stats-card--warning .stats-card-content {
  background: linear-gradient(135deg, hsl(var(--v-theme-warning)), #b45309);
}

.card-header {
  position: relative;
  width: 100%;
  margin-bottom: 16px;
}

.card-title {
  color: #ffffff;
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  padding-right: 50px;
}

.card-icon {
  position: absolute;
  top: 0;
  right: 0;
  color: rgba(255, 255, 255, 0.4);
  font-size: 32px;
  opacity: 0.7;
  transition: all 0.2s ease;
}

.card-icon:hover {
  opacity: 1;
  transform: scale(1.1);
}

.card-number {
  color: #ffffff;
  font-size: 32px;
  font-weight: 700;
  line-height: 1;
  margin: 16px 0 8px 0;
}

.card-subtitle {
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
  margin-top: auto;
}

/* Action Card Styles */
.action-card {
  height: fit-content;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.action-btn {
  justify-content: flex-start;
  width: 100%;
  text-align: left;
}

/* Management Card Styles */
.management-card {
  height: fit-content;
}

.management-links {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.management-item {
  display: flex;
  align-items: center;
  padding: 12px;
  color: hsl(var(--v-theme-on-surface));
  font-size: 14px;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.management-item:hover:not(.disabled) {
  background: hsl(var(--v-theme-surface-variant));
  border-color: hsl(var(--v-theme-surface-variant));
}

.management-item.disabled {
  color: hsl(var(--v-theme-on-surface-variant));
  cursor: not-allowed;
  opacity: 0.6;
}

/* Responsive */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .card-pattern {
    padding: 20px;
  }

  .card-number {
    font-size: 28px;
  }
}
</style>
