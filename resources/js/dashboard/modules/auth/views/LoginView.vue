<template>
  <v-app class="login-wrapper">
    <!-- Background grid -->
    <div class="background-grid">
      <div 
        v-for="i in backgroundImages" 
        :key="i" 
        class="background-image"
        :style="{ backgroundImage: `url(/storage/assets/${getBackgroundImage(i)})` }"
      >
        <div class="image-overlay"></div>
      </div>
    </div>

    <v-main class="d-flex align-center justify-center">
      <v-container class="login-container">
        <v-row justify="center" align="center" class="min-height-100">
          <v-col cols="12" sm="8" md="5" lg="4" xl="3">
            <!-- Header -->
            <header class="text-center mb-8">
              <div class="mb-4">
                <v-avatar size="72" color="primary" class="mb-4">
                  <v-icon size="48" color="white">mdi-kayaking</v-icon>
                </v-avatar>
              </div>
              <h2 class="text-h4 font-weight-bold mb-2">
                Dashboard
              </h2>
              <p class="text-subtitle-1 text-medium-emphasis">
                Panel administracyjny Kayak Map
              </p>
            </header>

            <!-- Login Form -->
            <v-card class="elevation-8 pa-8" rounded="xl">
              <v-form ref="loginForm" @submit.prevent="handleLogin">
                <v-text-field
                  v-model="form.email"
                  label="Adres e-mail"
                  type="email"
                  variant="outlined"
                  :rules="emailRules"
                  class="mb-4"
                  color="primary"
                  bg-color="grey-lighten-5"
                  prepend-inner-icon="mdi-email"
                />

                <v-text-field
                  v-model="form.password"
                  label="Hasło"
                  :type="showPassword ? 'text' : 'password'"
                  variant="outlined"
                  :rules="passwordRules"
                  :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="showPassword = !showPassword"
                  color="primary"
                  bg-color="grey-lighten-5"
                  prepend-inner-icon="mdi-lock"
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

                <v-btn
                  block
                  color="#2B381F"
                  size="large"
                  type="submit"
                  :loading="loading"
                  class="mt-6 mb-4"
                  flat
                >
                  Zaloguj do Dashboard
                </v-btn>
              </v-form>

              <div class="d-flex align-center my-6">
                <v-divider></v-divider>
                <span class="mx-4 text-medium-emphasis text-caption">lub</span>
                <v-divider></v-divider>
              </div>

              <v-btn
                block
                variant="outlined"
                color="black"
                class="mb-4"
                @click="goToMainApp"
                prepend-icon="mdi-arrow-left"
                flat
              >
                Powrót do głównej aplikacji
              </v-btn>

              <!-- Footer -->
              <div class="text-center text-caption text-medium-emphasis mt-6">
                © 2025 Kayak Map. Panel administracyjny.
              </div>
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
      showPassword: false,
      backgroundImages: [1, 2, 3],
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

    getBackgroundImage(index) {
      const images = [
        'river-hero.jpg',
        'pexels-sebastian-165505.jpg', 
        'pexels-bri-schneiter-28802-346529.jpg'
      ]
      return images[index - 1]
    },

    goToMainApp() {
      window.location.href = '/'
    }
  }
}
</script>

<style scoped>
.login-wrapper {
  position: relative;
  min-height: 100vh;
  background-color: rgb(246, 246, 246);
  font-family: "Inter", "Poppins", sans-serif;
}

.background-grid {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0;
  z-index: 0;
}

.background-image {
  position: relative;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.image-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(1px);
}

.login-container {
  position: relative;
  z-index: 1;
}

.min-height-100 {
  min-height: 100vh;
}

/* Card styling with backdrop */
:deep(.v-card) {
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95) !important;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

:deep(.v-btn) {
  text-transform: none;
  letter-spacing: normal;
}

:deep(.v-field__outline__start),
:deep(.v-field__outline__end) {
  border-color: rgba(0, 0, 0, 0.15) !important;
}

/* Header styling */
header h2 {
  color: white;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
}

header p {
  color: rgba(255, 255, 255, 0.9);
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
}

.v-avatar {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

@media (max-width: 600px) {
  .background-grid {
    display: none;
  }
  
  .login-wrapper {
    background: linear-gradient(135deg, #1976D2 0%, #42A5F5 100%);
  }
}
</style>
