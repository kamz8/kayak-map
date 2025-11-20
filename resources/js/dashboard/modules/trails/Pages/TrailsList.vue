<template>
  <div class="trails-list">
    <!-- Bulk actions toolbar -->
    <transition name="slide-down">
      <div v-if="selectedTrails.length > 0" class="bulk-actions-toolbar">
        <div class="bulk-actions-info">
          <v-icon>mdi-checkbox-marked-circle</v-icon>
          <span class="font-weight-medium">Zaznaczono: {{ selectedTrails.length }}</span>
        </div>

        <div class="bulk-actions-buttons">
          <!-- Bulk status change -->
          <v-menu location="bottom">
            <template #activator="{ props }">
              <UiButton
                v-bind="props"
                variant="outline"
                size="sm"
              >
                <v-icon start size="small">mdi-swap-horizontal</v-icon>
                Zmień status
              </UiButton>
            </template>

            <v-list density="compact" min-width="180">
              <v-list-item @click="bulkChangeStatus('active')">
                <template #prepend>
                  <v-icon size="small" color="success">mdi-check-circle</v-icon>
                </template>
                <v-list-item-title>Aktywny</v-list-item-title>
              </v-list-item>

              <v-list-item @click="bulkChangeStatus('draft')">
                <template #prepend>
                  <v-icon size="small" color="secondary">mdi-pencil-circle</v-icon>
                </template>
                <v-list-item-title>Szkic</v-list-item-title>
              </v-list-item>

              <v-list-item @click="bulkChangeStatus('archived')">
                <template #prepend>
                  <v-icon size="small">mdi-archive</v-icon>
                </template>
                <v-list-item-title>Archiwum</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>

          <!-- Bulk delete -->
          <UiButton
            variant="destructive"
            size="sm"
            @click="confirmBulkDelete"
          >
            <v-icon start size="small">mdi-delete</v-icon>
            Usuń zaznaczone
          </UiButton>

          <!-- Clear selection -->
          <UiButton
            variant="ghost"
            size="sm"
            @click="clearSelection"
          >
            <v-icon start size="small">mdi-close</v-icon>
            Anuluj
          </UiButton>
        </div>
      </div>
    </transition>

    <UiDataTable
      v-model:page="internalPage"
      v-model:items-per-page="internalItemsPerPage"
      v-model:selected="selectedTrails"
      title="Lista szlaków"
      :headers="headers"
      :items="trails"
      :loading="loading"
      :total-items="totalItems"
      :show-select="true"
      :actions="{ view: false, edit: true, delete: true }"
      @update:page="handlePageChange"
      @update:items-per-page="handleItemsPerPageChange"
      @edit="editTrail"
      @delete="confirmDeleteTrail"
    >
      <!-- Header actions -->
      <template #actions>
        <div class="trails-header-actions">
          <!-- Refresh button -->
          <v-tooltip text="Odśwież listę" location="top">
            <template #activator="{ props }">
              <UiButton
                v-bind="props"
                variant="ghost"
                size="icon"
                @click="refreshTrails"
                :loading="loading"
              >
                <v-icon>mdi-refresh</v-icon>
              </UiButton>
            </template>
          </v-tooltip>

          <!-- Add trail button -->
          <UiButton
            variant="default"
            size="sm"
            @click="$router.push('/dashboard/trails/create')"
          >
            <v-icon start size="small">mdi-plus</v-icon>
            Dodaj szlak
          </UiButton>
        </div>
      </template>

      <!-- Filters -->
      <template #filters>
        <div class="trails-filters">
          <!-- Difficulty filter -->
          <v-select
            v-model="filters.difficulty"
            :items="difficultyOptions"
            label="Trudność"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            class="filter-select"
            style="max-width: 180px"
          >
            <template #prepend-inner>
              <v-icon size="small">mdi-hiking</v-icon>
            </template>
          </v-select>

          <!-- River filter -->
          <v-autocomplete
            v-model="filters.river"
            :items="uniqueRivers"
            label="Rzeka"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            class="filter-select"
            style="max-width: 200px"
          >
            <template #prepend-inner>
              <v-icon size="small">mdi-waves</v-icon>
            </template>
          </v-autocomplete>

          <!-- Status filter -->
          <v-select
            v-model="filters.status"
            :items="statusOptions"
            label="Status"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            class="filter-select"
            style="max-width: 140px"
          >
            <template #prepend-inner>
              <v-icon size="small">mdi-check-circle</v-icon>
            </template>
          </v-select>

          <!-- Clear filters button -->
          <UiButton
            v-if="hasActiveFilters"
            variant="ghost"
            size="sm"
            @click="clearFilters"
          >
            <v-icon start size="small">mdi-filter-remove</v-icon>
            Wyczyść
          </UiButton>
        </div>
      </template>

      <!-- Custom columns -->
      <template #item.trail_name="{ value, item }">
        <v-tooltip location="top" :disabled="value.length < 30">
          <template #activator="{ props }">
            <span
              v-bind="props"
              class="trail-name"
              @click="viewTrail(item)"
            >
              {{ value }}
            </span>
          </template>
          {{ value }}
        </v-tooltip>
      </template>

      <template #item.river_name="{ value }">
        <v-tooltip location="top" :disabled="value.length < 20">
          <template #activator="{ props }">
            <span v-bind="props" class="river-name">
              {{ value }}
            </span>
          </template>
          {{ value }}
        </v-tooltip>
      </template>

      <template #item.difficulty="{ value }">
        <UiBadge :variant="getDifficultyVariant(value)">
          <v-icon start size="x-small">{{ getDifficultyIcon(value) }}</v-icon>
          {{ getDifficultyLabel(value) }}
        </UiBadge>
      </template>

      <template #item.trail_length="{ value }">
        <span class="font-weight-medium">{{ formatLength(value) }}</span>
      </template>

      <template #item.status="{ value }">
        <v-tooltip location="top">
          <template #activator="{ props }">
            <div v-bind="props" class="status-badge-wrapper">
              <UiBadge :variant="getStatusVariant(value)">
                <v-icon
                  start
                  :color="getStatusColor(value)"
                  style="font-size: 2em;"
                >
                  {{ getStatusIcon(value) }}
                </v-icon>
              </UiBadge>
            </div>
          </template>
          <div class="status-tooltip">
            <strong>{{ getStatusLabel(value) }}</strong>
            <br>
            <span class="text-caption">{{ getStatusDescription(value) }}</span>
          </div>
        </v-tooltip>
      </template>

      <!-- Custom row actions - only More menu, edit/delete come from UiDataTable default actions -->
      <template #row-actions="{ item }">
        <!-- More actions menu -->
        <v-menu location="bottom end">
          <template #activator="{ props }">
            <v-tooltip text="Więcej" location="top">
              <template #activator="{ props: tooltipProps }">
                <UiButton
                  v-bind="{ ...props, ...tooltipProps }"
                  variant="ghost"
                  size="icon"
                >
                  <v-icon size="small">mdi-dots-vertical</v-icon>
                </UiButton>
              </template>
            </v-tooltip>
          </template>

          <v-list density="compact" min-width="150">
            <v-list-item @click="viewTrail(item)">
              <template #prepend>
                <v-icon size="small">mdi-eye</v-icon>
              </template>
              <v-list-item-title>Podgląd</v-list-item-title>
            </v-list-item>

            <v-list-item @click="duplicateTrail(item)">
              <template #prepend>
                <v-icon size="small">mdi-content-copy</v-icon>
              </template>
              <v-list-item-title>Duplikuj</v-list-item-title>
            </v-list-item>

            <v-divider class="my-1" />

            <v-list-item @click="archiveTrail(item)">
              <template #prepend>
                <v-icon size="small">mdi-archive</v-icon>
              </template>
              <v-list-item-title>Archiwizuj</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </UiDataTable>

    <!-- Delete confirmation dialog -->
    <ConfirmDialog
      v-model="deleteDialog.show"
      title="Usunąć szlak?"
      :message="`Czy na pewno chcesz usunąć szlak &quot;${deleteDialog.trail?.trail_name}&quot;?`"
      details="Ta operacja jest nieodwracalna. Wszystkie dane szlaku zostaną trwale usunięte."
      icon="mdi-delete-alert"
      icon-color="error"
      confirm-text="Usuń szlak"
      cancel-text="Anuluj"
      confirm-color="error"
      :loading="deleteDialog.loading"
      @confirm="executeDelete"
      @cancel="deleteDialog.show = false"
    />

    <!-- Bulk delete confirmation dialog -->
    <ConfirmDialog
      v-model="bulkDeleteDialog.show"
      title="Usunąć zaznaczone szlaki?"
      :message="`Czy na pewno chcesz usunąć ${bulkDeleteDialog.count} ${getTrailsWord(bulkDeleteDialog.count)}?`"
      details="Ta operacja jest nieodwracalna. Wszystkie dane wybranych szlaków zostaną trwale usunięte."
      icon="mdi-delete-alert"
      icon-color="error"
      confirm-text="Usuń wszystkie"
      cancel-text="Anuluj"
      confirm-color="error"
      :loading="bulkDeleteDialog.loading"
      @confirm="executeBulkDelete"
      @cancel="bulkDeleteDialog.show = false"
    />

    <!-- Bulk status change confirmation dialog -->
    <ConfirmDialog
      v-model="bulkStatusDialog.show"
      title="Zmienić status szlaków?"
      :message="`Czy na pewno chcesz zmienić status ${bulkStatusDialog.count} ${getTrailsWord(bulkStatusDialog.count)} na &quot;${getStatusLabel(bulkStatusDialog.newStatus)}&quot;?`"
      details="Status wybranych szlaków zostanie zaktualizowany."
      icon="mdi-swap-horizontal"
      icon-color="primary"
      confirm-text="Zmień status"
      cancel-text="Anuluj"
      :loading="bulkStatusDialog.loading"
      @confirm="executeBulkStatusChange"
      @cancel="bulkStatusDialog.show = false"
    />
  </div>
