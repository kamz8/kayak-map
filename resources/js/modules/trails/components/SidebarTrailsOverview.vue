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
                            :ripple="false"
                            variant="plain"
                            class="text-subtitle-2 text-none font-weight-medium justify-center"
                            style="width: 100%; height: 100%;"
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
                <description-tab :description="currentTrail.description"/>
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
import appConfig from "@/config/appConfig.js";
import DescriptionTab from "@/modules/trails/components/Details/DescriptionTab.vue";
import TrailHeader from "@/modules/trails/components/Details/TrailHeader.vue";
import AuthorTab from "@/modules/trails/components/Details/AuthorTab.vue";
import WeatherTab from "@/modules/trails/components/Details/WeatherTab.vue";

export default {
    name: 'SidebarTrailsOverview',
    components: {WeatherTab, AuthorTab, TrailHeader, DescriptionTab},
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
</style>
