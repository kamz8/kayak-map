<template>
  <v-app class="dashboard-app" theme="dark">
    <!-- Sidebar -->
    <v-navigation-drawer
      v-model="drawer"
      :permanent="!$vuetify.display.mobile"
      :temporary="$vuetify.display.mobile"
      :width="260"
      class="sidebar-drawer"
      color="#111111"
    >
      <!-- Logo Header -->
      <div class="sidebar-header d-flex align-center pa-4">
        <Logo :size="36" />
      </div>

      <!-- Navigation Sections -->
      <div class="sidebar-content">
        <!-- Platform Section -->
        <div class="nav-section">
          <div class="nav-section-title">Platform</div>
          <v-list nav class="nav-list">
            <v-list-item
              to="/dashboard"
              exact
              class="nav-item"
              prepend-icon="mdi-view-dashboard"
              title="Dashboard"
            />
          </v-list>
        </div>

        <!-- Dynamic Navigation Sections -->
        <div
          v-for="section in navigationSections"
          :key="section.name"
          class="nav-section"
        >
          <div class="nav-section-title">{{ section.name }}</div>
          <v-list nav class="nav-list">
            <v-list-item
              v-for="route in section.routes"
              :key="route.name"
              :to="route.path"
              class="nav-item"
              :prepend-icon="route.meta.navigation.icon"
              :title="route.meta.navigation.title"
              :disabled="route.meta.navigation.disabled"
              :class="{ 'disabled': route.meta.navigation.disabled }"
            />

            <!-- Static disabled items for sections -->

            <template v-if="section.name === 'System'">
              <v-list-item
                class="nav-item disabled"
                prepend-icon="mdi-chart-line"
                title="Analityka"
                disabled
              />
            </template>
          </v-list>
        </div>
      </div>

      <!-- Footer Links -->
      <div class="sidebar-footer">
        <v-list nav class="nav-list">
          <v-list-item
            class="nav-item footer-link"
            prepend-icon="mdi-github"
            title="Github Repo"
            @click="openLink('https://github.com')"
          />
          <v-list-item
            class="nav-item footer-link"
            prepend-icon="mdi-book-open-variant"
            title="Dokumentacja"
            @click="openLink('/docs')"
          />
        </v-list>

        <!-- User Dropdown -->
        <v-menu
          v-model="userMenuOpen"
          :close-on-content-click="false"
          location="top end"
          offset="8"
        >
          <template #activator="{ props }">
            <div v-bind="props" class="user-dropdown">
              <UiAvatar
                :size="28"
                :name="user?.name || 'Admin User'"
                variant="primary"
                class="me-3"
              />
              <span class="user-name">{{ user?.name || 'Admin' }}</span>
              <v-icon size="16" class="chevron-icon">
                {{ userMenuOpen ? 'mdi-chevron-up' : 'mdi-chevron-down' }}
              </v-icon>
            </div>
          </template>

          <v-card class="user-menu-card" min-width="200">
            <v-list class="user-menu">
              <v-list-item
                  tag="a"
                to="/dashboard/settings/profile"
                @click="userMenuOpen = false"
              >
                <v-list-item-title class="d-flex align-center" >
                  <v-icon size="16" class="me-3">mdi-account</v-icon>
                  Profil
                </v-list-item-title>
              </v-list-item>

              <v-list-item
                to="/dashboard/settings"
                @click="userMenuOpen = false"
              >
                <v-list-item-title class="d-flex align-center">
                  <v-icon size="16" class="me-3">mdi-cog</v-icon>
                  Ustawienia
                </v-list-item-title>
              </v-list-item>

              <v-divider class="my-1" />

              <v-list-item @click="goToMainApp">
                <v-list-item-title class="d-flex align-center">
                  <v-icon size="16" class="me-3">mdi-arrow-left</v-icon>
                  Główna strona
                </v-list-item-title>
              </v-list-item>

              <v-divider class="my-1" />

              <v-list-item @click="handleLogout" class="logout-item">
                <v-list-item-title class="d-flex align-center">
                  <v-icon size="16" class="me-3">mdi-logout</v-icon>
                  Wyloguj się
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card>
        </v-menu>
      </div>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main class="main-content">
      <!-- Top Bar -->
      <v-app-bar
        flat
        color="#111111"
        class="top-bar"
        height="60"
      >
        <v-app-bar-nav-icon
          v-if="$vuetify.display.mobile"
          @click="drawer = !drawer"
          color="white"
        />
        <div class="page-header-content flex-grow-1">
          <h1 class="page-title">{{ getCurrentPageTitle() }}</h1>
          <p v-if="getCurrentPageSubtitle()" class="page-subtitle">{{ getCurrentPageSubtitle() }}</p>
        </div>
      </v-app-bar>

      <!-- Page Content -->
      <div class="page-wrapper">
        <!-- Breadcrumbs -->
        <div v-if="showBreadcrumbs" class="breadcrumb-container mb-4">
          <UiBreadcrumb
            :items="currentBreadcrumbs"
            :show-home="true"
            variant="subtle"
          />
        </div>

        <router-view />
      </div>
    </v-main>

    <!-- Global Snackbar for notifications -->
    <v-snackbar
      :model-value="snackbar.show"
      :color="snackbar.color"
      :timeout="snackbar.timeout"
      location="top right"
      variant="flat"
      style="z-index: 999"
      @update:model-value="handleSnackbarUpdate"
    >
      {{ snackbar.message }}

      <template #actions>
        <v-btn
          variant="text"
          icon
          size="small"
          @click="hideSnackbar"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { Logo, UiBreadcrumb, UiAvatar } from '@dashboard/components/ui'
