<template>
    <div class="map-container">
        <l-map
            :use-global-leaflet="true"
            ref="map"
            style="height: 100%; width: 100%;"
            :zoom="zoom"
            :center="center"
            :options="{ zoomControl: false, preferCanvas: true, maxZoom: 18, minZoom: 2 }"
            @ready="onMapReady"
            @moveend="onMapMoveEnd"
            @update:zoom="updateZoom"
            @click="handleMapClick"
        >
            <l-tile-layer :url="url" :attribution="attribution"/>
            <l-marker-cluster-group v-bind="clusterOptions" :icon-create-function="createClusterIcon">
            <map-markers
                :trails="trails"
                :active-trail="activeTrail"
                :highlighted-trail="highlightedTrail"
                @select-trail="selectTrail"
                @highlight-trail="highlightTrail"
                @clear-highlight-trail="clearHighlightTrail"
                @view-trail-details="viewTrailDetails"
                @clear-active-trail="clearActiveTrail"
            />
            </l-marker-cluster-group>
            <l-polyline
                v-if="activeTrailCoords && activeTrailCoords.length > 0"
                :lat-lngs="activeTrailCoords"
                :color="$vuetify.theme.current.colors['river-path']"
                :weight="5"
                :opacity="1"
                :lineCap="'round'"
                :lineJoin="'round'"
                class="trail-path"
            />
            <l-polyline
                v-if="highlightedTrailCoords && highlightedTrailCoords.length"
                :lat-lngs="highlightedTrailCoords"
                :color="$vuetify.theme.current.colors['highlight-path']"
                :weight="3"
                :opacity="0.7"
                :lineCap="'round'"
                :lineJoin="'round'"
                class="highlight-path"
            />
        </l-map>

        <div class="map-controls top-right-controls">
            <div class="layer-control" @mouseenter="showLayerOptions = true" @mouseleave="showLayerOptions = false">
                <v-btn icon="mdi-layers" density="comfortable" class="main-button" v-tooltip="'Warstwy mapy'"/>
                <transition name="fade">
                    <div v-if="showLayerOptions" class="layer-options">
                        <v-btn icon="mdi-map" class="layer-button" @click="setTileLayer('default')" v-tooltip="'Mapa domyślna'"/>
                        <v-btn icon="mdi-terrain" class="layer-button" @click="setTileLayer('terrain')" v-tooltip="'Mapa terenu'"/>
                        <v-btn icon="mdi-satellite-variant" class="layer-button" @click="setTileLayer('satellite')" v-tooltip="'Mapa satelitarna'"/>
                    </div>
                </transition>
            </div>
        </div>
        <div class="map-controls bottom-right-controls">
            <v-btn icon="mdi-plus" density="comfortable" class="control-button" @click="zoomIn" v-tooltip="'Przybliż'"/>
            <v-btn icon="mdi-minus" density="comfortable" class="control-button" @click="zoomOut" v-tooltip="'Oddal'"/>
            <v-btn icon="mdi-crosshairs-gps" density="comfortable" class="control-button" @click="locate" v-tooltip="'Zlokalizuj mnie'"/>
        </div>

    </div>
</template>

<script>
import { LMap, LTileLayer, LPolyline } from '@vue-leaflet/vue-leaflet';
import { LMarkerClusterGroup } from 'vue-leaflet-markercluster';
import { mapActions, mapGetters } from 'vuex';
import MapControls from './Map/MapControls.vue';
import MapMarkers from './Map/MapMarkers.vue';

