<template>
    <v-container class="main-page-container">
        <v-row dense>
            <base-regions-breadcrumbs :sorted-regions="getRegions" />
        </v-row>

        <v-card class="trail-card" rounded="xl" elevation="4">
            <trail-header
                :image-src="trailImageSrc"
                :trail-name="currentTrail.trail_name"
                :difficulty="currentTrail.difficulty"
                :rating="currentTrail.rating"
                :scenery="currentTrail.scenery"
                height="400"
            />

            <v-sheet class="pa-4" tag="section">
                <v-row dense>
                    <!-- Główna kolumna -->
                    <v-col cols="12" md="8" lg="9">
                        <v-row dense>
                            <v-col cols="6">
                                <div class="text-subtitle-2 text-medium-emphasis">Długość szlaku</div>
                                <div class="text-h6 font-weight-bold">
                                    {{ formatTrailLength(currentTrail.trail_length) }}
                                </div>
                            </v-col>
                            <v-col cols="6">
                                <div class="text-subtitle-2 text-medium-emphasis">Czas płynięcia</div>
                                <div class="text-h6 font-weight-bold">
                                    {{ formatAvgDuration(currentTrail.trail_length) }}
                                </div>
                            </v-col>
                        </v-row>

                        <v-spacer class="my-4" />

                        <description-tab :description="currentTrail.description"/>
                        <weather-tab
                            v-if="currentTrail"
                            :latitude="currentTrail.start_lat"
                            :longitude="currentTrail.start_lng"
                        />
                        <v-divider class="my-4" />
                        <trail-tabs :tabs="tabs" />
                        <author-tab :author="currentTrail.author" />

                    </v-col>

                    <!-- Sidebar -->
                    <v-col tag="aside" cols="12" md="4" lg="3">
                        <v-row>
                            <!-- Mapa -->
                            <v-col cols="12">
                                <v-card height="275" color="green" class="position-relative" rounded="lg">
                                    <v-img
                                        :src="mapImageUrl"
                                        height="275"
                                        cover
                                        lazy-src=""
                                        alt="Statyczna mapa szlaku"
                                        :loading="loadingMap"
                                    >
                                        <!-- Przycisk powiększenia -->
                                        <v-btn
                                            icon="mdi-arrow-expand"
                                            class="position-absolute float-end"
                                            style="bottom: 16px; right: 16px;"
                                            elevation="1"
                                            :to="{name: 'trail-overview', params:{slug: currentTrail.slug}}"
                                        />

                                        <!-- Skeleton loader -->
                                        <template v-slot:placeholder>
                                            <div class="d-flex flex-column align-center justify-center h-100 bg-grey-lighten-2">
                                                <v-icon
                                                    color="grey-lighten-1"
                                                    size="64"
                                                    class="mb-4"
                                                >
                                                    mdi-map
                                                </v-icon>
                                                <v-skeleton-loader
                                                    type="image"
                                                    width="100%"
                                                    class="position-absolute h-100 bg-teal-darken-4"
                                                ></v-skeleton-loader>
                                            </div>
                                        </template>

                                        <!-- Fallback gdy obrazek się nie załaduje -->
                                        <template v-slot:error>
                                            <div class="d-flex flex-column align-center justify-center h-100 bg-grey-lighten-3">
                                                <v-icon color="error" size="64">mdi-image-off</v-icon>
                                                <p class="text-subtitle-1 mt-2">Nie udało się załadować mapy</p>
                                            </div>
                                        </template>
                                    </v-img>
                                </v-card>
                                <div class="d-flex justify-end mt-4 mb-2">
                                    <v-btn
                                        density="comfortable"
                                        variant="text"
                                        :ripple="false"
                                        to="/explore"
                                    >
                                        Zobacz więcej
                                    </v-btn>
                                </div>
                            </v-col>

                            <!-- Najlepsze szlaki -->
                            <v-col cols="12">
                                <h4 class="text-h6 mb-2">Polecane szlaki</h4>
                                <trail-card v-for="trail in topTrails" :key="trail.id" :trail="trail"></trail-card>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
            </v-sheet>
        </v-card>
    </v-container>
</template>

<script>

