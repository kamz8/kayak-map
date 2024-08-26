<template>
    <div class="trail-header position-relative">
        <v-img
            :src="imageSrc"
            :alt="trailName"
            height="300"
            cover
        >
            <template v-slot:placeholder>
                <v-row class="fill-height ma-0" align="center" justify="center">
                    <v-progress-circular indeterminate color="grey-lighten-5"></v-progress-circular>
                </v-row>
            </template>
            <div class="fill-height d-flex flex-column justify-end">
                <v-sheet
                    class="pa-4"
                    color="rgba(0, 0, 0, 0.7)"
                    :style="{
            background: 'linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%)',
            boxShadow: '0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06)'
          }"
                >
                    <h2 class="text-h4 font-weight-bold text-white">{{ trailName }}</h2>
                    <div class="d-flex align-center">
                        <v-chip
                            size="small"
                            class="mr-2"
                            :color="getDifficultyColor(difficulty)"
                        >
                            {{ difficulty }}
                        </v-chip>
                        <v-rating
                            :model-value="rating"
                            color="amber"
                            half-increments
                            readonly
                            size="small"
                        ></v-rating>
                        <span class="ml-2 text-white">({{ rating }})</span>
                    </div>
                    <div class="text-white">
                        <v-icon icon="mdi-image-filter-hdr" color="white" size="small"></v-icon>
                        Malowniczość: {{ scenery }}/10
                    </div>
                </v-sheet>
            </div>
        </v-img>
    </div>
</template>

<script>
export default {
    name: 'TrailHeader',
    props: {
        imageSrc: String,
        trailName: String,
        difficulty: String,
        rating: Number,
        scenery: Number
    },
    methods: {
        getDifficultyColor(difficulty) {
            switch (difficulty) {
                case 'łatwy': return 'green';
                case 'umiarkowany': return 'orange';
                case 'trudny': return 'red';
                default: return 'grey';
            }
        }
    }
}
</script>

<style>
.v-overlay__scrim {
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>
