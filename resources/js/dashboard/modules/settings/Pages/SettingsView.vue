<template>
  <div class="settings-page">

    <v-row>
      <!-- Profile Settings Card -->
      <v-col cols="12" sm="6" md="6" lg="4" >
        <UiCard
          variant="elevated"
          class="settings-card h-100"
          @click="$router.push('/dashboard/settings/profile')"
        >
          <div class="settings-card-content">
            <div class="settings-icon mb-4">
              <v-icon size="48" color="primary">mdi-account-circle</v-icon>
            </div>
            <h3 class="settings-title mb-2">Profil użytkownika</h3>
            <p class="settings-description mb-4">
              Zarządzaj swoimi danymi osobistymi, avatarem i informacjami kontaktowymi
            </p>
            <UiButton variant="outline" size="sm" class="w-100">
              <v-icon start size="small">mdi-arrow-right</v-icon>
              Przejdź do profilu
            </UiButton>
          </div>
        </UiCard>
      </v-col>

      <!-- Security Settings Card -->
      <v-col cols="12" md="6" lg="4" sm="6">
        <UiCard variant="elevated" class="settings-card h-100 settings-card--disabled">
          <div class="settings-card-content">
            <div class="settings-icon mb-4">
              <v-icon size="48" color="secondary">mdi-shield-account</v-icon>
            </div>
            <h3 class="settings-title mb-2">Bezpieczeństwo</h3>
            <p class="settings-description mb-4">
              Zmień hasło, skonfiguruj uwierzytelnianie dwuskładnikowe i zarządzaj sesjami
            </p>
            <UiButton variant="outline" size="sm" class="w-100" disabled>
              <v-icon start size="small">mdi-lock</v-icon>
              Wkrótce dostępne
            </UiButton>
          </div>
        </UiCard>
      </v-col>

      <!-- Notifications Settings Card -->
      <v-col ccols="12" sm="6" md="6" lg="4">
        <UiCard variant="elevated" class="settings-card h-100 settings-card--disabled">
          <div class="settings-card-content">
            <div class="settings-icon mb-4">
              <v-icon size="48" color="secondary">mdi-bell-cog</v-icon>
            </div>
            <h3 class="settings-title mb-2">Powiadomienia</h3>
            <p class="settings-description mb-4">
              Skonfiguruj preferencje powiadomień email, SMS i push notifications
            </p>
            <UiButton variant="outline" size="sm" class="w-100" disabled>
              <v-icon start size="small">mdi-lock</v-icon>
              Wkrótce dostępne
            </UiButton>
          </div>
        </UiCard>
      </v-col>

      <!-- Privacy Settings Card -->
      <v-col cols="12" sm="6" md="6" lg="4">
        <UiCard variant="elevated" class="settings-card h-100 settings-card--disabled">
          <div class="settings-card-content">
            <div class="settings-icon mb-4">
              <v-icon size="48" color="secondary">mdi-eye-off</v-icon>
            </div>
            <h3 class="settings-title mb-2">Prywatność</h3>
            <p class="settings-description mb-4">
              Kontroluj widoczność swoich danych i ustawienia prywatności
            </p>
            <UiButton variant="outline" size="sm" class="w-100" disabled>
              <v-icon start size="small">mdi-lock</v-icon>
              Wkrótce dostępne
            </UiButton>
          </div>
        </UiCard>
      </v-col>

      <!-- App Settings Card -->
      <v-col cols="12" sm="6" md="6" lg="4">
        <UiCard variant="elevated" class="settings-card h-100 settings-card--disabled">
          <div class="settings-card-content">
            <div class="settings-icon mb-4">
              <v-icon size="48" color="secondary">mdi-cog</v-icon>
            </div>
            <h3 class="settings-title mb-2">Ustawienia aplikacji</h3>
            <p class="settings-description mb-4">
              Personalizuj interfejs, język, motyw i inne preferencje aplikacji
            </p>
            <UiButton variant="outline" size="sm" class="w-100" disabled>
              <v-icon start size="small">mdi-lock</v-icon>
              Wkrótce dostępne
            </UiButton>
          </div>
        </UiCard>
      </v-col>

      <!-- Data Management Card -->
      <v-col cols="12" sm="6" md="6" lg="4">
        <UiCard variant="elevated" class="settings-card h-100 settings-card--disabled">
          <div class="settings-card-content">
            <div class="settings-icon mb-4">
              <v-icon size="48" color="secondary">mdi-database-cog</v-icon>
            </div>
            <h3 class="settings-title mb-2">Zarządzanie danymi</h3>
            <p class="settings-description mb-4">
              Eksportuj, importuj lub usuń swoje dane zgodnie z RODO
            </p>
            <UiButton variant="outline" size="sm" class="w-100" disabled>
              <v-icon start size="small">mdi-lock</v-icon>
              Wkrótce dostępne
            </UiButton>
          </div>
        </UiCard>
      </v-col>
    </v-row>

    <!-- Quick Actions Section -->
    <div class="quick-actions mt-8">
      <h2 class="text-h5 font-weight-bold mb-4">Szybkie akcje</h2>

      <v-row>
        <v-col cols="12" md="4">
          <UiButton
            variant="outline"
            size="default"
            class="w-100 mb-3"
            @click="$router.push('/dashboard/settings/profile')"
          >
            <v-icon start>mdi-account-edit</v-icon>
            Edytuj profil
          </UiButton>
        </v-col>

        <v-col cols="12" md="4">
          <UiButton
            variant="outline"
            size="default"
            class="w-100 mb-3"
            disabled
          >
            <v-icon start>mdi-lock-reset</v-icon>
            Zmień hasło
          </UiButton>
        </v-col>

        <v-col cols="12" md="4">
          <UiButton
            variant="outline"
            size="default"
            class="w-100 mb-3"
            disabled
          >
            <v-icon start>mdi-download</v-icon>
            Pobierz dane
          </UiButton>
        </v-col>
      </v-row>
    </div>
  </div>
</template>

<script>
import { UiCard, UiButton } from '@/dashboard/components/ui'

export default {
  name: 'SettingsPage',
  components: {
    UiCard,
    UiButton
  }
}
</script>

<style scoped>
.settings-page {
  padding: 0;
}

.settings-header {
  margin-bottom: 24px;
}

.settings-card {
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 280px;
}

.settings-card:hover:not(.settings-card--disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.settings-card--disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

.settings-card--disabled:hover {
  transform: none;
  box-shadow: none;
}

.settings-card-content {
  padding: 24px;
  height: 100%;
  display: flex;
  flex-direction: column;
  text-align: center;
}

.settings-icon {
  display: flex;
  justify-content: center;
  align-items: center;
}

.settings-title {
  font-size: 18px;
  font-weight: 600;
  color: hsl(var(--v-theme-on-surface));
}

.settings-description {
  font-size: 14px;
  color: hsl(var(--v-theme-on-surface-variant));
  line-height: 1.5;
  flex: 1;
}

.quick-actions h2 {
  color: hsl(var(--v-theme-on-surface));
}

.w-100 {
  width: 100%;
}

.h-100 {
  height: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .settings-header h1 {
    font-size: 1.5rem;
  }

  .settings-card {
    min-height: auto;
  }

  .settings-card-content {
    padding: 20px;
  }

  .settings-icon .v-icon {
    font-size: 40px !important;
  }
}
</style>
