<template>
    <v-app>
        <component :is="layout">
            <router-view />
        </component>
    </v-app>
</template>

<script>
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import MainLayout from '@/layouts/MainLayout.vue';
import BasicLayout from '@/layouts/BasicLayout.vue';
import ExploreLayout from '@/layouts/ExploreLayout.vue';

export default {
    name: 'App',
    computed: {
        layout() {
            const layout = this.$route.meta.layout || 'DefaultLayout';
            switch (layout) {
                case 'MainLayout':
                    return MainLayout;
                case 'BasicLayout':
                    return BasicLayout;
                case 'ExploreLayout':
                    return ExploreLayout;
                default:
                    return DefaultLayout;
            }
        }
    }
};
</script>

<style>
/* Możesz dodać swoje style tutaj */
</style>
