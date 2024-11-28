<template>
    <v-container style="min-height: 200px">
        <template v-if="points?.length">
<!--            <div class="d-flex justify-end mb-4">
                <v-btn-toggle
                    v-model="sortDirection"
                    mandatory
                    density="comfortable"
                    color="primary"
                >
                    <v-btn value="asc" variant="text">
                        <v-icon>mdi-sort-ascending</v-icon>
                    </v-btn>
                    <v-btn value="desc" variant="text">
                        <v-icon>mdi-sort-descending</v-icon>
                    </v-btn>
                </v-btn-toggle>
            </div>-->
            <v-timeline side="end" align="start" density="comfortable">
                <v-timeline-item
                    v-for="point in sortedPoints"
                    :key="point.id"
                    :dot-color="getPointColor(point.point_type_key)"
                    :icon="getPointIcon(point.point_type_key)"
                    size="large"
                >

                    <template v-slot:opposite>
                        <div v-if="point.length_at !== -1" class="text-caption font-weight-bold" v-tooltip="'Kilometr na rzece'">
                            {{ point.length_at }} km
                        </div>
                    </template>
                    <v-card flat min-width="200" max-width="400">
                        <v-row class="ma-1">
                            <v-col cols="12" class="pa-2">
                                <div class="d-flex flex-column">
                                    <span class="font-weight-bold text-capitalize">{{ point.name || point.point_type_key}} <v-chip v-if="point.name" :color="getPointColor(point.point_type_key)" size="small">{{point.point_type_key}}</v-chip></span>
                                    <span v-if="point.description" class="text-body-2 text-medium-emphasis mt-1">
                                       {{ point.description }}
                                   </span>
                                    <span v-if="isValidLocation(point)"
                                          class="text-caption text-grey-darken-2 mt-2">
                                       {{ point.lat }}, {{ point.lng }}
                                   </span>
                                </div>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-timeline-item>
            </v-timeline>

        </template>
        <template v-else>
            <empty-tab-content icon="mdi-map-marker-off-outline">Brak punktów do wyświetlenia</empty-tab-content>
        </template>
    </v-container>
</template>

<script>
import EmptyTabContent from "@/modules/trails/components/TrailDetails/EmptyTabContent.vue";
import MapMixin from "@/mixins/MapMixin.js";
import {mapGetters} from "vuex";
export default {
    name: "PointsTabContent" ,
    components: {EmptyTabContent},
    mixins: [MapMixin],
    data() {
        return {
            sortDirection: 'desc'
        }
    },
    computed: {
        ...mapGetters('trails', ['currentTrail']),
        points() {
            return this.currentTrail.points
        },
        sortedPoints() {
            if (!this.points?.length) return [];

            return [...this.points].sort((a, b) => {
                if (this.sortDirection === 'asc') {
                    return a.length_at - b.length_at;
                }
                return b.length_at - a.length_at;
            });
        }
    },
    methods: {
        isValidLocation(point) {
            return point.lat !== "-1.0000000" && point.lng !== "-1.0000000";
        }
    }
}
</script>


<style scoped>
.v-timeline-item__body {
    padding: 0 !important;
}

.v-timeline-item__dot--small {
    padding: 4px;
}

:deep(.v-timeline-item__opposite) {
    margin-top: 12px;
    flex: 0 1 80px !important;
}
</style>
