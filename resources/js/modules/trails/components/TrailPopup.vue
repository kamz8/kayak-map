<template>
    <v-card class="trail-popup" width="300" max-height="120" outlined>
        <v-row no-gutters>
            <v-col cols="4">
                <v-img
                    :src="trail.main_image ? trail.main_image.path : placeholderImage"
                    height="100%"
                    width="100"
                    cover
                >
                    <template v-slot:placeholder>
                        <v-row class="fill-height ma-0" align="center" justify="center">
                            <v-progress-circular indeterminate color="grey-lighten-5"></v-progress-circular>
                        </v-row>
                    </template>
                </v-img>
            </v-col>
            <v-col cols="8">
                <v-card-item>
                    <v-card-subtitle class="pa-0">
                        <v-icon icon="mdi-star" color="amber" size="small"></v-icon>
                        <span class="text-body-2">{{ trail.rating }}</span>
                        <span class="text-grey-darken-1 pl-1 font-weight-light" style="font-size: 0.9em">{{ trail.difficulty ?? 'Trudność' }}</span>
                    </v-card-subtitle>
                    <v-card-title class="text-subtitle-1 font-weight-bold pa-0">
                        <router-link :to="{ name: 'trail-overview', params: { slug: trail.slug } }" class="text-decoration-none">
                            {{ trail.trail_name ?? 'nazwa szlaku' }}
                        </router-link>
                    </v-card-title>
                    <v-card-subtitle class="pa-0 pt-2">
                        <v-icon icon="mdi-waves" size="small" start></v-icon>
                        <span class="text-body-2">{{ trail.river_name }}</span>
                    </v-card-subtitle>
                    <v-card-subtitle class="pa-0">
                        <v-icon icon="mdi-clock" size="small" start></v-icon>
                        <span class="text-body-2">{{ formatAvgDuration(trail.trail_length) }} </span>
                    </v-card-subtitle>
                </v-card-item>
            </v-col>
        </v-row>
    </v-card>
</template>

<script>
export default {
    name: 'TrailPopup',
    props: {
        trail: {
            type: Object,
            required: true
        }
    },
    computed: {
        main_image_path() {
            return this.trail.main_image ? this.trail.main_image.path : this.placeholderImage;
        }
    },
    methods: {
        formatDuration(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours}h ${mins}m`;
        }
    }
}
</script>

<style scoped>
.trail-popup {
    box-shadow: none !important;
}
</style>
