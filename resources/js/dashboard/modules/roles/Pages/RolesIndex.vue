<template>
  <div class="roles-index">
    <UiDataTable
      title="Role systemowe"
      :headers="headers"
      :items="roles"
      :loading="loading"
      :actions="{
        view: false,
        edit: $can('roles.update'),
        delete: $can('roles.delete')
      }"
      searchable
      search-label="Szukaj ról..."
      @edit="editRole"
      @delete="confirmDeleteRole"
    >
      <!-- Actions in header -->
      <template #default>
        <div class="d-flex gap-2">
          <UiButton
            v-if="$can('roles.create')"
            variant="default"
            size="sm"
            @click="createRole"
          >
            <v-icon start>mdi-plus</v-icon>
            Dodaj rolę
          </UiButton>
          <UiButton
            v-if="$can('roles.view')"
            variant="outline"
            size="sm"
            @click="exportRoles"
            :disabled="roles.length === 0"
          >
            <v-icon start>mdi-download</v-icon>
            Eksport
          </UiButton>
        </div>
      </template>

      <!-- Custom columns -->
      <template #item.name="{ item }">
        <div class="d-flex align-center">
          <UiBadge
            :variant="getRoleVariant(item.name)"
            size="sm"
            class="me-2"
          >
            {{ item.name }}
          </UiBadge>
        </div>
      </template>

      <template #item.permissions_count="{ item }">
        <v-chip
          size="small"
          color="primary"
          variant="tonal"
        >
          {{ item.permissions_count || 0 }} uprawnień
        </v-chip>
      </template>

      <template #item.users_count="{ item }">
        <span class="text-body-2">{{ item.users_count || 0 }} użytkowników</span>
      </template>

      <template #item.guard_name="{ item }">
        <v-chip
          size="small"
          color="secondary"
          variant="outlined"
        >
          {{ item.guard_name }}
        </v-chip>
      </template>

      <template #item.created_at="{ value }">
        <span class="text-body-2">{{ formatDate(value) }}</span>
      </template>

      <!-- Custom actions -->
      <template #actions="{ item }">
        <v-tooltip v-if="$can('roles.assign_permissions')" text="Zarządzaj uprawnieniami">
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              icon="mdi-key"
              size="x-small"
              variant="text"
              color="success"
              @click="managePermissions(item)"
            />
          </template>
        </v-tooltip>
      </template>
    </UiDataTable>

    <!-- Role Management Modal -->
    <v-dialog
      v-model="roleDialog.show"
      max-width="600px"
      class="dashboard-dialog"
    >
      <v-card class="dashboard-dialog-card">
        <v-card-title class="dashboard-dialog-title">
          {{ roleDialog.mode === 'create' ? 'Dodaj nową rolę' : 'Edytuj rolę' }}
        </v-card-title>

        <v-card-text>
          <v-form ref="roleForm" v-model="roleDialog.valid">
            <div class="mb-4">
              <UiInput
                v-model="roleDialog.role.name"
                label="Nazwa roli"
                placeholder="np. Moderator"
                :rules="roleNameRules"
                :error-message="roleDialog.errors.name"
              />
            </div>

            <div class="mb-4">
              <UiInput
                v-model="roleDialog.role.guard_name"
                label="Guard"
                placeholder="web"
                :rules="guardNameRules"
                :error-message="roleDialog.errors.guard_name"
              />
            </div>
          </v-form>
        </v-card-text>

        <v-card-actions class="dashboard-dialog-actions">
          <v-spacer />
          <UiButton
            variant="outline"
            @click="closeRoleDialog"
            :disabled="roleDialog.loading"
          >
            Anuluj
          </UiButton>
          <UiButton
            variant="default"
            @click="saveRole"
            :loading="roleDialog.loading"
            :disabled="!roleDialog.valid"
          >
            {{ roleDialog.mode === 'create' ? 'Utwórz' : 'Zapisz' }}
          </UiButton>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Permission Management Modal -->
    <v-dialog
      v-model="permissionDialog.show"
      max-width="800px"
      class="dashboard-dialog"
    >
      <v-card class="dashboard-dialog-card">
        <v-card-title class="dashboard-dialog-title">
          Zarządzanie uprawnieniami - {{ permissionDialog.role?.name }}
        </v-card-title>

        <v-card-text v-if="permissionDialog.role">
          <div class="text-body-2 mb-4">
            Wybierz uprawnienia dla roli <strong>{{ permissionDialog.role.name }}</strong>
          </div>

          <!-- TODO: Implement permission selection component -->
          <v-alert type="info" variant="tonal">
            <v-icon start>mdi-information</v-icon>
            Zarządzanie uprawnieniami dla roli <strong>{{ permissionDialog.role.name }}</strong> jest obecnie dostępne tylko przez API.
            Super Admin ma domyślnie wszystkie uprawnienia.
          </v-alert>
        </v-card-text>

        <v-card-actions class="dashboard-dialog-actions">
          <v-spacer />
          <UiButton variant="outline" @click="closePermissionDialog">
            Zamknij
          </UiButton>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDialog
      v-model="deleteDialog.show"
      :title="`Usuń rolę`"
      :message="`Czy na pewno chcesz usunąć rolę '${deleteDialog.role?.name}'? Ta operacja nie może zostać cofnięta.`"
      confirm-text="Usuń"
      confirm-color="error"
      dangerous
      @confirm="deleteRole"
      @cancel="closeDeleteDialog"
    />
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'
import { UiDataTable, UiButton, UiBadge, UiInput } from '@/dashboard/components/ui'
import ConfirmDialog from '@/dashboard/components/ui/ConfirmDialog.vue'

