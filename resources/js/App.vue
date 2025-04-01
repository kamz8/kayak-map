<template>
    <v-app>
        <v-progress-linear
            :active="loading"
            :indeterminate="true"
            color="primary"
            absolute
            height="5px"
            class="ma-0"
            style="top: 0px;"
        />
        <component :ref="layout.name" :is="layout">
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
import AuthLayout from "@/layouts/AuthLayout.vue";

import { markRaw } from 'vue'

export default {
    name: 'App',
    components: {
        AlertMessages,
    },
    data() {
      return {
          loading: false,
          layouts: [
              { name: 'MainLayout', component: markRaw(MainLayout) },
              { name: 'BasicLayout', component: markRaw(BasicLayout) },
              { name: 'ExploreLayout', component: markRaw(ExploreLayout) },
              { name: 'CallbackLayout', component: markRaw(ExploreLayout) },
              { name: 'AuthLayout', component: markRaw(AuthLayout)},
              ],
      }
    },
    mixins: [AppMixin],
    computed: {
        /*Layout system configuration*/
        layout() {
            const layoutName = this.$route.meta.layout || 'DefaultLayout'
            const layout = this.layouts.find(l => l.name === layoutName)
            return layout ? layout.component : markRaw(DefaultLayout)
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
html{
    overflow: inherit;
}
</style>
