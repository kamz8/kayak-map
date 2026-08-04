<template>
    <div class="map-container">
        <l-map
            ref="map"
            :zoom="zoom"
            :center="mapCenter"
            :options="{ zoomControl: false, preferCanvas: true, maxZoom: 18, minZoom: 7 }"
            @ready="onMapReady"
        >
            <l-tile-layer :url="url" :attribution="attribution"/>

            <l-polyline
                v-if="trailPath.length > 1"
                :lat-lngs="trailPath"
                :color="trailColor"
                :weight="4"
                :opacity="0.8"
            />

            <l-marker :lat-lng="startPoint">
                <l-icon class="start-icon">
                    <v-icon size="34" class="icon-with-stroke" color="green darken-5">mdi-map-marker-circle</v-icon>
                </l-icon>
                <l-popup :options="miniPopupOptions">
                    <mini-popup :lat-lang="startPoint" :text="'Początek'"/>
                </l-popup>
            </l-marker>

            <l-marker v-if="isValidEndPoint" :lat-lng="endPoint">
                <l-icon class="end-icon">
                    <v-icon class="icon-with-stroke" size="32" color="red">mdi-flag-checkered</v-icon>
                </l-icon>
                <l-popup :options="miniPopupOptions">
                    <v-card flat width="200px" height="60px">
                        <v-row class="ma-1">
                            <v-col cols="12" class="pa-2">
                                <span class="font-weight-bold">Koniec</span>
                                <v-spacer></v-spacer>
                                <span style="line-height: 1.8em" class="text-grey-darken-2">{{ endPoint[0] }}, {{ endPoint[1] }}</span>
                            </v-col>
                        </v-row>
                    </v-card>
                </l-popup>
            </l-marker>

            <l-marker
                v-for="point in validTrailPoints"
                :key="point.id"
                :lat-lng="[parseFloat(point.lat), parseFloat(point.lng)]"
                :class="{ 'highlighted-marker': selectedPointId === point.id }"
                @click="onPointMarkerClick(point)"
            >
                <l-icon class-name="point-icon">
                    <v-icon size="32" class="icon-with-stroke" :color="getPointColor(point.point_type_key)">
                        {{ getPointIcon(point.point_type_key) }}
                    </v-icon>
                </l-icon>
                <l-popup :options="popupOptions">
                    <v-card class="point-popup" width="300" max-height="120" outlined>
                        <v-row no-gutters>
                            <v-col cols="4">
                                <v-img :src="placeholderImage" height="100%" width="100" cover>
                                    <template v-slot:placeholder>
                                        <v-row class="fill-height ma-0" align="center" justify="center">
                                            <v-progress-circular indeterminate color="grey-lighten-5"/>
                                        </v-row>
                                    </template>
                                </v-img>
                            </v-col>
                            <v-col cols="8">
                                <v-card-item>
                                    <v-card-title class="text-subtitle-1 font-weight-bold pa-0">
                                        {{ point.name }}
                                        <v-chip :color="getPointColor(point.point_type_key)" size="small" class="mr-2">
                                            {{ point.point_type_key }}
                                        </v-chip>
                                    </v-card-title>
                                    <v-card-text v-if="point.description" class="pa-0">{{ point.description }}</v-card-text>
                                    <v-card-text v-else class="pa-0 pt-3 text-subtitle-2">Brak opisu dla punktu</v-card-text>
                                </v-card-item>
                            </v-col>
                        </v-row>
                    </v-card>
                </l-popup>
            </l-marker>
        </l-map>

        <MapControls
            @change-layer="setTileLayer"
            @zoom-in="zoomIn"
            @zoom-out="zoomOut"
            @locate="locate"
        />
    </div>
</template>

<script>
import { LMap, LTileLayer, LPolyline, LMarker, LIcon, LPopup } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import { mapGetters, mapState, mapActions } from 'vuex'
import MiniPopup from "@/modules/trails/components/Map/MiniPopup.vue"
import MapControls from "@/modules/trails/components/Map/MapControls.vue"
import MapMixin from "@/mixins/MapMixin.js"

