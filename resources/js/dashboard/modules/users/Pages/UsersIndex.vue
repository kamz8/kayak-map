<template>
  <div class="users-index">

    <UiDataTable
      title="Lista użytkowników"
      :headers="headers"
      :items="users"
      :loading="loading"
      :actions="{
        view: false,
        edit: $can('users.update'),
        delete: $can('users.delete')
      }"
      :page="safePagination.current_page"
      :current-items-per-page="safePagination.per_page"
      :total-items="safePagination.total"
      searchable
      search-label="Szukaj użytkowników..."
      @edit="editUser"
      @delete="confirmDeleteUser"
      @update:page="handlePageChange"
      @update:items-per-page="handleItemsPerPageChange"
    >
      <!-- Actions in header -->
      <template #default>
        <div class="d-flex gap-2">
          <UiButton
            v-if="$can('users.create')"
            variant="default"
            size="sm"
            @click="$router.push('/dashboard/users/create')"
          >
            <v-icon start>mdi-plus</v-icon>
            Dodaj użytkownika
          </UiButton>
          <UiButton
            v-if="$can('users.view')"
            variant="outline"
            size="sm"
            @click="exportUsers"
            :disabled="users.length === 0"
          >
            <v-icon start>mdi-download</v-icon>
            Eksport
          </UiButton>
        </div>
      </template>

      <!-- Filters -->
      <template #filters>
        <UserFilters
          :filters="filters"
          :role-options="roleOptions"
          :status-options="statusOptions"
          @update:filters="handleFiltersUpdate"
          @reset="resetFilters"
        />
      </template>

      <!-- Custom columns -->
      <template #item.name="{ item }">
        <div class="d-flex align-center">
          <UiAvatar
            :src="item.avatar?.url"
            :name="item.full_name"
            size="32"
            class="me-3"
          />
          <div>
            <div class="font-weight-medium">{{ item.full_name }}</div>
            <div class="text-caption text-medium-emphasis">{{ item.email }}</div>
          </div>
        </div>
      </template>

      <template #item.roles="{ item }">
        <div class="d-flex flex-wrap gap-1">
          <UiBadge
            v-for="role in item.roles"
            :key="role.id"
            :variant="role.color"
            size="sm"
          >
            {{ role.display_name }}
          </UiBadge>
        </div>
      </template>

      <template #item.status="{ item }">
        <UserStatusBadge :status="item.status" />
      </template>

      <template #item.last_login_at="{ value }">
        <span v-if="value" class="text-body-2">
          {{ formatDate(value) }}
        </span>
        <span v-else class="text-medium-emphasis text-caption">
          Nigdy
        </span>
      </template>

      <template #item.created_at="{ value }">
        <span class="text-body-2">{{ formatDate(value) }}</span>
      </template>

      <!-- Custom actions -->
      <template #actions="{ item }">
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
          Zarządzanie rolami - {{ roleDialog.user?.full_name }}
        </v-card-title>

        <v-card-text v-if="roleDialog.user">
          <UserRoleManager
            :user="roleDialog.user"
            :available-roles="roleOptions"
            @roles-updated="handleRolesUpdated"
          />
        </v-card-text>

        <v-card-actions class="dashboard-dialog-actions">
          <v-spacer />
          <UiButton variant="outline" @click="closeRoleDialog">
            Zamknij
          </UiButton>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDialog
      v-model="deleteDialog.show"
      :title="`Usuń użytkownika`"
      :message="`Czy na pewno chcesz usunąć użytkownika '${deleteDialog.user?.full_name}'? Ta operacja nie może zostać cofnięta.`"
      confirm-text="Usuń"
      confirm-color="error"
      dangerous
      @confirm="deleteUser"
      @cancel="closeDeleteDialog"
    />
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'
import { UiDataTable, UiButton, UiBadge, UiCard, UiAvatar } from '@/dashboard/components/ui'
import ConfirmDialog from '@/dashboard/components/ui/ConfirmDialog.vue'
import UserFilters from '../components/UserFilters.vue'
import UserRoleManager from '../components/UserRoleManager.vue'
import UserStatusBadge from '../components/UserStatusBadge.vue'

const TABLE_HEADERS = [
  {
    title: 'Użytkownik',
    key: 'name',
    sortable: true,
    width: '280px'
  },
  {
    title: 'Role',
    key: 'roles',
    sortable: false,
    width: '200px'
  },
  {
    title: 'Status',
    key: 'status',
    sortable: false,
    width: '120px'
  },
  {
    title: 'Ostatnie logowanie',
    key: 'last_login_at',
    sortable: true,
    width: '160px'
  },
  {
    title: 'Data rejestracji',
    key: 'created_at',
    sortable: true,
    width: '140px'
  }
]

