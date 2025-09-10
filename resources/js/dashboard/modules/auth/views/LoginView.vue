<template>
  <v-app>
    <v-main>
      <v-container fluid class="fill-height">
        <v-row justify="center" align="center" class="fill-height">
          <v-col cols="12" sm="8" md="6" lg="4" xl="3">
            <v-card class="elevation-8" rounded="lg">
              <!-- Header -->
              <v-card-title class="text-center pa-6">
                <div class="w-100">
                  <v-avatar size="64" class="mb-4">
                    <v-icon size="48" color="primary">mdi-kayaking</v-icon>
                  </v-avatar>
                  <h1 class="text-h5 font-weight-bold">
                    Kayak Map Dashboard
                  </h1>
                  <p class="text-body-2 text-medium-emphasis mt-2">
                    Zaloguj się do panelu administracyjnego
                  </p>
                </div>
              </v-card-title>

              <!-- Login Form -->
              <v-card-text class="pa-6 pt-0">
                <v-form ref="loginForm" @submit.prevent="handleLogin">
                  <FormField
                    v-model="form.email"
                    type="email"
                    label="Adres email"
                    placeholder="admin@kayak-map.test"
                    prepend-inner-icon="mdi-email"
                    required
                    :rules="emailRules"
                    class="mb-4"
                  />

                  <FormField
                    v-model="form.password"
                    type="password"
                    label="Hasło"
                    placeholder="Wprowadź hasło"
                    prepend-inner-icon="mdi-lock"
                    required
                    :rules="passwordRules"
                    class="mb-4"
                  />

                  <!-- Error message -->
                  <v-alert
                    v-if="error"
                    type="error"
                    variant="tonal"
                    class="mb-4"
                  >
                    {{ error }}
                  </v-alert>

                  <!-- Login button -->
                  <v-btn
                    type="submit"
                    color="primary"
                    variant="flat"
                    size="large"
                    block
                    :loading="loading"
                    class="mb-4"
                  >
                    Zaloguj się do Dashboardu
                  </v-btn>

                  <v-divider class="mb-4">
                    <span class="text-medium-emphasis px-4">lub</span>
                  </v-divider>

                  <v-btn
                    variant="outlined"
                    size="large"
                    block
                    prepend-icon="mdi-arrow-left"
                    @click="goToMainApp"
                  >
                    Powrót do głównej aplikacji
                  </v-btn>
                </v-form>
              </v-card-text>

              <!-- Footer -->
              <v-card-actions class="pa-6 pt-0">
                <v-spacer />
                <div class="text-caption text-medium-emphasis">
                  © 2025 Kayak Map. Dashboard Module.
                </div>
                <v-spacer />
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import FormField from '@/dashboard/components/ui/FormField.vue'

export default {
  name: 'DashboardLoginView',
  components: {
    FormField
  },
  data() {
    return {
      form: {
        email: 'admin@kayak-map.test',
        password: 'password'
      },
      emailRules: [
        v => !!v || 'Email jest wymagany',
        v => /.+@.+\..+/.test(v) || 'Wprowadź poprawny adres email'
      ],
      passwordRules: [
        v => !!v || 'Hasło jest wymagane'
      ]
    }
  },
  computed: {
    ...mapState('auth', ['loading', 'error'])
  },
  methods: {
    ...mapActions('auth', ['login']),
    ...mapActions('ui', ['showSuccess', 'showError']),

    async handleLogin() {
      const { valid } = await this.$refs.loginForm.validate()
      if (!valid) return

      try {
        await this.login(this.form)
        this.showSuccess('Zalogowano pomyślnie!')
        this.$router.push('/dashboard')
      } catch (error) {
        // Error is already handled in the store
      }
    },

    goToMainApp() {
      window.location.href = '/'
    }
  }
}
</script>
