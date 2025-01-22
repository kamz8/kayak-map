<template>
    <v-card>
        <v-img
            :src="region.image"
            height="200"
            cover
        >
            <template v-slot:placeholder>
                <v-row
                    class="fill-height ma-0"
                    align="center"
                    justify="center"
                >
                    <v-progress-circular
                        indeterminate
                        color="grey-lighten-5"
                    />
                </v-row>
            </template>

            <v-chip
                class="ma-4"
                color="white"
                size="small"
                label
                style="position: absolute; right: 0; top: 0;"
            >
                {{ region.type === 'national_park' ? 'Park Narodowy' : 'Województwo' }}
            </v-chip>
        </v-img>

        <v-card-text>
            <h3 class="text-h6 font-weight-bold mb-2">{{ region.name }}</h3>
            <v-row no-gutters align="center" justify="space-between">
                <v-col cols="auto">
                    <v-icon size="small" color="primary" class="mr-1">
                        mdi-compass
                    </v-icon>
                    <span class="text-body-2">{{ region.trailsCount }} szlaków</span>
                </v-col>
                <v-col cols="auto">
                    <v-btn
                        variant="text"
                        color="primary"
                        class="px-0"
                        append-icon="mdi-arrow-right"
                        @click="viewDetails"
                    >
                        Zobacz więcej
                    </v-btn>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>

<script>
export default {
    name: 'RegionCard',

    props: {
        region: {
            type: Object,
            required: true
        }
    },

    methods: {
        viewDetails() {
            // TODO: Implementacja nawigacji do szczegółów regionu
            this.$router.push(`/regions/${this.region.id}`)
        }
    }
}
</script>
