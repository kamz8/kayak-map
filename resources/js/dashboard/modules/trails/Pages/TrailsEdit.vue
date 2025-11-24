<template>
  <div class="trails-edit">
    <TrailForm
      v-if="trail"
      :trail="trail"
      :loading="loading"
      @submit="handleSubmit"
    />
    <div v-else-if="loadingTrail" class="d-flex justify-center align-center" style="min-height: 400px;">
      <v-progress-circular indeterminate color="primary" size="64" />
    </div>
    <div v-else class="text-center pa-8">
      <v-icon size="64" color="error">mdi-alert-circle</v-icon>
      <h3 class="mt-4">Nie znaleziono szlaku</h3>
      <v-btn class="mt-4" @click="$router.push('/dashboard/trails')">
        Wróć do listy
      </v-btn>
    </div>
  </div>
</template>

<script>
import TrailForm from '../components/TrailForm.vue'
import { mapActions } from 'vuex'
import apiClient from '@/dashboard/plugins/axios.js'

export default {
  name: 'DashboardTrailsEdit',
  components: {
    TrailForm
  },
  data() {
    return {
      trail: null,
      loading: false,
      loadingTrail: true
    }
  },
  async created() {
    await this.fetchTrail()
  },
  methods: {
    ...mapActions('ui', ['showSuccess', 'showError']),

    async fetchTrail() {
      this.loadingTrail = true
      try {
        const trailId = this.$route.params.id
        const response = await apiClient.get(`/dashboard/trails/${trailId}`)

        this.trail = response.data.data
      } catch (error) {
        console.error('Failed to fetch trail:', error)

        if (error.response?.status === 404) {
          this.showError('Szlak nie został znaleziony')
        } else {
          this.showError('Błąd podczas pobierania szlaku')
        }
      } finally {
        this.loadingTrail = false
      }
    },

    async handleSubmit(formData) {
      this.loading = true
      try {
        const trailId = this.$route.params.id
        const response = await apiClient.put(`/dashboard/trails/${trailId}`, formData)

        this.showSuccess('Szlak został zaktualizowany pomyślnie!')
        this.$router.push('/dashboard/trails')
      } catch (error) {
        console.error('Failed to update trail:', error)

        if (error.response?.data?.errors) {
          // Show validation errors
          const errors = Object.values(error.response.data.errors).flat()
          this.showError(errors.join(', '))
        } else {
          this.showError('Błąd podczas aktualizacji szlaku')
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.trails-edit {
  width: 100%;
}
</style>