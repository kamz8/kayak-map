<template>
    <v-sheet tag="section" id="region-section" color="grey-lighten-4" class="py-16">
        <v-container>
            <v-row justify="center">
                <v-col cols="12" class="text-center">
                    <h2 class="text-h4 font-weight-bold mb-4 font-poppins">Indeks regionów</h2>
                    <p class="text-body-1 font-inter">
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
                        @click="clearSelection"
                    >
                        Powrót do wyboru kraju
                    </v-btn>
                    <v-btn
                        variant="text"
                        color="river-blue-light"
                        :to="'/region/'+country.slug"
                    >
                        Odkryj więcej
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
                    xl="2"
                >
                    <region-card :region="region" :useImageAnalysis="true"/>
                </v-col>
            </v-row>
        </v-container>
    </v-sheet>
</template>

<script>
import RegionCard from './RegionCard.vue'
import AppMixin from "@/mixins/AppMixin.js";
import {useGoTo} from "vuetify";
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
    setup () {
        const goTo = useGoTo()
        return { goTo }
    },
    methods: {
        clearSelection () {
            this.scrollToTop();
            this.$nextTick(() => {
                this.$emit('clear-selection')
            })
        },
        scrollToTop() {
            this.goTo("#app", {
                duration: 300,
                easing: 'easeInOutCubic'
            })
        },
    },
    emits: ['clear-selection']
}
</script>
