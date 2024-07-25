<template>
    <v-app>
        <component :is="layout">
            <router-view />
        </component>
        <AlertMessages />
    </v-app>
</template>

<script>
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import MainLayout from '@/layouts/MainLayout.vue';
import BasicLayout from '@/layouts/BasicLayout.vue';
import ExploreLayout from '@/layouts/ExploreLayout.vue';
import AlertMessages from "@/modules/system-messages/components/AlertMessages.vue";

export default {
    name: 'App',
    components: {
        AlertMessages,
    },
    computed: {
        layout() {
            const layout = this.$route.meta.layout || 'DefaultLayout'
            switch (layout) {
                case 'MainLayout':
                    return MainLayout
                case 'BasicLayout':
                    return BasicLayout
                case 'ExploreLayout':
                    return ExploreLayout
                default:
                    return DefaultLayout
            }
        }
    }
};
</script>

<style>

</style>
