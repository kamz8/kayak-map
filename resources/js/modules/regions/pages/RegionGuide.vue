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
            @load-more="handleLoadMoreRegions"
            ref="regionsSection"
            :loadingRegions="loadingRegions"
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
            loadingRegions: false
        }
    },

    inject: {
        // Opcjonalne wartości z provide, na wypadek gdyby ten komponent był używany w innym kontekście
        getRegionsLoadingState: { default: () => false },
        setRegionsLoadingState: { default: () => {} }
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
                        per_page: this.perPage,
                        page: 1
                    }
                });

                if (this.selectedCountry) {
                    this.selectedCountry.regions = response.data.data;
                    this.selectedCountry.pagination = response.data.meta;
                }
            } catch (error) {
                console.error('Error fetching regions:', error);
                this.$alertError({
                    text: 'Nie udało się pobrać szczegółów regionów.'
                });
            } finally {
                this.loadingRegions = false;

                // Użyj provide/inject aby zaktualizować stan ładowania w komponencie RegionsSection
                if (this.$refs.regionsSection) {
                    this.$refs.regionsSection.setLoadingState(false);
                }
            }
        },

        async handleLoadMoreRegions(payload) {
            if (!this.selectedCountry?.pagination?.has_more_pages) return;

            this.loadingRegions = true;

            // Aktualizacja stanu ładowania w RegionsSection za pomocą provide/inject
            if (this.$refs.regionsSection) {
                this.$refs.regionsSection.setLoadingState(true);
            }

            try {
                const response = await apiClient.get(`regions/country/${this.selectedCountry.slug}`, {
                    params: payload
                });

                if (this.selectedCountry) {
                    this.selectedCountry.regions = [
                        ...this.selectedCountry.regions,
                        ...response.data.data
                    ];
                    this.selectedCountry.pagination = response.data.meta;
                }
            } catch (error) {
                console.error('Error loading more regions:', error);
                this.$alertError({
                    text: 'Nie udało się załadować więcej regionów.'
                });
            } finally {
                this.loadingRegions = false;

                // Aktualizacja stanu ładowania w RegionsSection za pomocą provide/inject
                if (this.$refs.regionsSection) {
                    this.$refs.regionsSection.setLoadingState(false);
                }
            }
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
    height: 200px;
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
