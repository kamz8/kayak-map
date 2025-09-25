<template>
  <div class="trails-list">

    <UiDataTable
      title="Lista szlaków"
      :headers="headers"
      :items="filteredTrails"
      :loading="loading"
      :actions="{ view: true, edit: true, delete: true }"
      @view="viewTrail"
      @edit="editTrail"
      @delete="confirmDeleteTrail"
    >
      <!-- Actions in header -->
      <template #actions>
        <div class="d-flex gap-2">
          <UiButton
            variant="default"
            size="sm"
            @click="$router.push('/dashboard/trails/create')"
          >
            <v-icon start>mdi-plus</v-icon>
            Dodaj szlak
          </UiButton>
          <UiButton
            variant="outline"
            size="sm"
            disabled
          >
            <v-icon start>mdi-upload</v-icon>
            Import GPX
          </UiButton>
        </div>
      </template>

      <!-- Custom columns -->
      <template #item.difficulty="{ value }">
        <UiBadge :variant="getDifficultyVariant(value)">
          {{ getDifficultyLabel(value) }}
        </UiBadge>
      </template>

      <template #item.trail_length="{ value }">
        <span class="font-weight-medium">{{ formatLength(value) }}</span>
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
          <span class="text-caption ms-2">({{ formatRating(value) }})</span>
        </div>
      </template>
    </UiDataTable>
  </div>
</template>

<script>
import { UiDataTable, UiButton, UiBadge } from '@/dashboard/components/ui'
import { mapActions } from 'vuex'

const DIFFICULTY_CONFIG = {
  'łatwy': { variant: 'success', label: 'Łatwy' },
  'umiarkowany': { variant: 'warning', label: 'Umiarkowany' },
  'trudny': { variant: 'destructive', label: 'Trudny' },
  'ekspertowy': { variant: 'secondary', label: 'Ekspertowy' }
}

const TABLE_HEADERS = [
  { title: 'Nazwa szlaku', key: 'trail_name', sortable: true },
  { title: 'Rzeka', key: 'river_name', sortable: true },
  { title: 'Długość', key: 'trail_length', sortable: true, align: 'end' },
  { title: 'Trudność', key: 'difficulty', sortable: true },
  { title: 'Ocena', key: 'rating', sortable: true, align: 'center' },
  { title: 'Autor', key: 'author', sortable: true }
]

export default {
  name: 'DashboardTrailsList',
  components: {
    UiDataTable,
    UiButton,
    UiBadge
  },
  data() {
    return {
      loading: false,
      error: null,
      trails: [],
      searchQuery: '',
      selectedTrails: [],
      filters: {
        difficulty: null,
        minRating: null,
        author: null
      }
    }
  },
  computed: {
    headers() {
      return TABLE_HEADERS
    },

    difficultyOptions() {
      return Object.keys(DIFFICULTY_CONFIG).map(key => ({
        value: key,
        title: DIFFICULTY_CONFIG[key].label
      }))
    },

    filteredTrails() {
      let filtered = [...this.trails]

      // Apply difficulty filter
      if (this.filters.difficulty) {
        filtered = filtered.filter(trail => trail.difficulty === this.filters.difficulty)
      }

      // Apply rating filter
      if (this.filters.minRating) {
        filtered = filtered.filter(trail => trail.rating >= this.filters.minRating)
      }

      // Apply author filter
      if (this.filters.author) {
        filtered = filtered.filter(trail =>
          trail.author.toLowerCase().includes(this.filters.author.toLowerCase())
        )
      }

      return filtered
    },

    hasTrails() {
      return this.trails.length > 0
    },

    hasSelectedTrails() {
      return this.selectedTrails.length > 0
    }
  },
  async created() {
    await this.fetchTrails()
  },
  methods: {
    ...mapActions('ui', ['showSuccess', 'showError', 'showInfo']),

    async fetchTrails() {
      this.loading = true
      this.error = null

      try {
        // TODO: Replace with actual API call
        await new Promise(resolve => setTimeout(resolve, 800))

        // Mock data - replace with actual API response
        this.trails = [
          {
            id: 1,
            trail_name: 'Wisła - Kraków do Tynca',
            river_name: 'Wisła',
            trail_length: 12.5,
            difficulty: 'łatwy',
            rating: 4.2,
            author: 'Jan Kowalski',
            created_at: '2024-01-15',
            status: 'active'
          },
          {
            id: 2,
            trail_name: 'Dunajec - Spływ pontonowy',
            river_name: 'Dunajec',
            trail_length: 18.2,
            difficulty: 'umiarkowany',
            rating: 4.8,
            author: 'Anna Nowak',
            created_at: '2024-02-20',
            status: 'active'
          },
          {
            id: 3,
            trail_name: 'Bóbr - Przełom Bardo',
            river_name: 'Bóbr',
            trail_length: 25.7,
            difficulty: 'trudny',
            rating: 4.5,
            author: 'Marek Wiśniewski',
            created_at: '2024-03-10',
            status: 'active'
          }
        ]

        // const response = await this.$http.get('/api/v1/dashboard/trails')
        // this.trails = response.data
      } catch (error) {
        console.error('Failed to fetch trails:', error)
        this.error = error.message || 'Nie udało się pobrać szlaków'
        this.showError('Nie udało się pobrać listy szlaków')
      } finally {
        this.loading = false
      }
    },

    async viewTrail(trail) {
      this.showInfo(`Podgląd szlaku: ${trail.trail_name}`)
      // TODO: Navigate to trail view or show modal
      // this.$router.push(`/dashboard/trails/${trail.id}`)
    },

    async editTrail(trail) {
      this.$router.push(`/dashboard/trails/${trail.id}/edit`)
    },

    async confirmDeleteTrail(trail) {
      // TODO: Show confirmation dialog
      if (confirm(`Czy na pewno chcesz usunąć szlak "${trail.trail_name}"?`)) {
        await this.deleteTrail(trail)
      }
    },

    async deleteTrail(trail) {
      try {
        // TODO: Replace with actual API call
        await new Promise(resolve => setTimeout(resolve, 300))

        // Remove from local state
        this.trails = this.trails.filter(t => t.id !== trail.id)

        this.showSuccess(`Szlak "${trail.trail_name}" został usunięty`)

        // const response = await this.$http.delete(`/api/v1/dashboard/trails/${trail.id}`)
      } catch (error) {
        console.error('Failed to delete trail:', error)
        this.showError('Nie udało się usunąć szlaku')
      }
    },

    getDifficultyConfig(difficulty) {
      return DIFFICULTY_CONFIG[difficulty] || { variant: 'secondary', label: difficulty }
    },

    getDifficultyVariant(difficulty) {
      return this.getDifficultyConfig(difficulty).variant
    },

    getDifficultyLabel(difficulty) {
      return this.getDifficultyConfig(difficulty).label
    },

    formatLength(length) {
      return `${length} km`
    },

    formatRating(rating) {
      return Number(rating).toFixed(1)
    },

    clearFilters() {
      this.filters = {
        difficulty: null,
        minRating: null,
        author: null
      }
    },

    exportTrails() {
      // TODO: Implement export functionality
      this.showInfo('Funkcja eksportu będzie dostępna wkrótce')
    },

    async refreshTrails() {
      await this.fetchTrails()
      this.showInfo('Lista szlaków została odświeżona')
    }
  }
}
</script>
