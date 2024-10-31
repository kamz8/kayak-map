<template>
    <v-container fluid>
        <!-- Główna sekcja -->
        <v-sheet class="region-section">
            <!-- Header z breadcrumbs i akcjami -->
            <header class="position-sticky">
                <v-row>
                    <v-col cols="12" md="6" class="px-md-4">
                        <nav aria-label="breadcrumb">
                            <base-regions-breadcrumbs
                                :sorted-regions="sortedRegions"
                            />
                        </nav>
                    </v-col>
                    <v-col cols="12" md="6" class="d-flex justify-end align-center">
                        <v-btn color="default" density="default"
                               icon="mdi-printer" elevation="1" class="mr-3"
                               aria-label="Drukuj"
                        ></v-btn>
                    </v-col>
                </v-row>
            </header>

            <!-- Zawartość główna -->
            <v-row class="px-md-4">
                <!-- Lewa kolumna -->
                <v-col cols="12" md="6">
                    <!-- Sekcja ze zdjęciami -->
                    <v-card rounded="xl" elevation="0" class="mb-4" tag="section">
                        <photo-swiper
                            :images="region.images"
                            height="400"
                        />
                    </v-card>

                    <!-- Sekcja z informacjami o regionie -->
                    <v-sheet rounded="xl" elevation="0" class="mb-4" tag="article">
                        <header>
                            <h1 class="text-title text-lg-h3 text-md-h4 text-sm-h5 text-uppercase mb-2 pt-6">
                                Najlepsze szlaki w {{ region.name }}
                            </h1>
                            <div class="d-flex align-center font-weight-bold mb-4">
                                    <span v-tooltip:bottom="'Średnia ocena szlaków dla regionu'">
                                        <v-icon color="amber" class="mr-1"
                                                v-tooltip:bottom="'Średnia ocena szlaków dla regionu'">mdi-star</v-icon>
                                        {{ region.statistics.avg_rating }}
                                    </span>
                                <v-divider vertical class="mx-2"/>
                                <span v-tooltip:bottom="'Średnia ocena malowniczości tras w regionie'">
                                        <v-icon color="footer" size="small" class="mr-1"
                                                v-tooltip:bottom="'Średnia ocena malowniczości szlaków dla regionu'">mdi-forest</v-icon>
                                        {{ region.statistics.avg_scenery }}
                                    </span>
                            </div>
                        </header>
                        <p class="text-body">{{ leadText }}</p>
                    </v-sheet>

                    <!-- Sekcja z top trasami -->
                    <section aria-labelledby="top-trails-heading">
                        <v-sheet rounded="xl" elevation="0" class="top-trails-section">
                            <header class="pt-14">
                                <h2 id="top-trails-heading" class="text-h5 text-uppercase font-weight-medium mb-4 position-xs-sticky">
                                    Top trasy kajakowe
                                </h2>
                            </header>
                            <!--            mobilna mini mapa                -->
                            <v-col v-if="isMobile" cols="12" md="6" id="mobile-map-wrapper" class="map-column d-md-none d-sm-flex">
                                <v-card :height="mapHeight" rounded="xl">
                                    <mini-map
                                        :center="mapCenter"
                                        :bounds="mapBounds"
                                        :area="region.area"
                                        :trails="topTrails"
                                    />
                                    <div class="overlay-content">
                                        <span class="justify-center">TU BĘDZIE MAPA</span>
                                        <v-btn
                                            color="river-blue"
                                            class="text-capitalize px-8 white--text position-relative"
                                            rounded
                                            density="comfortable"
                                            tag="a"
                                            to="/"
                                            elevation="2"
                                        >
                                            Odkryj więcej
                                        </v-btn>
                                    </div>

                                </v-card>
                            </v-col>

                            <v-list class="pa-0 overflow-x-hidden" rounded="xl"
                                    :class="{ 'list-mobile': $vuetify.display.smAndDown }">

                                <v-list-item
                                    v-for="(trail, index) in topTrails"
                                    :key="trail.id"
                                    :to="{ name: 'trail-overview', params: { slug: trail.slug ?? '' }}"
                                    class="mb-2"
                                    rounded="xl"
                                >
                                    <template v-slot:prepend>
                                        <div class="trail-image-container mr-1 me-2">
                                            <v-img
                                                :src="trail.thumbnail"
                                                :aspect-ratio="1"
                                                max-width="192"
                                                :width="imageWidth"
                                                cover
                                                rounded="xl"
                                                :alt="`Zdjęcie trasy ${trail.name}`"
                                            />
                                        </div>
                                    </template>

                                    <v-list-item-title class="text-h6 mb-1 font-weight-bold pt-6">
                                        #{{ index + 1 }}&nbsp;{{ trail.name }}
                                    </v-list-item-title>

                                    <!-- Podstawowe informacje -->
                                    <v-list-item-subtitle class="mb-4 d-flex flex-wrap align-center gap-4 trail-stats">
                                        <v-rating
                                            :model-value="trail.rating"
                                            readonly
                                            density="compact"
                                            size="small"
                                            class="me-3"
                                            color="amber"
                                        />
                                        <v-spacer class="d-sm-inline-block d-md-none" />
                                        <span>{{ formatTrailLength(trail.trail_length) }}</span>
                                        <span>|</span>
                                        <span>{{ trail.difficulty }}</span>
                                        <span>|</span>
                                        <span v-tooltip:bottom="'Ocena malowniczości trasy'">
                                            <v-icon color="footer" size="x-small">mdi-forest</v-icon>
                                            {{ trail.scenery }}
                                        </span>
                                    </v-list-item-subtitle>

                                    <!-- Skrócony opis -->
                                    <v-list-item-subtitle class="d-flex align-center trail-description d-sm-none">
                                        <span
                                            class="text-body-2 text-truncate-4 flex-grow-1">{{ trail.description ?? 'Brak opisu dla szlaku' }}</span>
                                        <v-btn
                                            v-if="trail.description && !$vuetify.display.smAndDown"
                                            variant="plain"
                                            density="compact"
                                            class="ms-2 px-0 flex-shrink-0 font-weight-bold text-decoration-underline text-body-1 d-inline-flex"
                                            color="gray-darken-1"
                                            :ripple="false"
                                        >
                                            więcej
                                        </v-btn>
                                    </v-list-item-subtitle>
                                </v-list-item>
                                <v-list-item
                                    v-if="!topTrails.length"
                                    class="mb-2"
                                    rounded="xl"
                                >
                                    <template v-slot:prepend>
                                        <div class="trail-image-container mr-1 me-2">
                                            <v-img
                                                :src="placeholderImage"
                                                :aspect-ratio="1"
                                                max-width="192"
                                                :width="imageWidth"
                                                cover
                                                rounded="xl"
                                                alt="Domyślne zdjęcie trasy"
                                            />
                                        </div>
                                    </template>

                                    <v-list-item-title class="text-h6 mb-1 font-weight-bold pt-6">
                                        <span class="text-info">Brak danych...</span>
                                    </v-list-item-title>

                                     <span>Nie możemy ci jeszcze polecić tras dla tej lokalizacji... 😞</span>
                                </v-list-item>
                            </v-list>
                        </v-sheet>
                    </section>
                </v-col>

                <!-- Prawa kolumna - Mapa -->
                <v-col v-if="!isMobile" cols="12" md="6" sm="6" class="map-column position-sticky">
                    <aside class="position-sticky">
                        <v-card :height="mapHeight" rounded="xl">
                            <mini-map
                                :center="mapCenter"
                                :bounds="mapBounds"
                                :area="region.area"
                                :trails="topTrails"
                            >
                                <div class="overlay-content">
                                    <v-btn
                                        color="river-blue"
                                        class="text-capitalize px-8 white--text position-relative"
                                        rounded
                                        density="comfortable"
                                        tag="a"
                                        to="/"
                                        elevation="2"
                                    >
                                        Odkryj więcej
                                    </v-btn>
                                </div>
                            </mini-map>


                        </v-card>
                    </aside>
                </v-col>
            </v-row>
        </v-sheet>
    </v-container>
    <v-container fluid>
        <v-sheet class="region-section" min-height="200">

        </v-sheet>
    </v-container>
