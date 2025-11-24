<template>
  <UiCard :class="tableClasses">
    <!-- Header with title and actions -->
    <template #title>
      <div class="ui-data-table-header">
        <div class="ui-data-table-title">
          <slot name="title">
            <h3 class="ui-heading">{{ title }}</h3>
          </slot>
        </div>
        <div v-if="$slots.actions" class="ui-data-table-actions">
          <slot name="actions" />
        </div>
      </div>
    </template>

    <!-- Search and filters -->
    <template #default>
      <div v-if="searchable || $slots.filters" class="ui-data-table-controls">
        <div class="ui-data-table-search">
          <UiInput
            v-if="searchable"
            v-model="search"
            :placeholder="searchLabel"
            variant="default"
            size="sm"
            class="ui-search-input"
          >
            <template #prepend-inner>
              <v-icon icon="mdi-magnify" size="small" />
            </template>
          </UiInput>
        </div>

        <div v-if="$slots.filters" class="ui-data-table-filters">
          <slot name="filters" />
        </div>
      </div>

      <!-- Enhanced data table -->
      <v-data-table-server
        v-if="serverSide"
        v-model="internalSelected"
        :headers="headersWithActions"
        :items="items"
        :items-length="totalItems"
        :loading="loading"
        :items-per-page-options="itemsPerPageOptions"
        :class="dataTableClasses"
        :hover="hover"
        :density="density"
        :show-select="showSelect"
        :item-value="itemValue"
        :return-object="returnObject"
        :items-per-page-text="'Elementów na stronie:'"
        :page-text="'{0}-{1} z {2}'"
        :no-data-text="'Brak danych do wyświetlenia'"
        :loading-text="'Ładowanie... Proszę czekać'"
        @update:options="handleOptionsUpdate"
      >
        <!-- Custom header slots -->
        <template v-for="header in headers" :key="header.key" #[`header.${header.key}`]="{ column }">
          <slot :name="`header.${header.key}`" :column="column">
            <span class="ui-header-text">{{ column.title }}</span>
          </slot>
        </template>

        <!-- Custom item slots -->
        <template v-for="header in headers" :key="header.key" #[`item.${header.key}`]="{ item, value }">
          <slot :name="`item.${header.key}`" :item="item" :value="value">
            {{ value }}
          </slot>
        </template>

        <!-- Enhanced actions column -->
        <template v-if="hasActions" #item.actions="{ item }">
          <div class="ui-action-buttons">
            <v-tooltip v-if="actions.view" text="Podgląd" location="top">
              <template #activator="{ props }">
                <UiButton
                  v-bind="props"
                  variant="ghost"
                  size="icon"
                  @click="$emit('view', item)"
                >
                  <v-icon icon="mdi-eye" size="small" />
                </UiButton>
              </template>
            </v-tooltip>

            <v-tooltip v-if="actions.edit" text="Edytuj" location="top">
              <template #activator="{ props }">
                <UiButton
                  v-bind="props"
                  variant="ghost"
                  size="icon"
                  @click="$emit('edit', item)"
                >
                  <v-icon icon="mdi-pencil" size="small" />
                </UiButton>
              </template>
            </v-tooltip>

            <v-tooltip v-if="actions.delete" text="Usuń" location="top">
              <template #activator="{ props }">
                <UiButton
                  v-bind="props"
                  variant="ghost"
                  size="icon"
                  @click="$emit('delete', item)"
                >
                  <v-icon icon="mdi-delete" size="small" color="error" />
                </UiButton>
              </template>
            </v-tooltip>

            <slot name="row-actions" :item="item" />
          </div>
        </template>

        <!-- Loading slot -->
        <template #loading>
          <v-skeleton-loader
            class="mx-auto border"
            type="table"
          />
        </template>

        <!-- No data slot -->
        <template #no-data>
          <div class="ui-no-data">
            <v-icon :icon="noDataIcon" size="48" color="surface-variant" />
            <h4 class="ui-no-data-title">{{ noDataTitle }}</h4>
            <p class="ui-no-data-text">{{ noDataText }}</p>
          </div>
        </template>
      </v-data-table-server>

      <!-- Client-side data table -->
      <v-data-table
        v-else
        v-model="internalSelected"
        :headers="headersWithActions"
        :items="filteredItems"
        :loading="loading"
        :items-per-page-options="itemsPerPageOptions"
        :class="dataTableClasses"
        :hover="hover"
        :density="density"
        :show-select="showSelect"
        :item-value="itemValue"
        :return-object="returnObject"
        :items-per-page-text="'Elementów na stronie:'"
        :page-text="'{0}-{1} z {2}'"
        :no-data-text="'Brak danych do wyświetlenia'"
        :loading-text="'Ładowanie... Proszę czekać'"
      >
        <!-- Custom header slots -->
        <template v-for="header in headers" :key="header.key" #[`header.${header.key}`]="{ column }">
          <slot :name="`header.${header.key}`" :column="column">
            <span class="ui-header-text">{{ column.title }}</span>
          </slot>
        </template>

        <!-- Custom item slots -->
        <template v-for="header in headers" :key="header.key" #[`item.${header.key}`]="{ item, value }">
          <slot :name="`item.${header.key}`" :item="item" :value="value">
            {{ value }}
          </slot>
        </template>

        <!-- Enhanced actions column -->
        <template v-if="hasActions" #item.actions="{ item }">
          <div class="ui-action-buttons">
            <v-tooltip v-if="actions.view" text="Podgląd" location="top">
              <template #activator="{ props }">
                <UiButton
                  v-bind="props"
                  variant="ghost"
                  size="icon"
                  @click="$emit('view', item)"
                >
                  <v-icon icon="mdi-eye" size="small" />
                </UiButton>
              </template>
            </v-tooltip>

            <v-tooltip v-if="actions.edit" text="Edytuj" location="top">
              <template #activator="{ props }">
                <UiButton
                  v-bind="props"
                  variant="ghost"
                  size="icon"
                  @click="$emit('edit', item)"
                >
                  <v-icon icon="mdi-pencil" size="small" />
                </UiButton>
              </template>
            </v-tooltip>

            <v-tooltip v-if="actions.delete" text="Usuń" location="top">
              <template #activator="{ props }">
                <UiButton
                  v-bind="props"
                  variant="ghost"
                  size="icon"
                  @click="$emit('delete', item)"
                >
                  <v-icon icon="mdi-delete" size="small" color="error" />
                </UiButton>
              </template>
            </v-tooltip>

            <slot name="row-actions" :item="item" />
          </div>
        </template>

        <!-- Loading slot -->
        <template #loading>
          <v-skeleton-loader
            class="mx-auto border"
            type="table"
          />
        </template>

        <!-- No data slot -->
        <template #no-data>
          <div class="ui-no-data">
            <v-icon :icon="noDataIcon" size="48" color="surface-variant" />
            <h4 class="ui-no-data-title">{{ noDataTitle }}</h4>
            <p class="ui-no-data-text">{{ noDataText }}</p>
          </div>
        </template>
      </v-data-table>
    </template>
  </UiCard>
