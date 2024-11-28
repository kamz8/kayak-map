<template>
  <v-app-bar app flat border>
    <!-- Desktop Navbar -->
    <v-toolbar-title class="d-none d-md-flex align-center">
      <router-link to="/" class="d-flex align-center text-decoration-none">
        <v-img src="/storage/assets/logo.svg" alt="Logo" height="60" width="60" contain></v-img>
        <div class="ml-2 font-weight-bold logo-text text-black">KAYAK</div>
      </router-link>
    </v-toolbar-title>
    <v-spacer class="d-none d-md-block"></v-spacer>
    <v-btn text class="d-none d-md-flex" to="/explore">
      <v-icon left class="mr-1">mdi-compass</v-icon>
      Odkrywaj
    </v-btn>
    <v-btn text class="d-none d-md-flex" to="/regions">Regiony</v-btn>
    <v-btn text class="d-none d-md-flex">Zapisane</v-btn>
    <v-spacer class="d-none d-md-block"></v-spacer>
    <v-btn text class="d-none d-md-flex">Pomoc</v-btn>
    <v-btn color="green" dark class="d-none d-md-flex">Pobierz app</v-btn>

    <!-- Mobile Navbar -->
    <v-toolbar-title class="d-flex d-md-none align-center">
      <router-link to="/" class="d-flex align-center text-decoration-none">
        <v-img src="/storage/assets/logo.svg" alt="Logo" height="40" width="40" contain></v-img>
        <div class="ml-2 font-weight-bold logo-text text-black">KAYAK</div>
      </router-link>
    </v-toolbar-title>
    <v-spacer class="d-md-none"></v-spacer>
    <v-app-bar-nav-icon class="d-md-none" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
  </v-app-bar>

  <!-- Burger Menu for Mobile -->
  <v-navigation-drawer v-model="drawer" app temporary right :width="400">
    <v-list>
      <v-list-item to="/explore">
        <template v-slot:prepend>
          <v-icon>mdi-compass</v-icon>
        </template>
        <v-list-item-title>Odkrywaj</v-list-item-title>
      </v-list-item>
      <v-list-item>
        <template v-slot:prepend>
          <v-icon>mdi-map</v-icon>
        </template>
        <v-list-item-title>Regiony</v-list-item-title>
      </v-list-item>
      <v-list-item>
        <template v-slot:prepend>
          <v-icon>mdi-bookmark</v-icon>
        </template>
        <v-list-item-title>Zapisane</v-list-item-title>
      </v-list-item>
      <v-list-item>
        <template v-slot:prepend>
          <v-icon>mdi-help-circle</v-icon>
        </template>
        <v-list-item-title>Pomoc</v-list-item-title>
      </v-list-item>
      <v-list-item>
        <template v-slot:prepend>
          <v-icon>mdi-download</v-icon>
        </template>
        <v-list-item-title>Pobierz app</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
export default {
  name: 'Navbar',
  data() {
    return {
      drawer: false
    };
  }
};
</script>

<style scoped>
.v-app-bar {
  box-shadow: none;
}
.logo-text {
  font-size: 20px;
}
</style>
