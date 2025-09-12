<template>
  <v-card class="elevation-2">
    <!-- Toolbar -->
    <v-card-title v-if="title || $slots.toolbar">
      <div class="d-flex justify-space-between align-center w-100">
        <div class="text-h6">{{ title }}</div>
        <slot name="toolbar" />
      </div>
    </v-card-title>

    <!-- Search and filters -->
    <v-card-text v-if="searchable || $slots.filters">
      <v-row>
        <v-col v-if="searchable" cols="12" md="6">
          <v-text-field
            v-model="search"
            :label="searchLabel"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            single-line
          />
        </v-col>
        <v-col cols="12" :md="searchable ? 6 : 12">
          <slot name="filters" />
        </v-col>
      </v-row>
    </v-card-text>

    <!-- Data table -->
    <v-data-table
      v-model:page="page"
      v-model:items-per-page="itemsPerPage"
      v-model:sort-by="sortBy"
      :headers="headersWithActions"
      :items="filteredItems"
      :loading="loading"
      :items-per-page-options="itemsPerPageOptions"
      class="elevation-0"
    >
      <!-- Custom header slots -->
      <template v-for="header in headers" :key="header.key" #[`header.${header.key}`]="{ column }">
        <slot :name="`header.${header.key}`" :column="column">
          {{ column.title }}
        </slot>
      </template>

      <!-- Custom item slots -->
      <template v-for="header in headers" :key="header.key" #[`item.${header.key}`]="{ item, value }">
        <slot :name="`item.${header.key}`" :item="item" :value="value">
          {{ value }}
        </slot>
      </template>

      <!-- Actions column -->
      <template v-if="hasActions" #item.actions="{ item }">
        <div class="action-buttons">
          <v-tooltip v-if="actions.view" text="Podgląd">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon="mdi-eye"
                size="x-small"
                variant="text"
                @click="$emit('view', item)"
              />
            </template>
          </v-tooltip>

          <v-tooltip v-if="actions.edit" text="Edytuj">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon="mdi-pencil"
                size="x-small"
                variant="text"
                color="primary"
                @click="$emit('edit', item)"
              />
            </template>
          </v-tooltip>

          <v-tooltip v-if="actions.delete" text="Usuń">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon="mdi-delete"
                size="x-small"
                variant="text"
                color="error"
                @click="$emit('delete', item)"
              />
            </template>
          </v-tooltip>

          <slot name="actions" :item="item" />
        </div>
      </template>

      <!-- No data slot -->
      <template #no-data>
        <v-empty-state
          :icon="noDataIcon"
          :title="noDataTitle"
          :text="noDataText"
        />
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
export default {
  name: 'DataTable',
  emits: {
    view: (item) => item && typeof item === 'object',
    edit: (item) => item && typeof item === 'object', 
    delete: (item) => item && typeof item === 'object'
  },
  props: {
    title: {
      type: String,
      default: ''
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
      default: () => [],
      validator(items) {
        return Array.isArray(items)
      }
    },
    loading: {
      type: Boolean,
      default: false
    },
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
      }),
      validator(actions) {
        const allowedKeys = ['view', 'edit', 'delete']
        return Object.keys(actions).every(key => 
          allowedKeys.includes(key) && typeof actions[key] === 'boolean'
        )
      }
    },
    itemsPerPageOptions: {
      type: Array,
      default: () => [10, 25, 50, 100],
      validator(options) {
        return Array.isArray(options) && options.every(option => 
          typeof option === 'number' && option > 0
        )
      }
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
    }
  },
  data() {
    return {
      search: '',
      page: 1,
      itemsPerPage: 10,
      sortBy: []
    }
  },
  computed: {
    hasActions() {
      return Object.values(this.actions).some(action => action === true) || 
             (this.$slots.actions && this.$slots.actions().length > 0)
    },
    headersWithActions() {
      if (!this.hasActions) return this.headers
      
      const actionsHeader = {
        title: 'Akcje',
        key: 'actions',
        sortable: false,
        width: '100px',
        align: 'end'
      }
      
      return [...this.headers, actionsHeader]
    },
    filteredItems() {
      if (!this.search || !this.searchable) return this.items
      
      const searchTerm = this.search.toLowerCase().trim()
      if (!searchTerm) return this.items
      
      return this.items.filter(item => {
        return this.headers.some(header => {
          const value = this.getNestedValue(item, header.key)
          return value && String(value).toLowerCase().includes(searchTerm)
        })
      })
    }
  },
  watch: {
    items: {
      handler() {
        this.resetPagination()
      },
      deep: true
    },
    search() {
      this.resetPagination()
    }
  },
  methods: {
    getNestedValue(obj, path) {
      return path.split('.').reduce((current, key) => current && current[key], obj)
    },
    resetPagination() {
      this.page = 1
    },
    handleView(item) {
      this.$emit('view', item)
    },
    handleEdit(item) {
      this.$emit('edit', item)
    },
    handleDelete(item) {
      this.$emit('delete', item)
    }
  }
}
</script>

<style scoped>
.action-buttons {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
  align-items: center;
}

.action-buttons .v-btn {
  transition: all 0.2s ease;
}

.action-buttons .v-btn:hover {
  transform: scale(1.1);
}

:deep(.v-data-table-header th) {
  background-color: rgba(var(--v-theme-surface-variant), 0.12) !important;
  font-weight: 600 !important;
}

:deep(.v-data-table__td) {
  padding: 0 16px !important;
  height: 48px !important;
}

:deep(.v-data-table-rows-no-data) {
  height: 200px !important;
}
</style>