<template>
    <v-card class="trail-popup" width="300">
        <v-img
            :src="trail.image_url"
            height="150"
            cover
        >
            <template v-slot:placeholder>
                <v-row class="fill-height ma-0" align="center" justify="center">
                    <v-progress-circular indeterminate color="grey-lighten-5"></v-progress-circular>
                </v-row>
            </template>
        </v-img>
        <v-card-item>
            <v-card-title class="text-subtitle-1 font-weight-bold pa-0">
                {{ trail.name }}
                <v-icon icon="mdi-star" color="amber" size="small"></v-icon>
                <span class="text-body-2">{{ trail.rating }} ({{ trail.ratingCount }})</span>
            </v-card-title>
            <v-card-subtitle class="pa-0 pt-2">
                <v-icon icon="mdi-map-marker" size="small" start></v-icon>
                <span class="text-body-2">{{ trail.location }}</span>
            </v-card-subtitle>
            <v-card-subtitle class="pa-0">
                <v-icon icon="mdi-map" size="small" start></v-icon>
                <span class="text-body-2">{{ trail.length }} km • {{ formatDuration(trail.duration) }}</span>
            </v-card-subtitle>
        </v-card-item>
        <v-card-actions class="pa-4 pt-0">
            <v-btn
                variant="tonal"
                color="primary"
                block
                @click="$emit('view-details', trail.id)"
            >
                View Details
            </v-btn>
        </v-card-actions>
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
