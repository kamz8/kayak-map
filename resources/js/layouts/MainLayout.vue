<template>
    <v-app>
        <Navbar />
        <v-main>
            <slot />
        </v-main>
        <Footer />
    </v-app>
</template>

<script>
import Navbar from '@/modules/main-page/components/Navbar.vue';
import Footer from '@/modules/main-page/components/Footer.vue';

export default {
    name: 'MainLayout',
    components: {
        Navbar,
        Footer
    }
};
</script>

<style scoped>
.v-app-bar {
    box-shadow: none;
}
</style>
