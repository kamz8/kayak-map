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
                    <map-markers
                        :trails="trails"
                        :active-trail="activeTrail"
                        :highlighted-trail="highlightedTrail"
                        @highlight-trail="highlightTrail"
                        @clear-highlight-trail="clearHighlightTrail"
                    />
                <l-polyline
                    v-if="shouldShowPolyline"
                    :lat-lngs="highlightedTrailCoords"
                    :color="$vuetify.theme.current.colors['highlight-path']"
                    :weight="3"
                    :opacity="0.7"
                />
            </template>
        </l-map>

        <div class="map-controls d-print-none">
            <v-btn icon="mdi-plus" @click="zoomIn" class="control-button" />
            <v-btn icon="mdi-minus" @click="zoomOut" class="control-button" />
        </div>
        <div class="overlay-wrapper d-print-none">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import { LMap, LTileLayer, LPolyline } from "@vue-leaflet/vue-leaflet"
import { LMarkerClusterGroup } from "vue-leaflet-markercluster"
import MapMarkers from "@/modules/trails/components/Map/MapMarkers.vue"
import * as L from 'leaflet';
import easyPrint from 'leaflet-easyprint'

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
            validator: value => {
                return Array.isArray(value) &&
                    value.length === 2 &&
                    Array.isArray(value[0]) &&
                    Array.isArray(value[1]);
            }
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
        const printPlugin = null
        const mapOptions = {
            zoomControl: false,
            preferCanvas: true,
            maxZoom: 18,
            minZoom: 5
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
            printPlugin
        };
    },
    methods: {
        onMapReady(map) {
            this.mapInstance = map;

            if (this.bounding) {
                this.$nextTick(() => {
                    try {
                        map.fitBounds(this.bounding);
                    } catch (error) {
                        console.error('Error fitting bounds:', error);
                        map.setView(this.center, this.zoom);
                    }
                });
            }

/*            this.printPlugin = L.easyPrint({
                title: 'Mapa regionu',
                position: 'topright',
                sizeModes: ['Current', 'A4Portrait', 'A4Landscape'],
                exportOnly: false,
                filename: 'mapa-region',
                hideControlContainer: false,
                hidden: true,
                tileWait: 1000,
                timeout: 60000,
                defaultSizeTitles: {
                    Current: 'Aktualny widok',
                    A4Portrait: 'A4 Pionowo',
                    A4Landscape: 'A4 Poziomo'
                },
                buttonClass: 'mdi mdi-printer',
                spinnerClass: 'mdi mdi-loading mdi-spin',
                // Dodaj tę opcję
                customWindowTitle: document.title,
                // Dodaj tę opcję do konwersji fontów na base64
                resourceToDataUrl: async (url) => {
                    if (url.includes('materialdesignicons-webfont')) {
                        const response = await fetch(url);
                        const blob = await response.blob();
                        return new Promise((resolve, reject) => {
                            const reader = new FileReader();
                            reader.onloadend = () => resolve(reader.result);
                            reader.onerror = reject;
                            reader.readAsDataURL(blob);
                        });
                    }
                    return url;
                }
            }).addTo(map);*/

            this.isMapReady = true;
        },
        async printMap(size = 'Current', filename = 'mapa-region') {
            if (!this.printPlugin || !this.mapInstance) return;

            // Czekamy na załadowanie kafelków
            await new Promise(resolve => {
                const checkTiles = () => {
                    const container = this.mapInstance._container;
                    const tiles = container.querySelectorAll('.leaflet-tile-loaded');
                    const loading = container.querySelectorAll('.leaflet-tile-loading');

                    if (loading.length === 0 && tiles.length > 0) {
                        setTimeout(resolve, 500); // Dodatkowe opóźnienie dla pewności
                    } else {
                        setTimeout(checkTiles, 100);
                    }
                };
                checkTiles();
            });

            // Invalidate size przed drukowaniem
            this.mapInstance.invalidateSize();

            await this.$nextTick();

            try {
                this.printPlugin.printMap(size, filename);
            } catch (error) {
                console.error('Błąd podczas drukowania:', error);
                this.$alertError('Wystąpił błąd podczas przygotowywania mapy do druku');
            }
        },

        // Dodaj metodę walidacji bounds
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
        validateAndParseBounds(bounds) {
            if (!bounds || !Array.isArray(bounds) || bounds.length !== 2) {
                return null;
            }

            try {
                return [
                    [parseFloat(bounds[0][0]), parseFloat(bounds[0][1])],
                    [parseFloat(bounds[1][0]), parseFloat(bounds[1][1])]
                ];
            } catch (e) {
                console.warn('Invalid bounds format:', e);
                return null;
            }
        },
    },
    watch: {
        'bounding': {
            handler(newBounds) {
                if (!this.mapInstance || !newBounds) return;

                this.$nextTick(() => {
                    try {
                        // this.mapInstance.fitBounds(newBounds);
                    } catch (error) {
                        console.error('Error updating bounds:', error);
                    }
                });
            },
            deep: true
        }
    },
    beforeUnmount() {
        // Czyszczenie
        if (this.printPlugin) {
            this.printPlugin.remove();
            this.printPlugin = null;
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
    gap: 12px;
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
