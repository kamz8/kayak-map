<template>
  <div class="roles-index">
    <UiCard title="Role systemowe">
      <template #description>
        Zarządzanie rolami użytkowników w systemie
      </template>

      <div class="d-flex gap-2 mb-4">
        <UiButton variant="default" size="sm" disabled>
          <v-icon start>mdi-plus</v-icon>
          Dodaj rolę
        </UiButton>
        <UiButton variant="outline" size="sm" disabled>
          <v-icon start>mdi-download</v-icon>
          Eksport
        </UiButton>
      </div>

      <v-table>
        <thead>
          <tr>
            <th>Nazwa roli</th>
            <th>Opis</th>
            <th>Użytkownicy</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div class="d-flex align-center">
                <UiBadge variant="destructive" size="sm" class="me-2">
                  Super Admin
                </UiBadge>
              </div>
            </td>
            <td>Pełny dostęp do systemu</td>
            <td>1 użytkownik</td>
            <td>
              <v-btn icon="mdi-pencil" size="x-small" variant="text" disabled />
              <v-btn icon="mdi-delete" size="x-small" variant="text" color="error" disabled />
            </td>
          </tr>
          <tr>
            <td>
              <div class="d-flex align-center">
                <UiBadge variant="warning" size="sm" class="me-2">
                  Admin
                </UiBadge>
              </div>
            </td>
            <td>Administrator systemu</td>
            <td>2 użytkowników</td>
            <td>
              <v-btn icon="mdi-pencil" size="x-small" variant="text" disabled />
              <v-btn icon="mdi-delete" size="x-small" variant="text" color="error" disabled />
            </td>
          </tr>
          <tr>
            <td>
              <div class="d-flex align-center">
                <UiBadge variant="secondary" size="sm" class="me-2">
                  Editor
                </UiBadge>
              </div>
            </td>
            <td>Edytor treści</td>
            <td>5 użytkowników</td>
            <td>
              <v-btn icon="mdi-pencil" size="x-small" variant="text" disabled />
              <v-btn icon="mdi-delete" size="x-small" variant="text" color="error" disabled />
            </td>
          </tr>
          <tr>
            <td>
              <div class="d-flex align-center">
                <UiBadge variant="default" size="sm" class="me-2">
                  User
                </UiBadge>
              </div>
            </td>
            <td>Zwykły użytkownik</td>
            <td>127 użytkowników</td>
            <td>
              <v-btn icon="mdi-pencil" size="x-small" variant="text" disabled />
              <v-btn icon="mdi-delete" size="x-small" variant="text" color="error" disabled />
            </td>
          </tr>
        </tbody>
      </v-table>

      <div class="mt-4 text-center">
        <v-alert type="info" variant="tonal">
          <v-icon start>mdi-information</v-icon>
          Strona w budowie - funkcjonalność będzie dostępna wkrótce
        </v-alert>
      </div>
    </UiCard>
  </div>
</template>

<script>
import { UiCard, UiButton, UiBadge } from '@/dashboard/components/ui'

export default {
  name: 'RolesIndex',
  components: {
    UiCard,
    UiButton,
    UiBadge
  }
}
</script>

<style scoped>
.roles-index {
  height: 100%;
}
</style>