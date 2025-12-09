<template>
  <ui-dialog
      :model-value="show"
      max-width="600"
      persistent
      @update:model-value="$emit('update:show', $event)"
  >
    <UiCard>
      <template #title>
        <div class="dialog-header">
          <v-icon color="primary" size="large">mdi-map-marker-plus</v-icon>
          <span>{{ isNewPoi ? 'Dodaj nowy punkt POI' : 'Edytuj punkt POI' }}</span>
        </div>
      </template>

      <div class="dialog-content">
        <!-- Nazwa punktu -->
        <UiInput
            v-model="formData.name"
            label="Nazwa punktu"
            placeholder="np. Przystań kajakowa"
            :error-message="errors.name"
            required
        />

        <!-- Typ punktu -->
        <v-select
            v-model="formData.point_type_id"
            :items="pointTypeItems"
            item-title="name"
            item-value="id"
            label="Typ punktu"
            variant="outlined"
            density="comfortable"
            class="mt-4"
            :error-messages="errors.point_type_id"
        >
          <template #prepend-inner>
            <v-icon :icon="selectedPointTypeIcon" :color="selectedPointTypeColor" size="small" />
          </template>
          <template #item="{ props, item }">
            <v-list-item v-bind="props">
              <template #prepend>
                <v-icon :icon="item.raw.icon" :color="getPointTypeColor(item.raw.icon)" />
              </template>
            </v-list-item>
          </template>
        </v-select>

        <!-- Opis -->
        <v-textarea
            v-model="formData.description"
            label="Opis (opcjonalnie)"
            placeholder="Dodatkowe informacje o punkcie..."
            variant="outlined"
            density="comfortable"
            rows="3"
            class="mt-4"
            :error-messages="errors.description"
        />

        <!-- Współrzędne -->
        <div class="coordinates-row mt-4">
          <UiInput
              v-model.number="formData.lat"
              label="Szerokość geograficzna"
              type="number"
              step="0.000001"
              :error-message="errors.lat"
              required
          />
          <UiInput
              v-model.number="formData.lng"
              label="Długość geograficzna"
              type="number"
              step="0.000001"
              :error-message="errors.lng"
              required
              class="ml-2"
          />
        </div>

        <!-- Informacja o położeniu punktu na trasie (jeśli edytujemy) -->
        <div v-if="!isNewPoi && formData.at_length !== null" class="mt-3">
          <v-chip size="small" color="info" variant="tonal">
            <v-icon start size="small">mdi-map-marker-distance</v-icon>
            Km {{ (formData.at_length / 1000).toFixed(2) }} trasy
          </v-chip>
        </div>
      </div>

      <template #actions>
        <div class="dialog-actions">
          <!-- Przycisk usuwania (tylko dla istniejących POI) -->
          <UiButton
              v-if="!isNewPoi"
              variant="destructive"
              @click="handleDelete"
              class="mr-auto"
          >
            <v-icon start>mdi-delete</v-icon>
            Usuń
          </UiButton>

          <!-- Standardowe akcje -->
          <UiButton
              variant="outline"
              @click="handleCancel"
          >
            Anuluj
          </UiButton>
          <UiButton
              variant="default"
              @click="handleSave"
              :disabled="!isFormValid"
          >
            <v-icon start>mdi-content-save</v-icon>
            {{ isNewPoi ? 'Dodaj' : 'Zapisz' }}
          </UiButton>
        </div>
      </template>
    </UiCard>
  </ui-dialog>
</template>

<script>
import { UiCard, UiButton, UiInput } from '@/dashboard/components/ui'
import { mapState } from 'vuex'
import { getPointTypeColor } from '../utils/leafletIconUtils'
import UiDialog from "@ui/UiDialog.vue";

export default {
  name: 'PoiEditorDialog',

  components: {
    UiDialog,
    UiCard,
    UiButton,
    UiInput
  },

  props: {
    /** Czy dialog jest widoczny */
    show: {
      type: Boolean,
      default: false
    },
    /** Obiekt POI do edycji (null dla nowego) */
    poi: {
      type: Object,
      default: null
    }
  },

  emits: ['update:show', 'saved', 'cancelled', 'delete-poi'],

  data() {
    return {
      formData: {
        id: null,
        name: '',
        description: '',
        lat: 0,
        lng: 0,
        point_type_id: 1,
        at_length: null
      },
      errors: {}
    }
  },

  computed: {
    ...mapState('trailEditor', {
      availablePointTypes: 'availablePointTypes'
    }),

    /** Czy to nowy POI (id === null) */
    isNewPoi() {
      return !this.formData.id
    },

    /** Lista typów punktów dla v-select */
    pointTypeItems() {
      return this.availablePointTypes || []
    },

    /** Ikona wybranego typu punktu */
    selectedPointTypeIcon() {
      if (!this.formData.point_type_id) return 'mdi-map-marker'
      const selectedType = this.pointTypeItems.find(pt => pt.id === this.formData.point_type_id)
      return selectedType?.icon || 'mdi-map-marker'
    },

    /** Kolor wybranego typu punktu */
    selectedPointTypeColor() {
      const colorKey = getPointTypeColor(this.selectedPointTypeIcon)
      return colorKey
    },

    /** Walidacja formularza */
    isFormValid() {
      return !!(
          this.formData.name &&
          this.formData.name.trim().length > 0 &&
          this.formData.lat &&
          this.formData.lng &&
          this.formData.point_type_id
      )
    }
  },

  watch: {
    /** Aktualizacja danych formularza gdy zmienia się prop `poi` */
    poi: {
      immediate: true,
      handler(newPoi) {
        if (newPoi) {
          this.formData = {
            id: newPoi.id || null,
            name: newPoi.name || 'Nowy Punkt',
            description: newPoi.description || '',
            lat: newPoi.lat || newPoi.latitude || 0,
            lng: newPoi.lng || newPoi.longitude || 0,
            point_type_id: newPoi.point_type_id || (this.pointTypeItems[0]?.id || 1),
            at_length: newPoi.at_length || null
          }
        }
      }
    },

    /** Reset błędów gdy dialog jest zamykany */
    show(newVal) {
      if (!newVal) {
        this.errors = {}
      }
    }
  },

  methods: {
    // Helper function dostępna w template
    getPointTypeColor,

    /** Zapisz POI */
    handleSave() {
      // Walidacja
      this.errors = {}

      if (!this.formData.name || this.formData.name.trim().length === 0) {
        this.errors.name = 'Nazwa jest wymagana'
        return
      }

      if (!this.formData.lat || !this.formData.lng) {
        this.errors.lat = 'Współrzędne są wymagane'
        this.errors.lng = 'Współrzędne są wymagane'
        return
      }

      // Emit saved event with form data
      this.$emit('saved', {
        ...this.formData,
        latitude: this.formData.lat,
        longitude: this.formData.lng
      })

      // Zamknij dialog
      this.$emit('update:show', false)
    },

    /** Anuluj edycję */
    handleCancel() {
      this.errors = {}
      this.$emit('cancelled')
      this.$emit('update:show', false)
    },

    /** Usuń POI */
    handleDelete() {
      if (confirm(`Czy na pewno chcesz usunąć punkt "${this.formData.name}"?`)) {
        this.$emit('delete-poi', this.formData)
        this.$emit('update:show', false)
      }
    }
  }
}
</script>

<style scoped>
.dialog-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.dialog-content {
  padding: 20px 0;
}

.dialog-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
}

.coordinates-row {
  display: flex;
  gap: 8px;
}

.coordinates-row > * {
  flex: 1;
}
</style>