<!-- resources/js/components/StaticTrailMap.vue -->
<template>
    <div class="static-map" ref="mapContainer" style="width: 800px; height: 600px;">
        <l-map
            ref="map"
            :zoom="12"
            :center="mapCenter"
            :options="mapOptions"
            @ready="onMapReady"
        >
            <l-tile-layer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                attribution="© OpenStreetMap contributors"
            />

            <l-polyline
                v-if="trailPath.length > 1"
                :lat-lngs="trailPath"
                :color="'#4682B4'"
                :weight="4"
                :opacity="0.8"
            />

            <!-- Start marker -->
            <l-marker v-if="startPoint" :lat-lng="startPoint">
                <l-icon class-name="start-icon">
                    <div class="marker-icon">
                        <i class="mdi mdi-map-marker-circle" style="color: #2E7D32; font-size: 24px;"></i>
                    </div>
                </l-icon>
            </l-marker>

            <!-- End marker -->
            <l-marker v-if="endPoint" :lat-lng="endPoint">
                <l-icon class-name="end-icon">
                    <div class="marker-icon">
                        <i class="mdi mdi-flag-checkered" style="color: #FF0000; font-size: 24px;"></i>
                    </div>
                </l-icon>
            </l-marker>

            <!-- Trail points -->
            <l-marker
                v-for="point in points"
                :key="point.id"
                :lat-lng="[point.lat, point.lng]"
            >
                <l-icon class-name="point-icon">
                    <div class="marker-icon">
                        <i :class="getPointIcon(point.point_type_key)"
                           :style="{ color: getPointColor(point.point_type_key), fontSize: '24px' }">
                        </i>
                    </div>
                </l-icon>
            </l-marker>
        </l-map>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
    LMap,
    LTileLayer,
    LMarker,
    LIcon,
    LPolyline
} from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
    trail: {
        type: Object,
        required: true
    }
})

const mapContainer = ref(null)
const map = ref(null)

const mapOptions = {
    zoomControl: false,
    dragging: false,
    touchZoom: false,
    doubleClickZoom: false,
    scrollWheelZoom: false,
    boxZoom: false,
    keyboard: false
}

const mapCenter = computed(() => {
    if (props.trail.start_lat && props.trail.start_lng) {
        return [props.trail.start_lat, props.trail.start_lng]
    }
    return [52.237049, 21.017532] // Default center
})

const startPoint = computed(() => {
    if (props.trail.start_lat && props.trail.start_lng) {
        return [props.trail.start_lat, props.trail.start_lng]
    }
    return null
})

const endPoint = computed(() => {
    if (props.trail.end_lat && props.trail.end_lng) {
        return [props.trail.end_lat, props.trail.end_lng]
    }
    return null
})

const trailPath = computed(() => {
    if (props.trail.riverTrack?.track_points?.coordinates) {
        return props.trail.riverTrack.track_points.coordinates
    }
    return []
})

const points = computed(() => props.trail.points || [])

const getPointIcon = (type) => {
    const icons = {
        'Pole namiotowe': 'mdi mdi-tent',
        'Miejsce biwakowania': 'mdi mdi-tent',
        'Przeszkoda': 'mdi mdi-alert',
        'Niebezpieczeństwo': 'mdi mdi-alert',
        'uwaga': 'mdi mdi-alert',
        'Jaz': 'mdi mdi-water',
        'most': 'mdi mdi-bridge',
        'przenoska': 'mdi mdi-arrow-up-down',
        'ujście': 'mdi mdi-call-split',
        'sklep': 'mdi mdi-store'
    }
    return icons[type] || 'mdi mdi-map-marker'
}

const getPointColor = (type) => {
    const colors = {
        'Pole namiotowe': 'green',
        'Miejsce biwakowania': 'green',
        'Przeszkoda': 'red',
        'Niebezpieczeństwo': 'red',
        'uwaga': 'red',
        'Jaz': 'blue',
        'most': 'brown',
        'przenoska': 'orange',
        'ujście': 'purple',
        'sklep': 'amber'
    }
    return colors[type] || 'teal'
}

const onMapReady = (mapInstance) => {
    map.value = mapInstance
    if (trailPath.value.length > 1) {
        const bounds = L.latLngBounds(trailPath.value)
        map.value.fitBounds(bounds, { padding: [50, 50] })
    }
    // Signal that map is ready
    window.mapReady = true
}

onMounted(() => {
    // Additional initialization if needed
})
</script>

<style scoped>
.static-map {
    position: relative;
}

.marker-icon {
    background-color: white;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 32px;
    height: 32px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}
</style>
