<template>
    <div>
        <Navbar />
        <main>
            <slot></slot>
        </main>
        <Footer />
    </div>
</template>

<script>
import Navbar from '@/modules/main-page/components/Navbar.vue';
import Footer from '@/modules/main-page/components/Footer.vue';

export default {
    name: 'DefaultLayout',
    components: {
        Navbar,
        Footer
    }
};
</script>

<style>
/* Możesz dodać swoje style tutaj */
</style>
