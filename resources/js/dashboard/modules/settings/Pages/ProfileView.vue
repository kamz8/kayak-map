<template>
  <div class="profile-page">

    <v-row>
      <!-- Profile Information Card -->
      <v-col cols="12" lg="8">
        <UiCard title="" class="mb-6">
          <template #actions>
            <UiButton
              variant="outline"
              size="sm"
              :disabled="!isEditing"
              @click="handleSave"
            >
              <v-icon start size="small">mdi-content-save</v-icon>
              Zapisz
            </UiButton>
            <UiButton
              :variant="isEditing ? 'secondary' : 'default'"
              size="sm"
              @click="toggleEdit"
            >
              <v-icon start size="small">{{ isEditing ? 'mdi-cancel' : 'mdi-pencil' }}</v-icon>
              {{ isEditing ? 'Anuluj' : 'Edytuj' }}
            </UiButton>
          </template>

          <v-form ref="profileForm" @submit.prevent="handleSave">
            <v-row>
              <!-- Avatar Section -->
              <v-col cols="12" class="text-center mb-4">
                <div class="profile-avatar-section">
                  <UiAvatar
                    :size="120"
                    :src="user.avatar"
                    :name="userFullName"
                    :alt="`${user.first_name} ${user.last_name}`"
                    :uploadable="isEditing"
                    variant="primary"
                    class="profile-avatar mb-4"
                    @upload="handleAvatarUpload"
                  />

                  <div v-if="isEditing">
                    <UiButton variant="outline" size="sm" class="mb-2">
                      <v-icon start size="small">mdi-camera</v-icon>
                      Zmień zdjęcie
                    </UiButton>
                    <br>
                    <UiButton v-if="user.avatar" variant="ghost" size="sm" @click="removeAvatar">
                      Usuń zdjęcie
                    </UiButton>
                  </div>
                </div>
              </v-col>

              <!-- Personal Information -->
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profileForm.first_name"
                  label="Imię"
                  :readonly="!isEditing"
                  :rules="[rules.required]"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profileForm.last_name"
                  label="Nazwisko"
                  :readonly="!isEditing"
                  :rules="[rules.required]"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profileForm.email"
                  label="Email"
                  type="email"
                  :readonly="!isEditing"
                  :rules="[rules.required, rules.email]"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profileForm.phone"
                  label="Telefon"
                  :readonly="!isEditing"
                  :rules="[rules.phone]"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                />
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="profileForm.bio"
                  label="O mnie"
                  :readonly="!isEditing"
                  variant="filled"
                  density="compact"
                  rows="3"
                  class="mb-4"
                >
                  <template #append-inner>
                    <v-icon v-if="!isEditing" size="small">mdi-lock</v-icon>
                  </template>
                </v-textarea>
              </v-col>

              <!-- Additional Information -->
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profileForm.location"
                  label="Lokalizacja"
                  :readonly="!isEditing"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                >
                  <template #prepend-inner>
                    <v-icon size="small">mdi-map-marker</v-icon>
                  </template>
                </v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="profileForm.birth_date"
                  label="Data urodzenia"
                  type="date"
                  :readonly="!isEditing"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="profileForm.gender"
                  label="Płeć"
                  :items="genderOptions"
                  :readonly="!isEditing"
                  variant="outlined"
                  density="compact"
                  class="mb-4"
                />
              </v-col>
            </v-row>
          </v-form>
        </UiCard>
      </v-col>

      <!-- Sidebar -->
      <v-col cols="12" lg="4">
        <!-- Account Status Card -->
        <UiCard title="Status konta" class="mb-4">
          <div class="account-status">
            <div class="status-item d-flex justify-space-between align-center mb-3">
              <span class="text-body-2">Status konta:</span>
              <UiBadge :variant="user.is_active ? 'success' : 'destructive'">
                {{ user.is_active ? 'Aktywne' : 'Nieaktywne' }}
              </UiBadge>
            </div>

            <div class="status-item d-flex justify-space-between align-center mb-3">
              <span class="text-body-2">Email zweryfikowany:</span>
              <UiBadge :variant="user.email_verified_at ? 'success' : 'warning'">
                {{ user.email_verified_at ? 'Tak' : 'Nie' }}
              </UiBadge>
            </div>

            <div class="status-item d-flex justify-space-between align-center mb-3">
              <span class="text-body-2">Telefon zweryfikowany:</span>
              <UiBadge :variant="user.phone_verified ? 'success' : 'warning'">
                {{ user.phone_verified ? 'Tak' : 'Nie' }}
              </UiBadge>
            </div>

            <div class="status-item d-flex justify-space-between align-center mb-3">
              <span class="text-body-2">Rola:</span>
              <UiBadge :variant="user.is_admin ? 'secondary' : 'outline'">
                {{ user.is_admin ? 'Administrator' : 'Użytkownik' }}
              </UiBadge>
            </div>

            <div class="status-item d-flex justify-space-between align-center">
              <span class="text-body-2">Ostatnie logowanie:</span>
              <span class="text-body-2 text-medium-emphasis">
                {{ formatDate(user.last_login_at) }}
              </span>
            </div>
          </div>
        </UiCard>

        <!-- Personal Info Summary Card -->
        <UiCard title="Informacje osobiste" class="mb-4">
          <div class="personal-info-summary">
            <div v-if="user.location" class="info-item d-flex align-center mb-2">
              <v-icon size="16" class="me-2 text-medium-emphasis">mdi-map-marker</v-icon>
              <span class="text-body-2">{{ user.location }}</span>
            </div>

            <div v-if="user.birth_date" class="info-item d-flex align-center mb-2">
              <v-icon size="16" class="me-2 text-medium-emphasis">mdi-calendar</v-icon>
              <span class="text-body-2">{{ formatDate(user.birth_date) }} ({{ getUserAge() }} lat)</span>
            </div>

            <div v-if="user.gender" class="info-item d-flex align-center mb-2">
              <v-icon size="16" class="me-2 text-medium-emphasis">mdi-human</v-icon>
              <span class="text-body-2">{{ getGenderLabel(user.gender) }}</span>
            </div>

            <div v-if="user.phone" class="info-item d-flex align-center mb-2">
              <v-icon size="16" class="me-2 text-medium-emphasis">mdi-phone</v-icon>
              <span class="text-body-2">{{ user.phone }}</span>
              <UiBadge
                v-if="user.phone_verified"
                variant="success"
                size="sm"
                class="ms-2"
              >
                Zweryfikowany
              </UiBadge>
            </div>

            <div v-if="user.bio" class="info-item mt-3">
              <v-icon size="16" class="me-2 text-medium-emphasis">mdi-information</v-icon>
              <p class="text-body-2 mb-0 ms-4">{{ user.bio }}</p>
            </div>

            <div v-if="!hasPersonalInfo" class="text-center py-4">
              <v-icon size="48" class="text-disabled mb-2">mdi-account-edit</v-icon>
              <p class="text-body-2 text-disabled mb-0">
                Uzupełnij swoje informacje osobiste
              </p>
            </div>
          </div>
        </UiCard>

        <!-- Account Actions Card -->
        <UiCard title="Akcje konta">
          <div class="account-actions">
            <UiButton
              variant="outline"
              size="sm"
              class="mb-3 w-100"
              @click="$router.push('/dashboard/security/change-password')"
            >
              <v-icon start size="small">mdi-lock-reset</v-icon>
              Zmień hasło
            </UiButton>

            <UiButton
              v-if="!user.email_verified_at"
              variant="outline"
              size="sm"
              class="mb-3 w-100"
              @click="sendVerificationEmail"
            >
              <v-icon start size="small">mdi-email-check</v-icon>
              Wyślij link weryfikacyjny
            </UiButton>

            <UiButton
              variant="outline"
              size="sm"
              class="mb-3 w-100"
              @click="downloadUserData"
            >
              <v-icon start size="small">mdi-download</v-icon>
              Pobierz dane
            </UiButton>

            <UiButton
              variant="destructive"
              size="sm"
              class="w-100"
              @click="showDeleteAccountDialog = true"
            >
              <v-icon start size="small">mdi-account-remove</v-icon>
              Usuń konto
            </UiButton>
          </div>
        </UiCard>
      </v-col>
    </v-row>


    <!-- Delete Account Dialog -->
    <v-dialog v-model="showDeleteAccountDialog" max-width="500">
      <UiCard title="Usuń konto">
        <p class="text-body-1 mb-4">
          Czy na pewno chcesz usunąć swoje konto? Ta akcja jest nieodwracalna.
        </p>
        <p class="text-body-2 text-error mb-4">
          Wszystkie Twoje dane zostaną trwale usunięte z systemu.
        </p>

        <v-text-field
          v-model="deleteConfirmation"
          label="Wpisz 'USUŃ' aby potwierdzić"
          :rules="[rules.deleteConfirmation]"
          variant="outlined"
          density="compact"
          class="mb-4"
        />

        <template #actions>
          <UiButton variant="outline" @click="showDeleteAccountDialog = false">
            Anuluj
          </UiButton>
          <UiButton
            variant="destructive"
            :disabled="deleteConfirmation !== 'USUŃ'"
            @click="handleDeleteAccount"
          >
            Usuń konto
          </UiButton>
        </template>
      </UiCard>
    </v-dialog>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { UiCard, UiButton, UiBadge, UiAvatar } from '@/dashboard/components/ui'

