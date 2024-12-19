<template>
  <v-container fluid class="mt-5 mx-0" style="min-height: 500px">
    <v-row>
      <v-container>
        <v-row class="mt-5 mb-4">

          <v-col cols="12">
            <h2>Zobacz wszystkie regiony dostępne w naszej aplikacji </h2>
          </v-col>
        </v-row>

      </v-container>
    </v-row>
  </v-container>

</template>

<script>
import SectionOne from "@/modules/main-page/components/sections/SectionOne.vue";
import SectionTwo from "@/modules/main-page/components/sections/SectionExplore.vue";
import SectionThree from "@/modules/main-page/components/sections/SectionThree.vue";
import HeroSection from "@/modules/main-page/components/Hero.vue";
import NearbyTrailCard from "@/modules/main-page/components/NearbyTrailCard.vue";

export default {
  name: 'Home',
  components: {
  },
  data() {
    return {

    };
  },

};

</script>

<style scoped>
.scrolling-wrapper {
  display: flex;
  overflow-y: auto;
  max-height: 100%;
  flex-wrap: nowrap;
}

.trail-col {
  flex: 0 0 auto;
  width: 25%; /* Ustaw 25%, aby ograniczyć widoczność maksymalnie 4 kolumn jednocześnie */
}

@media (max-width: 1200px) {
  .trail-col {
    width: 33.33%; /* Na mniejszych ekranach możesz dostosować szerokość */
  }
}

@media (max-width: 960px) {
  .trail-col {
    width: 50%;
  }
}

@media (max-width: 600px) {
  .trail-col {
    width: 100%;
  }
}

.skeleton-image {
  height: 200px; /* Ustaw wysokość obrazu podobnie jak w pełnej karcie */
}

.skeleton-title {
  height: 24px;
  width: 70%;
}

.skeleton-subtitle {
  height: 18px;
  width: 50%;
}
</style>
