<template>
    <v-app>
        <component :is="layout">
            <router-view></router-view>
        </component>
    </v-app>
</template>

<script>
export default {
    name: 'App',
    computed: {
        layout() {
            const layout = this.$route.meta.layout || 'DefaultLayout';
            return () => import(`@/layouts/${layout}.vue`);
        }
    }
};
</script>

<style>
/* Możesz dodać swoje style tutaj */
</style>
