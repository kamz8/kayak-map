<template>
  <v-dialog
    :model-value="modelValue"
    max-width="500"
    persistent
  >
    <UiCard>
      <template #title>
        <div class="dialog-header">
          <v-icon color="warning" size="large">mdi-alert-circle</v-icon>
          <span>Niezapisane zmiany</span>
        </div>
      </template>

      <p class="dialog-content">
        Masz niezapisane zmiany w edytorze mapy. Czy na pewno chcesz opuścić tę stronę?
        Wszystkie niezapisane zmiany zostaną utracone.
      </p>

      <template #actions>
        <UiButton
          variant="outline"
          @click="$emit('cancel')"
        >
          Anuluj
        </UiButton>
        <UiButton
          variant="destructive"
          @click="$emit('confirm')"
        >
          Opuść bez zapisywania
        </UiButton>
      </template>
    </UiCard>
  </v-dialog>
</template>

<script>
import { UiCard, UiButton } from '@/dashboard/components/ui'

export default {
  name: 'UnsavedChangesDialog',
  components: {
    UiCard,
    UiButton
  },

  props: {
    modelValue: {
      type: Boolean,
      default: false
    }
  },

  emits: ['update:modelValue', 'confirm', 'cancel']
}
</script>

<style scoped>
.dialog-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.dialog-content {
  margin: 16px 0;
  line-height: 1.6;
  color: rgb(var(--v-theme-on-surface-variant));
}
</style>
