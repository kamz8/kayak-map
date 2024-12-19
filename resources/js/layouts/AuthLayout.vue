<!-- layouts/AuthLayout.vue -->
<template>
  <MainLayout>
    <v-container class="auth-wrapper ma-0 pa-0" fill-height fluid>
      <!-- Background image grid -->
      <div class="background-grid">
        <v-img
            v-for="(image, index) in backgroundImages"
            :key="index"
            :src="`login_img/login${index + 1}.jpg`"
            cover
            height="100%"
            class="background-image"
        >
          <div class="image-overlay"></div>
        </v-img>
      </div>

      <v-container fluid class="auth-container h-100 ma-0 pa-0" fill-height>
        <v-row justify="center" align="center">
          <v-col cols="12" sm="8" md="6" lg="4">
            <v-card
                class="mx-auto px-6 py-8"
                elevation="8"
                rounded="xl"
                color="white"
            >
              <slot></slot>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-container>
  </MainLayout>
</template>

<script>
import MainLayout from '@/layouts/MainLayout.vue'

export default {
  name: 'AuthLayout',
  components: {
    MainLayout
  },
  data() {
    return {
      backgroundImages: [1, 2, 3]
    }
  }
}
</script>

<style scoped>
.auth-wrapper {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: rgb(246, 246, 246);
  font-family: "Inter", "Poppins", sans-serif;
}

.background-grid {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0;
  z-index: 0;
}

.background-image {
  position: relative;
}

.image-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
}

.auth-container {
  position: relative;
  z-index: 1;
  flex: 1 0 auto;
  display: flex;
  justify-content: center;
  min-height: 100vh;
}

:deep(.v-btn) {
  text-transform: none;
  letter-spacing: normal;
}

:deep(.v-field__outline__start),
:deep(.v-field__outline__end) {
  border-color: rgba(0, 0, 0, 0.15) !important;
}

@media (max-width: 600px) {
  .background-grid {
    display: none;
  }
}
</style>
