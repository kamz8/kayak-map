<template>
    <div class="map-container">
        <l-map
            :use-global-leaflet="true"
            ref="map"
            style="height: 100%; width: 100%;"
            :zoom="zoom"
            :center="center"
            :bounds="bounding"
            :options="{ zoomControl: false, preferCanvas: true, maxZoom: 18, minZoom: 2 }"
        >
            <!-- Warstwa mapy domyślnej -->
            <l-tile-layer :url="url" :attribution="attribution"/>

            <!-- Klastry markerów -->
            <l-marker-cluster-group v-bind="clusterOptions">
                <map-markers
                    :trails="trails"
                    @highlight-trail="highlightTrail"
                    @clear-highlight-trail="clearHighlightTrail"
                    @show-popup="showPopup"
                />
            </l-marker-cluster-group>

            <!-- Podświetlona ścieżka szlaku -->
            <l-polyline
                v-if="highlightedTrailCoords && highlightedTrailCoords.length > 0"
                :lat-lngs="highlightedTrailCoords"
                :color="$vuetify.theme.current.colors['highlight-path']"
                :weight="3"
                :opacity="0.7"
                :lineCap="'round'"
                :lineJoin="'round'"
                class="highlight-path"
            />

            <!-- Popup z informacjami o trasie -->
            <mini-popup v-if="popupData" :trail="popupData" @close="popupData = null" />
        </l-map>

        <!-- Kontrolki Zoomu -->
        <div class="map-controls bottom-left-controls">
            <v-btn icon="mdi-plus" @click="zoomIn" class="control-button" />
            <v-btn icon="mdi-minus" @click="zoomOut" class="control-button" />
        </div>
        <slot></slot>
    </div>
</template>

<script>
import { LMap, LTileLayer, LPolyline } from "@vue-leaflet/vue-leaflet";
import { LMarkerClusterGroup } from "vue-leaflet-markercluster";
import MapMarkers from "@/modules/trails/components/Map/MapMarkers.vue";
import MiniPopup from "@/modules/trails/components/Map/MiniPopup.vue";

export default {
    name: "MiniMap",
    components: {LMap, MapMarkers, LPolyline},
    omponents: {
        LMap,
        LTileLayer,
        LPolyline,
        LMarkerClusterGroup,
        MapMarkers,
        MiniPopup,
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
        },
    },
    data() {
        return {
            zoom: 7,
            url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            attribution: "Leaflet.js | © OpenStreetMap contributors",
            clusterOptions: {
                maxClusterRadius: 100,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
            },
            highlightedTrailCoords: [],
            popupData: null, // Dane do miniPopup
        }
    },
    methods: {
        zoomIn() {
            if (this.zoom < 18) this.zoom += 1
        },
        zoomOut() {
            if (this.zoom > 2) this.zoom -= 1
        },
        highlightTrail(trail) {
            this.highlightedTrailCoords = trail.river_track.track_points.map(point => [point[0], point[1]])
        },
        clearHighlightTrail() {
            this.highlightedTrailCoords = []
        },
        showPopup(trail) {
            this.popupData = trail // Wyświetla popup z danymi trasy
        },
    },
}
</script>

<style scoped>
.map-container {
    position: relative;
    height: 100%;
    width: 100%;
}

.map-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.control-button {
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    border-radius: 50%;
    width: 36px;
    height: 36px;
}

.highlight-path {
    stroke-opacity: 0.7;
}

.bottom-left-controls {
    display: flex;
    flex-direction: column;
    gap: 16px;
    bottom: 20px;
    left: 20px;
}
</style>
