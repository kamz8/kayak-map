<template>
  <div class="permission-selector">
    <div v-if="loading" class="d-flex justify-center align-center py-8">
      <v-progress-circular indeterminate color="primary" />
      <span class="ml-3">Ładowanie uprawnień...</span>
    </div>

    <div v-else-if="error" class="pa-4">
      <v-alert type="error" variant="tonal">
        <v-icon start>mdi-alert-circle</v-icon>
        {{ error }}
      </v-alert>
    </div>

    <div v-else class="permission-selector-content">
      <!-- Search -->
      <div class="mb-4">
        <UiInput
          v-model="searchQuery"
          label="Szukaj uprawnień"
          placeholder="Wpisz nazwę uprawnienia..."
          prepend-inner-icon="mdi-magnify"
          clearable
        />
      </div>

      <!-- Selected count -->
      <div class="mb-4">
        <UiBadge variant="secondary" size="sm">
          Wybrane: {{ selectedCount }} / {{ totalCount }}
        </UiBadge>
        <UiButton
          v-if="selectedCount > 0"
          variant="outline"
          size="sm"
          class="ml-2"
          @click="clearAll"
        >
          Wyczyść wszystko
        </UiButton>
      </div>

      <!-- Permissions grouped by module -->
      <v-expansion-panels
        v-if="filteredModules.length > 0"
        multiple
        variant="accordion"
        class="permission-modules"
      >
        <v-expansion-panel
          v-for="module in filteredModules"
          :key="module.name"
          :title="getModuleTitle(module.name)"
          :text="`${module.permissions.length} uprawnień`"
        >
          <template #title>
            <div class="d-flex align-center w-100">
              <v-icon :icon="getModuleIcon(module.name)" class="me-3" />
              <div class="flex-grow-1">
                <div class="font-weight-medium">{{ getModuleTitle(module.name) }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ getSelectedInModule(module.name) }} / {{ module.permissions.length }} wybrane
                </div>
              </div>
              <UiBadge
                :variant="getSelectedInModule(module.name) === module.permissions.length ? 'default' : 'secondary'"
                size="sm"
              >
                {{ getSelectedInModule(module.name) }} / {{ module.permissions.length }}
              </UiBadge>
            </div>
          </template>

          <template #text>
            <div class="pb-2">
              <!-- Module actions -->
              <div class="d-flex gap-2 mb-3">
                <UiButton
                  variant="outline"
                  size="sm"
                  @click="selectAllInModule(module.name)"
                  :disabled="getSelectedInModule(module.name) === module.permissions.length"
                >
                  <v-icon start>mdi-check-all</v-icon>
                  Wybierz wszystkie
                </UiButton>
                <UiButton
                  variant="outline"
                  size="sm"
                  @click="clearAllInModule(module.name)"
                  :disabled="getSelectedInModule(module.name) === 0"
                >
                  <v-icon start>mdi-close</v-icon>
                  Wyczyść wszystkie
                </UiButton>
              </div>

              <!-- Permission list -->
              <div class="permissions-list">
                <v-checkbox
                  v-for="permission in module.permissions"
                  :key="permission.id"
                  v-model="selectedPermissions"
                  :value="permission.id"
                  :label="permission.name"
                  :hint="permission.description"
                  persistent-hint
                  density="comfortable"
                  hide-details="auto"
                  class="permission-checkbox"
                >
                  <template #label>
                    <div class="permission-label">
                      <div class="font-weight-medium">{{ permission.name }}</div>
                      <div v-if="permission.description" class="text-caption text-medium-emphasis">
                        {{ permission.description }}
                      </div>
                    </div>
                  </template>
                </v-checkbox>
              </div>
            </div>
          </template>
        </v-expansion-panel>
      </v-expansion-panels>

      <!-- Empty state -->
      <div v-else class="text-center py-8">
        <v-icon size="64" color="disabled">mdi-shield-search</v-icon>
        <div class="text-h6 mt-3 text-disabled">Brak uprawnień</div>
        <div class="text-body-2 text-disabled">
          {{ searchQuery ? 'Nie znaleziono uprawnień pasujących do wyszukiwania' : 'Brak dostępnych uprawnień' }}
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="permission-selector-actions pt-4 border-t">
      <div class="d-flex justify-space-between align-center">
        <div class="text-body-2 text-medium-emphasis">
          Wybrane uprawnienia: {{ selectedCount }}
        </div>
        <div class="d-flex gap-2">
          <UiButton
            variant="outline"
            @click="$emit('cancel')"
            :disabled="saving"
          >
            Anuluj
          </UiButton>
          <UiButton
            variant="default"
            @click="savePermissions"
            :loading="saving"
            :disabled="!hasChanges"
          >
            <v-icon start>mdi-content-save</v-icon>
            Zapisz uprawnienia
          </UiButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { UiInput, UiButton, UiBadge } from '@/dashboard/components/ui'

