<template>
  <div class="dashboard-overview">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-h4 font-weight-bold mb-2">Dashboard Kayak Map</h1>
      <p class="text-body-1 text-medium-emphasis">
        Panel administracyjny - przegląd systemu i szybkie akcje
      </p>
    </div>

    <!-- Stats Cards -->
    <v-row class="mb-6">
      <v-col cols="12" sm="6" lg="3">
        <v-card class="stats-card pa-4">
          <div class="d-flex align-center">
            <v-avatar color="primary" size="48" class="me-4">
              <v-icon color="white">mdi-map-marker-path</v-icon>
            </v-avatar>
            <div>
              <div class="text-overline text-medium-emphasis">Szlaki</div>
              <div class="text-h4 font-weight-bold">{{ stats.trails }}</div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card class="stats-card pa-4">
          <div class="d-flex align-center">
            <v-avatar color="success" size="48" class="me-4">
              <v-icon color="white">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <div class="text-overline text-medium-emphasis">Użytkownicy</div>
              <div class="text-h4 font-weight-bold">{{ stats.users }}</div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card class="stats-card pa-4">
          <div class="d-flex align-center">
            <v-avatar color="info" size="48" class="me-4">
              <v-icon color="white">mdi-map</v-icon>
            </v-avatar>
            <div>
              <div class="text-overline text-medium-emphasis">Regiony</div>
              <div class="text-h4 font-weight-bold">{{ stats.regions }}</div>
            </div>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" lg="3">
        <v-card class="stats-card pa-4">
          <div class="d-flex align-center">
            <v-avatar color="warning" size="48" class="me-4">
              <v-icon color="white">mdi-map-marker-alert</v-icon>
            </v-avatar>
            <div>
              <div class="text-overline text-medium-emphasis">Ostrzeżenia</div>
              <div class="text-h4 font-weight-bold">{{ stats.points }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Actions -->
    <v-row class="mb-6">
      <v-col cols="12">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="me-2">mdi-lightning-bolt</v-icon>
            Szybkie akcje
          </v-card-title>
          
          <v-card-text>
            <v-row>
              <v-col cols="12" md="3">
                <v-btn
                  color="primary"
                  variant="tonal"
                  block
                  size="large"
                  prepend-icon="mdi-plus"
                  @click="$router.push('/dashboard/trails/create')"
                >
                  Dodaj szlak
                </v-btn>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-btn
                  color="success"
                  variant="tonal"
                  block
                  size="large"
                  prepend-icon="mdi-upload"
                  disabled
                >
                  Import GPX
                </v-btn>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-btn
                  color="info"
                  variant="tonal"
                  block
                  size="large"
                  prepend-icon="mdi-account-plus"
                  disabled
                >
                  Dodaj użytkownika
                </v-btn>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-btn
                  color="warning"
                  variant="tonal"
                  block
                  size="large"
                  prepend-icon="mdi-cog"
                  disabled
                >
                  Ustawienia
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Navigation -->
    <v-row>
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="me-2">mdi-view-dashboard</v-icon>
            Zarządzanie
          </v-card-title>
          
          <v-list>
            <v-list-item
              prepend-icon="mdi-map-marker-path"
              title="Szlaki kajakowe"
              subtitle="Zarządzaj szlakami w systemie"
              @click="$router.push('/dashboard/trails')"
            >
              <template #append>
                <v-chip size="small" color="primary">{{ stats.trails }}</v-chip>
              </template>
            </v-list-item>
            
            <v-list-item
              prepend-icon="mdi-map"
              title="Regiony"
              subtitle="Zarządzaj regionami"
              disabled
            >
              <template #append>
                <v-chip size="small" color="info">{{ stats.regions }}</v-chip>
              </template>
            </v-list-item>
            
            <v-list-item
              prepend-icon="mdi-account-group"
              title="Użytkownicy"
              subtitle="Zarządzaj użytkownikami"
              disabled
            >
              <template #append>
                <v-chip size="small" color="success">{{ stats.users }}</v-chip>
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="me-2">mdi-information</v-icon>
            Status systemu
          </v-card-title>
          
          <v-card-text>
            <div class="mb-4">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2">Dashboard Module</span>
                <v-chip color="success" size="small">Aktywny</v-chip>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2">API Connection</span>
                <v-chip color="success" size="small">Połączony</v-chip>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2">Vite HMR</span>
                <v-chip color="info" size="small">Development</v-chip>
              </div>
            </div>
            
            <v-btn
              variant="outlined"
              block
              prepend-icon="mdi-arrow-left"
              @click="goToMainApp"
            >
              Przejdź do głównej aplikacji
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'DashboardOverview',
  data() {
    return {
      stats: {
        trails: 187,
        users: 1234,
        regions: 89,
        points: 456
      }
    }
  },
  computed: {
    ...mapGetters('auth', ['user'])
  },
  methods: {
    goToMainApp() {
      window.location.href = '/'
    }
  }
}
</script>

<style scoped>
.stats-card {
  transition: all 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}
</style>