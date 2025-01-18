<template>
    <v-app-bar app flat border
       :fixed="true"
       style="position: fixed"
    >
        <!-- Desktop Navbar -->
        <v-toolbar-title class="d-none d-md-flex align-center">
            <router-link to="/" class="d-flex align-center text-decoration-none">
                <v-img src="/storage/assets/logo.svg" alt="Logo" height="60" width="60" contain></v-img>
                <div class="ml-2 font-weight-bold logo-text text-black">WARTKI NURT</div>
            </router-link>
        </v-toolbar-title>
<!--        <v-spacer class="d-none d-md-block"></v-spacer>-->
        <v-btn v-for="link in visibleDesktopLinks" :key="link.text" text class="d-none d-md-flex" :to="link.url">
            <v-icon v-if="link.icon" left class="mr-1">{{ link.icon }}</v-icon>
            {{ link.text }}
        </v-btn>
        <v-spacer class="d-none d-md-block"></v-spacer>
        <v-btn v-for="action in visibleDesktopActions" :key="action.text" :color="action.color" dark class="d-none d-md-flex">
            {{ action.text }}
        </v-btn>

        <!-- Mobile Navbar -->
        <v-toolbar-title class="d-flex d-md-none align-center">
            <router-link to="/" class="d-flex align-center text-decoration-none">
                <v-img src="/storage/assets/logo.svg" alt="Logo" height="40" width="40" contain></v-img>
                <div class="ml-2 font-weight-bold logo-text text-black">WartkiNurt</div>
            </router-link>
        </v-toolbar-title>
        <v-spacer class="d-md-none"></v-spacer>
        <v-app-bar-nav-icon class="d-md-none" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
    </v-app-bar>

    <!-- Burger Menu for Mobile -->
    <v-navigation-drawer v-model="drawer" app temporary right :width="400">
        <v-list>
            <v-list-item v-for="link in visibleMobileLinks" :key="link.text" :to="link.url">
                <template v-slot:prepend>
                    <v-icon>{{ link.icon }}</v-icon>
                </template>
                <v-list-item-title>{{ link.text }}</v-list-item-title>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>

<script>
export default {
    name: 'Navbar',
    data() {
        return {
            drawer: false,
            desktopLinks: [
                { text: 'Odkrywaj', url: '/explore', icon: 'mdi-compass' },
                { text: 'Regiony', url: '/regions' },
                { text: 'Zapisane', url: '/saved',hide: true  },
            ],
            desktopActions: [
                { text: 'Pomoc', url: '/help',hide: true },
                { text: 'Pobierz app', color: 'green',hide: true },
                { text: 'Zaloguj',url:'/login',color:'river-blue', hide: process.env.NODE_ENV !== 'development' },
            ],
            mobileLinks: [
                { text: 'Odkrywaj', url: '/explore', icon: 'mdi-compass' },
                { text: 'Regiony', url: '/regions', icon: 'mdi-map' },
                { text: 'Zapisane', url: '/saved', icon: 'mdi-bookmark',hide: true },
                { text: 'Pomoc', url: '/help', icon: 'mdi-help-circle',hide:true },
                { text: 'Pobierz app', url: '/download', icon: 'mdi-download',hide:true },
                { text: 'Zaloguj',url:'/login',color:'river-blue', hide: process.env.NODE_ENV !== 'development' },
            ],
        };
    },
    computed: {
        visibleDesktopLinks() {
            return this.desktopLinks.filter(link => !link.hide);
        },
        visibleDesktopActions() {
            return this.desktopActions.filter(action => !action.hide);
        },
        visibleMobileLinks() {
            return this.mobileLinks.filter(link => !link.hide);
        },
    },
};
</script>

<style scoped>
.v-app-bar {
    box-shadow: none;
}
.logo-text {
    font-size: 20px;
}

.sticky-navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
}
@media (max-width: 599px) {
    .logo-text {
        font-size: 14px;
    }
}

@media (min-width: 600px) and (max-width: 959px) {
    .logo-text {
        font-size: 16px;
    }
}

@media (min-width: 960px) {
    .logo-text {
        font-size: 20px;
    }
}
</style>
