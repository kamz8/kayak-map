<template>
  <v-form ref="trailForm" @submit.prevent="handleSubmit">
    <v-row>
      <!-- Main Form - Left Column -->
      <v-col cols="12" lg="8">
        <!-- Basic Information Card -->
        <v-card class="mb-6">
          <v-card-title>Podstawowe informacje</v-card-title>

          <v-card-text>
            <v-row>
              <v-col cols="12" md="6">
                <FormField
                  v-model="formData.trail_name"
                  label="Nazwa szlaku"
                  placeholder="np. Wisła - Kraków do Tynca"
                  required
                  :rules="[v => !!v || 'Nazwa szlaku jest wymagana']"
                />
              </v-col>

              <v-col cols="12" md="6">
                <FormField
                  v-model="formData.river_name"
                  label="Nazwa rzeki"
                  placeholder="np. Wisła"
                  required
                  :rules="[v => !!v || 'Nazwa rzeki jest wymagana']"
                />
              </v-col>

              <v-col cols="12">
                <FormField
                  v-model="formData.description"
                  type="textarea"
                  label="Opis szlaku"
                  placeholder="Opisz szlak, jego atrakcje, poziom trudności..."
                  :rows="4"
                  required
                  :rules="[v => !!v || 'Opis jest wymagany']"
                />
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Technical Details Card -->
        <v-card class="mb-6">
          <v-card-title>Parametry techniczne</v-card-title>

          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <FormField
                  v-model.number="formData.trail_length"
                  type="text"
                  label="Długość szlaku (km)"
                  placeholder="12.5"
                  required
                  :rules="[
                    v => !!v || 'Długość jest wymagana',
                    v => v > 0 || 'Długość musi być większa od 0'
                  ]"
                />
              </v-col>

              <v-col cols="12" md="4">
                <FormField
                  v-model="formData.difficulty"
                  type="select"
                  label="Trudność"
                  :options="difficultyOptions"
                  required
                  :rules="[v => !!v || 'Wybierz poziom trudności']"
                />
              </v-col>

              <v-col cols="12" md="4">
                <FormField
                  v-model="formData.author"
                  label="Autor szlaku"
                  placeholder="Jan Kowalski"
                />
              </v-col>

              <v-col cols="12" md="4">
                <FormField
                  v-model.number="formData.scenery"
                  type="text"
                  label="Ocena krajobrazu (0-10)"
                  placeholder="8"
                  :rules="[
                    v => v === '' || v === null || (v >= 0 && v <= 10) || 'Ocena musi być między 0 a 10'
                  ]"
                />
              </v-col>

              <v-col cols="12" md="4">
                <FormField
                  v-model.number="formData.rating"
                  type="text"
                  label="Ocena szlaku (0-10)"
                  placeholder="7.5"
                  :rules="[
                    v => v === '' || v === null || (v >= 0 && v <= 10) || 'Ocena musi być między 0 a 10'
                  ]"
                />
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Coordinates Card (Optional) -->
        <v-card>
          <v-card-title>
            Współrzędne geograficzne
            <span class="text-caption text-medium-emphasis ml-2">(opcjonalne)</span>
          </v-card-title>

          <v-card-text>
            <v-row>
              <v-col cols="12" md="6">
                <FormField
                  v-model.number="formData.start_lat"
                  type="text"
                  label="Szerokość geograficzna startu"
                  placeholder="50.0614"
                  :rules="[
                    v => v === '' || v === null || (v >= -90 && v <= 90) || 'Wartość musi być między -90 a 90'
                  ]"
                />
              </v-col>

              <v-col cols="12" md="6">
                <FormField
                  v-model.number="formData.start_lng"
                  type="text"
                  label="Długość geograficzna startu"
                  placeholder="19.9383"
                  :rules="[
                    v => v === '' || v === null || (v >= -180 && v <= 180) || 'Wartość musi być między -180 a 180'
                  ]"
                />
              </v-col>

              <v-col cols="12" md="6">
                <FormField
                  v-model.number="formData.end_lat"
                  type="text"
                  label="Szerokość geograficzna końca"
                  placeholder="50.0614"
                  :rules="[
                    v => v === '' || v === null || (v >= -90 && v <= 90) || 'Wartość musi być między -90 a 90'
                  ]"
                />
              </v-col>

              <v-col cols="12" md="6">
                <FormField
                  v-model.number="formData.end_lng"
                  type="text"
                  label="Długość geograficzna końca"
                  placeholder="19.9383"
                  :rules="[
                    v => v === '' || v === null || (v >= -180 && v <= 180) || 'Wartość musi być między -180 a 180'
                  ]"
                />
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Actions Sidebar - Right Column -->
      <v-col cols="12" lg="4">
        <v-card>
          <v-card-title>Status i akcje</v-card-title>

          <v-card-text>
            <div class="d-flex flex-column gap-3">
              <FormField
                v-model="formData.status"
                type="select"
                label="Status"
                :options="statusOptions"
              />

              <v-btn
                type="submit"
                color="primary"
                size="large"
                block
                :loading="loading"
                prepend-icon="mdi-content-save"
              >
                {{ isEdit ? 'Zaktualizuj szlak' : 'Zapisz szlak' }}
              </v-btn>

              <v-btn
                variant="outlined"
                block
                prepend-icon="mdi-arrow-left"
                @click="$router.back()"
              >
                Anuluj
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import FormField from '@/dashboard/components/ui/FormField.vue'
import { useTrailDifficulty } from '@/dashboard/composables/useTrailDifficulty.js'
import { useTrailStatus } from '@/dashboard/composables/useTrailStatus.js'

export default {
  name: 'TrailForm',
  components: {
    FormField
  },
  setup() {
    const { getDifficultyOptions } = useTrailDifficulty()
    const { getStatusOptions } = useTrailStatus()

    return {
      difficultyOptions: getDifficultyOptions(),
      statusOptions: getStatusOptions()
    }
  },
  props: {
    trail: {
      type: Object,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['submit'],
  data() {
    return {
      formData: {
        trail_name: '',
        river_name: '',
        description: '',
        trail_length: null,
        difficulty: null,
        author: '',
        scenery: null,
        rating: null,
        status: 'draft',
        start_lat: null,
        start_lng: null,
        end_lat: null,
        end_lng: null
      }
    }
  },
  computed: {
    isEdit() {
      return !!this.trail
    }
  },
  watch: {
    trail: {
      immediate: true,
      handler(newTrail) {
        if (newTrail) {
          // Populate form with trail data for editing
          this.formData = {
            trail_name: newTrail.trail_name || '',
            river_name: newTrail.river_name || '',
            description: newTrail.description || '',
            trail_length: newTrail.trail_length || null,
            difficulty: newTrail.difficulty || null,
            author: newTrail.author || '',
            scenery: newTrail.scenery || null,
            rating: newTrail.rating || null,
            status: newTrail.status || 'draft',
            start_lat: newTrail.start_lat || null,
            start_lng: newTrail.start_lng || null,
            end_lat: newTrail.end_lat || null,
            end_lng: newTrail.end_lng || null
          }
        }
      }
    }
  },
  methods: {
    async handleSubmit() {
      const { valid } = await this.$refs.trailForm.validate()
      if (!valid) return

      // Emit form data to parent
      this.$emit('submit', this.formData)
    }
  }
}
</script>

<style scoped>
.gap-3 {
  gap: 12px;
}
</style>