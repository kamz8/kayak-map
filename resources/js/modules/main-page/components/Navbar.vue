<template>
    <v-app-bar app flat border>
        <!-- Desktop Navbar -->
        <v-toolbar-title class="hidden-sm-and-down d-flex align-center">
            <router-link to="/" class="d-flex align-center text-decoration-none">
                <v-img src="/storage/assets/logo.svg" alt="Logo" height="60" width="60" contain></v-img>
                <div class="ml-2 font-weight-bold logo-text text-black">KAYAK</div>
            </router-link>
        </v-toolbar-title>
        <v-spacer class="hidden-sm-and-down"></v-spacer>
        <v-btn text class="hidden-sm-and-down" to="/explore">
            <v-icon left class="mr-1">mdi-compass</v-icon>
            Odkrywaj
        </v-btn>
        <v-btn text class="hidden-sm-and-down">Społeczność</v-btn>
        <v-btn text class="hidden-sm-and-down">Zapisane</v-btn>
        <v-spacer class="hidden-sm-and-down"></v-spacer>
        <v-btn text class="hidden-sm-and-down">Help</v-btn>
        <v-btn color="green" dark class="hidden-sm-and-down">Pobierz app</v-btn>

        <!-- Mobile Navbar -->
        <v-toolbar-title class="hidden-md-and-up">
            <v-img src="/storage/assets/logo.svg" alt="Logo" height="60" contain></v-img>
            <span class="ml-2">KAYAK</span>
        </v-toolbar-title>
        <v-spacer class="hidden-md-and-up"></v-spacer>
        <v-btn icon class="hidden-md-and-up" @click.stop="drawer = !drawer">
            <v-icon>mdi-menu</v-icon>
        </v-btn>

        <!-- Burger Menu for Mobile -->
        <v-navigation-drawer v-model="drawer" app temporary class="hidden-md-and-up">
            <v-list>
                <v-list-item link @click="goToExplore">
                    <v-list-item-title>Odkrywaj</v-list-item-title>
                </v-list-item>
                <v-list-item @click="goToCommunity">
                    <v-list-item-title>Społeczność</v-list-item-title>
                </v-list-item>
                <v-list-item @click="goToSaved">
                    <v-list-item-title>Obszary</v-list-item-title>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>
    </v-app-bar>
</template>

<script>
export default {
    name: 'Navbar',
    data() {
        return {
            drawer: false
        };
    },
    methods: {
        goToProfile() {
            this.$router.push('/profile');
        },
        logout() {
            // Implement logout logic here
            this.$router.push('/logout');
        },
        goToExplore() {
            this.$router.push('/explore');
            this.drawer = false;
        },
        goToCommunity() {
            this.$router.push('/community');
            this.drawer = false;
        },
        goToSaved() {
            this.$router.push('/saved');
            this.drawer = false;
        }
    }
};
</script>

<style scoped>
.v-app-bar {
    box-shadow: none;
}
.logo-text{
    font-size: 20px;
}
</style>
