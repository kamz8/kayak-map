<template>
  <div class="change-password-page">

    <v-row>
      <!-- Main Form -->
      <v-col cols="12" lg="8">
        <UiCard title="Zmiana hasła">
          <template #actions>
            <UiButton
              variant="outline"
              size="sm"
              :disabled="!isFormValid"
              :loading="loading"
              @click="handleChangePassword"
            >
              <v-icon start size="small">mdi-content-save</v-icon>
              Zmień hasło
            </UiButton>
          </template>

          <v-form ref="passwordForm" @submit.prevent="handleChangePassword">
            <v-row>
              <!-- Current Password -->
              <v-col cols="12">
                <v-text-field
                  v-model="passwordForm.current_password"
                  label="Obecne hasło"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  :rules="[rules.required]"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                >
                  <template #prepend-inner>
                    <v-icon size="small">mdi-lock</v-icon>
                  </template>
                  <template #append-inner>
                    <v-icon
                      size="small"
                      @click="showCurrentPassword = !showCurrentPassword"
                      style="cursor: pointer"
                    >
                      {{ showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye' }}
                    </v-icon>
                  </template>
                </v-text-field>
              </v-col>

              <!-- New Password -->
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="passwordForm.new_password"
                  label="Nowe hasło"
                  :type="showNewPassword ? 'text' : 'password'"
                  :rules="[rules.required, rules.passwordStrength]"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                >
                  <template #prepend-inner>
                    <v-icon size="small">mdi-lock-plus</v-icon>
                  </template>
                  <template #append-inner>
                    <v-icon
                      size="small"
                      @click="showNewPassword = !showNewPassword"
                      style="cursor: pointer"
                    >
                      {{ showNewPassword ? 'mdi-eye-off' : 'mdi-eye' }}
                    </v-icon>
                  </template>
                </v-text-field>
              </v-col>

              <!-- Confirm Password -->
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="passwordForm.new_password_confirmation"
                  label="Potwierdź nowe hasło"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  :rules="[rules.required, rules.passwordMatch]"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                >
                  <template #prepend-inner>
                    <v-icon size="small">mdi-lock-check</v-icon>
                  </template>
                  <template #append-inner>
                    <v-icon
                      size="small"
                      @click="showConfirmPassword = !showConfirmPassword"
                      style="cursor: pointer"
                    >
                      {{ showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye' }}
                    </v-icon>
                  </template>
                </v-text-field>
              </v-col>

              <!-- Password Strength Indicator -->
              <v-col cols="12" v-if="passwordForm.new_password">
                <div class="password-strength mb-4">
                  <div class="d-flex align-center mb-2">
                    <span class="text-body-2 me-2">Siła hasła:</span>
                    <UiBadge :variant="passwordStrengthVariant">
                      {{ passwordStrengthText }}
                    </UiBadge>
                  </div>
                  
                  <v-progress-linear
                    :model-value="passwordStrengthScore * 25"
                    :color="passwordStrengthColor"
                    height="6"
                    rounded
                  />
                  
                  <div class="password-requirements mt-3">
                    <div class="text-caption mb-1">Wymagania hasła:</div>
                    <div class="requirements-list">
                      <div 
                        v-for="req in passwordRequirements" 
                        :key="req.text"
                        class="requirement-item d-flex align-center mb-1"
                      >
                        <v-icon 
                          :color="req.met ? 'success' : 'error'" 
                          size="14" 
                          class="me-2"
                        >
                          {{ req.met ? 'mdi-check' : 'mdi-close' }}
                        </v-icon>
                        <span 
                          class="text-caption"
                          :class="{ 'text-success': req.met, 'text-error': !req.met }"
                        >
                          {{ req.text }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </v-col>
            </v-row>
          </v-form>
        </UiCard>
      </v-col>

      <!-- Security Tips Sidebar -->
      <v-col cols="12" lg="4">
        <!-- Security Tips Card -->
        <UiCard title="Wskazówki bezpieczeństwa" class="mb-4">
          <div class="security-tips">
            <div class="tip-item d-flex mb-3">
              <v-icon color="primary" size="20" class="me-3 mt-1">mdi-shield-check</v-icon>
              <div>
                <div class="text-body-2 font-weight-medium mb-1">Silne hasło</div>
                <div class="text-caption text-medium-emphasis">
                  Używaj minimum 8 znaków z kombinacją liter, cyfr i symboli
                </div>
              </div>
            </div>
            
            <div class="tip-item d-flex mb-3">
              <v-icon color="primary" size="20" class="me-3 mt-1">mdi-eye-off</v-icon>
              <div>
                <div class="text-body-2 font-weight-medium mb-1">Unikalne hasło</div>
                <div class="text-caption text-medium-emphasis">
                  Nie używaj tego samego hasła w innych serwisach
                </div>
              </div>
            </div>
            
            <div class="tip-item d-flex">
              <v-icon color="primary" size="20" class="me-3 mt-1">mdi-update</v-icon>
              <div>
                <div class="text-body-2 font-weight-medium mb-1">Regularne zmiany</div>
                <div class="text-caption text-medium-emphasis">
                  Zmieniaj hasło co 3-6 miesięcy lub gdy podejrzewasz naruszenie
                </div>
              </div>
            </div>
          </div>
        </UiCard>

        <!-- Recent Activity Card -->
        <UiCard title="Ostatnia aktywność">
          <div class="recent-activity">
            <div class="activity-item d-flex justify-space-between align-center mb-3">
              <div class="d-flex align-center">
                <v-icon size="16" class="me-2 text-medium-emphasis">mdi-login</v-icon>
                <span class="text-body-2">Ostatnie logowanie</span>
              </div>
              <span class="text-caption text-medium-emphasis">{{ formatDate(user.last_login_at) }}</span>
            </div>
            
            <div class="activity-item d-flex justify-space-between align-center mb-3">
              <div class="d-flex align-center">
                <v-icon size="16" class="me-2 text-medium-emphasis">mdi-account-edit</v-icon>
                <span class="text-body-2">Aktualizacja profilu</span>
              </div>
              <span class="text-caption text-medium-emphasis">{{ formatDate(user.updated_at) }}</span>
            </div>
            
            <div class="activity-item d-flex justify-space-between align-center">
              <div class="d-flex align-center">
                <v-icon size="16" class="me-2 text-medium-emphasis">mdi-lock-reset</v-icon>
                <span class="text-body-2">Zmiana hasła</span>
              </div>
              <span class="text-caption text-medium-emphasis">-</span>
            </div>
          </div>
        </UiCard>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { UiCard, UiButton, UiBadge } from '@/dashboard/components/ui'

export default {
  name: 'ChangePasswordView',
  components: {
    UiCard,
    UiButton,
    UiBadge
  },
  data() {
    return {
      loading: false,
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      
      passwordForm: {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      },
      
      rules: {
        required: value => !!value || 'To pole jest wymagane',
        passwordStrength: value => {
          if (!value) return true
          const minLength = value.length >= 8
          const hasLetter = /[a-zA-Z]/.test(value)
          const hasNumber = /\d/.test(value)
          const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(value)
          
          if (!minLength) return 'Hasło musi mieć minimum 8 znaków'
          if (!hasLetter) return 'Hasło musi zawierać litery'
          if (!hasNumber) return 'Hasło musi zawierać cyfry'
          if (!hasSymbol) return 'Hasło musi zawierać symbole'
          
          return true
        },
        passwordMatch: value => {
          return value === this.passwordForm.new_password || 'Hasła nie są identyczne'
        }
      }
    }
  },
  computed: {
    ...mapGetters('auth', ['user']),
    
    isFormValid() {
      return this.passwordForm.current_password &&
             this.passwordForm.new_password &&
             this.passwordForm.new_password_confirmation &&
             this.passwordForm.new_password === this.passwordForm.new_password_confirmation &&
             this.passwordStrengthScore >= 3
    },
    
    passwordStrengthScore() {
      const password = this.passwordForm.new_password
      if (!password) return 0
      
      let score = 0
      if (password.length >= 8) score++
      if (/[a-z]/.test(password)) score++
      if (/[A-Z]/.test(password)) score++
      if (/\d/.test(password)) score++
      if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score++
      
      return Math.min(score, 4)
    },
    
    passwordStrengthText() {
      const labels = ['Bardzo słabe', 'Słabe', 'Średnie', 'Silne', 'Bardzo silne']
      return labels[this.passwordStrengthScore]
    },
    
    passwordStrengthVariant() {
      const variants = ['destructive', 'warning', 'warning', 'success', 'success']
      return variants[this.passwordStrengthScore]
    },
    
    passwordStrengthColor() {
      const colors = ['error', 'warning', 'orange', 'success', 'success']
      return colors[this.passwordStrengthScore]
    },
    
    passwordRequirements() {
      const password = this.passwordForm.new_password
      return [
        { text: 'Minimum 8 znaków', met: password.length >= 8 },
        { text: 'Zawiera małe litery', met: /[a-z]/.test(password) },
        { text: 'Zawiera duże litery', met: /[A-Z]/.test(password) },
        { text: 'Zawiera cyfry', met: /\d/.test(password) },
        { text: 'Zawiera symbole (!@#$%^&*)', met: /[!@#$%^&*(),.?":{}|<>]/.test(password) }
      ]
    }
  },
  methods: {
    ...mapActions('auth', ['changePassword']),
    ...mapActions('ui', ['showSuccess', 'showError']),
    
    async handleChangePassword() {
      if (!this.$refs.passwordForm.validate()) {
        return
      }
      
      this.loading = true
      try {
        await this.changePassword(this.passwordForm)
        this.showSuccess('Hasło zostało pomyślnie zmienione')
        
        // Reset form
        this.passwordForm = {
          current_password: '',
          new_password: '',
          new_password_confirmation: ''
        }
        this.$refs.passwordForm.resetValidation()
        
        // Redirect to profile after successful change
        setTimeout(() => {
          this.$router.push('/dashboard/settings/profile')
        }, 2000)
        
      } catch (error) {
        this.showError('Błąd podczas zmiany hasła. Sprawdź obecne hasło.')
      } finally {
        this.loading = false
      }
    },
    
    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('pl-PL', {
        year: 'numeric',
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>

<style scoped>
.change-password-page {
  max-width: 1200px;
  margin: 0 auto;
}

.password-strength {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  background: #f8f9fa;
}

.requirements-list {
  margin-left: 4px;
}

.requirement-item {
  transition: all 0.2s ease;
}

.security-tips .tip-item {
  padding-bottom: 12px;
  border-bottom: 1px solid #f0f0f0;
}

.security-tips .tip-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.recent-activity .activity-item {
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.recent-activity .activity-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

/* Dark theme adjustments */
.v-theme--dark .password-strength {
  background: #1e1e1e;
  border-color: #333333;
}

.v-theme--dark .security-tips .tip-item {
  border-bottom-color: #333333;
}

.v-theme--dark .recent-activity .activity-item {
  border-bottom-color: #333333;
}
</style>