export default {
    name: 'SingleTrailMap',
    components: {
        MiniPopup,
        MapControls,
        LPopup,
        LMap,
        LTileLayer,
        LPolyline,
        LMarker,
        LIcon
    },
    mixins: [MapMixin],
    props: {
        staticMode: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            zoom: 12,
            mapCenter: [52.237049, 21.017532],
            url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            attribution: '© OpenStreetMap contributors',
            trailColor: '#4682B4',
            showLayerOptions: false,
            popupOptions: {
                closeButton: false,
                className: 'custom-popup'
            },
            miniPopupOptions: {
                closeButton: false,
                className: 'mini-popup'
            }
        }
    },
    computed: {
        ...mapState('trails', ['currentTrail']),
        ...mapGetters('trails', ['selectedPointId']),
        startPoint() {
            return this.isValidLatLng(this.currentTrail?.start_lat, this.currentTrail?.start_lng)
                ? [this.currentTrail.start_lat, this.currentTrail.start_lng]
                : null;
        },
        endPoint() {
            return this.isValidLatLng(this.currentTrail?.end_lat, this.currentTrail?.end_lng)
                ? [this.currentTrail.end_lat, this.currentTrail.end_lng]
                : null;
        },
        trailPath() {
            const coordinates = this.currentTrail?.river_track?.track_points?.coordinates;
            if (!Array.isArray(coordinates)) return [];
            return this.convertCoordinates(coordinates);
        },
        isValidStartPoint() {
            return this.startPoint !== null;
        },
        isValidEndPoint() {
            return this.endPoint !== null;
        },
        trailLength() {
            return this.currentTrail?.trail_length || 0;
        },
        trailPoints() {
            return this.currentTrail?.points || [];
        },
        validTrailPoints() {
            return this.trailPoints.filter(point =>
                this.isValidLatLng(parseFloat(point.lat), parseFloat(point.lng))
            );
        }
    },
    watch: {
        currentTrail: {
            handler() {
                this.$nextTick(this.fitMapToTrail);
            },
            immediate: true,
            deep: true
        },
        selectedPointId(newPointId) {
            if (!newPointId) return;
            const point = this.trailPoints.find(p => p.id === newPointId);
            if (point && this.isValidLatLng(parseFloat(point.lat), parseFloat(point.lng))) {
                this.moveToPoint({
                    lat: parseFloat(point.lat),
                    lng: parseFloat(point.lng),
                    zoom: 16
                });
            }
        }
    },
    methods: {
        ...mapActions('trails', ['selectPoint']),
        onMapReady(mapInstance) {
            this.mapInstance = mapInstance;
            this.fitMapToTrail();
        },
        fitMapToTrail() {
            if (!this.isValidStartPoint) return;
            this.mapCenter = [...this.startPoint];
            if (this.trailPath.length > 1 && this.mapInstance) {
                const bounds = this.calculateBounds();
                if (bounds) {
                    this.mapInstance.fitBounds(bounds, { padding: [50, 50] });
                    this.zoom = this.mapInstance.getZoom();
                }
            } else {
                this.zoom = this.calculateZoomFromTrailLength();
            }
            this.mapInstance?.panTo(this.mapCenter);
        },
        calculateBounds() {
            if (this.trailPath.length < 2) return null;
            return this.trailPath.reduce(
                (bounds, point) => bounds.extend(point),
                window.L.latLngBounds(this.trailPath[0], this.trailPath[1])
            );
        },
        calculateZoomFromTrailLength() {
            if (this.trailLength < 1000) return 18;
            if (this.trailLength < 2000) return 14;
            if (this.trailLength < 5000) return 13;
            if (this.trailLength < 20000) return 11;
            if (this.trailLength < 50000) return 9;
            return 7;
        },
        moveToPoint({ lat, lng, zoom = 16 }) {
            if (!this.mapInstance || !this.isValidLatLng(lat, lng)) return;
            this.mapCenter = [lat, lng];
            this.zoom = zoom;
            this.mapInstance.setView([lat, lng], zoom);
        },
        onPointMarkerClick(point) {
            this.selectPoint(point);
        },
        zoomIn() {
            this.$refs.map?.leafletObject?.zoomIn();
        },
        zoomOut() {
            this.$refs.map?.leafletObject?.zoomOut();
        },
        async locate() {
            if (!("geolocation" in navigator)) return;
            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                });
                const { latitude, longitude } = position.coords;
                if (isFinite(latitude) && isFinite(longitude)) {
                    this.mapCenter = [latitude, longitude];
                    this.zoom = 10;
                }
            } catch {
                // geolocation unavailable or denied
            }
        },
        setTileLayer(layer) {
            switch (layer) {
                case 'terrain':
                    this.url = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
                    this.attribution = 'Map data: © OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)';
                    break;
                case 'satellite':
                    this.url = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
                    this.attribution = 'Tiles &copy; Esri';
                    break;
                default:
                    this.url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                    this.attribution = '© OpenStreetMap contributors';
            }
        },
        isValidLatLng(lat, lng) {
            return typeof lat === 'number' && typeof lng === 'number' &&
                !isNaN(lat) && !isNaN(lng) &&
                lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 &&
                lat !== -1.0 && lng !== -1.0;
        }
    }
}
</script>

<style scoped>
.map-container {
    position: relative;
    height: 100%;
    width: 100%;
}

.start-icon,
.end-icon,
.point-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: white;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.icon-with-stroke {
    filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.5));
}

.point-popup {
    min-width: 200px;
}

:deep(.custom-popup .leaflet-popup .leaflet-popup-content-wrapper) {
    padding: 0;
    overflow: hidden;
    background: none;
    border: none;
    box-shadow: none;
}

:deep(.custom-popup .leaflet-popup-content) {
    margin: 0;
    width: auto !important;
}

:deep(.custom-popup .leaflet-popup-tip-container) {
    display: none;
}

:deep(.mini-popup .leaflet-popup .leaflet-popup-content-wrapper) {
    padding: 0;
    overflow: hidden;
    background: none;
    border: none;
    box-shadow: none;
}

:deep(.mini-popup .leaflet-popup-content) {
    margin: 0;
    width: auto !important;
}

:deep(.mini-popup .leaflet-popup-tip-container) {
    display: none;
}
</style>
