<template>
  <div class="map-container">
    <l-map
        ref="map"
        style="height: 100%; width: 100%;"
        :zoom="zoom"
        :center="center"
        :options="{ zoomControl: false, preferCanvas: true }"

        @ready="onMapReady"
        @moveend="onMapMoveEnd"
    >
      <l-tile-layer :url="url" :attribution="attribution" />
      <l-marker-cluster :options="{ chunkedLoading: true }">
        <l-marker
            v-for="trail in trails"
            :key="trail.id"
            :icon="createCustomIcon()"
            :lat-lng="[trail.start_lat, trail.start_lng]"
        >
          <l-popup>
            <trail-popup :trail="trail" @view-details="viewTrailDetails" />
          </l-popup>
        </l-marker>
      </l-marker-cluster>
    </l-map>
    <div class="map-controls top-right-controls">
      <div class="layer-control" @mouseenter="showLayerOptions = true" @mouseleave="showLayerOptions = false">
        <v-btn icon="mdi-layers" density="comfortable" class="main-button" v-tooltip="'Warstwy mapy'" />
        <transition name="fade">
          <div v-if="showLayerOptions" class="layer-options">
            <v-btn icon="mdi-map" class="layer-button" @click="setTileLayer('default')" v-tooltip="'Mapa domyślna'" />
            <v-btn icon="mdi-terrain" class="layer-button" @click="setTileLayer('terrain')" v-tooltip="'Mapa terenu'" />
            <v-btn icon="mdi-satellite-variant" class="layer-button" @click="setTileLayer('satellite')" v-tooltip="'Mapa satelitarna'" />
          </div>
        </transition>
      </div>
    </div>
    <div class="map-controls bottom-right-controls">
      <v-btn icon="mdi-plus" density="comfortable" class="control-button" @click="zoomIn" v-tooltip="'Przybliż'" />
      <v-btn icon="mdi-minus" density="comfortable" class="control-button" @click="zoomOut" v-tooltip="'Oddal'" />
      <v-btn icon="mdi-crosshairs-gps" density="comfortable" class="control-button" @click="locate" v-tooltip="'Zlokalizuj mnie'" />
    </div>
  </div>
</template>

<script>
import "leaflet/dist/leaflet.css";
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet';
import { useGeolocation } from '@vueuse/core';
import { mapActions, mapGetters } from 'vuex';
import LMarkerCluster from "@/modules/trails/components/LMarkerCluster.vue";
import TrailPopup from "@/modules/trails/components/TrailPopup.vue";
import {Icon} from "leaflet/src/layer/index.js";

export default {
  name: "Map",
  components: {
    TrailPopup,
    LMarkerCluster,
    LMap,
    LTileLayer,
    LMarker,
    LPopup,
  },
  data() {
    return {
      zoom: 6,
      center: [52.0689, 19.4803], // Środek Polski
      url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      attribution: 'Leaflet.js | © OpenStreetMap contributors',
      selectedLayer: 'default',
      showLayerOptions: false,
      mapInstance: null,
      markersCluster: null,
    };
  },
  computed: {
    ...mapGetters({
      trails: 'trails/trails',
    }),
  },
  methods: {
    ...mapActions({
      fetchTrails: 'trails/fetchTrails',
      addMessage: 'system_messages/addMessage',
    }),
    onMapReady(map) {
      this.mapInstance = map;
      this.fetchLocalTrails();
    },
    onMapMoveEnd() {
      this.fetchLocalTrails();
    },
    async fetchLocalTrails() {
      const bounds = this.mapInstance.getBounds();

      const startLat = bounds.getSouthWest().lat;
      const endLat = bounds.getNorthEast().lat;
      const startLng = bounds.getSouthWest().lng;
      const endLng = bounds.getNorthEast().lng;
      await this.fetchTrails({ startLat, endLat, startLng, endLng });
    },
    zoomIn() {
      this.zoom++;
    },
    zoomOut() {
      this.zoom--;
    },
    async locate() {
      const { coords } = useGeolocation();

      if ("geolocation" in navigator) {
        try {
          await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject);
          });

          if (coords.value) {
            this.center = [coords.value.latitude, coords.value.longitude];
            this.zoom = 13;
          }
        } catch (err) {
          console.log("Użytkownik odmówił dostępu do geolokalizacji");
          this.setWarszawaLocation();
        }
      } else {
        console.log("Geolokalizacja nie jest dostępna");
        this.addMessage({ type: 'error', message: 'Geolokalizacja nie jest dostępna.' });
        this.setWarszawaLocation();
      }
    },
    setWarszawaLocation() {
      this.center = [52.2297, 21.0122]; // Współrzędne Warszawy
      this.zoom = 12;
    },
    setTileLayer(layer) {
      this.selectedLayer = layer;
      switch(layer) {
        case 'terrain':
          this.url = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
          break;
        case 'satellite':
          this.url = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
          this.attribution = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
          break;
        case 'hybrid':
          this.url = 'https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
          this.attribution = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
          break;
        default:
          this.url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
          this.attribution = 'Leaflet.js | © OpenStreetMap contributors';
      }
    },
    formatDuration(minutes) {
      const hours = Math.floor(minutes / 60);
      const remainingMinutes = minutes % 60;
      return `${hours}h ${remainingMinutes}m`;
    },
    viewTrailDetails(trailId) {
      console.log(`Viewing details for trail ${trailId}`);
      // this.$router.push({ name: 'TrailDetails', params: { id: trailId } });
    },
    createCustomIcon() {
          return new Icon({
              iconUrl: 'data:image/svg+xml;base64,' + btoa(`
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
        <path d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5z" fill="${this.$vuetify.theme.current.colors.anchor}" stroke="white" stroke-width="2"/>
      </svg>
    `),
              iconSize: [42, 42],
              iconAnchor: [21, 42],
          });
      }

  },
};
</script>

<style scoped>
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

.custom-popup {
  width: 300px;
}

:deep(.leaflet-popup-content-wrapper) {
  padding: 0;
  overflow: hidden;
}

:deep(.leaflet-popup-content) {
  margin: 0;
  width: 300px !important;
}

.custom-marker {
    background-color: var(--v-theme-secondary);
    border: 2px solid white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-marker-icon {
    width: 12px;
    height: 12px;
    background-color: white;
    border-radius: 50%;
}

</style>
