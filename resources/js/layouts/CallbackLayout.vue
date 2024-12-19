<template>
    <app>
        <main>
            <slot></slot>
        </main>
    </app>
</template>

<script>
import Navbar from '@/modules/main-page/components/Navbar.vue';
import Footer from '@/modules/main-page/components/Footer.vue';
import App from "@/App.vue";

export default {
    name: 'CallbackLayout',
    components: {
        App,
        Navbar,
        Footer
    }
};
</script>

<style>
/* Możesz dodać swoje style tutaj */
</style>
