<template>
  <router-view />
</template>

<script>
export default {
  name: 'DashboardApp',

  mounted() {
    // Listen for session expiration events
    window.addEventListener('session-expired', this.handleSessionExpired)
  },

  beforeUnmount() {
    window.removeEventListener('session-expired', this.handleSessionExpired)
  },

  methods: {
    handleSessionExpired(event) {
      const message = event.detail?.message || 'Twoja sesja wygasła. Zaloguj się ponownie.'

      // Show error notification
      this.$store.dispatch('ui/showError', message)
    }
  }
}
</script>