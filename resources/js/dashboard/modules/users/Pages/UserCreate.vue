<template>
  <div class="user-create">
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
      <div>
        <h1 class="text-h5 font-weight-bold">Dodaj nowego użytkownika</h1>
        <p class="text-body-2 text-medium-emphasis">
          Utwórz konto dla nowego użytkownika systemu
        </p>
      </div>
    </div>

    <!-- Form Card -->
    <UiCard>
      <v-card-text>
        <UserForm
          :available-roles="roleOptions"
          :loading="loading"
          @submit="handleSubmit"
        />
      </v-card-text>
    </UiCard>

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
import { mapGetters, mapActions } from 'vuex'
import { UiButton, UiCard } from '@/dashboard/components/ui'
import UserForm from '../components/UserForm.vue'

export default {
  name: 'UserCreate',
  components: {
    UiButton,
    UiCard,
    UserForm
  },
  data() {
    return {
      loading: false,
      showSuccess: false,
      successMessage: ''
    }
  },
  computed: {
    ...mapGetters('users', ['roleOptions']),
    ...mapGetters('auth', ['user as currentUser'])
  },
  methods: {
    ...mapActions('users', ['createUser']),
    ...mapActions('ui', ['showSuccess as showSuccessMessage', 'showError']),

    async handleSubmit(formData) {
      this.loading = true

      try {
        const createdUser = await this.createUser(formData)

        this.successMessage = `Użytkownik '${createdUser.full_name}' został utworzony pomyślnie`
        this.showSuccess = true

        // Redirect to users list after a short delay
        setTimeout(() => {
          this.$router.push('/dashboard/users')
        }, 2000)

      } catch (error) {
        console.error('Create user error:', error)

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
          this.showError('Nie udało się utworzyć użytkownika. Spróbuj ponownie.')
        }
      } finally {
        this.loading = false
      }
    }
  },
  // Set page title
  created() {
    document.title = 'Dodaj użytkownika - Kayak Map Dashboard'
  }
}
</script>

<style scoped>
.user-create {
  max-width: 800px;
  margin: 0 auto;
  padding: 24px;
}

@media (max-width: 768px) {
  .user-create {
    padding: 16px;
  }
}
</style>