</template>

<script>
import { UiDataTable, UiButton, UiBadge, ConfirmDialog } from '@/dashboard/components/ui'
import { mapActions } from 'vuex'
import apiClient from '@/dashboard/plugins/axios.js'
import { useTrailStatus } from '@/dashboard/composables/useTrailStatus.js'

const DIFFICULTY_CONFIG = {
  'łatwy': {
    variant: 'success',
    color: 'success',
    label: 'Łatwy',
    icon: 'mdi-chevron-up'
  },
  'umiarkowany': {
    variant: 'warning',
    color: 'warning',
    label: 'Umiarkowany',
    icon: 'mdi-chevron-double-up'
  },
  'trudny': {
    variant: 'destructive',
    color: 'error',
    label: 'Trudny',
    icon: 'mdi-chevron-triple-up'
  },
  'ekspertowy': {
    variant: 'secondary',
    color: 'purple',
    label: 'Ekspertowy',
    icon: 'mdi-skull'
  }
}

const TABLE_HEADERS = [
  { title: 'Nazwa szlaku', key: 'trail_name', sortable: true },
  { title: 'Rzeka', key: 'river_name', sortable: true },
  { title: 'Długość', key: 'trail_length', sortable: true, align: 'end', width: '100px' },
  { title: 'Trudność', key: 'difficulty', sortable: true, width: '140px' },
  { title: 'Status', key: 'status', sortable: true, width: '80px', align: 'center' }
]