export default {
  name: 'PermissionSelector',
  components: {
    UiInput,
    UiButton,
    UiBadge
  },
  props: {
    role: {
      type: Object,
      required: true
    },
    permissions: {
      type: Array,
      default: () => []
    },
    initialPermissions: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    saving: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: null
    }
  },
  emits: ['update:permissions', 'save', 'cancel'],
  data() {
    return {
      searchQuery: '',
      selectedPermissions: [...(this.initialPermissions.map(p => p.id) || [])]
    }
  },
  computed: {
    // Group permissions by module
    groupedPermissions() {
      if (!this.permissions.length) return []

      const groups = {}
      this.permissions.forEach(permission => {
        const module = permission.module || 'general'
        if (!groups[module]) {
          groups[module] = {
            name: module,
            permissions: []
          }
        }
        groups[module].permissions.push(permission)
      })

      return Object.values(groups).sort((a, b) => a.name.localeCompare(b.name))
    },

    // Filter modules based on search
    filteredModules() {
      if (!this.searchQuery) return this.groupedPermissions

      return this.groupedPermissions.map(module => ({
        ...module,
        permissions: module.permissions.filter(permission =>
          permission.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          (permission.description && permission.description.toLowerCase().includes(this.searchQuery.toLowerCase()))
        )
      })).filter(module => module.permissions.length > 0)
    },

    selectedCount() {
      return this.selectedPermissions.length
    },

    totalCount() {
      return this.permissions.length
    },

    hasChanges() {
      const initialIds = new Set(this.initialPermissions.map(p => p.id))
      const selectedIds = new Set(this.selectedPermissions)

      if (initialIds.size !== selectedIds.size) return true

      for (const id of initialIds) {
        if (!selectedIds.has(id)) return true
      }

      return false
    }
  },
  watch: {
    selectedPermissions: {
      handler(newPermissions) {
        this.$emit('update:permissions', newPermissions)
      },
      deep: true
    },
    initialPermissions: {
      handler(newInitial) {
        this.selectedPermissions = [...(newInitial.map(p => p.id) || [])]
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    getModuleTitle(moduleName) {
      const titles = {
        'users': 'Użytkownicy',
        'roles': 'Role i uprawnienia',
        'trails': 'Szlaki',
        'dashboard': 'Panel administracyjny',
        'general': 'Ogólne',
        'system': 'System'
      }
      return titles[moduleName] || moduleName.charAt(0).toUpperCase() + moduleName.slice(1)
    },

    getModuleIcon(moduleName) {
      const icons = {
        'users': 'mdi-account-group',
        'roles': 'mdi-shield-account',
        'trails': 'mdi-map-marker-path',
        'dashboard': 'mdi-view-dashboard',
        'general': 'mdi-cog',
        'system': 'mdi-server'
      }
      return icons[moduleName] || 'mdi-key'
    },

    getSelectedInModule(moduleName) {
      const modulePermissions = this.groupedPermissions.find(m => m.name === moduleName)?.permissions || []
      return modulePermissions.filter(p => this.selectedPermissions.includes(p.id)).length
    },

    selectAllInModule(moduleName) {
      const modulePermissions = this.groupedPermissions.find(m => m.name === moduleName)?.permissions || []
      const moduleIds = modulePermissions.map(p => p.id)

      // Add all module permissions that aren't already selected
      const newSelections = [...new Set([...this.selectedPermissions, ...moduleIds])]
      this.selectedPermissions = newSelections
    },

    clearAllInModule(moduleName) {
      const modulePermissions = this.groupedPermissions.find(m => m.name === moduleName)?.permissions || []
      const moduleIds = new Set(modulePermissions.map(p => p.id))

      // Remove all module permissions
      this.selectedPermissions = this.selectedPermissions.filter(id => !moduleIds.has(id))
    },

    clearAll() {
      this.selectedPermissions = []
    },

    savePermissions() {
      this.$emit('save', this.selectedPermissions)
    }
  }
}
</script>

<style scoped>
.permission-selector {
  max-height: 600px;
  display: flex;
  flex-direction: column;
}

.permission-selector-content {
  flex: 1;
  overflow-y: auto;
}

.permission-modules {
  margin-bottom: 1rem;
}

.permissions-list {
  max-height: 300px;
  overflow-y: auto;
}

.permission-checkbox {
  margin-bottom: 8px;
}

.permission-label {
  line-height: 1.2;
}

.permission-selector-actions {
  flex-shrink: 0;
  background-color: rgb(var(--v-theme-surface));
  margin: 0 -24px -24px -24px;
  padding: 16px 24px;
}

:deep(.v-expansion-panel-text__wrapper) {
  padding: 16px 24px;
}

:deep(.v-expansion-panel-title) {
  padding: 12px 24px;
}
</style>