</template>

<script>
import UiCard from './UiCard.vue'
import UiButton from './UiButton.vue'
import UiInput from './UiInput.vue'
import { cn } from '@/dashboard/lib/utils'

export default {
  name: 'UiDataTable',
  components: {
    UiCard,
    UiButton,
    UiInput
  },
  emits: {
    view: (item) => item && typeof item === 'object',
    edit: (item) => item && typeof item === 'object',
    delete: (item) => item && typeof item === 'object',
    'update:options': (options) => options && typeof options === 'object',
    'update:selected': (selected) => Array.isArray(selected),
    'update:search': (search) => typeof search === 'string'
  },
  props: {
    title: String,
    variant: {
      type: String,
      default: 'default',
      validator: (v) => ['default', 'outlined', 'elevated'].includes(v)
    },
    headers: {
      type: Array,
      required: true,
      validator(headers) {
        return Array.isArray(headers) && headers.every(header =>
          header && typeof header === 'object' && header.key && header.title
        )
      }
    },
    items: {
      type: Array,
      default: () => []
    },
    loading: Boolean,
    searchable: {
      type: Boolean,
      default: true
    },
    searchLabel: {
      type: String,
      default: 'Szukaj...'
    },
    actions: {
      type: Object,
      default: () => ({
        view: false,
        edit: true,
        delete: true
      })
    },
    itemsPerPageOptions: {
      type: Array,
      default: () => [10, 25, 50, 100]
    },
    hover: {
      type: Boolean,
      default: true
    },
    density: {
      type: String,
      default: 'default',
      validator: (v) => ['default', 'comfortable', 'compact'].includes(v)
    },
    noDataIcon: {
      type: String,
      default: 'mdi-database-off'
    },
    noDataTitle: {
      type: String,
      default: 'Brak danych'
    },
    noDataText: {
      type: String,
      default: 'Nie znaleziono żadnych rekordów.'
    },
    class: String,
    page: {
      type: Number,
      default: 1
    },
    totalItems: {
      type: Number,
      default: 0
    },
    currentItemsPerPage: {
      type: Number,
      default: 10
    },
    showSelect: {
      type: Boolean,
      default: false
    },
    selected: {
      type: Array,
      default: () => []
    },
    serverSide: {
      type: Boolean,
      default: false
    },
    itemValue: {
      type: String,
      default: 'id'
    },
    returnObject: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      search: '',
      sortBy: [],
      searchDebounceTimer: null
    }
  },
  computed: {
    tableClasses() {
      return cn(
        'ui-data-table',
        `ui-data-table--${this.variant}`,
        'px-0',
        this.class
      )
    },

    dataTableClasses() {
      return cn(
        'ui-data-table-content',
        'elevation-0'
      )
    },

    hasActions() {
      // Check if any default actions are enabled OR if custom row-actions slot is provided
      const hasDefaultActions = Object.values(this.actions).some(action => action === true)
      const hasCustomRowActions = !!this.$slots['row-actions']
      const hasHeaderActions = !!this.$slots.actions

      return hasDefaultActions || hasCustomRowActions || hasHeaderActions
    },

    headersWithActions() {
      if (!this.hasActions) return this.headers

      const actionsHeader = {
        title: 'Akcje',
        key: 'actions',
        sortable: false,
        width: '120px',
        align: 'end'
      }

      return [...this.headers, actionsHeader]
    },

    filteredItems() {
      // W trybie server-side, zwracamy items bez filtrowania lokalnego
      if (this.serverSide) return this.items

      if (!this.search || !this.searchable) return this.items

      const searchTerm = this.search.toLowerCase().trim()
      if (!searchTerm) return this.items

      return this.items.filter(item => {
        return this.headers.some(header => {
          const value = this.getNestedValue(item, header.key)
          return value && String(value).toLowerCase().includes(searchTerm)
        })
      })
    },

    internalSelected: {
      get() {
        return this.selected
      },
      set(value) {
        this.$emit('update:selected', value)
      }
    }
  },
  watch: {
    search(newVal) {
      // W trybie server-side, emitujemy search do rodzica z debounce
      if (this.serverSide) {
        clearTimeout(this.searchDebounceTimer)
        this.searchDebounceTimer = setTimeout(() => {
          this.$emit('update:search', newVal)
        }, 500)
      }
    }
  },
  methods: {
    getNestedValue(obj, path) {
      return path.split('.').reduce((current, key) => current && current[key], obj)
    },
    handleOptionsUpdate(options) {
      // In server-side mode, emit to parent to handle pagination
      if (this.serverSide) {
        this.$emit('update:options', options)
      }
    }
  }
}
</script>

