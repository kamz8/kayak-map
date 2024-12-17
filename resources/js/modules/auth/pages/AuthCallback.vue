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
import { mapActions } from 'vuex';

export default {
    name: 'AuthCallback',
    async created() {
        try {
            await this.handleAuthCallback({
                provider: this.$route.params.provider,
                code: this.$route.query.code
            });
        } catch (error) {
            this.$alertError('Błąd podczas logowania. Spróbuj ponownie.');
            this.$router.push('/login');
        }
    },
    methods: {
        ...mapActions('auth', ['handleAuthCallback'])
    }
}
</script>
