<template>
  <v-form ref="form" @submit.prevent="handleSubmit">
    <v-row>
      <!-- Basic Information -->
      <v-col cols="12">
        <h3 class="text-h6 mb-4">
          <v-icon start>mdi-account</v-icon>
          Informacje podstawowe
        </h3>
      </v-col>

      <v-col cols="12" md="6">
        <FormField
          v-model="form.first_name"
          label="Imię"
          :rules="rules.first_name"
          required
        />
      </v-col>

      <v-col cols="12" md="6">
        <FormField
          v-model="form.last_name"
          label="Nazwisko"
          :rules="rules.last_name"
          required
        />
      </v-col>

      <v-col cols="12" md="6">
        <FormField
          v-model="form.email"
          label="Adres e-mail"
          type="email"
          :rules="rules.email"
          required
        />
      </v-col>

      <v-col cols="12" md="6">
        <FormField
          v-model="form.phone"
          label="Numer telefonu"
          :rules="rules.phone"
          placeholder="+48 123 456 789"
        />
      </v-col>

      <!-- Password Section -->
      <v-col cols="12" md="6" v-if="isCreate">
        <FormField
          v-model="form.password"
          label="Hasło"
          type="password"
          :rules="rules.password"
          required
          hint="Zostaw puste aby wygenerować automatycznie"
        />
      </v-col>

      <v-col cols="12" md="6" v-if="isCreate">
        <FormField
          v-model="form.password_confirmation"
          label="Potwierdź hasło"
          type="password"
          :rules="rules.password_confirmation"
          required
        />
      </v-col>

      <v-col cols="12" v-if="!isCreate && !showPasswordField">
        <UiButton
          variant="outline"
          size="sm"
          @click="showPasswordField = true"
        >
          <v-icon start>mdi-key-variant</v-icon>
          Zmień hasło
        </UiButton>
      </v-col>

      <!-- Expanded Password Change Section -->
      <template v-if="!isCreate && showPasswordField">
        <v-col cols="12">
          <v-card variant="outlined" class="mb-4">
            <v-card-title class="d-flex align-center">
              <v-icon start>mdi-key-variant</v-icon>
              Zmiana hasła
              <v-spacer />
              <v-btn
                icon
                size="small"
                variant="text"
                @click="cancelPasswordChange"
              >
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <FormField
                    v-model="form.password"
                    label="Nowe hasło"
                    type="password"
                    :rules="rules.passwordUpdate"
                    required
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <FormField
                    v-model="form.password_confirmation"
                    label="Potwierdź nowe hasło"
                    type="password"
                    :rules="rules.password_confirmation"
                    required
                  />
                </v-col>
              </v-row>
              <v-alert
                type="info"
                variant="tonal"
                density="compact"
                class="mt-2"
              >
                <v-icon start>mdi-information</v-icon>
                Hasło musi mieć co najmniej 8 znaków. Zostaw pola puste aby nie zmieniać hasła.
              </v-alert>
            </v-card-text>
          </v-card>
        </v-col>
      </template>

      <!-- Additional Information -->
      <v-col cols="12">
        <h3 class="text-h6 mb-4 mt-4">
          <v-icon start>mdi-information</v-icon>
          Informacje dodatkowe
        </h3>
      </v-col>

      <v-col cols="12" md="6">
        <FormField
          v-model="form.location"
          label="Lokalizacja"
          placeholder="Kraków, Polska"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-text-field
          v-model="form.birth_date"
          label="Data urodzenia"
          type="date"
          variant="outlined"
          hide-details="auto"
          :rules="rules.birth_date"
        />
      </v-col>

      <v-col cols="12" md="3">
        <v-select
          v-model="form.gender"
          :items="genderOptions"
          label="Płeć"
          variant="outlined"
          clearable
          hide-details="auto"
        />
      </v-col>

      <v-col cols="12">
        <v-textarea
          v-model="form.bio"
          label="Biografia"
          variant="outlined"
          rows="3"
          hide-details="auto"
          :rules="rules.bio"
          placeholder="Krótki opis o użytkowniku..."
        />
      </v-col>

      <!-- Settings -->
      <v-col cols="12">
        <h3 class="text-h6 mb-4 mt-4">
          <v-icon start>mdi-cog</v-icon>
          Ustawienia
        </h3>
      </v-col>

      <v-col cols="12" md="6">
        <v-switch
          v-model="form.is_active"
          label="Konto aktywne"
          color="success"
          hide-details
        />
        <p class="text-caption text-medium-emphasis mt-1">
          Nieaktywne konta nie mogą się logować
        </p>
      </v-col>

      <v-col cols="12" md="6">
        <v-switch
          v-model="form.preferences.email_notifications"
          label="Powiadomienia e-mail"
          color="primary"
          hide-details
        />
      </v-col>

      <!-- Roles Section (Only for users with appropriate permissions) -->
      <v-col cols="12" v-if="canAssignRoles">
        <h3 class="text-h6 mb-4 mt-4">
          <v-icon start>mdi-shield-account</v-icon>
          Role użytkownika
        </h3>

        <v-select
          v-model="form.roles"
          :items="availableRoleOptions"
          item-title="label"
          item-value="value"
          label="Wybierz role"
          variant="outlined"
          multiple
          chips
          closable-chips
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
          v-if="roleWarning"
          type="warning"
          variant="tonal"
          class="mt-3"
          density="compact"
        >
          <v-icon start>mdi-shield-alert</v-icon>
          {{ roleWarning }}
        </v-alert>
      </v-col>
    </v-row>

    <!-- Form Actions -->
    <v-row class="mt-6">
      <v-col cols="12">
        <div class="d-flex justify-end gap-2">
          <UiButton
            variant="outline"
            @click="$router.back()"
            :disabled="loading"
          >
            Anuluj
          </UiButton>

          <UiButton
            type="submit"
            :loading="loading"
            :disabled="!isFormValid"
          >
            {{ isCreate ? 'Utwórz użytkownika' : 'Zapisz zmiany' }}
          </UiButton>
        </div>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import { mapGetters } from 'vuex'
