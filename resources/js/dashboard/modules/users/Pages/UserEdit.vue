<template>
  <div class="user-edit">
    <!-- Loading State -->
    <div v-if="loadingUser" class="d-flex justify-center py-8">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <!-- Content -->
    <div v-else-if="user">
      <!-- Page Header -->
      <div class="d-flex align-center mb-6">
        <UiButton
          variant="ghost"
          size="sm"
          @click="$router.back()"
          class="me-3"
        >
          <v-icon start>mdi-arrow-left</v-icon>
          Wróć
        </UiButton>
        <div class="d-flex align-center">
          <UiAvatar
            :src="user.avatar?.url"
            :name="user.full_name"
            size="48"
            class="me-4"
          />
          <div>
            <h1 class="text-h5 font-weight-bold">{{ user.full_name }}</h1>
            <p class="text-body-2 text-medium-emphasis">
              Edytuj dane użytkownika
            </p>
          </div>
        </div>
      </div>

      <!-- User Status Info -->
      <v-alert
        v-if="user.status !== 'active'"
        :type="getStatusAlertType(user.status)"
        variant="tonal"
        class="mb-4"
        density="compact"
      >
        <v-icon start>{{ getStatusIcon(user.status) }}</v-icon>
        {{ getStatusMessage(user.status) }}
      </v-alert>

      <!-- Warning for Super Admin -->
      <v-alert
        v-if="user.is_super_admin && !currentUser.is_super_admin"
        type="warning"
        variant="tonal"
        class="mb-4"
        density="compact"
      >
        <v-icon start>mdi-shield-alert</v-icon>
        Ten użytkownik ma uprawnienia Super Admin. Tylko inni Super Admin mogą edytować to konto.
      </v-alert>

      <!-- Form Card -->
      <UiCard>
        <v-card-text>
          <UserForm
            :user="user"
            :available-roles="roleOptions"
            :loading="loading"
            @submit="handleSubmit"
          />
        </v-card-text>
      </UiCard>

      <!-- Additional Actions -->
      <UiCard class="mt-6">
        <v-card-title>
          <v-icon start>mdi-cog</v-icon>
          Zaawansowane akcje
        </v-card-title>
        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <h4 class="text-subtitle-1 mb-2">Zarządzanie kontem</h4>
              <p class="text-body-2 text-medium-emphasis mb-3">
                Wykonaj specjalne akcje na koncie użytkownika
              </p>

              <div class="d-flex flex-column gap-2">
                <UiButton
                  variant="outline"
                  size="sm"
                  :disabled="user.email_verified_at !== null"
                  @click="resendVerification"
                >
                  <v-icon start>mdi-email-send</v-icon>
                  {{ user.email_verified_at ? 'Email zweryfikowany' : 'Wyślij link weryfikacji' }}
                </UiButton>

                <UiButton
                  variant="outline"
                  size="sm"
                  @click="resetPassword"
                >
                  <v-icon start>mdi-key-variant</v-icon>
                  Wyślij link resetowania hasła
                </UiButton>
              </div>
            </v-col>

            <v-col cols="12" md="6">
              <h4 class="text-subtitle-1 mb-2">Informacje systemowe</h4>
              <div class="text-body-2 text-medium-emphasis">
                <div class="mb-2">
                  <strong>ID:</strong> {{ user.id }}
                </div>
                <div class="mb-2">
                  <strong>Utworzono:</strong> {{ formatDate(user.created_at) }}
                </div>
                <div class="mb-2">
                  <strong>Ostatnia modyfikacja:</strong> {{ formatDate(user.updated_at) }}
                </div>
                <div class="mb-2">
                  <strong>Ostatnie logowanie:</strong>
                  {{ user.last_login_at ? formatDate(user.last_login_at) : 'Nigdy' }}
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </UiCard>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="d-flex flex-column align-center py-8">
      <v-icon color="error" size="64" class="mb-4">mdi-alert-circle</v-icon>
      <h2 class="text-h6 mb-2">Nie znaleziono użytkownika</h2>
      <p class="text-body-2 text-medium-emphasis mb-4">{{ error }}</p>
      <UiButton @click="$router.push('/dashboard/users')">
        Powrót do listy użytkowników
      </UiButton>
    </div>

    <!-- Success Message -->
    <v-snackbar
      v-model="showSuccess"
      color="success"
      timeout="3000"
      location="top right"
    >
      <v-icon start>mdi-check-circle</v-icon>
      {{ successMessage }}
    </v-snackbar>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'
