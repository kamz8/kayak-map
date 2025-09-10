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
      :headers="headers"
      :items="filteredItems"
      :loading="loading"
      :items-per-page-options="itemsPerPageOptions"
      :search="search"
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
                size="small"
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
                size="small"
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
                size="small"
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
  emits: ['view', 'edit', 'delete'],
  props: {
    title: String,
    headers: {
      type: Array,
      required: true
    },
    items: {
      type: Array,
      default: () => []
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
      })
    },
    itemsPerPageOptions: {
      type: Array,
      default: () => [10, 25, 50, 100]
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
      return Object.values(this.actions).some(action => action) || this.$slots.actions
    },
    filteredItems() {
      return this.items
    },
    headersWithActions() {
      if (!this.hasActions) return this.headers
      
      return [
        ...this.headers,
        {
          title: 'Akcje',
          key: 'actions',
          sortable: false,
          width: '120px'
        }
      ]
    }
  },
  watch: {
    hasActions: {
      immediate: true,
      handler(hasActions) {
        if (hasActions && !this.headers.find(h => h.key === 'actions')) {
          // Update headers to include actions
        }
      }
    }
  }
}
</script>