import { UiButton } from '@/dashboard/components/ui'
import FormField from '@/dashboard/components/ui/FormField.vue'

const ROLE_COLORS = {
  'Super Admin': 'error',
  'Admin': 'warning',
  'Editor': 'info',
  'User': 'success'
}

const DEFAULT_FORM = {
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  bio: '',
  location: '',
  birth_date: '',
  gender: '',
  is_active: true,
  preferences: {
    email_notifications: true,
    language: 'pl'
  },
  notification_settings: {
    enabled: true,
    email: true,
    push: false
  },
  roles: []
}

export default {
  name: 'UserForm',
  components: {
    UiButton,
    FormField
  },
  props: {
    user: {
      type: Object,
      default: null
    },
    availableRoles: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['submit'],
  data() {
    return {
      form: { ...DEFAULT_FORM },
      showPasswordField: false,
      genderOptions: [
        { title: 'Mężczyzna', value: 'male' },
        { title: 'Kobieta', value: 'female' },
        { title: 'Inne', value: 'other' }
      ],
      rules: {
        first_name: [
          v => !!v || 'Imię jest wymagane',
          v => (v && v.length <= 255) || 'Imię może mieć maksymalnie 255 znaków'
        ],
        last_name: [
          v => !!v || 'Nazwisko jest wymagane',
          v => (v && v.length <= 255) || 'Nazwisko może mieć maksymalnie 255 znaków'
        ],
        email: [
          v => !!v || 'Adres e-mail jest wymagany',
          v => /.+@.+\..+/.test(v) || 'Wprowadź poprawny adres e-mail'
        ],
        phone: [
          v => !v || v.length <= 20 || 'Numer telefonu może mieć maksymalnie 20 znaków'
        ],
        password: [
          v => !!v || 'Hasło jest wymagane',
          v => !v || v.length >= 8 || 'Hasło musi mieć co najmniej 8 znaków'
        ],
        passwordUpdate: [
          v => !v || v.length >= 8 || 'Hasło musi mieć co najmniej 8 znaków'
        ],
        password_confirmation: [
          v => {
            if (this.isCreate) {
              return !!v || 'Potwierdzenie hasła jest wymagane'
            }
            return !this.form.password || !!v || 'Potwierdzenie hasła jest wymagane gdy ustawiasz nowe hasło'
          },
          v => v === this.form.password || 'Hasła muszą być identyczne'
        ],
        bio: [
          v => !v || v.length <= 1000 || 'Biografia może mieć maksymalnie 1000 znaków'
        ],
        birth_date: [
          v => !v || new Date(v) < new Date() || 'Data urodzenia musi być datą z przeszłości'
        ]
      }
    }
  },
  computed: {
    ...mapGetters('auth', ['user as currentUser']),

    isCreate() {
      return !this.user
    },

    isFormValid() {
      const basicValid = this.form.first_name &&
                        this.form.last_name &&
                        this.form.email

      if (this.isCreate) {
        return basicValid &&
               (!this.form.password || this.form.password === this.form.password_confirmation)
      }

      if (this.showPasswordField && this.form.password) {
        return basicValid &&
               this.form.password.length >= 8 &&
               this.form.password === this.form.password_confirmation
      }

      return basicValid
    },

    canAssignRoles() {
      if (!this.currentUser) return false

      return this.currentUser.is_super_admin ||
             this.currentUser.roles?.some(role => ['Super Admin', 'Admin'].includes(role.name))
    },

    availableRoleOptions() {
      if (!this.currentUser) return this.availableRoles

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

    roleWarning() {
      if (!this.currentUser) return null

      if (this.form.roles.includes('Super Admin') && !this.currentUser.is_super_admin) {
        return 'Tylko Super Admin może przypisywać rolę Super Admin'
      }
      return null
    }
  },
  created() {
    this.initializeForm()
  },
  watch: {
    user: {
      handler() {
        this.initializeForm()
      },
      deep: true
    }
  },
  methods: {
    initializeForm() {
      if (this.user) {
        // Edit mode - populate form with user data
        this.form = {
          first_name: this.user.first_name || '',
          last_name: this.user.last_name || '',
          email: this.user.email || '',
          phone: this.user.phone || '',
          password: '',
          password_confirmation: '',
          bio: this.user.bio || '',
          location: this.user.location || '',
          birth_date: this.user.birth_date || '',
          gender: this.user.gender || '',
          is_active: this.user.is_active ?? true,
          preferences: {
            email_notifications: this.user.preferences?.email_notifications ?? true,
            language: this.user.preferences?.language || 'pl'
          },
          notification_settings: {
            enabled: this.user.notification_settings?.enabled ?? true,
            email: this.user.notification_settings?.email ?? true,
            push: this.user.notification_settings?.push ?? false
          },
          roles: this.user.roles ? this.user.roles.map(role => role.name) : []
        }
      } else {
        // Create mode - use defaults
        this.form = { ...DEFAULT_FORM }
      }
    },

    async handleSubmit() {
      const { valid } = await this.$refs.form.validate()
      if (!valid) return

      // Prepare form data
      const formData = { ...this.form }

      // Remove password fields if not set (for updates)
      if (!this.isCreate && !formData.password) {
        delete formData.password
        delete formData.password_confirmation
      } else if (formData.password_confirmation) {
        // Remove confirmation field - it's only for validation
        delete formData.password_confirmation
      }

      // Clean empty values
      Object.keys(formData).forEach(key => {
        if (formData[key] === '') {
          formData[key] = null
        }
      })

      this.$emit('submit', formData)
    },

    cancelPasswordChange() {
      this.showPasswordField = false
      this.form.password = ''
      this.form.password_confirmation = ''
    },

    getRoleColor(roleName) {
      return ROLE_COLORS[roleName] || 'primary'
    }
  }
}
</script>

<style scoped>
:deep(.v-chip--variant-flat) {
  border: 1px solid rgba(var(--v-border-color), 0.3);
}

:deep(.v-text-field .v-field__input) {
  font-size: 0.875rem;
}

:deep(.v-textarea .v-field__input) {
  font-size: 0.875rem;
}
</style>