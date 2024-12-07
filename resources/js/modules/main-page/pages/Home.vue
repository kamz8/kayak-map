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
  <section-explore></section-explore>
  <section-three/>
</template>

<script>
import SectionOne from "@/modules/main-page/components/sections/SectionOne.vue";
import SectionThree from "@/modules/main-page/components/sections/SectionThree.vue";
import HeroSection from "@/modules/main-page/components/Hero.vue";
import NearbyTrailCard from "@/modules/main-page/components/NearbyTrailCard.vue";
import axios from 'axios';
import SectionExplore from "@/modules/main-page/components/sections/SectionExplore.vue";
import apiClient from "@/plugins/apiClient.js";

export default {
  name: 'Home',
  components: {
    SectionExplore,
    SectionThree,
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

          // Użycie getCacheWithTTL zamiast get
          const storedLocation = await this.$cache.getCacheWithTTL('locationData');

          if (storedLocation && storedLocation.lat && storedLocation.long) {
              this.city = storedLocation.city;
              this.coordinates.lat = storedLocation.lat;
              this.coordinates.long = storedLocation.long;

              // Pobranie szlaków na podstawie zapisanej lokalizacji
              await this.fetchTrailsNearby(storedLocation.lat, storedLocation.long, storedLocation.city);
          } else {
              // Jeśli brak danych w cache lub TTL wygasł, pobierz geolokalizację
              await this.getGeolocation();
          }
      },

      async getGeolocation() {
          try {
              if (navigator.geolocation) {
                  navigator.geolocation.getCurrentPosition(async (position) => {
                      const lat = position.coords.latitude;
                      const long = position.coords.longitude;

                      await this.reverseGeocode(lat, long);

                      // Zapisanie lokalizacji do cache z TTL 24 godziny (86400 sekund)
                      const locationData = { city: this.city, lat: lat, long: long };
                      this.$cache.setCacheWithTTL('locationData', locationData, 86400);
                  }, () => {
                      this.$alertError('Nie udało się uzyskać lokalizacji.');
                  });
              } else {
                  this.$alertWarning('Geolokalizacja nie jest wspierana w tej przeglądarce.');
              }
          } catch (error) {
              this.$alertError('Błąd podczas uzyskiwania geolokalizacji.');
          }
      },

      async reverseGeocode(lat, long) {
          try {
              const response = await axios.get(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${long}`);

              // Sprawdzenie, czy API zwróciło poprawne dane
              if (response.data && response.data.address) {
                  this.city = response.data.address.city || 'Nieznane miasto';

                  const locationData = { city: this.city, lat: lat, long: long };
                  this.$cache.setCacheWithTTL('locationData', locationData, 86400);

                  await this.fetchTrailsNearby(lat, long, this.city);
              } else {
                  throw new Error('Nie można uzyskać adresu z API.');
              }

          } catch (error) {
              console.error('Błąd podczas reverse geocoding:', error.response || error.message);
              this.$alertError('Nie udało się uzyskać nazwy lokalizacji.');
          }
      },

      async fetchTrailsNearby(lat, long, locationName) {

          try {
              // Zapisz wyniki w cache z TTL 24 godziny (86400 sekund)
              this.trails = await this.$cache.remember(`trailsNearby_${locationName}`, 86400,
                  async () => {
                      const response = await apiClient.get(`/api/v1/trails/nearby`, {
                          params: { lat, long, location_name: locationName },
                      });
                      return response.data.data;
                  }
                  , ['trails']);
          } catch (error) {
              this.$alertError('Nie udało się pobrać lokalnych szlaków.');
          }
      },

      async loadDefaultTrails() {
          this.isLoading = true;

          const fetchFunction = async () => {
              const response = await axios.get(`/api/v1/trails/nearby?location_name=Polska`);
              return response.data.data;
          };

          try {
              // Używamy cache dla szlaków domyślnych z TTL 24 godziny (86400 sekund)
              this.trails = await this.$cache.remember('defaultTrails_Poland', 86400, fetchFunction, ['trails']);
          } catch (error) {
              this.$alertError('Nie udało się pobrać tras dla Polski.');
          } finally {
              this.isLoading = false;
          }
      }

  }
};

</script>

<style scoped>

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