import BaseRegionsBreadcrumbs from "@/components/BaseRegionsBreadcrumbs.vue";
import WeatherTab from "@/modules/trails/components/Details/WeatherTab.vue";
import AuthorTab from "@/modules/trails/components/Details/AuthorTab.vue";
import TrailHeader from "@/modules/trails/components/Details/TrailHeader.vue";
import DescriptionTab from "@/modules/trails/components/Details/DescriptionTab.vue";
import UnitMixin from "@/mixins/UnitMixin.js";
import {mapGetters} from "vuex";
import AppMixin from "@/mixins/AppMixin.js";
import apiClient from "@/plugins/apiClient.js";
import TrailTabs from "@/modules/trails/components/TrailDetails/TrailTabs.vue";
import LinksList from "@/modules/trails/components/TrailDetails/LinksList.vue";
import PhotosTabContent from "@/modules/trails/components/TrailDetails/PhotosTabContent.vue";
import PointsTabContent from "@/modules/trails/components/TrailDetails/PointsTabContent.vue";
import SectionsTabContent from "@/modules/trails/components/TrailDetails/SectionsTabContent.vue";
import TrailCard from "@/modules/trails/components/TrailCard.vue";
import {markRaw, shallowRef} from "vue";

export default {
    name: "TrailDetails",
    components: {TrailCard, TrailTabs, BaseRegionsBreadcrumbs, WeatherTab, AuthorTab, TrailHeader, DescriptionTab},
    mixins: [UnitMixin,AppMixin],

    data() {
        return {
            activeTab: null,
            mapImageBlobUrl: '',
            loadingMap: true,
            loadingTopTrails: false,
            topTrails: [],
            tabs: [
                {
                    id: 'sections',
                    title: 'Odcinki',
                    count: this.currentTrail?.sections.length || 0,
                    component: markRaw(SectionsTabContent)
                },
                {
                    id: 'points',
                    title: 'Punkty',
                    count: this.currentTrail?.points.length || 0,
                    component: PointsTabContent
                },
                {
                    id: 'photos',
                    title: `Zdjęcia`,
                    count: this.currentTrail?.images.length || 0,
                    component: markRaw(PhotosTabContent)
                },
                {
                    id: 'links',
                    title: 'Linki',
                    component: markRaw(LinksList),
                    props: {
                        links: this.currentTrail?.links || []
                    }
                }
            ]
        }
    },
    computed: {
        ...mapGetters('trails', ['currentTrail']),
        trailImageSrc() {
            return this.currentTrail.main_image?.path || this.appConfig.placeholderImage;
        },
        getRegions() {
            return this.currentTrail?.regions
        },
        mapImageUrl() {
            // Zwracamy albo blob URL albo pusty string podczas ładowania
            return this.mapImageBlobUrl || `${import.meta.env.VITE_APP_URL}/storage/assets/map-placeholder.jpg`;
        },
    },

    async created() {
        if (this.currentTrail) {
            await this.loadMapImage();
            await this.fetchTopTrails();
        }
    },
    methods: {
        async fetchTopTrails() {
            this.loadingTopTrails = true;

            apiClient.get(`/trails/${this.currentTrail.slug}/recommended?radius=100`)
                .then(response => {
                    this.topTrails = response.data.data;
                    this.loadingTopTrails = false;
                })
                .catch(error => {
                        this.$alertError('Wystąpił błąd podczas pobierania polecanych szlaków');
                        this.loadingTopTrails = false;
                        console.log(error)

                });
        },
        async loadMapImage() {
            try {
                this.loadingMap = true;
                const response = await apiClient.get(
                    `/trails/${this.currentTrail.slug}/static-map`,
                    {
                        responseType: 'blob',
                        headers: {
                            'X-Client-Type': 'web' // required client type
                        }
                    }
                );
                this.mapImageBlobUrl = URL.createObjectURL(response.data);
            } catch (error) {
                console.error('Błąd ładowania mapy:', error);
                this.mapImageBlobUrl = this.appConfig.placeholderImage;
            } finally {
                this.loadingMap = false;
            }
        },
    },


    beforeUnmount() {
        if (this.mapImageBlobUrl) {
            URL.revokeObjectURL(this.mapImageBlobUrl);
        }
    }
}
</script>

<style scoped>
.trail-card {
    overflow: hidden; /* jeśli potrzebne dla rounded corners */
}
.main-page-container{
    max-width: 1248px;
}
</style>