const TABLE_HEADERS = [
  {
    title: 'Nazwa roli',
    key: 'name',
    sortable: true,
    width: '200px'
  },
  {
    title: 'Uprawnienia',
    key: 'permissions_count',
    sortable: false,
    width: '140px'
  },
  {
    title: 'Użytkownicy',
    key: 'users_count',
    sortable: false,
    width: '140px'
  },
  {
    title: 'Guard',
    key: 'guard_name',
    sortable: false,
    width: '120px'
  },
  {
    title: 'Utworzono',
    key: 'created_at',
    sortable: true,
    width: '140px'
  }
]

export default {
  name: 'RolesIndex',
  components: {
    UiDataTable,
    UiButton,
    UiBadge,
    UiInput,
    ConfirmDialog
  },
  data() {
    return {
      roleDialog: {
        show: false,
        mode: 'create', // 'create' or 'edit'
        valid: false,
        loading: false,
        role: {
          id: null,
          name: '',
          guard_name: 'web'
        },
        errors: {}
      },
      permissionDialog: {
        show: false,
        role: null
      },
      deleteDialog: {
        show: false,
        role: null
      },
      roleNameRules: [
        v => !!v || 'Nazwa roli jest wymagana',
        v => (v && v.length >= 3) || 'Nazwa roli musi mieć co najmniej 3 znaki',
        v => (v && v.length <= 50) || 'Nazwa roli może mieć maksymalnie 50 znaków'
      ],
      guardNameRules: [
        v => !!v || 'Guard jest wymagany',
        v => (v && v.length >= 2) || 'Guard musi mieć co najmniej 2 znaki'
      ]
    }
  },
  computed: {
    ...mapState('roles', ['roles', 'loading']),
    ...mapActions('ui', ['showSuccess', 'showError', 'showInfo']),

    headers() {
      return TABLE_HEADERS
    }
  },
  async created() {
    await this.fetchRoles()
  },
  methods: {
    ...mapActions('roles', [
      'fetchRoles',
      'createRole',
      'updateRole',
      'deleteRole'
    ]),
    ...mapActions('ui', ['showSuccess', 'showError', 'showInfo']),

    getRoleVariant(roleName) {
      const variants = {
        'Super Admin': 'destructive',
        'Admin': 'warning',
        'Editor': 'secondary',
        'User': 'default'
      }
      return variants[roleName] || 'default'
    },

    formatDate(date) {
      if (!date) return '-'

      return new Intl.DateTimeFormat('pl-PL', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(new Date(date))
    },

    createRole() {
      this.roleDialog = {
        show: true,
        mode: 'create',
        valid: false,
        loading: false,
        role: {
          id: null,
          name: '',
          guard_name: 'web'
        },
        errors: {}
      }
    },

    editRole(role) {
      this.roleDialog = {
        show: true,
        mode: 'edit',
        valid: true,
        loading: false,
        role: { ...role },
        errors: {}
      }
    },

    async saveRole() {
      if (!this.roleDialog.valid) return

      try {
        this.roleDialog.loading = true

        if (this.roleDialog.mode === 'create') {
          await this.createRole({
            name: this.roleDialog.role.name,
            guard_name: this.roleDialog.role.guard_name
          })
          this.showSuccess(`Rola '${this.roleDialog.role.name}' została utworzona`)
        } else {
          await this.updateRole({
            id: this.roleDialog.role.id,
            roleData: {
              name: this.roleDialog.role.name,
              guard_name: this.roleDialog.role.guard_name
            }
          })
          this.showSuccess(`Rola '${this.roleDialog.role.name}' została zaktualizowana`)
        }

        this.closeRoleDialog()
      } catch (error) {
        console.error('Save role error:', error)

        // Handle validation errors
        if (error.response?.status === 422) {
          this.roleDialog.errors = error.response.data.errors || {}
        }

        this.showError(
          error.response?.data?.message ||
          `Nie udało się ${this.roleDialog.mode === 'create' ? 'utworzyć' : 'zaktualizować'} roli`
        )
      } finally {
        this.roleDialog.loading = false
      }
    },

    closeRoleDialog() {
      this.roleDialog = {
        show: false,
        mode: 'create',
        valid: false,
        loading: false,
        role: {
          id: null,
          name: '',
          guard_name: 'web'
        },
        errors: {}
      }
    },

    confirmDeleteRole(role) {
      // Check if role is system role that cannot be deleted
      const systemRoles = ['Super Admin', 'Admin', 'Editor', 'User']
      if (systemRoles.includes(role.name)) {
        this.showError('Nie można usunąć roli systemowej')
        return
      }

      this.deleteDialog = {
        show: true,
        role: role
      }
    },

    async deleteRole() {
      if (!this.deleteDialog.role) return

      try {
        await this.$store.dispatch('roles/deleteRole', this.deleteDialog.role.id)
        this.showSuccess(`Rola '${this.deleteDialog.role.name}' została usunięta`)
      } catch (error) {
        console.error('Delete role error:', error)
        this.showError(
          error.response?.data?.message || 'Nie udało się usunąć roli'
        )
      } finally {
        this.closeDeleteDialog()
      }
    },

    closeDeleteDialog() {
      this.deleteDialog = {
        show: false,
        role: null
      }
    },

    managePermissions(role) {
      this.permissionDialog = {
        show: true,
        role: role
      }
    },

    closePermissionDialog() {
      this.permissionDialog = {
        show: false,
        role: null
      }
    },

    async exportRoles() {
      try {
        // Simple CSV export of current roles data
        const csvData = this.roles.map(role => ({
          'ID': role.id,
          'Nazwa roli': role.name,
          'Guard': role.guard_name,
          'Liczba uprawnień': role.permissions_count || 0,
          'Liczba użytkowników': role.users_count || 0,
          'Data utworzenia': role.created_at
        }))

        if (csvData.length === 0) {
          this.showInfo('Brak danych do eksportu')
          return
        }

        // Generate CSV
        const headers = Object.keys(csvData[0])
        const csvContent = [
          headers.join(','),
          ...csvData.map(row =>
            headers.map(header => `"${(row[header] || '').toString().replace(/"/g, '""')}"`).join(',')
          )
        ].join('\n')

        // Download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
        const link = document.createElement('a')
        link.href = URL.createObjectURL(blob)
        link.download = `role_systemowe_${new Date().toISOString().split('T')[0]}.csv`
        link.click()

        this.showSuccess(`Wyeksportowano ${csvData.length} ról`)
      } catch (error) {
        this.showError('Nie udało się wyeksportować ról')
      }
    }
  }
}
</script>

<style scoped>
.roles-index {
  height: 100%;
}
</style>
