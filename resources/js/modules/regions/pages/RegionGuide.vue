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
import apiClient from "@/plugins/apiClient.js";

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
                totalRegions: 3,
                countries: [],

            },
            countryColors: {
                'polska': '#922820',
                'litwa': '#1c7a19',
                'ukraina': 'river-blue'
            },
            countryDescriptions: {
                'Polska': 'Spływ rzekami przez najpiękniejsze parki narodowe',
                'Litwa': 'Odkryj malownicze szlaki przez litewskie jeziora i lasy',
                'Ukraina': 'Dzikie szlaki przez ukraińskie Karpaty'
            },
            loading: false,
            perPage: 8,
            currentPage: 1,
            loadingRegions: false,
        }
    },
    methods: {
        async fetchCounty() {
            try {
                const response = await apiClient.get(`regions`);
                const apiData = response.data.data;

                // Mapujemy dane z API do formatu wymaganego przez komponenty
                this.stats.countries = apiData.map(country => ({
                    id: country.slug,
                    name: country.name,
                    trailsCount: country.statistics.total_trails_count,
                    regionsCount: country.statistics.cities_count,
                    nationalParks: country.statistics.national_parks_count,
                    image: country.main_image?.path,
                    description: this.countryDescriptions[country.name] || 'Odkryj malownicze szlaki kajakowe',
                    color: this.countryColors[country.slug],
                    slug: country.slug,
                    regions: []
                }));

                // Aktualizacja ogólnych statystyk
/*                this.stats.totalTrails = apiData.reduce((sum, country) =>
                    sum + country.statistics.total_trails_count, 0);*/
                this.stats.totalRegions = apiData.reduce((sum, country) =>
                    sum + country.statistics.cities_count, 0);

            } catch (error) {
                console.error('Error fetching regions:', error);
                this.$alert({
                    type: 'error',
                    text: 'Nie udało się pobrać szczegółów regionów. Spróbuj ponownie później.'
                });
            }
        },

        async fetchCountryRegion(countrySlug) {
            this.loadingRegions = true;
            try {
                const response = await apiClient.get(`regions/country/${countrySlug}`, {
                    params: {
                        perPage: this.perPage,
                        page: 1 // pierwsza strona
                    }
                });

                if (this.selectedCountry) {
                    // ustawiamy pobrane regiony i dane z paginacji
                    this.selectedCountry.regions = response.data.data;
                    this.selectedCountry.pagination = response.data.meta;
                }
            } catch (error) {
                console.error('Error fetching regions:', error);
                this.$error({
                    text: 'Nie udało się pobrać szczegółów regionów.'
                });
            } finally {
                this.loadingRegions = false;
            }
        },

        async loadMoreRegions() {
            if (!this.selectedCountry?.pagination?.next_page_url) return;

            this.loadingRegions = true;
            try {
                const response = await apiClient.get(`regions/country/${this.selectedCountry.slug}`, {
                    params: {
                        perPage: this.perPage,
                        page: this.selectedCountry.pagination.current_page + 1
                    }
                });

                // Dodajemy nowe regiony do istniejących
                this.selectedCountry.regions = [
                    ...this.selectedCountry.regions,
                    ...response.data.data
                ];

                // Aktualizujemy paginację
                this.selectedCountry.pagination = response.data.meta;

            } catch (error) {
                console.error('Error loading more regions:', error);
                this.$error({
                    text: 'Nie udało się załadować więcej regionów.'
                });
            } finally {
                this.loadingRegions = false;
            }
        },

        hasMoreRegions() {
            if (!this.selectedCountry || !this.selectedCountry._allRegions) return false;

            return this.selectedCountry.regions.length < this.selectedCountry._allRegions.length;
        },

        selectCountry(country) {
            this.selectedCountry = country;
            this.fetchCountryRegion(country.slug);
        }
    },

    created() {
        this.fetchCounty();
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
