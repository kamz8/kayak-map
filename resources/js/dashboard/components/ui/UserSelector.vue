<template>
  <div class="user-selector">
    <div class="mb-3">
      <h4 class="text-h6 mb-2">Przypisz użytkowników</h4>
      <p class="text-body-2 text-medium-emphasis">
        Wybierz użytkowników, którzy otrzymają tę rolę
      </p>
    </div>

    <!-- Search -->
    <UiInput
      v-model="searchQuery"
      label="Szukaj użytkowników"
      placeholder="Wpisz nazwę, email..."
      prepend-inner-icon="mdi-magnify"
      class="mb-4"
      clearable
    />

    <!-- User list -->
    <div class="user-list">
      <v-progress-linear
        v-if="loading"
        indeterminate
        class="mb-3"
      />

      <div v-if="!loading && filteredUsers.length === 0" class="text-center text-medium-emphasis py-4">
        <v-icon size="48" class="mb-2">mdi-account-search</v-icon>
        <p>{{ searchQuery ? 'Nie znaleziono użytkowników' : 'Brak dostępnych użytkowników' }}</p>
      </div>

      <div v-else class="user-grid">
        <v-card
          v-for="user in filteredUsers"
          :key="user.id"
          :class="['user-card', { 'selected': isSelected(user.id) }]"
          @click="toggleUser(user)"
          hover
        >
          <v-card-text class="d-flex align-center pa-3">
            <v-checkbox
              :model-value="isSelected(user.id)"
              @click.stop
              @change="toggleUser(user)"
              hide-details
              class="me-3"
            />

            <UiAvatar
              :src="user.avatar"
              :name="user.full_name || user.name"
              size="sm"
              class="me-3"
            />

            <div class="flex-grow-1">
              <div class="text-subtitle-2">{{ user.full_name || user.name }}</div>
              <div class="text-caption text-medium-emphasis">{{ user.email }}</div>
              <div v-if="user.roles && user.roles.length > 0" class="mt-1">
                <UiBadge
                  v-for="role in user.roles.slice(0, 2)"
                  :key="role.id"
                  size="xs"
                  variant="outline"
                  class="me-1"
                >
                  {{ role.name }}
                </UiBadge>
                <span v-if="user.roles.length > 2" class="text-caption text-medium-emphasis">
                  +{{ user.roles.length - 2 }} więcej
                </span>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </div>
    </div>

    <!-- Selected summary -->
    <div v-if="selectedUsers.length > 0" class="selected-summary mt-4 pa-3 bg-primary-container rounded">
      <div class="d-flex align-center justify-space-between mb-2">
        <span class="text-subtitle-2">Wybrani użytkownicy ({{ selectedUsers.length }})</span>
        <UiButton
          variant="text"
          size="sm"
          @click="clearSelection"
        >
          Wyczyść wszystko
        </UiButton>
      </div>
      <div class="d-flex flex-wrap gap-1">
        <v-chip
          v-for="user in selectedUsers"
          :key="user.id"
          size="small"
          closable
          @click:close="removeUser(user.id)"
        >
          {{ user.full_name || user.name }}
        </v-chip>
      </div>
    </div>

    <!-- Actions -->
    <div class="d-flex justify-end gap-2 mt-4">
      <UiButton
        variant="outline"
        @click="$emit('cancel')"
        :disabled="saving"
      >
        Anuluj
      </UiButton>
      <UiButton
        @click="saveSelection"
        :loading="saving"
        :disabled="selectedUsers.length === 0"
      >
        Przypisz użytkowników ({{ selectedUsers.length }})
      </UiButton>
    </div>
  </div>
</template>

<script>
import { UiInput, UiAvatar, UiBadge, UiButton } from '@/dashboard/components/ui'

export default {
  name: 'UserSelector',
  components: {
    UiInput,
    UiAvatar,
    UiBadge,
    UiButton
  },
  props: {
    users: {
      type: Array,
      default: () => []
    },
    initialSelection: {
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
    role: {
      type: Object,
      default: null
    }
  },
  emits: ['save', 'cancel'],
  data() {
    return {
      searchQuery: '',
      selectedUserIds: [...this.initialSelection]
    }
  },
  computed: {
    filteredUsers() {
      if (!this.searchQuery) {
        return this.users
      }

      const query = this.searchQuery.toLowerCase()
      return this.users.filter(user => {
        const name = (user.full_name || user.name || '').toLowerCase()
        const email = (user.email || '').toLowerCase()
        return name.includes(query) || email.includes(query)
      })
    },

    selectedUsers() {
      return this.users.filter(user => this.selectedUserIds.includes(user.id))
    }
  },
  watch: {
    initialSelection: {
      handler(newSelection) {
        this.selectedUserIds = [...newSelection]
      },
      immediate: true
    }
  },
  methods: {
    isSelected(userId) {
      return this.selectedUserIds.includes(userId)
    },

    toggleUser(user) {
      const index = this.selectedUserIds.indexOf(user.id)
      if (index === -1) {
        this.selectedUserIds.push(user.id)
      } else {
        this.selectedUserIds.splice(index, 1)
      }
    },

    removeUser(userId) {
      const index = this.selectedUserIds.indexOf(userId)
      if (index !== -1) {
        this.selectedUserIds.splice(index, 1)
      }
    },

    clearSelection() {
      this.selectedUserIds = []
    },

    saveSelection() {
      this.$emit('save', this.selectedUserIds)
    }
  }
}
</script>

<style scoped>
.user-selector {
  max-height: 600px;
  overflow: auto;
}

.user-grid {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid rgba(var(--v-border-color), 0.12);
  border-radius: 8px;
}

.user-card {
  border-radius: 0;
  border-bottom: 1px solid rgba(var(--v-border-color), 0.12);
  cursor: pointer;
  transition: all 0.2s ease;
}

.user-card:last-child {
  border-bottom: none;
}

.user-card:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.user-card.selected {
  background-color: rgba(var(--v-theme-primary), 0.1);
  border-color: rgb(var(--v-theme-primary));
}

.selected-summary {
  border: 1px solid rgba(var(--v-theme-primary), 0.2);
}

:deep(.v-checkbox) {
  flex: 0 0 auto;
}
</style>