import { navigationRoutes } from '../router/index.js'

export default {
  name: 'DashboardLayout',
  components: {
    Logo,
    UiBreadcrumb,
    UiAvatar
  },
  data() {
    return {
      drawer: true,
      userMenuOpen: false
    }
  },
  computed: {
    ...mapGetters('auth', ['user']),
    ...mapGetters('ui', ['snackbar']),
    ...mapGetters('breadcrumbs', { dynamicUpdates: 'updates' }),

    showBreadcrumbs() {
      return this.currentBreadcrumbs && this.currentBreadcrumbs.length > 1
    },

    currentBreadcrumbs() {
      let breadcrumbs = []

      // 1. Get base breadcrumbs from route.meta (always primary source)
      if (this.$route.meta && this.$route.meta.breadcrumbs) {
        breadcrumbs = this.$route.meta.breadcrumbs
      } else {
        // 2. Generate default breadcrumbs based on route path
        const path = this.$route.path
        const segments = path.split('/').filter(Boolean)

        if (segments.length <= 1) {
          return [] // Don't show breadcrumbs for root dashboard
        }

        breadcrumbs = [{ text: 'Dashboard', to: '/dashboard' }]

        let currentPath = ''
        for (let i = 0; i < segments.length; i++) {
          if (segments[i] === 'dashboard') continue

          currentPath += `/${segments[i]}`
          const fullPath = `/dashboard${currentPath}`

          const isLast = i === segments.length - 1
          const text = this.capitalize(segments[i])

          if (isLast) {
            breadcrumbs.push({ text })
          } else {
            breadcrumbs.push({ text, to: fullPath })
          }
        }
      }

      // 3. Merge with dynamic updates from store (for items with keys)
      if (this.dynamicUpdates && Object.keys(this.dynamicUpdates).length > 0) {
        breadcrumbs = breadcrumbs.map(item => {
          if (item.key && this.dynamicUpdates[item.key]) {
            return {
              ...item,
              ...this.dynamicUpdates[item.key]
            }
          }
          return item
        })
      }

      return breadcrumbs
    },

    navigationSections() {
      // Group routes by navigation section
      const sections = {}

      navigationRoutes.forEach(route => {
        // Check if user has permission to access this route
        const permissions = route.meta.permissions || [route.meta.permission].filter(Boolean)
        if (permissions.length > 0 && !this.$canAny(permissions)) {
          return // Skip this route if user doesn't have permission
        }

        const sectionName = route.meta.navigation.section
        if (!sections[sectionName]) {
          sections[sectionName] = {
            name: sectionName,
            routes: []
          }
        }
        sections[sectionName].routes.push(route)
      })

      // Sort routes within each section by order
      Object.values(sections).forEach(section => {
        section.routes.sort((a, b) => {
          const orderA = a.meta.navigation.order || 999
          const orderB = b.meta.navigation.order || 999
          return orderA - orderB
        })
      })

      // Sort sections by the lowest order of routes in each section
      return Object.values(sections).sort((a, b) => {
        const minOrderA = Math.min(...a.routes.map(route => route.meta.navigation.order || 999))
        const minOrderB = Math.min(...b.routes.map(route => route.meta.navigation.order || 999))
        return minOrderA - minOrderB
      })
    }
  },
  watch: {
    '$route'() {
      // Clear dynamic updates when route changes
      // New route will have its own route.meta.breadcrumbs
      this.$store.dispatch('breadcrumbs/clearUpdates')
    }
  },
  methods: {
    ...mapActions('auth', ['logout']),
    ...mapActions('ui', ['hideSnackbar']),

    goToMainApp() {
      window.location.href = '/'
    },


    openLink(url) {
      if (url.startsWith('http')) {
        window.open(url, '_blank')
      } else {
        window.location.href = url
      }
    },

    getCurrentPageTitle() {
      const routeMap = {
        '/dashboard': 'Dashboard',
        '/dashboard/trails': 'Szlaki kajakowe',
        '/dashboard/trails/create': 'Dodaj nowy szlak',
        '/dashboard/users': 'Zarządzanie użytkownikami',
        '/dashboard/users/create': 'Dodaj użytkownika',
        '/dashboard/roles': 'Zarządzanie rolami',
        '/dashboard/roles/create': 'Dodaj rolę',
        '/dashboard/permissions': 'Zarządzanie uprawnieniami',
        '/dashboard/permissions/create': 'Dodaj uprawnienie',
        '/dashboard/settings': 'Ustawienia systemu',
        '/dashboard/settings/profile': 'Profil użytkownika',
        '/dashboard/settings/general': 'Ustawienia ogólne',
        '/dashboard/settings/security': 'Ustawienia bezpieczeństwa'
      }

      // Handle dynamic routes
      if (this.$route.path.includes('/dashboard/users/') && this.$route.path.includes('/edit')) {
        return 'Edytuj użytkownika'
      }
      if (this.$route.path.includes('/dashboard/roles/') && this.$route.path.includes('/edit')) {
        return 'Edytuj rolę'
      }
      if (this.$route.path.includes('/dashboard/permissions/') && this.$route.path.includes('/edit')) {
        return 'Edytuj uprawnienie'
      }
      if (this.$route.path.includes('/dashboard/trails/') && this.$route.path.includes('/sections/') && this.$route.path.includes('/links')) {
        return 'Zarządzanie linkami sekcji'
      }
      if (this.$route.path.includes('/dashboard/trails/') && this.$route.path.includes('/links')) {
        return 'Zarządzanie linkami szlaku'
      }
      if (this.$route.path.includes('/dashboard/trails/') && this.$route.path.includes('/edit')) {
        return 'Edytuj szlak'
      }

      return routeMap[this.$route.path] || 'Dashboard'
    },

    getCurrentPageSubtitle() {
      const subtitleMap = {
        '/dashboard': 'Przegląd systemu zarządzania szlakami kajakowymi',
        '/dashboard/trails': 'Zarządzanie trasami i szlakami w systemie Kayak Map',
        '/dashboard/trails/create': 'Utwórz nowy szlak kajakowy w systemie',
        '/dashboard/settings': 'Zarządzaj ustawieniami swojego konta i aplikacji',
        '/dashboard/settings/profile': 'Zarządzaj swoim profilem i danymi osobistymi',
        '/dashboard/security/change-password': 'Utwórz silne hasło aby zabezpieczyć swoje konto'
      }

      // Handle dynamic routes (check most specific paths first)
      if (this.$route.path.includes('/dashboard/trails/') && this.$route.path.includes('/sections/') && this.$route.path.includes('/links')) {
        return 'Zarządzanie linkami i zasobami zewnętrznymi sekcji'
      }
      if (this.$route.path.includes('/dashboard/trails/') && this.$route.path.includes('/links')) {
        return 'Zarządzanie linkami i zasobami zewnętrznymi szlaku'
      }
      if (this.$route.path.includes('/dashboard/trails/') && this.$route.path.includes('/edit')) {
        return 'Edycja informacji o szlaku kajakowym'
      }

      return subtitleMap[this.$route.path] || null
    },

    capitalize(str) {
      const specialWords = {
        'trails': 'Szlaki',
        'settings': 'Ustawienia',
        'profile': 'Profil',
        'dashboard': 'Dashboard'
      }
      return specialWords[str.toLowerCase()] || str.charAt(0).toUpperCase() + str.slice(1)
    },

    async handleLogout() {
      this.userMenuOpen = false
      try {
        await this.logout()
        this.$router.push('/dashboard/login')
      } catch (error) {
        console.error('Logout error:', error)
      }
    },

    handleSnackbarUpdate(value) {
      if (!value) {
        this.hideSnackbar()
      }
    }
  },
  mounted() {
    // Initialize drawer state based on screen size
    this.drawer = !this.$vuetify.display.mobile
  }
}
</script>

