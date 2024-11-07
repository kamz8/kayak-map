<template>
    <v-app>
        <v-progress-linear
            :active="loading || pageLoading"
            :indeterminate="true"
            color="primary"
            absolute
            height="10px"
        />
        <component :is="layout">
            <Suspense>
                <template #default>
                    <router-view v-slot="{ Component }">
                        <keep-alive>
                            <component :is="Component" />
                        </keep-alive>
                    </router-view>
                </template>
                <template #fallback>
                    <v-skeleton-loader type="article" />
                </template>
            </Suspense>
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
import AppMixin from "@/mixins/AppMixin.js";

export default {
    name: 'App',
    components: {
        AlertMessages,
    },
    data() {
      return {
          loading: false
      }
    },
    mixins: [AppMixin],
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
    },
    created() {
        this.$router.beforeEach((to, from, next) => {
            this.loading = true
            next()
        })
        this.$router.afterEach(() => {
            this.loading = false
        })
    }
};
</script>

<style>

</style>
