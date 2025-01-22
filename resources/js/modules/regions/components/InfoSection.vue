<template>
    <v-container class="py-16">
        <v-row>
            <v-col
                v-for="(info, index) in infoCards"
                :key="index"
                cols="12"
                sm="4"
                class="d-flex flex-column align-center"
            >
                <v-card flat class="text-center">
                    <v-card-text>
                        <v-icon
                            size="x-large"
                            color="primary"
                            class="mb-4"
                        >
                            {{ info.icon }}
                        </v-icon>
                        <h3 class="text-h6 font-weight-bold mb-2">{{ info.title }}</h3>
                        <p class="text-body-1">{{ info.description }}</p>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
export default {
    name: 'InfoSection',

    props: {
        countries: {
            type: Array,
            required: true
        }
    },

    computed: {
        infoCards() {
            return [
                {
                    icon: 'mdi-compass',
                    title: 'Największa baza szlaków',
                    description: `Polska przoduje z ${this.countries[0].trailsCount} szczegółowo opisanymi trasami`
                },
                {
                    icon: 'mdi-image-filter-hdr-outline',
                    title: 'Eksploruj parki narodowe',
                    description: 'Ponad 17 parków narodowych w 3 krajach'
                },
                {
                    icon: 'mdi-shield-check',
                    title: 'Zweryfikowane trasy',
                    description: 'Każdy szlak jest dokładnie sprawdzony i opisany'
                }
            ]
        }
    }
}
</script>
