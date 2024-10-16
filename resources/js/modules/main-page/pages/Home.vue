<template>
  <HeroSection :city="city" :coordinates="coordinates"/>
  <v-container fluid class="mt-5 mx-0">
    <v-row>
      <v-container>
        <v-row class="mt-5 mb-4">

          <v-col cols="12">
            <h2>Lokalne szlaki w pobliżu {{ city }}</h2>
          </v-col>
        </v-row>

        <v-row>
          <!-- Szkielet ładowania -->
          <v-col
              v-if="isLoading"
              v-for="n in 4"
              :key="n"
              cols="12"
              sm="6"
              md="4"
              lg="3"
          >
            <v-card height="400">
              <!-- Szkielet obrazu -->
              <v-skeleton-loader boilerplate type="image" class="skeleton-image" />

              <!-- Szkielet zawartości karty -->
              <v-card-text>
                <!-- Szkielet tytułu -->
                <v-skeleton-loader boilerplate type="text" class="skeleton-title mb-2" />

                <!-- Szkielet opisu -->
                <v-skeleton-loader boilerplate type="text" class="skeleton-subtitle mb-2" />

                <!-- Szkielet gwiazdek (przykład za pomocą ikon) -->
                <div class="d-flex mb-2">
                  <v-skeleton-loader boilerplate type="text" width="120px" />
                </div>

                <!-- Szkielet dodatkowych informacji -->
                <v-skeleton-loader boilerplate type="text" width="80px" />
              </v-card-text>
            </v-card>
          </v-col>
          <v-col
              v-else
              v-for="trail in limitedTrails"
              :key="trail.id"
              cols="12"
              sm="6"
              md="4"
              lg="3"
              class="trail-col"
          >
            <nearby-trail-card :trail="trail" :appConfig="appConfig" />
          </v-col>
        </v-row>

      </v-container>
    </v-row>
  </v-container>

  <section-one></section-one>
  <section-two></section-two>
  <section-three/>
</template>

<script>
import SectionOne from "@/modules/main-page/components/sections/SectionOne.vue";
import SectionTwo from "@/modules/main-page/components/sections/SectionTwo.vue";
import SectionThree from "@/modules/main-page/components/sections/SectionThree.vue";
import HeroSection from "@/modules/main-page/components/Hero.vue";
import NearbyTrailCard from "@/modules/main-page/components/NearbyTrailCard.vue";
import axios from 'axios';

export default {
  name: 'Home',
  components: {
    SectionThree,
    SectionTwo,
    SectionOne,
    NearbyTrailCard,
    HeroSection,
  },
  data() {
    return {
      trails: [],
      city: 'Polska',
      error: '',
      coordinates: {
        lat: 52.237049,  // Domyślne współrzędne dla centrum Polski (Warszawa)
        long: 21.017532,
      },
      locationAttempted: false,
      isLoading: true,
    };
  },
  mounted() {
    this.loadDefaultTrails(); // Domyślnie ładujemy szlaki dla Polski
    this.initializeLocation(); // Próbujemy pobrać lokalizację użytkownika, jeśli jest dostępna
  },
  computed: {
    limitedTrails() {
      return this.trails.slice(0, 4);
    }
  },
  methods: {
    async initializeLocation() {
      if (this.locationAttempted) return;

      this.locationAttempted = true;
      const storedLocation = JSON.parse(localStorage.getItem('locationData'));

      if (storedLocation && storedLocation.lat && storedLocation.long) {
        this.city = storedLocation.city;
        this.coordinates.lat = storedLocation.lat;
        this.coordinates.long = storedLocation.long;
        await this.fetchTrailsNearby(storedLocation.lat, storedLocation.long, storedLocation.city);
      } else {
        await this.getGeolocation();
      }
    },
    async getGeolocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async (position) => {
              this.coordinates.lat = position.coords.latitude;
              this.coordinates.long = position.coords.longitude;
              await this.reverseGeocode(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
              this.$alertError('Nie udało się uzyskać lokalizacji.');
            }
        );
      } else {
        this.$alertError('Geolokalizacja nie jest wspierana przez tę przeglądarkę.');
      }
    },
    async reverseGeocode(lat, long) {
      try {
        const response = await axios.get(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${long}`);
        this.city = response.data.address.city || 'Nieznane miasto';
        const storedLocation = { city: this.city, lat: lat, long: long };
        localStorage.setItem('locationData', JSON.stringify(storedLocation));
        await this.fetchTrailsNearby(lat, long, this.city);
      } catch (error) {
        this.$alertError('Nie udało się uzyskać nazwy lokalizacji.');
      }
    },
    async fetchTrailsNearby(lat, long, locationName) {
      try {
        const response = await axios.get(`/api/v1/trails/nearby`, {
          params: { lat, long, location_name: locationName },
        });
        this.trails = response.data.data;
      } catch (error) {
        this.$alertError('Nie udało się pobrać lokalnych szlaków.');
      }
    },
    async loadDefaultTrails() {
      this.isLoading = true;
      try {
        const response = await axios.get(`/api/v1/trails/nearby?location_name=Polska`);
        this.isLoading = false;
        this.trails = response.data.data;
      } catch (error) {
        this.$alertError('Nie udało się pobrać tras dla Polski.');
      }
    }
  }
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
