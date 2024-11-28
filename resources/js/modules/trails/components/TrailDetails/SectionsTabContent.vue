<template>
        <v-container style="min-height: 200px">
            <template v-if="sortedSections?.length">
                <v-row class="mb-6">
                    <v-col cols="12">
                        <v-card-text class="text-left">
                            <div class="text-h5 mb-2">Odcinki szlaku</div>
                            <div class="text-subtitle-1 text-medium-emphasis">
                                Odcinki szlaku kajakowego są segmentami trasy o podobnej charakterystyce, trudności i walorach.
                                Każdy odcinek posiada ocenę malowniczości (1-10), poziom uciążliwości (U1-U6) oraz ocenę czystości wody (KL1-KL3).
                            </div>
                        </v-card-text>
                    </v-col>
                </v-row>

                <v-timeline side="end" align="start" density="comfortable" line-inset="12">
                    <v-timeline-item
                        dot-color="river-blue"
                        v-for="(section, index) in sortedSections"
                        :key="section.id"
                    >
                        <template v-slot:opposite>
                            <div class="text-h5 font-weight-bold text-teal-darken-2">
                                {{ getLetterForIndex(index) }}
                            </div>
                        </template>

                        <template v-slot:dot>
                            <v-avatar color="river-blue">
                                {{ getLetterForIndex(index) }}
                            </v-avatar>
                        </template>

                        <v-card elevation="2" class="section-card">
                            <v-card-title class="text-h6 text-capitalize">
                                {{ section.name }}
                            </v-card-title>

                            <v-card-text>
                                <div class="text-body-2 text-medium-emphasis mb-4 text-capitalize">
                                    {{ section.description }}
                                </div>

                                <v-row dense>
                                    <v-col cols="12" sm="6" md="3" class="d-flex align-center">
                                        <v-icon icon="mdi-forest" color="footer" class="mr-2"/>
                                        <span>Malowniczość: {{ section.scenery }}/10</span>
                                    </v-col>

                                    <v-col cols="12" sm="6" md="3" class="d-flex align-center">
                                        <v-icon icon="mdi-waves" color="deep-purple" class="mr-2"/>
                                        <span>Uciążliwość: {{ section.nuisance }}</span>
                                    </v-col>

                                    <v-col cols="12" sm="6" md="3" class="d-flex align-center">
                                        <v-icon icon="mdi-water-check" color="blue" class="mr-2"/>
                                        <span>Czystość: {{ section.cleanliness }}</span>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-timeline-item>
                </v-timeline>
            </template>
            <template v-else>
                <empty-tab-content icon="mdi-map-marker-path">Brak odcinków trasy do wyświetlenia</empty-tab-content>
            </template>
        </v-container>
</template>

<script>
import EmptyTabContent from "@/modules/trails/components/TrailDetails/EmptyTabContent.vue";
import {mapGetters} from "vuex";

export default {
    name: "SectionsTabContent" ,
    components: {EmptyTabContent},
    computed: {
        ...mapGetters('trails', ['currentTrail']),
        sortedSections() {
            if (!this.currentTrail) return []
            return [...this.currentTrail.sections].sort((a, b) => b.id - a.id);
        }
    },
    methods: {
        getLetterForIndex(index) {
            return String.fromCharCode(65 + index);
        },
    }
}
</script>



<style scoped>
.section-card {
    border-radius: 8px;
    transition: transform 0.2s;
}

.section-card:hover {
    transform: translateY(-2px);
}

:deep(.v-timeline-item__opposite) {
    flex: 0 1 50px !important;
    text-align: center;
}

:deep(.v-avatar) {
    font-weight: bold;
    color: white;
}

.v-timeline .v-timeline-divider {
    border-left: none !important;
    background-image: radial-gradient(
        circle,
        var(--v-border-color) 1px,
        transparent 1px
    );
    background-size: 2px 8px;
    background-repeat: repeat-y;
    background-position: center;
}

/* Wzór zygzak */
.v-timeline .v-timeline-divider::after {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    width: 2px;
    height: 100%;
    background:
        linear-gradient(
            45deg,
            transparent 33.333%,
            var(--v-border-color) 33.333%,
            var(--v-border-color) 66.667%,
            transparent 66.667%
        );
    background-size: 4px 12px;
    transform: translateX(-50%);
}
:deep(.v-timeline .v-timeline-item__body) {
    min-width: 100%;
}
</style>
