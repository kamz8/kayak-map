<!-- AuthCallback.vue -->
<template>
  <div class="flex min-h-screen items-center justify-center">
    <v-progress-circular
        indeterminate
        color="primary"
        size="64"
    ></v-progress-circular>
  </div>
</template>

<script>
export default {
  name: 'AuthCallback',
  mounted() {
    // Pobieramy parametry z URL
    const code = this.$route.query.code;
    const error = this.$route.query.error;
    const errorDescription = this.$route.query.error_description;

    // Jeśli jest błąd od providera
    if (error) {
      window.opener.postMessage({
        error: errorDescription || error
      }, window.location.origin);
      window.close();
      return;
    }

    // Jeśli jest kod autoryzacji
    if (code) {
      window.opener.postMessage({
        code,
        headers: {
          'X-Client-Type': 'web'
        }
      }, window.location.origin);
    }

    // W każdym innym przypadku
    window.close();
  }
}
</script>
