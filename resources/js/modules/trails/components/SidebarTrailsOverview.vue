<template>
    <div class="sidebar-trails-overview">
        <v-toolbar color="white" density="compact" elevation="4">
            <v-toolbar-title class="ma-0" style="font-size: 0.85em">
                <v-row no-gutters class="flex-nowrap" style="width: 100%;">
                    <v-col cols="6">
                        <v-btn
                            :ripple="false"
                            variant="plain"
                            href="/explore"
                            class="text-subtitle-2 text-none font-weight-bold justify-start"
                            style="width: 100%; height: 100%;"
                        >
                            <v-icon start>mdi-arrow-left</v-icon>
                            Więcej szlaków
                        </v-btn>
                    </v-col>
                    <v-col cols="6">
                        <v-btn
                            tag="a"
                            :ripple="false"
                            variant="plain"
                            class="text-subtitle-2 text-none font-weight-medium justify-center"
                            style="width: 100%; height: 100%;"
                            :to="{name: 'trail-details', param: {'slug': currentTrail.slug}}"
                        >
                            Zobacz szczegóły szlaku
                        </v-btn>
                    </v-col>
                </v-row>
            </v-toolbar-title>
        </v-toolbar>
        <v-divider></v-divider>

        <div v-if="currentTrail">
            <trail-header
                :image-src="trailImageSrc"
                :trail-name="currentTrail.trail_name"
                :difficulty="currentTrail.difficulty"
                :rating="currentTrail.rating"
                :scenery="currentTrail.scenery"
            />

            <v-card flat class="pa-4" tag="section">
                <v-row>
                    <v-col cols="6">
                        <div class="text-subtitle-2 font-weight-light">Długość szlaku</div>
                        <div class="text-h6 font-weight-bold">
                            {{ formatTrailLength(currentTrail.trail_length) }}
                        </div>
                    </v-col>
                    <v-col cols="6">
                        <div class="text-subtitle-2 font-weight-light">Czas płynięcia</div>
                        <div class="text-h6 font-weight-bold">
                            {{ formatAvgDuration(currentTrail.trail_length) }}
                        </div>
                    </v-col>
                </v-row>

                <v-spacer class="pb-2"/>

                <!-- DescriptionTab z dodatkową zakładką Punkty -->
                <description-tab :description="currentTrail.description">
                    <template #additional-tabs>
                        <v-tab value="points" v-if="hasPoints">
                            Punkty <span v-if="pointsCount">({{ pointsCount }})</span>
                        </v-tab>
                    </template>

                    <template #additional-content>
                        <v-tabs-window-item value="points" v-if="hasPoints">
                            <div class="sidebar-points-content">
                                <points-tab-content
                                    @point-clicked="handlePointClick"
                                />
                            </div>
                        </v-tabs-window-item>
                    </template>
                </description-tab>

                <weather-tab
                    v-if="currentTrail"
                    :latitude="currentTrail.start_lat"
                    :longitude="currentTrail.start_lng"
                />

                <author-tab :author="currentTrail.author"></author-tab>
            </v-card>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import UnitMixin from '@/mixins/UnitMixin';
import DescriptionTab from "@/modules/trails/components/Details/DescriptionTab.vue";
import TrailHeader from "@/modules/trails/components/Details/TrailHeader.vue";
import AuthorTab from "@/modules/trails/components/Details/AuthorTab.vue";
import WeatherTab from "@/modules/trails/components/Details/WeatherTab.vue";
import PointsTabContent from "@/modules/trails/components/TrailDetails/PointsTabContent.vue";

export default {
    name: 'SidebarTrailsOverview',
    components: {
        PointsTabContent,
        WeatherTab,
        AuthorTab,
        TrailHeader,
        DescriptionTab
    },
    mixins: [UnitMixin],
    data() {
        return {
            activeTab: null,
        }
    },
    computed: {
        ...mapGetters('trails', ['currentTrail']),
        trailImageSrc() {
            return this.currentTrail.main_image?.path || this.appConfig.placeholderImage;
        },
        hasPoints() {
            return this.currentTrail?.points?.length > 0;
        },
        pointsCount() {
            return this.currentTrail?.points?.length || 0;
        },
    },
    methods: {
        goBack() {
            this.$router.push({name: 'explore'})
        },
        getDifficultyColor(difficulty) {
            switch (difficulty) {
                case 'łatwy': return 'green';
                case 'umiarkowany': return 'orange';
                case 'trudny': return 'red';
                default: return 'grey';
            }
        },

        handlePointClick(point) {
            // Check if point has valid coordinates
            if (this.isValidLocation(point)) {
                // Emit event to parent component (probably Explore.vue)
                // to move map to point coordinates
                this.$emit('move-map-to-point', {
                    lat: parseFloat(point.lat),
                    lng: parseFloat(point.lng),
                    zoom: 16, // Zoom level for point focus
                    point: point
                });
            }
        },
        isValidLocation(point) {
            return point.lat !== "-1.0000000" &&
                point.lng !== "-1.0000000" &&
                point.lat !== null &&
                point.lng !== null;
        },
    }
}
</script>

<style scoped>
.sidebar-trails-overview {
    height: 100%;
    overflow-y: auto;
}

.trail-header {
    position: relative;
}

.overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);
}

.v-toolbar {
    min-height: 48px !important;
}

/* Stylowanie dla zakładek w kompaktowym trybie */
@media (max-width: 600px) {
    :deep(.v-tab) {
        min-width: 0;
        font-size: 0.875rem;
    }
}

/* Responsive adjustments for points tab */
@media (max-width: 768px) {
    .sidebar-points-content {
        max-height: 400px;
    }
}
</style>
