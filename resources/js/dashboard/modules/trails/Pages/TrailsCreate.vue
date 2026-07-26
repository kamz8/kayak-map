<template>
  <div class="trails-create">
    <TrailForm
      :loading="loading"
      @submit="handleSubmit"
    />
  </div>
</template>

<script>
import TrailForm from '../components/TrailForm.vue'
import { mapActions } from 'vuex'
import apiClient from '@/dashboard/plugins/axios.js'

export default {
  name: 'DashboardTrailsCreate',
  components: {
    TrailForm
  },
  data() {
    return {
      loading: false
    }
  },
  methods: {
    ...mapActions('ui', ['showSuccess', 'showError']),

    async handleSubmit(formData) {
      this.loading = true
      try {
        const response = await apiClient.post('/dashboard/trails', formData)

        this.showSuccess('Szlak został utworzony pomyślnie!')
        this.$router.push('/dashboard/trails')
      } catch (error) {
        console.error('Failed to create trail:', error)

        if (error.response?.data?.errors) {
          // Show validation errors
          const errors = Object.values(error.response.data.errors).flat()
          this.showError(errors.join(', '))
        } else {
          this.showError('Błąd podczas tworzenia szlaku')
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.trails-create {
  width: 100%;
}
</style>