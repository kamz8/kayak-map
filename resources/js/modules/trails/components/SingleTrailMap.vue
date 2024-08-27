<template>
    <div class="map-container">
        <l-map
            ref="map"
            :zoom="zoom"
            :center="mapCenter"
            :options="{ zoomControl: false, preferCanvas: true, maxZoom: 18, minZoom: 7 }"
            @ready="onMapReady"
        >
            <l-tile-layer :url="url" :attribution="attribution" />

            <l-polyline
                v-if="trailPath.length > 1"
                :lat-lngs="trailPath"
                :color="trailColor"
                :weight="4"
                :opacity="0.8"
            />

            <l-marker :lat-lng="startPoint" >
                <l-icon class="start-icon">
                    <v-icon size="34" class="icon-with-stroke" color="green darken-5">mdi-map-marker-circle</v-icon>
                </l-icon>
                <l-popup :options="popupOptions">
                    <v-card outlined flat>
                        <v-row>
                            <v-col cols="12">
                                <span class="font-weight-bold">Początek</span>
                                <v-spacer></v-spacer>
                                <span>{{startPoint[0]}}, {{startPoint[1]}}</span>
                            </v-col>
                        </v-row>
                    </v-card>


                </l-popup>
            </l-marker>

            <l-marker v-if="isValidEndPoint" :lat-lng="endPoint">
                <l-icon class="end-icon">
                    <v-icon class="icon-with-stroke" size="32" color="red">mdi-flag-checkered</v-icon>
                </l-icon>
                <l-popup :options="popupOptions">
                    <v-card outlined flat>
                        <v-row>
                            <v-col cols="12">
                                <span class="font-weight-bold">Koniec</span>
                                <v-spacer></v-spacer>
                                <span>{{endPoint[0]}}, {{endPoint[1]}}</span>
                            </v-col>
                        </v-row>
                    </v-card>


                </l-popup>
            </l-marker>

            <!-- Trail Points -->
            <l-marker v-for="point in trailPoints" :key="point.id" :lat-lng="[point.lat, point.lng]">
                <l-icon class-name="point-icon">
                    <v-icon size="32" class="icon-with-stroke" :color="getPointColor(point.point_type_key)">{{ getPointIcon(point.point_type_key) }}</v-icon>
                </l-icon>
                <l-popup :options="popupOptions">
                    <v-card class="point-popup" width="300" max-height="120" outlined>
                        <v-row no-gutters>
                            <v-col cols="4">
                                <v-img
                                    :src="placeholderImage"
                                    height="100%"
                                    width="100"
                                    cover
                                >
                                    <template v-slot:placeholder>
                                        <v-row class="fill-height ma-0" align="center" justify="center">
                                            <v-progress-circular indeterminate color="grey-lighten-5"></v-progress-circular>
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
                                    <v-card-text v-if="point.description" class="pa-0 text-truncate">
                                        {{ point.description }}
                                    </v-card-text>
                                    <v-card-text v-else class="pa-0 pt-3 text-truncate text-subtitle-2">
                                        Brak opisu dla punktu
                                    </v-card-text>
                                </v-card-item>
                            </v-col>
                        </v-row>
                    </v-card>
                </l-popup>
            </l-marker>
            <!--map controls-->
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
        </l-map>




    </div>
</template>

<script>
import {LMap, LTileLayer, LPolyline, LMarker, LIcon, LPopup} from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import { mapState } from 'vuex'