export default {
  name: 'ProfilePage',
  components: {
    UiCard,
    UiButton,
    UiBadge,
    UiAvatar
  },
  data() {
    return {
      isEditing: false,
      loading: false,
      showDeleteAccountDialog: false,
      deleteConfirmation: '',

      profileForm: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        bio: '',
        location: '',
        birth_date: '',
        gender: null
      },

      genderOptions: [
        { title: 'Mężczyzna', value: 'male' },
        { title: 'Kobieta', value: 'female' },
        { title: 'Inne', value: 'other' },
        { title: 'Wolę nie podawać', value: 'prefer_not_to_say' }
      ],


      rules: {
        required: value => !!value || 'To pole jest wymagane',
        email: value => {
          const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
          return pattern.test(value) || 'Nieprawidłowy format email'
        },
        phone: value => {
          if (!value) return true
          const pattern = /^[0-9+\-\s()]+$/
          return pattern.test(value) || 'Nieprawidłowy format telefonu'
        },
        minLength: min => value => (value && value.length >= min) || `Minimum ${min} znaków`,
        deleteConfirmation: value => value === 'USUŃ' || 'Wpisz "USUŃ" aby potwierdzić'
      }
    }
  },
  computed: {
    ...mapGetters('auth', ['user']),

    userFullName() {
      if (!this.user) return 'Admin User'
      const firstName = this.user.first_name || this.user.name || ''
      const lastName = this.user.last_name || ''
      return `${firstName} ${lastName}`.trim() || 'Admin User'
    },

    hasPersonalInfo() {
      return this.user.location || this.user.birth_date || this.user.gender || this.user.phone || this.user.bio
    },

    breadcrumbs() {
      return [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Ustawienia', to: '/dashboard/settings' },
        { text: 'Profil', to: '/dashboard/settings/profile' }
      ]
    }
  },
  created() {
    this.initializeForm()
  },
  methods: {
    ...mapActions('auth', ['updateProfile', 'deleteAccount']),
    ...mapActions('ui', ['showSuccess', 'showError', 'showInfo']),

    handleAvatarUpload() {
      // TODO: Implement avatar upload functionality
      this.showInfo('Funkcja uploadu zdjęcia zostanie wkrótce dodana')
    },

    initializeForm() {
      this.profileForm = {
        first_name: this.user.first_name || '',
        last_name: this.user.last_name || '',
        email: this.user.email || '',
        phone: this.user.phone || '',
        bio: this.user.bio || '',
        location: this.user.location || '',
        birth_date: this.user.birth_date || '',
        gender: this.user.gender || null
      }
    },

    toggleEdit() {
      if (this.isEditing) {
        this.initializeForm()
      }
      this.isEditing = !this.isEditing
    },

    async handleSave() {
      if (!this.$refs.profileForm.validate()) {
        return
      }

      this.loading = true
      try {
        await this.updateProfile(this.profileForm)
        this.showSuccess('Profil został zaktualizowany')
        this.isEditing = false
      } catch (error) {
        this.showError('Nie udało się zaktualizować profilu')
        console.error('Profile update error:', error)
      } finally {
        this.loading = false
      }
    },


    async sendVerificationEmail() {
      try {
        // TODO: Implement email verification
        this.showInfo('Link weryfikacyjny został wysłany')
      } catch (error) {
        this.showError('Nie udało się wysłać linku weryfikacyjnego')
      }
    },

    async downloadUserData() {
      try {
        // Create JSON data export of user profile
        const userData = {
          id: this.user.id,
          first_name: this.user.first_name,
          last_name: this.user.last_name,
          email: this.user.email,
          phone: this.user.phone,
          created_at: this.user.created_at,
          updated_at: this.user.updated_at,
          last_login_at: this.user.last_login_at
        }

        const jsonData = JSON.stringify(userData, null, 2)
        const blob = new Blob([jsonData], { type: 'application/json' })
        const link = document.createElement('a')
        link.href = URL.createObjectURL(blob)
        link.download = `moje_dane_${new Date().toISOString().split('T')[0]}.json`
        link.click()

        this.showSuccess('Dane zostały pobrane pomyślnie')
      } catch (error) {
        this.showError('Nie udało się pobrać danych')
      }
    },

    async handleDeleteAccount() {
      try {
        await this.deleteAccount()
        this.showInfo('Konto zostało usunięte')
        this.$router.push('/login')
      } catch (error) {
        this.showError('Nie udało się usunąć konta')
        console.error('Account deletion error:', error)
      }
    },

    removeAvatar() {
      // TODO: Implement avatar removal
      this.showInfo('Usuwanie zdjęcia będzie wkrótce dostępne')
    },

    formatDate(date) {
      if (!date) return 'Nigdy'
      return new Date(date).toLocaleDateString('pl-PL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    getUserAge() {
      if (!this.user.birth_date) return 0
      const birthDate = new Date(this.user.birth_date)
      const today = new Date()
      let age = today.getFullYear() - birthDate.getFullYear()
      const monthDiff = today.getMonth() - birthDate.getMonth()

      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--
      }

      return age
    },

    getGenderLabel(gender) {
      const genderMap = {
        'male': 'Mężczyzna',
        'female': 'Kobieta',
        'other': 'Inne',
        'prefer_not_to_say': 'Wolę nie podawać'
      }
      return genderMap[gender] || gender
    }
  }
}
</script>

<style scoped>
.profile-page {
  padding: 0;
}

.profile-header {
  margin-bottom: 24px;
}

.profile-avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.profile-avatar {
  border: 4px solid hsl(var(--v-theme-surface-variant));
  transition: all 0.3s ease;
}

.profile-avatar:hover {
  border-color: hsl(var(--v-theme-primary));
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.account-status {
  padding: 8px 0;
}
.info-item {
    display: inline-flex;
}
.status-item {
  padding: 4px 0;
}

.account-actions {
  padding: 8px 0;
}

.w-100 {
  width: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .profile-header h1 {
    font-size: 1.5rem;
  }

  .profile-avatar {
    width: 100px !important;
    height: 100px !important;
  }
}
</style>
