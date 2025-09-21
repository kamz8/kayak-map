<template>
  <v-row>
    <v-col cols="12" md="3">
      <v-select
        v-model="localFilters.role"
        :items="roleOptions"
        item-title="label"
        item-value="value"
        label="Rola"
        variant="outlined"
        density="compact"
        clearable
        hide-details
        @update:model-value="emitFilters"
      >
        <template #prepend-inner>
          <v-icon size="16">mdi-shield-account</v-icon>
        </template>
      </v-select>
    </v-col>

    <v-col cols="12" md="3">
      <v-select
        v-model="localFilters.status"
        :items="statusOptions"
        item-title="label"
        item-value="value"
        label="Status"
        variant="outlined"
        density="compact"
        clearable
        hide-details
        @update:model-value="emitFilters"
      >
        <template #prepend-inner>
          <v-icon size="16">mdi-account-check</v-icon>
        </template>
      </v-select>
    </v-col>

    <v-col cols="12" md="2">
      <v-text-field
        v-model="localFilters.created_from"
        type="date"
        label="Od daty"
        variant="outlined"
        density="compact"
        hide-details
        @update:model-value="emitFilters"
      >
        <template #prepend-inner>
          <v-icon size="16">mdi-calendar-start</v-icon>
        </template>
      </v-text-field>
    </v-col>

    <v-col cols="12" md="2">
      <v-text-field
        v-model="localFilters.created_to"
        type="date"
        label="Do daty"
        variant="outlined"
        density="compact"
        hide-details
        @update:model-value="emitFilters"
      >
        <template #prepend-inner>
          <v-icon size="16">mdi-calendar-end</v-icon>
        </template>
      </v-text-field>
    </v-col>

    <v-col cols="12" md="2">
      <div class="d-flex gap-1">
        <UiButton
          variant="outline"
          size="sm"
          :disabled="!hasActiveFilters"
          @click="resetFilters"
          block
        >
          <v-icon start size="16">mdi-filter-off</v-icon>
          Reset
        </UiButton>
      </div>
    </v-col>
  </v-row>
</template>

<script>
import { UiBadge, UiButton } from '@/dashboard/components/ui'

export default {
  name: 'UserFilters',
  components: {
    UiBadge,
    UiButton
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    roleOptions: {
      type: Array,
      default: () => []
    },
    statusOptions: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:filters', 'reset'],
  data() {
    return {
      localFilters: { ...this.filters }
    }
  },
  computed: {
    hasActiveFilters() {
      return Object.values(this.localFilters).some(value =>
        value !== '' && value !== null && value !== undefined
      )
    }
  },
  watch: {
    filters: {
      handler(newFilters) {
        this.localFilters = { ...newFilters }
      },
      deep: true
    }
  },
  methods: {
    emitFilters() {
      // Debounce filters to avoid too many API calls
      clearTimeout(this.filterTimeout)
      this.filterTimeout = setTimeout(() => {
        this.$emit('update:filters', { ...this.localFilters })
      }, 300)
    },

    resetFilters() {
      this.localFilters = {
        search: '',
        role: '',
        status: '',
        created_from: '',
        created_to: '',
        sort_by: 'created_at',
        sort_direction: 'desc'
      }
      this.$emit('reset')
    }
  },
  beforeUnmount() {
    if (this.filterTimeout) {
      clearTimeout(this.filterTimeout)
    }
  }
}
</script>

<style scoped>
:deep(.v-field--variant-outlined .v-field__outline__start),
:deep(.v-field--variant-outlined .v-field__outline__end) {
  border-color: rgba(var(--v-border-color), var(--v-border-opacity)) !important;
}

:deep(.v-field--focused .v-field__outline) {
  border-color: rgb(var(--v-theme-primary)) !important;
  border-width: 2px !important;
}
</style>