<style>
.dashboard-app {
  background: #0a0a0a;
}

/* Sidebar Styles */
.sidebar-drawer .v-navigation-drawer__content {
  background: #111111;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.sidebar-header {
  border-bottom: 1px solid #1f1f1f;
  flex-shrink: 0;
}

.sidebar-content {
  flex: 1;
  overflow-y: auto;
  padding: 24px 0;
}

.nav-section {
  margin-bottom: 32px;
}

.nav-section-title {
  color: #6b7280;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 16px 12px 16px;
}

.nav-list {
  background: transparent;
  padding: 0;
}

.nav-item {
  color: #d1d5db;
  border-radius: 0;
  margin: 0;
  min-height: 40px;
  font-size: 14px;
  font-weight: 500;
  padding-left: 16px;
  padding-right: 16px;
}

.nav-item:hover {
  background: #1a1a1a;
  color: #ffffff;
}

.nav-item.v-list-item--active {
  background: #1e293b;
  color: #ffffff;
  border-right: 2px solid #3b82f6;
}

.nav-item.disabled {
  color: #4b5563;
  opacity: 0.6;
}

.nav-item .v-list-item__prepend .v-icon {
  font-size: 16px;
  opacity: 0.8;
}

/* Sidebar Footer */
.sidebar-footer {
  border-top: 1px solid #1f1f1f;
  padding: 16px 0 20px 0;
  flex-shrink: 0;
}

