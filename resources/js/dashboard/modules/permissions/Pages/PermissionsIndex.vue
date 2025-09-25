<template>
  <div class="permissions-index">
    <UiCard title="Uprawnienia systemowe">
      <template #description>
        Zarządzanie uprawnieniami i pozwoleniami w systemie
      </template>

      <div class="d-flex gap-2 mb-4">
        <UiButton variant="default" size="sm" disabled>
          <v-icon start>mdi-plus</v-icon>
          Dodaj uprawnienie
        </UiButton>
        <UiButton variant="outline" size="sm" disabled>
          <v-icon start>mdi-download</v-icon>
          Eksport
        </UiButton>
      </div>

      <div class="row">
        <div class="col-md-6 mb-4">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2" color="primary">mdi-account-group</v-icon>
              Moduł Użytkownicy
            </v-card-title>
            <v-card-text>
              <v-chip class="ma-1" size="small">users.view</v-chip>
              <v-chip class="ma-1" size="small">users.create</v-chip>
              <v-chip class="ma-1" size="small">users.edit</v-chip>
              <v-chip class="ma-1" size="small">users.delete</v-chip>
            </v-card-text>
          </v-card>
        </div>

        <div class="col-md-6 mb-4">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2" color="warning">mdi-shield-account</v-icon>
              Moduł Role
            </v-card-title>
            <v-card-text>
              <v-chip class="ma-1" size="small">roles.view</v-chip>
              <v-chip class="ma-1" size="small">roles.create</v-chip>
              <v-chip class="ma-1" size="small">roles.edit</v-chip>
              <v-chip class="ma-1" size="small">roles.delete</v-chip>
            </v-card-text>
          </v-card>
        </div>

        <div class="col-md-6 mb-4">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2" color="success">mdi-key</v-icon>
              Moduł Uprawnienia
            </v-card-title>
            <v-card-text>
              <v-chip class="ma-1" size="small">permissions.view</v-chip>
              <v-chip class="ma-1" size="small">permissions.create</v-chip>
              <v-chip class="ma-1" size="small">permissions.edit</v-chip>
              <v-chip class="ma-1" size="small">permissions.delete</v-chip>
            </v-card-text>
          </v-card>
        </div>

        <div class="col-md-6 mb-4">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2" color="info">mdi-map-marker-path</v-icon>
              Moduł Szlaki
            </v-card-title>
            <v-card-text>
              <v-chip class="ma-1" size="small">trails.view</v-chip>
              <v-chip class="ma-1" size="small">trails.create</v-chip>
              <v-chip class="ma-1" size="small">trails.edit</v-chip>
              <v-chip class="ma-1" size="small">trails.delete</v-chip>
            </v-card-text>
          </v-card>
        </div>

        <div class="col-md-6 mb-4">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2" color="secondary">mdi-cog</v-icon>
              Moduł Ustawienia
            </v-card-title>
            <v-card-text>
              <v-chip class="ma-1" size="small">settings.view</v-chip>
              <v-chip class="ma-1" size="small">settings.general.edit</v-chip>
              <v-chip class="ma-1" size="small">settings.security.edit</v-chip>
            </v-card-text>
          </v-card>
        </div>

        <div class="col-md-6 mb-4">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2" color="error">mdi-view-dashboard</v-icon>
              Moduł Dashboard
            </v-card-title>
            <v-card-text>
              <v-chip class="ma-1" size="small">dashboard.view</v-chip>
              <v-chip class="ma-1" size="small">dashboard.admin</v-chip>
            </v-card-text>
          </v-card>
        </div>
      </div>

      <div class="mt-4 text-center">
        <v-alert type="info" variant="tonal">
          <v-icon start>mdi-information</v-icon>
          System uprawnień działa w trybie tylko do odczytu. Uprawnienia są zarządzane automatycznie przez system.
        </v-alert>
      </div>
    </UiCard>
  </div>
</template>

<script>
import { UiCard, UiButton } from '@/dashboard/components/ui'

export default {
  name: 'PermissionsIndex',
  components: {
    UiCard,
    UiButton
  }
}
</script>

<style scoped>
.permissions-index {
  height: 100%;
}
</style>