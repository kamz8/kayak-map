<template>
    <v-app>
        <Navbar />
        <v-main>
            <slot />
        </v-main>
        <Footer class="v-footer" />
    </v-app>
</template>

<script>
import Navbar from '@/modules/main-page/components/Navbar.vue';
import Footer from '@/modules/main-page/components/Footer.vue';
import appMixin from "@/mixins/AppMixin.js";

export default {
    name: 'MainLayout',
    computed: {
        appMixin() {
            return appMixin
        }
    },
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
/*.v-footer {
    margin-top: auto; !* Footer będzie się trzymał na dole *!
}*/

</style>
