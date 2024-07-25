<template>
    <v-card class="mx-auto my-4" max-width="344" outlined>
        <v-img
            :src="imageSrc"
            height="200px"
            class="white--text align-end"
        >
            <v-card-title>{{ trail.trail_name }}</v-card-title>
        </v-img>
        <v-card-subtitle>{{ trail.river_name }} - {{ trail.difficulty }}</v-card-subtitle>
        <v-card-subtitle>{{ formatTrailLength(trail.trail_length) }}</v-card-subtitle>
        <v-card-subtitle>{{ trail.location }}</v-card-subtitle>
        <v-card-actions>
            <v-btn icon>
                <v-icon>mdi-star</v-icon>
            </v-btn>
            <v-btn icon>
                <v-icon>mdi-bookmark</v-icon>
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
export default {
    name: 'TrailCard',
    props: {
        trail: {
            type: Object,
            required: true
        }
    },
    computed: {
        imageSrc() {
            // Placeholder image, you can replace it with a real one if available
            return 'https://via.placeholder.com/344x200';
        }
    },
    methods: {
        formatTrailLength(length) {
            // Convert length in meters to kilometers with 1 decimal place
            return `${(length / 1000).toFixed(1)} km`;
        }
    }
};
</script>

<style scoped>
</style>
