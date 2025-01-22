<template>
  <v-container fluid class="pa-0 mx-0" style="min-height: 500px">

          <hero-section
              :stats="stats"
              :countries="stats.countries"
              @select-country="selectCountry"
          />

          <info-section
              :countries="stats.countries"
          />

          <regions-section
              v-if="selectedCountry"
              :country="selectedCountry"
              @clear-selection="selectedCountry = null"
          />

  </v-container>

</template>

<script>
import HeroSection from '../components/HeroSection.vue'
import InfoSection from '../components/InfoSection.vue'
import RegionsSection from '../components/RegionSection.vue'
import axios from 'axios';

export default {
    name: 'RegionsPage',

    components: {
        HeroSection,
        InfoSection,
        RegionsSection
    },

    data() {
        return {
            selectedCountry: null,
            stats: {
                totalTrails: 169,
                totalRegions: 45,
                countries: [
                    {
                        id: 'lt',
                        name: "Litwa",
                        trailsCount: 256,
                        regionsCount: 18,
                        nationalParks: 5,
                        image: "https://images.pexels.com/photos/1315892/pexels-photo-1315892.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1",
                        description: "Odkryj malownicze szlaki przez litewskie jeziora i lasy",
                        color: '#1c7a19',
                        regions: [
                            {
                                id: 1,
                                name: "Park Narodowy Auksztocki",
                                type: "national_park",
                                trailsCount: 45,
                                image: "https://images.pexels.com/photos/338936/pexels-photo-338936.jpeg"
                            },
                            {
                                id: 2,
                                name: "Region Wileński",
                                type: "state",
                                trailsCount: 32,
                                image: "https://images.pexels.com/photos/5851051/pexels-photo-5851051.jpeg"
                            }
                        ]
                    },
                    {
                        id: 'pl',
                        name: "Polska",
                        trailsCount: 142,
                        regionsCount: 16,
                        nationalParks: 8,
                        image: "https://images.pexels.com/photos/1761279/pexels-photo-1761279.jpeg",
                        description: "Spływ rzekami przez najpiękniejsze parki narodowe",
                        color: '#922820',
                        regions: [
                            {
                                id: 3,
                                name: "Biebrzański Park Narodowy",
                                type: "national_park",
                                trailsCount: 28,
                                image: "https://images.pexels.com/photos/1761279/pexels-photo-1761279.jpeg"
                            },
                            {
                                id: 4,
                                name: "Województwo Podlaskie",
                                type: "state",
                                trailsCount: 35,
                                image: "https://images.pexels.com/photos/1626481/pexels-photo-1626481.jpeg"
                            },
                            {
                                id: 5,
                                name: "Drawieński Park Narodowy",
                                type: "national_park",
                                trailsCount: 18,
                                image: "https://images.pexels.com/photos/2662116/pexels-photo-2662116.jpeg"
                            },
                            {
                                id: 6,
                                name: "Województwo Zachodniopomorskie",
                                type: "state",
                                trailsCount: 42,
                                image: "https://images.pexels.com/photos/1665928/pexels-photo-1665928.jpeg"
                            },
                            {
                                id: 7,
                                name: "Biebrzański Park Narodowy",
                                type: "national_park",
                                trailsCount: 28,
                                image: "https://images.pexels.com/photos/1761279/pexels-photo-1761279.jpeg"
                            },
                            {
                                id: 8,
                                name: "Województwo Podlaskie",
                                type: "state",
                                trailsCount: 35,
                                image: "https://images.pexels.com/photos/1626481/pexels-photo-1626481.jpeg"
                            },
                            {
                                id: 9,
                                name: "Drawieński Park Narodowy",
                                type: "national_park",
                                trailsCount: 18,
                                image: "https://images.pexels.com/photos/2662116/pexels-photo-2662116.jpeg"
                            },
                            {
                                id: 10,
                                name: "Województwo Zachodniopomorskie",
                                type: "state",
                                trailsCount: 42,
                                image: "https://images.pexels.com/photos/1665928/pexels-photo-1665928.jpeg"
                            }
                        ]
                    },
                    {
                        id: 'ua',
                        name: "Ukraina",
                        trailsCount: 60,
                        regionsCount: 11,
                        nationalParks: 4,
                        image: "https://images.pexels.com/photos/3442567/pexels-photo-3442567.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1",
                        description: "Dzikie szlaki przez ukraińskie Karpaty",
                        color: 'river-blue',
                        regions: [
                            {
                                id: 7,
                                name: "Karpacki Park Narodowy",
                                type: "national_park",
                                trailsCount: 15,
                                image: "https://images.pexels.com/photos/8961345/pexels-photo-8961345.jpeg"
                            },
                            {
                                id: 8,
                                name: "Obwód Zakarpacki",
                                type: "state",
                                trailsCount: 22,
                                image: "https://images.pexels.com/photos/2832034/pexels-photo-2832034.jpeg"
                            },
                            {
                                id: 9,
                                name: "Park Narodowy Synewyr",
                                type: "national_park",
                                trailsCount: 12,
                                image: "https://images.pexels.com/photos/3442567/pexels-photo-3442567.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
                            }
                        ]
                    }
                ]
            }
        }
    },

    methods: {
        selectCountry(country) {
            this.selectedCountry = country
        }
    }
}

</script>

<style scoped>
.scrolling-wrapper {
  display: flex;
  overflow-y: auto;
  max-height: 100%;
  flex-wrap: nowrap;
}

@media (max-width: 1200px) {

}

@media (max-width: 960px) {
}

@media (max-width: 600px) {
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
