<template>
  <div class="user-role-manager">
    <v-card-text>
      <div class="mb-4">
        <h3 class="text-h6 mb-2">Aktualne role</h3>
        <div v-if="user.roles && user.roles.length > 0" class="d-flex flex-wrap gap-2">
          <UiBadge
            v-for="role in user.roles"
            :key="role.id"
            :variant="role.color"
            size="sm"
          >
            {{ role.display_name }}
          </UiBadge>
        </div>
        <p v-else class="text-medium-emphasis">
          Użytkownik nie ma przypisanych ról
        </p>
      </div>

      <v-divider class="my-4" />

      <div class="mb-4">
        <h3 class="text-h6 mb-3">Zarządzaj rolami</h3>

        <v-select
          v-model="selectedRoles"
          :items="availableRoleOptions"
          item-title="label"
          item-value="value"
          label="Wybierz role"
          variant="outlined"
          multiple
          chips
          closable-chips
          :loading="loading"
          :disabled="!canManageRoles"
          hide-details="auto"
        >
          <template #chip="{ props, item }">
            <v-chip
              v-bind="props"
              :color="getRoleColor(item.raw.value)"
              variant="flat"
              size="small"
            >
              {{ item.raw.label }}
            </v-chip>
          </template>
        </v-select>

        <v-alert
          v-if="!canManageRoles"
          type="warning"
          variant="tonal"
          class="mt-3"
          density="compact"
        >
          <v-icon start>mdi-shield-alert</v-icon>
          Nie masz uprawnień do zarządzania wszystkimi rolami tego użytkownika
        </v-alert>

        <v-alert
          v-if="securityWarning"
          type="error"
          variant="tonal"
          class="mt-3"
          density="compact"
        >
          <v-icon start>mdi-alert-circle</v-icon>
          {{ securityWarning }}
        </v-alert>
      </div>

      <div class="d-flex justify-end gap-2 mt-6">
        <UiButton
          variant="outline"
          :disabled="loading"
          @click="resetChanges"
        >
          Anuluj
        </UiButton>

        <UiButton
          :loading="loading"
          :disabled="!hasChanges || !canManageRoles"
          @click="saveChanges"
        >
          Zapisz zmiany
        </UiButton>
      </div>
    </v-card-text>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { UiBadge, UiButton } from '@/dashboard/components/ui'

const ROLE_COLORS = {
  'Super Admin': 'error',
  'Admin': 'warning',
  'Editor': 'info',
  'User': 'success'
}

export default {
  name: 'UserRoleManager',
  components: {
    UiBadge,
    UiButton
  },
  props: {
    user: {
      type: Object,
      required: true
    },
    availableRoles: {
      type: Array,
      default: () => []
    }
  },
  emits: ['roles-updated'],
  data() {
    return {
      selectedRoles: [],
      loading: false,
      error: null,
      securityWarning: ''
    }
  },
  computed: {
    ...mapGetters('auth', ['user as currentUser']),

    // Get current user roles as array of role names
    currentUserRoles() {
      return this.user.roles ? this.user.roles.map(role => role.name) : []
    },

    // Check if there are unsaved changes
    hasChanges() {
      const current = [...this.currentUserRoles].sort()
      const selected = [...this.selectedRoles].sort()
      return JSON.stringify(current) !== JSON.stringify(selected)
    },

    // Filter available roles based on current user permissions
    availableRoleOptions() {
      return this.availableRoles.filter(role => {
        // Super Admin can assign any role
        if (this.currentUser.is_super_admin) {
          return true
        }

        // Admin cannot assign Super Admin role
        if (role.value === 'Super Admin') {
          return false
        }

        return true
      })
    },

    // Check if current user can manage roles for this user
    canManageRoles() {
      // Super Admin can manage anyone
      if (this.currentUser.is_super_admin) {
        return true
      }

      // Cannot manage your own roles through this interface
      if (this.user.id === this.currentUser.id) {
        return false
      }

      // If target user is Super Admin, only Super Admin can manage
      if (this.user.is_super_admin) {
        return false
      }

      return true
    }
  },
  created() {
    this.initializeSelectedRoles()
    this.checkSecurityWarnings()
  },
  watch: {
    user: {
      handler() {
        this.initializeSelectedRoles()
        this.checkSecurityWarnings()
      },
      deep: true
    },
    selectedRoles() {
      this.checkSecurityWarnings()
    }
  },
  methods: {
    ...mapActions('users', ['syncRoles']),
    ...mapActions('ui', ['showSuccess', 'showError']),

    initializeSelectedRoles() {
      this.selectedRoles = this.currentUserRoles
    },

    resetChanges() {
      this.initializeSelectedRoles()
      this.error = null
      this.securityWarning = ''
    },

    async saveChanges() {
      if (!this.hasChanges || !this.canManageRoles) return

      this.loading = true
      this.error = null

      try {
        const updatedUser = await this.syncRoles({
          userId: this.user.id,
          roles: this.selectedRoles
        })

        this.$emit('roles-updated', updatedUser)

      } catch (error) {
        const message = error.response?.data?.message || 'Nie udało się zaktualizować ról'
        this.error = message
        this.showError(message)

        // Reset on error
        this.resetChanges()
      } finally {
        this.loading = false
      }
    },

    getRoleColor(roleName) {
      return ROLE_COLORS[roleName] || 'primary'
    },

    checkSecurityWarnings() {
      this.securityWarning = ''

      // Check if trying to assign Super Admin without being Super Admin
      if (this.selectedRoles.includes('Super Admin') && !this.currentUser.is_super_admin) {
        this.securityWarning = 'Tylko Super Admin może przypisywać rolę Super Admin'
        return
      }

      // Check if trying to modify Super Admin user without being Super Admin
      if (this.user.is_super_admin && !this.currentUser.is_super_admin) {
        this.securityWarning = 'Tylko Super Admin może modyfikować role innych Super Admin'
        return
      }

      // Check if trying to modify own roles
      if (this.user.id === this.currentUser.id) {
        this.securityWarning = 'Nie możesz modyfikować własnych ról przez ten panel'
        return
      }
    }
  }
}
</script>

<style scoped>
.user-role-manager {
  min-height: 300px;
}

:deep(.v-chip--variant-flat) {
  border: 1px solid rgba(var(--v-border-color), 0.3);
}

:deep(.v-select__selection) {
  max-width: 200px;
}
</style>