export default {
    name: "MapView",
    components: {
        LMap,
        LTileLayer,
        LPolyline,
        MapControls,
        MapMarkers,
        LMarkerClusterGroup
    },
    data() {
        return {
            zoom: 7,
            center: [52.0689, 19.4803], // Środek Polski
            url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            attribution: 'Leaflet.js | © OpenStreetMap contributors',
            mapInstance: null,
            showLayerOptions: false,
            clusterOptions: {
                maxClusterRadius: 100,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
                disableClusteringAtZoom: 12,
                removeOutsideVisibleBounds: true,
                chunkedLoading: true,
                animate: true
            },
        };
    },

    computed: {
        ...mapGetters('trails', ['trails', 'activeTrail', 'highlightedTrail', 'boundingBox']),
        activeTrailCoords() {
            return this.activeTrail
                ? this.activeTrail.river_track.track_points.map(point => [point[0], point[1]])
                : [];
        },
        highlightedTrailCoords() {
            return (this.highlightedTrail)
                ? this.highlightedTrail.river_track.track_points
                : [];
        }
    },
    provide() {
        return {
            getLeafletMap: () => this.mapInstance
        };
    },
    watch: {
        '$route.query': 'updateMapFromUrl'
    },
    mounted() {
        this.updateMapFromUrl();
    },
    methods: {
        ...mapActions('trails', [
            'updateBoundingBox',
            'selectTrail',
            'highlightTrail',
            'clearHighlightTrail',
            'clearActiveTrail'
        ]),
        onMapReady(mapInstance) {
            this.mapInstance = mapInstance;
            this.updateBoundingBoxFromMap();
        },
        onMapMoveEnd() {
            this.updateBoundingBoxFromMap();
            this.updateUrlFromMap();
        },
        updateBoundingBoxFromMap() {
            if (!this.mapInstance) return;
            const bounds = this.mapInstance.getBounds();
            const boundingBox = {
                start_lat: bounds.getSouth(),
                end_lat: bounds.getNorth(),
                start_lng: bounds.getWest(),
                end_lng: bounds.getEast()
            };
            this.updateBoundingBox(boundingBox);
        },

        async fetchLocalTrails() {
            if (!this.mapBounds) return;
            const { _southWest: sw, _northEast: ne } = this.mapBounds;
            await this.fetchTrails({
                startLat: sw.lat,
                endLat: ne.lat,
                startLng: sw.lng,
                endLng: ne.lng
            });
        },
        viewTrailDetails(trailId) {
            console.log(`Viewing details for trail ${trailId}`);
            // Implement navigation to trail details page
        },
        updateZoom(newZoom) {
            this.zoom = newZoom;
        },
        zoomIn() {
            if (this.mapInstance) {
                this.mapInstance.zoomIn();
            }
        },
        zoomOut() {
            if (this.mapInstance) {
                this.mapInstance.zoomOut();
            }
        },

        async locate() {
            if ("geolocation" in navigator) {
                try {
                    const position = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        });
                    });

                    const {latitude, longitude} = position.coords;

                    if (isFinite(latitude) && isFinite(longitude)) {
                        this.center = [latitude, longitude];
                        this.zoom = 10;
                    } else {
                        console.error("Received invalid coordinates:", {latitude, longitude});
                        this.setWarszawaLocation();
                    }
                } catch (err) {
                    console.error("Geolocation error:", err.message);
                    this.addMessage({type: 'error', message: 'Wystąpił błąd podczas lokalizowania'});
                    this.setWarszawaLocation();
                }
            } else {
                console.error("Geolokalizacja nie jest dostępna");
                this.addMessage({type: 'error', message: 'Geolokalizacja nie jest dostępna.'});
                this.setWarszawaLocation();
            }
        },
        setWarszawaLocation() {
            this.center = [52.2297, 21.0122]; // Współrzędne Warszawy
            this.zoom = 12;
        },
        updateUrlFromMap() {
            const bounds = this.mapInstance.getBounds();
            const bbox = {
                b_tl_lat: bounds.getNorth().toFixed(6),
                b_tl_lng: bounds.getWest().toFixed(6),
                b_br_lat: bounds.getSouth().toFixed(6),
                b_br_lng: bounds.getEast().toFixed(6)
            };
            this.$router.replace({
                path: this.$route.path,
                query: {
                    ...bbox,
                    zoom: this.zoom
                }
            });
        },
        setTileLayer(layer) {
            switch (layer) {
                case 'terrain':
                    this.url = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
                    break;
                case 'satellite':
                    this.url = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
                    this.attribution = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
                    break;
                default:
                    this.url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                    this.attribution = 'Leaflet.js | © OpenStreetMap contributors';
            }
        },
        updateMapFromUrl() {
            if (!this.mapInstance) {
                console.warn('Map instance not ready');
                return;
            }
            const { b_tl_lat, b_tl_lng, b_br_lat, b_br_lng, zoom } = this.$route.query;

            if (b_tl_lat && b_tl_lng && b_br_lat && b_br_lng && zoom) {
                const bounds = [
                    [parseFloat(b_tl_lat), parseFloat(b_tl_lng)],
                    [parseFloat(b_br_lat), parseFloat(b_br_lng)]
                ];

                this.mapInstance.fitBounds(bounds);
                this.zoom = parseInt(zoom);
            } else {
                this.setWarszawaLocation();
            }
        },
        handleMapClick(event) {
            const clickedOnMarker = event.originalEvent.target.closest('.leaflet-marker-icon');
            if (!clickedOnMarker) {
                this.clearActiveTrail();
            }
        },
        createClusterIcon(cluster) {
            const count = cluster.getChildCount();
            const size = 32;

            return L.divIcon({
                html: `<div><span>${count}</span></div>`,
                className: 'custom-cluster-icon',
                iconSize: L.point(size, size),
                iconAnchor: L.point(size/2, size/2)
            });
        }
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
    z-index: 2;
}

.top-right-controls {
    top: 20px;
    right: 20px;
}

.bottom-right-controls {
    display: flex;
    flex-direction: column;
    gap: 16px;
    bottom: 20px;
    right: 20px;
}

.layer-control {
    position: relative;
}

.main-button,
.layer-button,
.control-button {
    border-radius: 50% !important;
    width: 40px !important;
    height: 40px !important;
    background-color: white !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
}

.layer-options {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s, transform 0.3s;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.trail-path {
    stroke: var(--v-theme-secondary);
    stroke-width: 5;
    stroke-opacity: 1;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
}

.trail-path.active {
    box-shadow: 0 0 10px 2px rgba(0, 0, 0, 0.5);
}

:deep(.custom-cluster-icon) {
    background-color: v-bind('$vuetify.theme.current.colors.anchor');
    color: white;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;

}

:deep(.custom-cluster-icon div) {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

:deep(.custom-cluster-icon span) {
    font-weight: bold;
    font-size: 14px;
}
</style>