export default {
  name: 'UsersIndex',
  components: {
    UiDataTable,
    UiButton,
    UiBadge,
    UiCard,
    UiAvatar,
    ConfirmDialog,
    UserFilters,
    UserRoleManager,
    UserStatusBadge
  },
  data() {
    return {
      roleDialog: {
        show: false,
        user: null
      },
      deleteDialog: {
        show: false,
        user: null
      }
    }
  },
  computed: {
    ...mapState('users', ['users', 'loading', 'pagination', 'filters']),
    ...mapGetters('users', ['roleOptions', 'statusOptions']),

    headers() {
      return TABLE_HEADERS
    },

    safePagination() {
      return this.pagination || {
        current_page: 1,
        per_page: 10,
        total: 0
      }
    }
  },
  async created() {
    await Promise.all([
      this.fetchUsers(),
      this.fetchRoles()
    ])
  },
  methods: {
    ...mapActions('users', [
      'fetchUsers',
      'fetchRoles',
      'deleteUser',
      'setFilters',
      'resetFilters',
      'syncRoles',
      'changeItemsPerPage',
      'changePage'
    ]),
    ...mapActions('ui', ['showSuccess', 'showError', 'showInfo']),

    async handleFiltersUpdate(newFilters) {
      try {
        await this.setFilters(newFilters)
      } catch (error) {
        this.showError('Nie udało się zastosować filtrów')
      }
    },

    async resetFilters() {
      try {
        await this.resetFilters()
        this.showInfo('Filtry zostały zresetowane')
      } catch (error) {
        this.showError('Nie udało się zresetować filtrów')
      }
    },

    editUser(user) {
      this.$router.push(`/dashboard/users/${user.id}/edit`)
    },

    confirmDeleteUser(user) {
      this.deleteDialog = {
        show: true,
        user: user
      }
    },

    async deleteUser() {
      if (!this.deleteDialog.user) return

      try {
        await this.$store.dispatch('users/deleteUser', this.deleteDialog.user.id)
        this.showSuccess(`Użytkownik '${this.deleteDialog.user.full_name}' został usunięty`)

        // Refresh list
        await this.fetchUsers()
      } catch (error) {
        console.error('Delete user error:', error)

        let message = 'Nie udało się usunąć użytkownika'

        if (error.response?.status === 500) {
          message = 'Błąd serwera - spróbuj ponownie później'
        } else if (error.response?.data?.message) {
          message = error.response.data.message
        }

        this.showError(message)
      } finally {
        // Always close dialog regardless of success or failure
        this.closeDeleteDialog()
      }
    },

    closeDeleteDialog() {
      this.deleteDialog = {
        show: false,
        user: null
      }
    },

    manageUserRoles(user) {
      this.roleDialog = {
        show: true,
        user: user
      }
    },

    async handleRolesUpdated(updatedUser) {
      this.showSuccess('Role zostały zaktualizowane')
      this.closeRoleDialog()

      // Refresh users list to show updated roles
      await this.fetchUsers()
    },

    closeRoleDialog() {
      this.roleDialog = {
        show: false,
        user: null
      }
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

    async exportUsers() {
      try {
        // Simple CSV export of current users data
        const csvData = this.users.map(user => ({
          'ID': user.id,
          'Imię': user.first_name || '',
          'Nazwisko': user.last_name || '',
          'Email': user.email,
          'Role': user.roles ? user.roles.map(r => r.name).join(', ') : '',
          'Status': user.status,
          'Ostatnie logowanie': user.last_login_at || 'Nigdy',
          'Data rejestracji': user.created_at
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
        link.download = `uzytkownicy_${new Date().toISOString().split('T')[0]}.csv`
        link.click()

        this.showSuccess(`Wyeksportowano ${csvData.length} użytkowników`)
      } catch (error) {
        this.showError('Nie udało się wyeksportować użytkowników')
      }
    },

    async handlePageChange(page) {
      if (!page || typeof page !== 'number') return

      try {
        await this.changePage(page)
      } catch (error) {
        this.showError('Nie udało się zmienić strony')
      }
    },

    async handleItemsPerPageChange(itemsPerPage) {
      if (!itemsPerPage || typeof itemsPerPage !== 'number') return

      try {
        await this.changeItemsPerPage(itemsPerPage)
      } catch (error) {
        this.showError('Nie udało się zmienić ilości elementów na stronie')
      }
    }
  }
}
</script>

<style scoped>
.users-index {
  height: 100%;
}

:deep(.v-data-table) {
  background: transparent;
}

:deep(.v-data-table__wrapper) {
  border-radius: 8px;
}

</style>