</template>
<script>
import PhotoSwiper from '@/components/UIKit/PhotoSwiper.vue';
import MiniMap from "@/modules/regions/components/MiniMap.vue";
import BaseRegionsBreadcrumbs from "@/components/BaseRegionsBreadcrumbs.vue";
import {useDisplay} from "vuetify";
import apiClient from "@/plugins/apiClient.js";
import UnitMixin from "@/mixins/UnitMixin.js";

export default {
    name: 'RegionView',

    components: {
        BaseRegionsBreadcrumbs,
        PhotoSwiper,
        MiniMap
    },
    mixins: [UnitMixin],

    data() {
        return {
            loading: false,
            region: {
                id: null,
                name: '',
                slug: '',
                type: '',
                is_root: false,
                center: null,
                bounds: null,
                area: null,
                images: [],
                main_image: null,
                links: [],
                breadcrumbs: [],
                statistics: {
                    total_trails: 0,
                    avg_rating: "0.00",
                    total_length: "0",
                    difficulty_stats: {
                        easy: "0",
                        moderate: "0",
                        hard: "0"
                    },
                    avg_scenery: "0.00"
                },
                nearby_regions: []
            },
            sortedRegions: [
                {
                    id: 1,
                    name: 'Polska',
                    slug: 'polska',
                    type: 'country',
                    parent_id: null
                },
                {
                    id: 2,
                    name: 'Dolnośląskie',
                    slug: 'dolnoslaskie',
                    type: 'state',
                    parent_id: 1
                },
                {
                    id: 76,
                    name: 'Dolina Baryczy',
                    slug: 'dolina-baryczy',
                    type: 'geographic_area',
                    parent_id: 2
                }
            ],
            topTrails: [],
            isMobile: false, // Przechowuje informację, czy ekran jest mały
        };
    },

    computed: {
        imageWidth() {
            switch (true) {
                case this.$vuetify.display.xs:
                    return '64';
                case this.$vuetify.display.sm:
                    return '150';
                default:
                    return '194';
            }
        },
        mapHeight() {
            switch (true) {
                case this.$vuetify.display.xs:
                    return '300';
                case this.$vuetify.display.sm:
                    return '400';
                case this.$vuetify.display.md:
                    return '500';
                default:
                    return '700';
            }
        },
        mapCenter() {
            if (!this.region.center?.coordinates) return [0, 0];
            const [lng, lat] = this.region.center.coordinates;
            return [lat, lng];
        },

        mapBounds() {
            if (!this.region.bounds) return null;
            return this.region.bounds.map(([lat, lng]) => [lng, lat]);
        },

        sortedRegions() {
            return this.region.breadcrumbs || [];
        },

        leadText() {
            if (!this.region.type) return '';

            const texts = {
                country: `Odkryj najpiękniejsze szlaki kajakowe w Polsce. Przygotowaliśmy dla Ciebie starannie wyselekcjonowane trasy z dokładnymi opisami, mapami i zdjęciami. Od spokojnych rzek nizinnych po wartkie górskie potoki - każdy znajdzie tu coś dla siebie.`,
                state: `Województwo ${this.region.name} to prawdziwy raj dla miłośników kajakarstwa. Lokalne rzeki i jeziora oferują różnorodne trasy, które zadowolą zarówno początkujących, jak i zaawansowanych kajakarzy. Każda trasa została dokładnie opisana i udokumentowana.`,
                geographic_area: `Region ${this.region.name} to wyjątkowe miejsce na mapie polskiego kajakarstwa. Malownicze szlaki wodne, piękne krajobrazy i bogata przyroda tworzą idealne warunki do niezapomnianych spływów kajakowych. Odkryj nasze trasy i zaplanuj swoją kolejną przygodę.`,
                city: `Okolice ${this.region.name} kryją wiele ciekawych szlaków kajakowych. To doskonała okazja, by połączyć zwiedzanie miasta z aktywnym wypoczynkiem na wodzie. Przygotowane przez nas trasy pomogą Ci zaplanować idealny spływ kajakowy.`
            };

            return texts[this.region.type] || texts.geographic_area;
        }
    },

    methods: {
        placeholder,
        capitalize(string) {
            if (!string) return '';
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
        async loadTopTrails() {
            const regionSlug = this.$route.params.slug.split('/').pop();

            try {
                const response = await apiClient.get(`/regions/${regionSlug}/top-trails-nearby`);
                this.topTrails = response.data.map(trail => ({
                    id: trail.id,
                    name: trail.trail_name,
                    slug: trail.slug,
                    river_name: trail.river_name,
                    trail_length: trail.trail_length,
                    difficulty: trail.difficulty,
                    scenery: trail.scenery,
                    rating: parseFloat(trail.rating),
                    description: trail.description,
                    thumbnail: trail.main_image?.path || this.placeholderImage,
                    distance: trail.trail_length / 1000
                }));
            } catch (error) {
                console.error('Błąd podczas ładowania top tras:', error);
                this.$alertError('Nie udało się załadować listy najlepszych tras');
            }
        },
    },

    beforeRouteEnter(to, from, next) {
        const regionSlug = to.params.slug.split('/').pop();

        apiClient.get(`/regions/${regionSlug}`)
            .then(response => {
                next(vm => {
                    vm.region = response.data;
                    vm.loading = false;
                });
            })
            .catch(error => {
                if (error.response?.status === 404) {
                    next({
                        name: 'not-found',
                        params: {
                            pathMatch: to.path.substring(1).split('/')
                        }
                    });
                } else {
                    next(vm => {
                        vm.$alertError('Wystąpił błąd podczas ładowania danych regionu');
                        vm.loading = false;
                    });
                }
            });
    },

    beforeRouteUpdate(to, from, next) {
        this.loading = true;
        const regionSlug = to.params.slug.split('/').pop();

        apiClient.get(`/regions/${regionSlug}`)
            .then(response => {
                this.region = response.data;
                this.loading = false;
                next();
            })
            .catch(error => {
                if (error.response?.status === 404) {
                    next({
                        name: 'not-found',
                        params: {
                            pathMatch: to.path.substring(1).split('/')
                        }
                    });
                } else {
                    this.$alertError('Wystąpił błąd podczas ładowania danych regionu');
                    this.loading = false;
                    next(false);
                }
            });
    },

    async created() {
        // await this.loadTopTrails();
    },

    mounted() {
        const display = useDisplay();
        this.isMobile = display.xs.value;

        this.$watch(
            () => display.xs.value,
            (newValue) => {
                this.isMobile = newValue;
            }
        );
    }
}
</script>

<style scoped>
.region-section {
    position: relative;
    padding: 0 !important;
    min-height: 100vh;
}

.map-column {
    top: 2rem;
    height: 100%;
}

.sticky-container {
    position: sticky;
    top: 24px;
    height: fit-content;
}

.top-trails-section {
    padding: 0;
}

@media (max-width: 959px) {
    .sticky-container {
        position: relative;
        top: 0;
    }
}

/* Dostosowanie list item dla mobile */
:deep(.v-list-item) {
    @media (max-width: 600px) {
        padding: 8px;
        gap: 12px;
    }
}

/* Dodajemy style dla listy mobilnej */
:deep(.list-mobile) {
    .v-list-item {
        padding-left: 0;
        padding-right: 0;
    }
}

/* Aktualizujemy style dla trail-stats */
.trail-stats {
    row-gap: 0.5rem !important;

    @media (max-width: 600px) {
        flex-wrap: wrap;

        > span {
            padding: 0.25rem 0;
        }
    }
}


/* Dostosowanie kontenera zdjęcia dla różnych breakpointów */
.trail-image-container {
    flex-shrink: 0;
}

.trail-stats > span {
    padding: 0.25rem;
}

.overlay-content {
    min-height: 100%;
    position: relative;
    display: flex;
    justify-content: center;
    align-content: end;
}

.overlay-content > * {
    position: absolute !important;
    bottom: 2rem;
}

.trail-description > .text-truncate-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
    flex: 1;
}

/* Responsywność */
@media (max-width: 600px) {
    .trail-image-container {
        width: 64px;
    }

    :deep(.v-list-item-title) {
        font-size: 1rem;
        line-height: 1.2;
        padding: 8px 0; /* Usuwamy padding horyzontalny */
        gap: 12px;
    }

    :deep(.v-list-item-subtitle) {
        font-size: 0.875rem;
    }
    .position-xs-sticky{
        position: sticky;
    }
}

@media (min-width: 601px) and (max-width: 960px) {
    .trail-image-container {
        width: 150px;
    }
}

@media (min-width: 961px) {
    .trail-image-container {
        width: 194px;
    }
}
</style>
