<template>
    <v-app>
        <v-container fluid class="d-flex flex-column pa-0 full-height">
            <!-- Navbar -->
            <Navbar />

            <!-- Toolbar for search and filters -->
            <v-app-bar>
                <router-view name="toolbar" />
            </v-app-bar>

            <v-row class="d-flex flex-grow-1">
                <!-- Sidebar Panel -->
                <v-navigation-drawer v-model="drawer" app clipped width="350" elevation="2" class="d-flex flex-column" >
                    <v-list-item title="Wybrane trasy" subtitle="dostępne w okolicy" color="teal-darken-3"></v-list-item>
                    <router-view name="sidebar"></router-view>
                    <v-btn
                        icon
                        @click="toggleDrawer"
                        class="drawer-toggle-btn"
                        size="s"
                    >
                        <v-icon>{{ drawer ? 'mdi-chevron-left' : 'mdi-chevron-right' }}</v-icon>
                    </v-btn>
                </v-navigation-drawer>

                <!-- Main Content -->
                <v-main class="flex-grow-1">
                    <v-container fluid class="pa-0 d-flex flex-column" style="height: calc(100vh - 64px - 56px);">
                        <router-view name="main"></router-view>
                    </v-container>
                </v-main>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
import Navbar from '@/modules/main-page/components/Navbar.vue'

export default {
    components: {
        Navbar,
    },
    data() {
        return {
            drawer: true,
        }
    },
    methods: {
        toggleDrawer() {
            this.drawer = !this.drawer;
            this.$nextTick(() => {
                if (this.$refs.map) {
                    this.$refs.map.mapObject.invalidateSize();
                }
            });
        },
    },
};
</script>

<style scoped>
.full-height {
    height: 100vh;
}

.drawer-toggle-btn {
    position: absolute;
    top: 10px;
    right: -20px;
    z-index: 1000;
    background-color: white;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    border-bottom-left-radius: 0;
    border-top-left-radius: 0;
}
</style>