.footer-link {
  font-size: 13px;
  color: #9ca3af;
  min-height: 36px;
}

.footer-link:hover {
  background: #1a1a1a;
  color: #d1d5db;
}

.user-dropdown {
  display: flex;
  align-items: center;
  padding: 8px 16px;
  margin: 8px 0 0 0;
  cursor: pointer;
  transition: background 0.2s ease;
  border-radius: 0;
}

.user-dropdown:hover {
  background: #1a1a1a;
}

.user-name {
  flex: 1;
  color: #d1d5db;
  font-size: 14px;
  font-weight: 500;
}

.chevron-icon {
  color: #9ca3af;
  transition: transform 0.2s ease;
}

/* User Menu */
.user-menu-card {
  background: #1a1a1a;
  border: 1px solid #2d2d2d;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

.user-menu {
  background: transparent;
  padding: 8px 0;
}

.user-menu .v-list-item {
  color: #d1d5db;
  font-size: 14px;
  min-height: 40px;
  padding: 0 16px;
  border-radius: 0;
}

.user-menu .v-list-item:hover {
  background: #2a2a2a;
  color: #ffffff;
}

.logout-item:hover {
  background: #7f1d1d;
  color: #fca5a5;
}

/* Main Content */
.main-content {
  background: #0a0a0a;
}

.top-bar {
  background: #111111;
  border-bottom: 1px solid #1f1f1f;
}

.page-header-content {
  padding: 8px 16px;
}

.page-title {
  color: #ffffff;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  line-height: 1.2;
}

.page-subtitle {
  color: #9ca3af;
  font-size: 13px;
  font-weight: 400;
  margin: 2px 0 0 0;
  line-height: 1.3;
  opacity: 0.9;
}

.page-wrapper {
  padding: 32px;
  min-height: calc(100vh - 60px);
}

.breadcrumb-container {
  padding: 0;
  margin-bottom: 16px;
}

/* Breadcrumb theme overrides for dark theme */
.breadcrumb-container .ui-breadcrumb {
  color: #9ca3af;
}

.breadcrumb-container .ui-breadcrumb-link {
  color: #9ca3af;
}

.breadcrumb-container .ui-breadcrumb-link:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.05);
}

.breadcrumb-container .ui-breadcrumb-text--current {
  color: #ffffff;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .page-wrapper {
    padding: 16px;
  }

  .page-title {
    font-size: 16px;
  }
}

/* Custom Scrollbar */
.sidebar-content::-webkit-scrollbar {
  width: 4px;
}

.sidebar-content::-webkit-scrollbar-track {
  background: #111111;
}

.sidebar-content::-webkit-scrollbar-thumb {
  background: #333333;
  border-radius: 2px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
  background: #444444;
}
</style>
