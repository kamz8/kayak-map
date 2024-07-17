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
        <v-main>
            <slot></slot>
        </v-main>
        <v-footer app color="dark-green" class="white--text">
            <v-container>
                <v-row>
                    <v-col>
                        <h3>Footer Column 1</h3>
                        <p>Link 1</p>
                        <p>Link 2</p>
                    </v-col>
                    <v-col>
                        <h3>Footer Column 2</h3>
                        <p>Link 3</p>
                        <p>Link 4</p>
                    </v-col>
                    <v-col>
                        <h3>Footer Column 3</h3>
                        <p>Link 5</p>
                        <p>Link 6</p>
                    </v-col>
                </v-row>
            </v-container>
        </v-footer>
    </v-app>
</template>

<script>
export default {
    name: 'BasicLayout',
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
.dark-green {
    background-color: #006400;
}
</style>
