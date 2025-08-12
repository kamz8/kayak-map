<template>
    <v-container style="min-height: 200px">
        <template v-if="points?.length">
            <v-timeline side="end" align="start" density="comfortable">
                <v-timeline-item
                    v-for="point in sortedPoints"
                    :key="point?.id || Math.random()"
                    :dot-color="getPointColor(point.point_type_key)"
                    :icon="getPointIcon(point.point_type_key)"
                    size="large"
                    :ref="el => setPointRef(el, point.id)"
                >
                    <template v-slot:opposite>
                        <div v-if="point.length_at !== -1" class="text-caption font-weight-bold"
                             v-tooltip="'Kilometr na rzece'">
                            {{ point.length_at }} km
                        </div>
                    </template>

                    <v-card
                        flat
                        min-width="200"
                        max-width="400"
                        :class="{
                            'clickable-point': isValidLocation(point),
                            'selected-point': selectedPointId === point.id
                        }"
                        @click="handlePointClick(point)"
                        :style="isValidLocation(point) ? 'cursor: pointer;' : ''"
                        :id="`point-card-${point.id}`"
                    >
                        <v-row class="ma-1">
                            <v-col cols="12" class="pa-2">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-center justify-space-between">
                                        <span class="font-weight-bold text-capitalize">
                                            {{ point.name || point.point_type_key }}
                                            <v-chip v-if="point.name" :color="getPointColor(point.point_type_key)"
                                                    size="small">
                                                {{ point.point_type_key }}
                                            </v-chip>
                                        </span>
                                        <v-btn
                                            v-if="isValidLocation(point) && showMapButtons"
                                            icon="mdi-map-marker"
                                            size="small"
                                            variant="text"
                                            color="primary"
                                            @click.stop="handlePointClick(point)"
                                            v-tooltip="'Pokaż na mapie'"
                                        />
                                    </div>

                                    <span v-if="point.description" class="text-body-2 text-medium-emphasis mt-1">
                                       {{ point.description }}
                                    </span>

                                    <span v-if="isValidLocation(point) && point.lat && point.lng"
                                          class="text-caption text-grey-darken-2 mt-2">
                                       {{ point.lat ?? 0 }}, {{ point.lng ?? 0 }}
                                    </span>

                                    <span v-else-if="!isValidLocation(point)"
                                          class="text-caption text-grey mt-2">
                                       <v-icon size="small" color="grey">mdi-map-marker-off</v-icon>
                                       Brak współrzędnych
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
import {mapGetters, mapActions} from "vuex";

export default {
    name: "PointsTabContent",
    components: {EmptyTabContent},
    mixins: [MapMixin],
    emits: ['point-clicked'],
    props: {
        showMapButtons: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            sortDirection: 'desc',
            pointRefs: {} // Store refs for scrolling
        }
    },
    computed: {
        ...mapGetters('trails', ['currentTrail', 'selectedPointId']),
        points() {
            return this.currentTrail?.points || []
        },
        sortedPoints() {
            if (!this.points?.length) return [];

            return this.points.filter(point => point && typeof point === 'object').sort((a, b) => {
                if (this.sortDirection === 'asc') {
                    return (a.length_at || 0) - (b.length_at || 0);
                }
                return (b.length_at || 0) - (a.length_at || 0);
            });
        }
    },
    watch: {
        // Watch for selectedPointId changes from map clicks
        selectedPointId: {
            handler(newPointId) {
                if (newPointId) {
                    this.scrollToPoint(newPointId);
                }
            },
            immediate: true
        }
    },
    methods: {
        ...mapActions('trails', ['selectPoint']),
        handlePointClick(point) {
            if (this.isValidLocation(point)) {
                // Update Vuex state for bidirectional sync
                this.selectPoint(point);
                // Emit event for parent component (map interaction)
                this.$emit('point-clicked', point);
            }
        },
        setPointRef(el, pointId) {
            if (el) {
                this.pointRefs[pointId] = el;
            }
        },
/*        isValidLocation(point) {
            return point &&
                typeof point === 'object' &&
                point.lat !== undefined &&
                point.lng !== undefined &&
                point.lat !== "-1.0000000" &&
                point.lng !== "-1.0000000" &&
                point.lat !== null &&
                point.lng !== null;
        },*/
        scrollToPoint(pointId) {
            setTimeout(() => {
                document.getElementById(`point-card-${pointId}`)?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 300);
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

.clickable-point {
    transition: all 0.2s ease;
}

.clickable-point:hover {
    background-color: rgba(var(--v-theme-primary), 0.04);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.selected-point {
    background-color: rgba(var(--v-theme-primary), 0.08);
    border-left: 4px solid rgb(var(--v-theme-primary));
}

/* Visual feedback for clickable points */
.clickable-point::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: transparent;
    transition: background-color 0.2s ease;
    border-radius: inherit;
    z-index: 0;
}

.clickable-point:hover::before {
    background-color: rgba(var(--v-theme-primary), 0.02);
}
</style>
