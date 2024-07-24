<template>
  <div class="map-container">
    <l-map
        ref="map"
        style="height: 100%; width: 100%;"
        :zoom="zoom"
        :center="center"
        :options="{ zoomControl: false }"
        :use-global-leaflet="false"
        @ready="onMapReady"
    >
      <l-tile-layer :url="url" :attribution="attribution" />
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
import { LMap, LTileLayer } from "@vue-leaflet/vue-leaflet";
import { useGeolocation } from "@vueuse/core";

export default {
  name: "Map",
  components: {
    LMap,
    LTileLayer,
  },
  data() {
    return {
      zoom: 6,
      center: [52.0689, 19.4803], // Środek Polski
      url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      attribution: 'Leaflet.js | © OpenStreetMap contributors',
      selectedLayer: 'default',
      showLayerOptions: false,
      mapRotation: 0,
      mapInstance: null,
    };
  },
  methods: {
    onMapReady(map) {
      this.mapInstance = map;
    },
    zoomIn() {
      this.zoom = Math.min(this.zoom + 1, 18); // Maksymalny poziom zoomu to 18
    },
    zoomOut() {
      this.zoom = Math.max(this.zoom - 1, 1); // Minimalny poziom zoomu to 1
    },
    async locate() {
      const { coords, error } = useGeolocation();

      if ("geolocation" in navigator) {
        try {
          const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject);
          });

          if (position) {
            const { latitude, longitude } = position.coords;
            this.center = [latitude, longitude];
            this.zoom = 13; // Możesz dostosować poziom zoomu
          }
        } catch (err) {
          console.log("Użytkownik odmówił dostępu do geolokalizacji");
          this.setWarszawaLocation();
        }
      } else {
        console.log("Geolokalizacja nie jest dostępna");
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
        default:
          this.url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
          this.attribution = 'Leaflet.js | © OpenStreetMap contributors';
      }
    },
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
</style>