<style scoped>
.ui-data-table {
  overflow: hidden;
}

.ui-data-table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 16px;
}

.ui-data-table-title {
  flex: 1;
}

.ui-heading {
  font-size: 18px;
  font-weight: 600;
  color: hsl(var(--v-theme-on-surface));
  margin: 0;
}

.ui-data-table-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.ui-data-table-controls {
  display: flex;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.ui-data-table-search {
  flex: 1;
  min-width: 250px;
}

.ui-search-input {
  max-width: 400px;
}

.ui-data-table-filters {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.ui-action-buttons {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
  align-items: center;
}

.ui-no-data {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 24px;
  text-align: center;
}

.ui-no-data-title {
  font-size: 16px;
  font-weight: 600;
  color: hsl(var(--v-theme-on-surface));
  margin: 16px 0 8px 0;
}

.ui-no-data-text {
  font-size: 14px;
  color: hsl(var(--v-theme-on-surface-variant));
  margin: 0;
}

/* Data table styling */
:deep(.ui-data-table-content) {
  border-radius: 0;
  border: none;
}

:deep(.ui-data-table-content .v-data-table-header th) {
  background-color: hsl(var(--v-theme-surface-variant)) !important;
  font-weight: 600 !important;
  color: hsl(var(--v-theme-on-surface)) !important;
  border-bottom: 1px solid hsl(var(--v-theme-surface-variant)) !important;
}

:deep(.ui-data-table-content .v-data-table__td) {
  padding: 12px 16px !important;
  border-bottom: 1px solid hsl(var(--v-theme-surface-variant)) !important;
}

:deep(.ui-data-table-content .v-data-table__tr:hover) {
  background-color: hsl(var(--v-theme-surface-variant) / 0.5) !important;
}

.ui-header-text {
  font-size: 13px;
  font-weight: 600;
  color: hsl(var(--v-theme-on-surface-variant));
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Items per page dropdown styling */
:deep(.v-data-table-footer .v-select) {
  max-width: 80px !important;
  transform: scale(0.85) !important;
  transform-origin: left center !important;
}

:deep(.v-data-table-footer .v-select .v-field) {
  font-size: 12px !important;
}

:deep(.v-data-table-footer .v-select .v-field__input) {
  padding: 4px 8px !important;
  min-height: 28px !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .ui-data-table-controls {
    flex-direction: column;
    align-items: stretch;
  }

  .ui-data-table-search {
    min-width: unset;
  }

  .ui-search-input {
    max-width: none;
  }
}
</style>