export default {
  name: 'DashboardTrailsList',
  components: {
    UiDataTable,
    UiButton,
    UiBadge,
    ConfirmDialog
  },
  setup() {
    const {
      getStatusVariant,
      getStatusColor,
      getStatusLabel,
      getStatusDescription,
      getStatusIcon,
      getStatusOptions
    } = useTrailStatus()

    return {
      getStatusVariant,
      getStatusColor,
      getStatusLabel,
      getStatusDescription,
      getStatusIcon,
      statusOptions: getStatusOptions()
    }
  },
  data() {
    return {
      loading: false,
      error: null,
      trails: [],
      totalItems: 0,
      internalPage: 1,
      internalItemsPerPage: 10,
      selectedTrails: [],
      filters: {
        difficulty: null,
        river: null,
        status: null
      },
      deleteDialog: {
        show: false,
        trail: null,
        loading: false
      },
      bulkDeleteDialog: {
        show: false,
        count: 0,
        loading: false
      },
      bulkStatusDialog: {
        show: false,
        count: 0,
        newStatus: null,
        loading: false
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

    uniqueRivers() {
      return [...new Set(this.trails.map(t => t.river_name))].sort()
    },

    hasActiveFilters() {
      return Object.values(this.filters).some(v => v !== null)
    }
  },
  watch: {
    filters: {
      handler() {
        this.internalPage = 1
        this.fetchTrails()
      },
      deep: true
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
        const params = {
          page: this.internalPage,
          per_page: this.internalItemsPerPage,
          ...this.buildFilterParams()
        }

        const response = await apiClient.get('/dashboard/trails', { params })

        this.trails = response.data.data
        this.totalItems = response.data.meta.total

      } catch (error) {
        console.error('Failed to fetch trails:', error)

        // Fallback to mock data if API fails (temporary)
        if (error.response?.status === 404 || error.code === 'ERR_NETWORK') {
          console.warn('API not available, using mock data')
          await this.loadMockData()
        } else {
          this.error = error.message || 'Nie udało się pobrać szlaków'
          this.showError('Nie udało się pobrać listy szlaków')
        }
      } finally {
        this.loading = false
      }
    },

    async loadMockData() {
      // Temporary mock data until API is ready
      await new Promise(resolve => setTimeout(resolve, 500))

      const mockTrails = [
        {
          id: 1,
          trail_name: 'Wisła - Kraków do Tynca',
          river_name: 'Wisła',
          trail_length: 12.5,
          difficulty: 'łatwy',
          status: 'active'
        },
        {
          id: 2,
          trail_name: 'Dunajec - Spływ pontonowy przez Pieniny',
          river_name: 'Dunajec',
          trail_length: 18.2,
          difficulty: 'umiarkowany',
          status: 'active'
        },
        {
          id: 3,
          trail_name: 'Bóbr - Przełom Bardo',
          river_name: 'Bóbr',
          trail_length: 25.7,
          difficulty: 'trudny',
          status: 'active'
        },
        {
          id: 4,
          trail_name: 'Czarna Hańcza - Wigierski Park Narodowy',
          river_name: 'Czarna Hańcza',
          trail_length: 15.3,
          difficulty: 'łatwy',
          status: 'active'
        },
        {
          id: 5,
          trail_name: 'Drawa - Rezerwat Przyrody',
          river_name: 'Drawa',
          trail_length: 22.1,
          difficulty: 'umiarkowany',
          status: 'draft'
        },
        {
          id: 6,
          trail_name: 'San - Bieszczadzki szlak',
          river_name: 'San',
          trail_length: 35.8,
          difficulty: 'ekspertowy',
          status: 'active'
        },
        {
          id: 7,
          trail_name: 'Wda - Rezerwat Przełom Wdy',
          river_name: 'Wda',
          trail_length: 19.5,
          difficulty: 'trudny',
          status: 'active'
        },
        {
          id: 8,
          trail_name: 'Pilica - Spała do Tomaszowa',
          river_name: 'Pilica',
          trail_length: 28.3,
          difficulty: 'umiarkowany',
          status: 'archived'
        }
      ]

      // Apply filters
      let filtered = [...mockTrails]

      if (this.filters.difficulty) {
        filtered = filtered.filter(t => t.difficulty === this.filters.difficulty)
      }

      if (this.filters.river) {
        filtered = filtered.filter(t => t.river_name === this.filters.river)
      }

      if (this.filters.status) {
        filtered = filtered.filter(t => t.status === this.filters.status)
      }

      this.trails = filtered
      this.totalItems = filtered.length
    },

    buildFilterParams() {
      const params = {}

      if (this.filters.difficulty) {
        params.difficulty = this.filters.difficulty
      }

      if (this.filters.river) {
        params.river_name = this.filters.river
      }

      if (this.filters.status) {
        params.status = this.filters.status
      }

      return params
    },

    handlePageChange(page) {
      this.internalPage = page
      this.fetchTrails()
    },

    handleItemsPerPageChange(itemsPerPage) {
      this.internalItemsPerPage = itemsPerPage
      this.internalPage = 1
      this.fetchTrails()
    },

    async viewTrail(trail) {
      this.showInfo(`Podgląd szlaku: ${trail.trail_name}`)
      // TODO: Navigate to trail edit view
      // this.$router.push(`/dashboard/trails/${trail.id}`)
    },

    async editTrail(trail) {
      this.$router.push(`/dashboard/trails/${trail.id}/edit`)
    },

    confirmDeleteTrail(trail) {
      this.deleteDialog.trail = trail
      this.deleteDialog.show = true
    },

    async executeDelete() {
      this.deleteDialog.loading = true

      try {
        await apiClient.delete(`/dashboard/trails/${this.deleteDialog.trail.id}`)

        // Remove from local state
        this.trails = this.trails.filter(t => t.id !== this.deleteDialog.trail.id)
        this.totalItems -= 1

        this.showSuccess(`Szlak "${this.deleteDialog.trail.trail_name}" został usunięty`)
        this.deleteDialog.show = false

      } catch (error) {
        console.error('Failed to delete trail:', error)

        // Fallback for mock data (temporary)
        if (error.response?.status === 404 || error.code === 'ERR_NETWORK') {
          this.trails = this.trails.filter(t => t.id !== this.deleteDialog.trail.id)
          this.totalItems -= 1
          this.showSuccess(`Szlak "${this.deleteDialog.trail.trail_name}" został usunięty (mock)`)
          this.deleteDialog.show = false
        } else {
          this.showError('Nie udało się usunąć szlaku')
        }
      } finally {
        this.deleteDialog.loading = false
        this.deleteDialog.trail = null
      }
    },

    async duplicateTrail(trail) {
      this.showInfo(`Duplikowanie szlaku: ${trail.trail_name}`)
      // TODO: Implement duplicate functionality
    },

    async archiveTrail(trail) {
      this.showInfo(`Archiwizowanie szlaku: ${trail.trail_name}`)
      // TODO: Implement archive functionality
    },

    async refreshTrails() {
      await this.fetchTrails()
      this.showInfo('Lista szlaków została odświeżona')
    },

    clearFilters() {
      this.filters = {
        difficulty: null,
        river: null,
        status: null
      }
    },

    getDifficultyConfig(difficulty) {
      return DIFFICULTY_CONFIG[difficulty] || {
        variant: 'secondary',
        label: difficulty,
        icon: 'mdi-help-circle'
      }
    },

    getDifficultyVariant(difficulty) {
      return this.getDifficultyConfig(difficulty).variant
    },

    getDifficultyLabel(difficulty) {
      return this.getDifficultyConfig(difficulty).label
    },

    getDifficultyIcon(difficulty) {
      return this.getDifficultyConfig(difficulty).icon
    },

    formatLength(length) {
      return `${length} km`
    },

    // Bulk operations
    clearSelection() {
      this.selectedTrails = []
    },

    confirmBulkDelete() {
      this.bulkDeleteDialog.count = this.selectedTrails.length
      this.bulkDeleteDialog.show = true
    },

    async executeBulkDelete() {
      this.bulkDeleteDialog.loading = true

      try {
        const ids = this.selectedTrails.map(trail => trail.id)

        // API call for bulk delete
        await apiClient.post('/dashboard/trails/bulk-delete', { ids })

        // Remove from local state
        this.trails = this.trails.filter(t => !ids.includes(t.id))
        this.totalItems -= ids.length

        this.showSuccess(`Usunięto ${ids.length} ${this.getTrailsWord(ids.length)}`)
        this.bulkDeleteDialog.show = false
        this.clearSelection()

      } catch (error) {
        console.error('Failed to bulk delete trails:', error)

        // Fallback for mock data (temporary)
        if (error.response?.status === 404 || error.code === 'ERR_NETWORK') {
          const ids = this.selectedTrails.map(trail => trail.id)
          this.trails = this.trails.filter(t => !ids.includes(t.id))
          this.totalItems -= ids.length
          this.showSuccess(`Usunięto ${ids.length} ${this.getTrailsWord(ids.length)} (mock)`)
          this.bulkDeleteDialog.show = false
          this.clearSelection()
        } else {
          this.showError('Nie udało się usunąć szlaków')
        }
      } finally {
        this.bulkDeleteDialog.loading = false
      }
    },

    bulkChangeStatus(newStatus) {
      this.bulkStatusDialog.count = this.selectedTrails.length
      this.bulkStatusDialog.newStatus = newStatus
      this.bulkStatusDialog.show = true
    },

    async executeBulkStatusChange() {
      this.bulkStatusDialog.loading = true

      try {
        const ids = this.selectedTrails.map(trail => trail.id)
        const newStatus = this.bulkStatusDialog.newStatus

        // API call for bulk status change
        await apiClient.post('/dashboard/trails/bulk-status', {
          ids,
          status: newStatus
        })

        // Update local state
        this.trails = this.trails.map(trail => {
          if (ids.includes(trail.id)) {
            return { ...trail, status: newStatus }
          }
          return trail
        })

        this.showSuccess(`Zaktualizowano status ${ids.length} ${this.getTrailsWord(ids.length)}`)
        this.bulkStatusDialog.show = false
        this.clearSelection()

      } catch (error) {
        console.error('Failed to bulk change status:', error)

        // Fallback for mock data (temporary)
        if (error.response?.status === 404 || error.code === 'ERR_NETWORK') {
          const ids = this.selectedTrails.map(trail => trail.id)
          const newStatus = this.bulkStatusDialog.newStatus

          this.trails = this.trails.map(trail => {
            if (ids.includes(trail.id)) {
              return { ...trail, status: newStatus }
            }
            return trail
          })

          this.showSuccess(`Zaktualizowano status ${ids.length} ${this.getTrailsWord(ids.length)} (mock)`)
          this.bulkStatusDialog.show = false
          this.clearSelection()
        } else {
          this.showError('Nie udało się zmienić statusu')
        }
      } finally {
        this.bulkStatusDialog.loading = false
      }
    },

    getTrailsWord(count) {
      if (count === 1) return 'szlak'
      if (count % 10 >= 2 && count % 10 <= 4 && (count % 100 < 10 || count % 100 >= 20)) {
        return 'szlaki'
      }
      return 'szlaków'
    }
  }
}
</script>

<style scoped>
.trails-list {
  width: 100%;
}

.trails-header-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.trails-filters {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.filter-select :deep(.v-field) {
  font-size: 13px;
}

.trail-name {
  display: inline-block;
  max-width: 250px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 500;
  color: hsl(var(--v-theme-primary));
  cursor: pointer;
  transition: all 0.2s ease;
}

.trail-name:hover {
  text-decoration: underline;
  color: hsl(var(--v-theme-primary-darken-1));
}

.river-name {
  display: inline-block;
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.row-actions {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
  align-items: center;
}

/* Select column width adjustment */
:deep(.v-data-table__td.v-data-table-column--select),
:deep(.v-data-table__th.v-data-table-column--select) {
  width: 48px !important;
  padding: 0 12px !important;
}

/* Status column styling */
.status-badge-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: help;
}

.status-tooltip {
  text-align: center;
  padding: 4px 0;
}

.status-tooltip .text-caption {
  opacity: 0.9;
}

/* Bulk actions toolbar */
.bulk-actions-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 12px 16px;
  margin-bottom: 16px;
  background: hsl(var(--v-theme-primary) / 0.08);
  border: 1px solid hsl(var(--v-theme-primary) / 0.2);
  border-radius: 8px;
}

.bulk-actions-info {
  display: flex;
  align-items: center;
  gap: 8px;
  color: hsl(var(--v-theme-primary));
}

.bulk-actions-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

/* Slide down animation */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .trails-filters {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-select {
    max-width: 100% !important;
  }

  .trails-header-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .bulk-actions-toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .bulk-actions-info {
    justify-content: center;
  }

  .bulk-actions-buttons {
    justify-content: center;
  }
}

@media (max-width: 600px) {
  .trail-name {
    max-width: 150px;
  }

  .river-name {
    max-width: 120px;
  }

  .bulk-actions-buttons {
    flex-direction: column;
  }
}
</style>
