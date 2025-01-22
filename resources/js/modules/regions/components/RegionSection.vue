<template>
    <v-sheet color="grey-lighten-4" class="py-16">
        <v-container>
            <v-row justify="center">
                <v-col cols="12" class="text-center">
                    <h2 class="text-h4 font-weight-bold mb-4">Indeks regionów</h2>
                    <p class="text-body-1">
                        Regiony i parki narodowe w {{ country.name }}
                    </p>
                </v-col>
            </v-row>

            <v-row class="mt-8">
                <v-col cols="12">
                    <v-btn
                        prepend-icon="mdi-arrow-left"
                        variant="text"
                        color="primary"
                        @click="$emit('clear-selection')"
                    >
                        Powrót do wyboru kraju
                    </v-btn>
                </v-col>
            </v-row>

            <v-row>
                <v-col
                    v-for="region in country.regions"
                    :key="region.id"
                    cols="12"
                    sm="4"
                    md="3"
                >
                    <region-card :region="region" />
                </v-col>
            </v-row>
        </v-container>
    </v-sheet>
</template>

<script>
import RegionCard from './RegionCard.vue'

export default {
    name: 'RegionsSection',

    components: {
        RegionCard
    },

    props: {
        country: {
            type: Object,
            required: true
        }
    },

    emits: ['clear-selection']
}
</script>
