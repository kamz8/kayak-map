<template>
    <v-app>
        <v-app-bar app flat>
            <v-toolbar-title>
                <img src="/path-to-your-logo.png" alt="Logo" height="40">
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn text to="/explore">
                <v-icon left>mdi-kayak</v-icon>
                Odkrywaj
            </v-btn>
            <v-menu>
                <template v-slot:activator="{ on, attrs }">
                    <v-btn text v-bind="attrs" v-on="on">Menu</v-btn>
                </template>
                <v-list>
                    <v-list-item v-for="(item, index) in menuItems" :key="index">
                        <v-list-item-title>{{ item }}</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
        </v-app-bar>
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
export default {
    name: 'ExploreLayout',
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
