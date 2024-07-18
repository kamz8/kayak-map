<template>
    <v-app>
        <Navbar />
        <v-navigation-drawer v-model="drawer" app>
            <router-view />
        </v-navigation-drawer>
        <v-main>
            <v-btn icon @click="drawer = !drawer" class="toggle-drawer">
                <v-icon>mdi-menu</v-icon>
            </v-btn>
            <div class="main-content">
                <Map />

            </div>
        </v-main>
    </v-app>
</template>

<script>
import Navbar from "@/modules/main-page/components/Navbar.vue";
import Map from "@/modules/trails/components/Map.vue";

export default {
    name: "ExploreLayout",
    components: { Navbar, Map },
    data() {
        return {
            drawer: true,
        };
    },
    methods: {
        navigateTo(route) {
            this.$router.push(route);
            this.drawer = false; // Zamknij drawer po nawigacji
        },
    },
};
</script>

<style scoped>
.v-app-bar {
    box-shadow: none;
}

.toggle-drawer {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1000;
}

.main-content {
    display: flex;
    height: 100vh;
}
</style>
