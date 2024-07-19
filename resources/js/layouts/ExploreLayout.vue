<template>
    <v-app>
        <v-layout full-height>
            <!-- Navbar -->
            <Navbar />

            <!-- Toolbar for search and filters -->
            <v-app-bar>
                <v-container>
                    <v-row>
                        <slot name="search-bar"></slot>
                    </v-row>
                </v-container>
            </v-app-bar>
            <v-main>
                <v-container fluid>
                    <v-row>
                        <!-- Sidebar Panel -->
                        <v-col :cols="drawer ? 4 : 0">
                            <v-navigation-drawer v-model="drawer" app clipped permanent>
                                <v-list-item title="Wybrane trasy" subtitle="dostępne w okolicy" color="teal-darken-3" ></v-list-item>
                                <router-view name="sidebar"></router-view>
                            </v-navigation-drawer>
                        </v-col>
                        <!-- Main Content -->
                        <v-col :cols="drawer ? 8 : 10">
                            <router-view name="main"></router-view>
                        </v-col>
                    </v-row>
                </v-container>
            </v-main>
        </v-layout>

    </v-app>
</template>

<script>
import Navbar from '@/modules/main-page/components/Navbar.vue';
import Footer from "@/modules/main-page/components/Footer.vue";

export default {
    components: {
        Footer,
        Navbar,
    },
    data() {
        return {
            drawer: true,
        };
    },
    methods: {
        toggleDrawer() {
            this.drawer = !this.drawer;
        },
    },
};
</script>

<style scoped>
/* Dodaj stylizacje, jeśli potrzebne */
</style>
