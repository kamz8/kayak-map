<template>
    <div class="map-container">
        <l-map
            ref="map"
            :use-global-leaflet="false"
            :zoom="zoom"
            :center="center"
            :options="mapOptions"
            @ready="onMapReady"
        >
            <l-tile-layer :url="url" :attribution="attribution"/>

            <template v-if="isMapReady">
                <l-marker-cluster-group v-bind="clusterOptions">
                    <map-markers
                        :trails="trails"
                        :active-trail="activeTrail"
                        :highlighted-trail="highlightedTrail"
                        @highlight-trail="highlightTrail"
                        @clear-highlight-trail="clearHighlightTrail"
                    />
                </l-marker-cluster-group>

                <l-polyline
                    v-if="shouldShowPolyline"
                    :lat-lngs="highlightedTrailCoords"
                    :color="$vuetify.theme.current.colors['highlight-path']"
                    :weight="3"
                    :opacity="0.7"
                />
            </template>
        </l-map>

        <div class="map-controls">
            <v-btn icon="mdi-plus" @click="zoomIn" class="control-button" />
            <v-btn icon="mdi-minus" @click="zoomOut" class="control-button" />
        </div>
        <div class="overlay-wrapper">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import { LMap, LTileLayer, LPolyline } from "@vue-leaflet/vue-leaflet";
import { LMarkerClusterGroup } from "vue-leaflet-markercluster";
import MapMarkers from "@/modules/trails/components/Map/MapMarkers.vue";

export default defineComponent({
    name: "MiniMap",
    components: {
        LMap,
        LTileLayer,
        LPolyline,
        LMarkerClusterGroup,
        MapMarkers
    },
    props: {
        center: {
            type: Array,
            default: () => [52.0689, 19.4803],
        },
        bounding: {
            type: Array,
            required: true,
        },
        trails: {
            type: Array,
            default: () => [],
        }
    },
    setup() {
        const isMapReady = ref(false);
        const mapInstance = ref(null);
        const highlightedTrail = ref(null);
        const activeTrail = ref(null);
        const highlightedTrailCoords = ref([]);
        const zoom = ref(7);

        const mapOptions = {
            zoomControl: false,
            preferCanvas: true,
            maxZoom: 18,
            minZoom: 8
        };

        const clusterOptions = {
            maxClusterRadius: 100,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
        };

        const shouldShowPolyline = computed(() =>
            isMapReady.value && highlightedTrailCoords.value.length > 0
        );

        return {
            isMapReady,
            mapInstance,
            highlightedTrail,
            activeTrail,
            highlightedTrailCoords,
            zoom,
            mapOptions,
            clusterOptions,
            shouldShowPolyline,
            url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            attribution: "Leaflet.js | © OpenStreetMap contributors",
        };
    },
    methods: {
        onMapReady(map) {
            this.mapInstance = map;

            if (this.bounding?.length === 2) {
                this.$nextTick(() => {
                    map.fitBounds(this.bounding);
                });
            }

            this.isMapReady = true;
        },
        zoomIn() {
            if (this.mapInstance && this.zoom < 18) {
                this.mapInstance.setZoom(this.zoom + 1);
            }
        },
        zoomOut() {
            if (this.mapInstance && this.zoom > 2) {
                this.mapInstance.setZoom(this.zoom - 1);
            }
        },
        highlightTrail(trail) {
            if (!trail?.river_track?.track_points) return;

            this.highlightedTrail = trail;
            this.highlightedTrailCoords = trail.river_track.track_points;
        },
        clearHighlightTrail() {
            this.highlightedTrail = null;
            this.highlightedTrailCoords = [];
        },
    },
    watch: {
        'bounding': {
            handler(newBounds) {
                if (!this.mapInstance || !newBounds?.length) return;

                this.$nextTick(() => {
                    this.mapInstance.fitBounds(newBounds);
                });
            },
            deep: true
        }
    }
});
</script>

<style scoped>
.map-container {
    position: relative;
    height: 100%;
    width: 100%;
}

.map-controls {
    position: absolute;
    bottom: 10px;
    left: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    z-index: 999;
}

.control-button {
    background-color: white !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    border-radius: 50% !important;
    width: 36px !important;
    height: 36px !important;
}

.overlay-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    display: flex;
    justify-content: center;
    align-items: flex-end;
    padding-bottom: 2rem;
    pointer-events: none;
}

/* Przywracamy interakcje dla elementów w overlay */
.overlay-wrapper :deep(*) {
    pointer-events: auto;
}

</style>
