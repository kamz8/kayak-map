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
        <FormField
            v-model="formData.name"
            label="Nazwa punktu"
            placeholder="np. Przystań kajakowa"
            :error-message="errors.name"
            required
        />

        <!-- Typ punktu -->
        <UiSelect
            v-model="formData.point_type_id"
            label="Typ punktu"
            :items="pointTypeSelectOptions"
            item-title="title"
            item-value="value"
            class="mt-4"
            :error-message="errors.point_type_id ? errors.point_type_id[0] : ''"
        >
          <template #item="{ props, item }">
            <v-list-item v-bind="props" :prepend-icon="item.raw.icon" :title="item.raw.title"></v-list-item>
          </template>
        </UiSelect>

        <!-- Opis -->
        <FormField
            v-model="formData.description"
            type="textarea"
            label="Opis (opcjonalnie)"
            placeholder="Dodatkowe informacje o punkcie..."
            :rows="3"
            class="mt-4"
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
              variant="outline"
              color="red"
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
import { UiCard, UiButton, UiInput, FormField } from '@/dashboard/components/ui'
import { mapState, mapActions } from 'vuex'
import { getPointTypeColor } from '../utils/leafletIconUtils'
import { trailEditorActions } from '../store/trailEditor'
import UiDialog from "@ui/UiDialog.vue";
import UiSelect from "@ui/UiSelect.vue";

export default {
  name: 'PoiEditorDialog',

  components: {
    UiSelect,
    UiDialog,
    UiCard,
    UiButton,
    UiInput,
    FormField
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

  mounted() {
    // Załaduj typy punktów z API
    this.fetchPointTypes()
  },

  computed: {
    ...mapState('trailEditor', {
      availablePointTypes: 'availablePointTypes'
    }),

    /** Czy to nowy POI (ma tymczasowy ID) */
    isNewPoi() {
      // Nowy punkt ma ID zaczynające się na 'temp-'
      // Istniejący punkt ma rzeczywiste ID (liczba lub liczba jako string)
      return this.formData.id && this.formData.id.toString().startsWith('temp-')
    },

    /** Lista typów punktów dla v-select */
    pointTypeItems() {
      return this.availablePointTypes || []
    },

    /** Opcje dla FormField select - format {title, value} */
    pointTypeSelectOptions() {
      return this.pointTypeItems.map(type => ({
        title: type.type,  // API zwraca "type" nie "name"
        value: type.id,
        icon: type.icon
      }))
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
        if (newPoi && Object.keys(newPoi).length > 0) {
          this.formData = {
            id: newPoi.id !== undefined ? newPoi.id : null,
            name: newPoi.name || 'Nowy Punkt',
            description: newPoi.description || '',
            lat: newPoi.lat || newPoi.latitude || 0,
            lng: newPoi.lng || newPoi.longitude || 0,
            point_type_id: newPoi.point_type_id || (this.pointTypeItems[0]?.id || 1),
            at_length: newPoi.at_length || null
          }
        } else {
          this.formData = {
            id: null,
            name: '',
            description: '',
            lat: 0,
            lng: 0,
            point_type_id: this.pointTypeItems[0]?.id || 1,
            at_length: null
          }
        }
      },
      deep: true
    },

    /** Reset błędów gdy dialog jest zamykany */
    show(newVal) {
      if (!newVal) {
        this.errors = {}
      } else if (newVal && this.poi) {
        // Wymuś update watchers gdy dialog się otwiera
        this.$nextTick(() => {
          this.poi.id
        })
      }
    }
  },

  methods: {
    ...mapActions('trailEditor', {
      fetchPointTypes: trailEditorActions.FETCH_POINT_TYPES,
      deletePoi: 'deletePoi'
    }),

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
    async handleDelete() {
      await this.deletePoi(this.formData.id)
      this.$emit('update:show', false)

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