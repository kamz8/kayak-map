<template>
    <v-app>
        <navbar></navbar>
        <v-navigation-drawer app>
            <v-list dense>
                <v-list-item-group>
                    <v-list-item>
                        <v-list-item-icon>
                            <v-icon>mdi-map</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Map</v-list-item-title>
                    </v-list-item>
                    <v-list-item>
                        <v-list-item-icon>
                            <v-icon>mdi-compass</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Explore</v-list-item-title>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>
        <v-main>
            <slot></slot>
        </v-main>
    </v-app>
</template>

<script>
import Navbar from "@/modules/main-page/components/Navbar.vue";

export default {
    name: 'ExploreLayout',
    components: {Navbar},
    data() {
        return {
            menuItems: ['Opcja 1', 'Opcja 2', 'Opcja 3']
        };
    }
};
</script>

<style scoped>
.v-app-bar {
    box-shadow: none;
}
</style>
