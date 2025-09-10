<template>
  <div class="trails-create">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-h4 font-weight-bold mb-2">Dodaj nowy szlak</h1>
      <p class="text-body-1 text-medium-emphasis">
        Utwórz nowy szlak kajakowy w systemie
      </p>
    </div>

    <v-form ref="trailForm" @submit.prevent="handleSubmit">
      <v-row>
        <!-- Main Form -->
        <v-col cols="12" lg="8">
          <v-card class="mb-6">
            <v-card-title>Podstawowe informacje</v-card-title>

            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <FormField
                    v-model="form.trail_name"
                    label="Nazwa szlaku"
                    placeholder="np. Wisła - Kraków do Tynca"
                    required
                    :rules="[v => !!v || 'Nazwa szlaku jest wymagana']"
                  />
                </v-col>

                <v-col cols="12" md="6">
                  <FormField
                    v-model="form.river_name"
                    label="Nazwa rzeki"
                    placeholder="np. Wisła"
                    required
                    :rules="[v => !!v || 'Nazwa rzeki jest wymagana']"
                  />
                </v-col>

                <v-col cols="12">
                  <FormField
                    v-model="form.description"
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

          <!-- Technical Details -->
          <v-card>
            <v-card-title>Parametry techniczne</v-card-title>

            <v-card-text>
              <v-row>
                <v-col cols="12" md="4">
                  <FormField
                    v-model.number="form.trail_length"
                    type="number"
                    label="Długość szlaku (km)"
                    placeholder="12.5"
                    required
                    :rules="[v => !!v || 'Długość jest wymagana', v => v > 0 || 'Długość musi być większa od 0']"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <FormField
                    v-model="form.difficulty"
                    type="select"
                    label="Trudność"
                    :options="difficultyOptions"
                    required
                    :rules="[v => !!v || 'Wybierz poziom trudności']"
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <FormField
                    v-model="form.author"
                    label="Autor szlaku"
                    placeholder="Jan Kowalski"
                  />
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Actions Sidebar -->
        <v-col cols="12" lg="4">
          <v-card>
            <v-card-title>Akcje</v-card-title>

            <v-card-text>
              <div class="d-flex flex-column gap-3">
                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  :loading="loading"
                  prepend-icon="mdi-content-save"
                >
                  Zapisz szlak
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
  </div>
</template>

<script>
import FormField from '@/dashboard/components/ui/FormField.vue'
import { mapActions } from 'vuex'

export default {
  name: 'DashboardTrailsCreate',
  components: {
    FormField
  },
  data() {
    return {
      loading: false,
      form: {
        trail_name: '',
        river_name: '',
        description: '',
        trail_length: null,
        difficulty: null,
        author: ''
      },
      difficultyOptions: [
        { title: 'Łatwy', value: 'łatwy' },
        { title: 'Umiarkowany', value: 'umiarkowany' },
        { title: 'Trudny', value: 'trudny' },
        { title: 'Ekspertowy', value: 'ekspertowy' }
      ]
    }
  },
  methods: {
    ...mapActions('ui', ['showSuccess', 'showError']),

    async handleSubmit() {
      const { valid } = await this.$refs.trailForm.validate()
      if (!valid) return

      this.loading = true
      try {
        // Mock API call
        await new Promise(resolve => setTimeout(resolve, 1000))

        this.showSuccess('Szlak został utworzony pomyślnie!')
        this.$router.push('/dashboard/trails')
      } catch (error) {
        this.showError('Błąd podczas tworzenia szlaku')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
