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
            <v-row>
                <v-col class="text-center">
                    <v-btn v-if="country.pagination && country.pagination.has_more_pages"
                           variant="tonal" color="primary"
                           rounded="lg"
                           :loading="isLoadMore"
                           density="default"
                           size="large"
                           @click="requestLoadMore"
                    >Pokaż więcej</v-btn>
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
        },
        loadingRegions: {
            type: Boolean,
            default: false,
        }
    },
    setup () {
        const goTo = useGoTo()
        return { goTo }
    },
    data() {
        return {
            isLoadMore: false,
        }
    },
    provide() {
        return {
            loadMoreRegions: this.requestLoadMore,
            setRegionsLoadingState: this.setLoadingState,
            getRegionsLoadingState: () => this.isLoadMore
        }
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
        requestLoadMore() {
            if (!this.country.pagination.has_more_pages) return;

            this.isLoadMore = true;
            const nextPage = this.country.pagination.current_page + 1;
            const payload = {
                page: nextPage,
                per_page: 8
            }
            this.$emit('load-more', payload);
        },
        setLoadingState(state) {
            this.isLoadMore = state;
        }
    },
    watch: {
        loadingRegions(newVal) {
            if (newVal === false) {
                this.isLoadMore = false;
            }
        }
    },
    emits: ['clear-selection', 'load-more']
}
</script>