import { UiButton, UiCard, UiAvatar } from '@/dashboard/components/ui'
import UserForm from '../components/UserForm.vue'

export default {
  name: 'UserEdit',
  components: {
    UiButton,
    UiCard,
    UiAvatar,
    UserForm
  },
  data() {
    return {
      loadingUser: false,
      loading: false,
      showSuccess: false,
      successMessage: ''
    }
  },
  computed: {
    ...mapState('users', ['user', 'error']),
    ...mapGetters('users', ['roleOptions']),
    ...mapGetters('auth', ['user as currentUser']),

    userId() {
      return this.$route.params.id
    }
  },
  async created() {
    await this.loadUser()
    this.updatePageTitle()
  },
  watch: {
    '$route.params.id': {
      handler() {
        this.loadUser()
      }
    },
    user: {
      handler() {
        this.updatePageTitle()
      },
      deep: true
    }
  },
  methods: {
    ...mapActions('users', ['fetchUser', 'updateUser']),
    ...mapActions('ui', ['showSuccess as showSuccessMessage', 'showError', 'showInfo']),

    async loadUser() {
      if (!this.userId) return

      this.loadingUser = true
      try {
        await this.fetchUser(this.userId)
      } catch (error) {
        console.error('Load user error:', error)
      } finally {
        this.loadingUser = false
      }
    },

    async handleSubmit(formData) {
      if (!this.user) return

      this.loading = true

      try {
        const updatedUser = await this.updateUser({
          userId: this.user.id,
          userData: formData
        })

        this.successMessage = `Dane użytkownika '${updatedUser.full_name}' zostały zaktualizowane`
        this.showSuccess = true

        // Update page title if name changed
        this.updatePageTitle()

      } catch (error) {
        console.error('Update user error:', error)

        // Handle validation errors
        if (error.response?.status === 422) {
          const errors = error.response.data.errors
          if (errors) {
            // Show first validation error
            const firstError = Object.values(errors)[0][0]
            this.showError(firstError)
          } else {
            this.showError('Błędy walidacji - sprawdź wprowadzone dane')
          }
        }
        // Handle specific business logic errors
        else if (error.response?.data?.message) {
          this.showError(error.response.data.message)
        }
        // Handle generic errors
        else {
          this.showError('Nie udało się zaktualizować użytkownika. Spróbuj ponownie.')
        }
      } finally {
        this.loading = false
      }
    },

    async resendVerification() {
      try {
        // TODO: Implement resend verification API
        this.showInfo('Funkcja wysyłania linku weryfikacji będzie dostępna wkrótce')
      } catch (error) {
        this.showError('Nie udało się wysłać linku weryfikacji')
      }
    },

    async resetPassword() {
      try {
        // TODO: Implement reset password API
        this.showInfo('Funkcja resetowania hasła będzie dostępna wkrótce')
      } catch (error) {
        this.showError('Nie udało się wysłać linku resetowania hasła')
      }
    },

    updatePageTitle() {
      const userName = this.user?.full_name || 'Użytkownik'
      document.title = `${userName} - Kayak Map Dashboard`
    },

    getStatusAlertType(status) {
      switch (status) {
        case 'inactive': return 'warning'
        case 'unverified': return 'info'
        case 'deleted': return 'error'
        default: return 'info'
      }
    },

    getStatusIcon(status) {
      switch (status) {
        case 'inactive': return 'mdi-pause-circle'
        case 'unverified': return 'mdi-email-alert'
        case 'deleted': return 'mdi-delete'
        default: return 'mdi-information'
      }
    },

    getStatusMessage(status) {
      switch (status) {
        case 'inactive':
          return 'To konto jest nieaktywne i użytkownik nie może się logować.'
        case 'unverified':
          return 'Użytkownik nie zweryfikował jeszcze swojego adresu e-mail.'
        case 'deleted':
          return 'To konto zostało usunięte (soft delete).'
        default:
          return 'Status użytkownika wymaga uwagi administratora.'
      }
    },

    formatDate(date) {
      if (!date) return 'Brak danych'

      return new Intl.DateTimeFormat('pl-PL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(new Date(date))
    }
  }
}
</script>

<style scoped>
.user-edit {
  max-width: 800px;
  margin: 0 auto;
  padding: 24px;
}

@media (max-width: 768px) {
  .user-edit {
    padding: 16px;
  }
}
</style>
