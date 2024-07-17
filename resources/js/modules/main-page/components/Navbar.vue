<template>
    <v-app-bar app flat color="white">
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
                <v-list-item v-for="(item, index) in menuItems" :key="index" @click="">
                    <v-list-item-title>{{ item }}</v-list-item-title>
                </v-list-item>
            </v-list>
        </v-menu>
    </v-app-bar>
</template>

<script>
export default {
    name: 'Navbar',
    data() {
        return {
            menuItems: ['Opcja 1', 'Opcja 2', 'Opcja 3']
        };
    }
};
</script>

<style>
.v-app-bar {
    box-shadow: none;
}
</style>
