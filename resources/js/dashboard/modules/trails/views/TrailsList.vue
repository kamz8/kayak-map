<template>
  <div class="trails-list">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-h4 font-weight-bold mb-2">Szlaki kajakowe</h1>
      <p class="text-body-1 text-medium-emphasis">
        Zarządzanie szlakami w systemie Kayak Map
      </p>
    </div>

    <DataTable
      title="Lista szlaków"
      :headers="headers"
      :items="trails"
      :loading="loading"
      :actions="{ view: true, edit: true, delete: true }"
      @view="viewTrail"
      @edit="editTrail"
      @delete="confirmDeleteTrail"
    >
      <!-- Toolbar -->
      <template #toolbar>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="$router.push('/dashboard/trails/create')"
        >
          Dodaj szlak
        </v-btn>
        <v-btn
          color="success"
          prepend-icon="mdi-upload"
          variant="tonal"
          class="ms-2"
          disabled
        >
          Import GPX
        </v-btn>
      </template>

      <!-- Custom columns -->
      <template #item.difficulty="{ value }">
        <v-chip
          :color="getDifficultyColor(value)"
          size="small"
          variant="tonal"
        >
          {{ getDifficultyLabel(value) }}
        </v-chip>
      </template>

      <template #item.trail_length="{ value }">
        <span class="font-weight-medium">{{ value }} km</span>
      </template>

      <template #item.rating="{ value }">
        <div class="d-flex align-center">
          <v-rating
            :model-value="value"
            color="warning"
            size="small"
            half-increments
            readonly
            density="compact"
          />
          <span class="text-caption ms-2">({{ value }})</span>
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script>
import DataTable from '@/dashboard/components/ui/DataTable.vue'
import { mapActions } from 'vuex'

export default {
  name: 'DashboardTrailsList',
  components: {
    DataTable
  },
  data() {
    return {
      loading: false,
      trails: [
        {
          id: 1,
          trail_name: 'Wisła - Kraków do Tynca',
          river_name: 'Wisła',
          trail_length: 12.5,
          difficulty: 'łatwy',
          rating: 4.2,
          author: 'Jan Kowalski'
        },
        {
          id: 2,
          trail_name: 'Dunajec - Spływ pontonowy',
          river_name: 'Dunajec',
          trail_length: 18.2,
          difficulty: 'umiarkowany',
          rating: 4.8,
          author: 'Anna Nowak'
        }
      ],
      headers: [
        { title: 'Nazwa szlaku', key: 'trail_name', sortable: true },
        { title: 'Rzeka', key: 'river_name', sortable: true },
        { title: 'Długość', key: 'trail_length', sortable: true },
        { title: 'Trudność', key: 'difficulty', sortable: true },
        { title: 'Ocena', key: 'rating', sortable: true },
        { title: 'Autor', key: 'author', sortable: true }
      ]
    }
  },
  methods: {
    ...mapActions('ui', ['showSuccess', 'showError', 'showInfo']),

    viewTrail(trail) {
      this.showInfo(`Podgląd szlaku: ${trail.trail_name}`)
    },

    editTrail(trail) {
      this.$router.push(`/dashboard/trails/${trail.id}/edit`)
    },

    confirmDeleteTrail(trail) {
      this.showInfo(`Usuwanie szlaku: ${trail.trail_name}`)
    },

    getDifficultyColor(difficulty) {
      const colors = {
        'łatwy': 'success',
        'umiarkowany': 'warning',
        'trudny': 'error',
        'ekspertowy': 'purple'
      }
      return colors[difficulty] || 'grey'
    },

    getDifficultyLabel(difficulty) {
      return difficulty.charAt(0).toUpperCase() + difficulty.slice(1)
    }
  }
}
</script>