export default {
    name: 'SingleTrailMap',
    components: {
        LPopup,
        LMap,
        LTileLayer,
        LPolyline,
        LMarker,
        LIcon
    },
    data() {
        return {
            zoom: 12,
            mapCenter: [52.237049, 21.017532], // Default center (Warsaw, Poland)
            url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            attribution: '© OpenStreetMap contributors',
            trailColor: '#4682B4', // Steel Blue color from your color scheme
            showLayerOptions: false,
            popupOptions: {
                closeButton: false,
                className: 'custom-popup'

            }
        }
    },
    computed: {
        ...mapState('trails', ['currentTrail']),
        trailPath() {
            if (!this.currentTrail || !this.currentTrail.river_track) return []

            const trackData = this.currentTrail.river_track
            if (Array.isArray(trackData)) {
                return trackData.filter(point => this.isValidLatLng(point.lat, point.lng))
                    .map(point => [point.lat, point.lng])
            } else if (typeof trackData === 'object' && trackData.track_points) {
                return trackData.track_points.filter(point => this.isValidLatLng(point.lat, point.lng))
                    .map(point => [point.lat, point.lng])
            }

            return []
        },
        startPoint() {
            if (this.trailPath.length > 0) {
                return this.trailPath[0]
            }
            return this.isValidLatLng(this.currentTrail?.start_lat, this.currentTrail?.start_lng)
                ? [this.currentTrail.start_lat, this.currentTrail.start_lng]
                : null
        },
        endPoint() {
            if (this.trailPath.length > 1) {
                return this.trailPath[this.trailPath.length - 1]
            }
            return this.isValidLatLng(this.currentTrail?.end_lat, this.currentTrail?.end_lng)
                ? [this.currentTrail.end_lat, this.currentTrail.end_lng]
                : null
        },
        isValidStartPoint() {
            return this.startPoint !== null
        },
        isValidEndPoint() {
            return this.endPoint !== null
        },
        trailLength() {
            return this.currentTrail?.trail_length || 0
        },
        trailPoints() {
            const points = this.currentTrail?.points || []
            console.log('Trail points:', points)
            return points
        }
    },
    methods: {
        onMapReady(mapInstance) {
            console.log('Map ready')
            this.mapInstance = mapInstance
            this.fitMapToTrail()
        },
        fitMapToTrail() {
            console.log('Fitting map to trail')
            if (this.isValidStartPoint) {
                this.mapCenter = [...this.startPoint]
                console.log('Setting map center to:', this.mapCenter)

                if (this.trailPath.length > 1 && this.mapInstance) {
                    const bounds = this.calculateBounds()
                    if (bounds) {
                        this.mapInstance.fitBounds(bounds, { padding: [50, 50] })
                        this.zoom = this.mapInstance.getZoom()
                    }
                } else {
                    this.zoom = this.calculateZoomFromTrailLength()
                }

                if (this.mapInstance) {
                    this.mapInstance.panTo(this.mapCenter)
                }
            }
        },
        calculateBounds() {
            if (this.trailPath.length < 2) return null
            return this.trailPath.reduce(
                (bounds, point) => bounds.extend(point),
                L.latLngBounds(this.trailPath[0], this.trailPath[0])
            )
        },
        isValidLatLng(lat, lng) {
            return typeof lat === 'number' && typeof lng === 'number' &&
                !isNaN(lat) && !isNaN(lng) &&
                lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180
        },
        calculateZoomFromTrailLength() {
            if (this.trailLength < 1000) return 18
            if (this.trailLength < 2000) return 14
            if (this.trailLength < 5000) return 13
            if (this.trailLength < 20000) return 11
            if (this.trailLength < 50000) return 9
            return 7
        },
        getPointIcon(pointType) {
            switch (pointType) {
                case 'Pole namiotowe':
                case 'Miejsce biwakowania':
                    return 'mdi-tent'
                case 'Przeszkoda':
                case 'Niebezpieczeństwo':
                case 'uwaga':
                    return 'mdi-alert'
                case 'Jaz':
                    return 'mdi-water'
                case 'most':
                    return 'mdi-bridge'
                case 'przenoska':
                    return 'mdi-arrow-up-down'
                case 'ujście':
                    return 'mdi-call-split'
                case 'sklep':
                    return 'mdi-store'
                case 'Inny':
                case 'Other':
                default:
                    return 'mdi-map-marker'
            }
        },
        getPointColor(pointType) {
            switch (pointType) {
                case 'Pole namiotowe':
                case 'Miejsce biwakowania':
                    return 'green'
                case 'Przeszkoda':
                case 'Niebezpieczeństwo':
                case 'uwaga':
                    return 'red'
                case 'Jaz':
                    return 'blue'
                case 'most':
                    return 'brown'
                case 'przenoska':
                    return 'orange'
                case 'ujście':
                    return 'purple'
                case 'sklep':
                    return 'amber'
                case 'Inny':
                case 'Other':
                default:
                    return 'teal'
            }
        },
        zoomIn() {
            if (this.$refs.map) {
                this.$refs.map.leafletObject.zoomIn()
            }
        },
        zoomOut() {
            if (this.$refs.map) {
                this.$refs.map.leafletObject.zoomOut()
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
                        })
                    })

                    const {latitude, longitude} = position.coords

                    if (isFinite(latitude) && isFinite(longitude)) {
                        this.mapCenter = [latitude, longitude]
                        this.zoom = 10
                    } else {
                        console.error("Received invalid coordinates:", {latitude, longitude})
                    }
                } catch (err) {
                    console.error("Geolocation error:", err.message)
                    // You might want to show an error message to the user here
                }
            } else {
                console.error("Geolocation is not available")
                // You might want to show an error message to the user here
            }
        },
        setTileLayer(layer) {
            switch (layer) {
                case 'terrain':
                    this.url = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png'
                    this.attribution = 'Map data: © OpenStreetMap contributors, SRTM | Map style: © OpenTopoMap (CC-BY-SA)'
                    break
                case 'satellite':
                    this.url = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
                    this.attribution = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                    break
                default:
                    this.url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
                    this.attribution = '© OpenStreetMap contributors'
            }
        },
    },
    watch: {
        currentTrail: {
            handler(newTrail) {
                console.log('Current trail updated:', newTrail)
                this.$nextTick(this.fitMapToTrail)
            },
            immediate: true,
            deep: true
        }
    },
    mounted() {
        console.log('Component mounted, initial center:', this.mapCenter) // Debugging log
    }
}
</script>

<style>
.map-container {
    position: relative;
    height: 100%;
    width: 100%;
}

.map-controls {
    position: absolute;
    z-index: 1000;
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

.start-icon, .end-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: white;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.error-message {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    width: 100%;
    background-color: #f8d7da;
    color: #721c24;
    font-size: 16px;
    padding: 20px;
    text-align: center;
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


.start-icon, .end-icon, .point-icon {
    background-color: white;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}
.point-popup {
    min-width: 200px;
}
.point-popup h3 {
    margin-top: 0;
    margin-bottom: 8px;
}
.point-popup p {
    margin: 4px 0;
}

</style>
