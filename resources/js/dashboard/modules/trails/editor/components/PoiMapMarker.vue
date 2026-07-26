<template>
  <l-marker
      :lat-lng="[poi.lat, poi.lng]"
      :draggable="isDraggable"
      ref="marker"
      @dragend="onMarkerDragEnd"
  >
    <l-icon class-name="point-icon">
      <v-icon
          size="32"
          class="icon-with-stroke"
          :color="getPoiIconColor(getPoiIconName(poi))"
      >
        {{ getPoiIconName(poi) }}
      </v-icon>
    </l-icon>
    <l-popup :options="popupOptions">
      <div :id="popupContentId"></div>
    </l-popup>
  </l-marker>
</template>

<script>
import { createApp, h } from 'vue';
import { LMarker, LPopup, LIcon } from '@vue-leaflet/vue-leaflet';
import { POI_TYPE_COLOR_MAP } from '../utils/leafletIconUtils';
import PoiPopupContent from './PoiPopupContent.vue';
import vuetify from '@/dashboard/plugins/vuetify.js';

export default {
  name: 'PoiMapMarker',

  components: {
    LMarker,
    LPopup,
    LIcon
  },

  props: {
    poi: {
      type: Object,
      required: true
    },
    pointTypes: {
      type: Array,
      default: () => []
    },
    isDraggable: {
      type: Boolean,
      default: false
    }
  },

  emits: ['edit-poi', 'marker-click', 'marker-moved'],

  data() {
    return {
      popupOptions: {
        closeButton: false,
        className: 'custom-popup'
      },
      popupContentId: `poi-popup-content-${this.poi.id || Math.random().toString(36).substring(2)}`,
      vueApp: null
    };
  },

  mounted() {
    this.$nextTick(() => {
      if (this.$refs.marker && this.$refs.marker.leafletObject) {
        const markerObject = this.$refs.marker.leafletObject;
        markerObject.on('popupopen', this.onPopupOpen);
        markerObject.on('popupclose', this.onPopupClose);
        markerObject.on('click', this.handleMarkerClick); // Add click listener
      }
    });
  },

  beforeUnmount() {
    if (this.$refs.marker && this.$refs.marker.leafletObject) {
      const markerObject = this.$refs.marker.leafletObject;
      markerObject.off('popupopen', this.onPopupOpen);
      markerObject.off('popupclose', this.onPopupClose);
      markerObject.off('click', this.handleMarkerClick); // Remove click listener
    }
    this.onPopupClose(); // Ensure app is unmounted
  },

  methods: {
    handleMarkerClick() {
      this.$emit('marker-click', { lat: this.poi.lat, lng: this.poi.lng });
    },
    onPopupOpen() {
      if (this.vueApp) return;

      const self = this;
      this.vueApp = createApp({
        render() {
          return h(PoiPopupContent, {
            poi: self.poi,
            onEditPoi: (poi) => self.$emit('edit-poi', poi)
          });
        },
        // In case the component needs access to the main app's providers
        parent: this.$root
      });

      this.vueApp.use(vuetify);
      this.vueApp.mount(`#${this.popupContentId}`);
    },

    onPopupClose() {
      if (this.vueApp) {
        this.vueApp.unmount();
        this.vueApp = null;
      }
    },
    
    getPoiIconName(poi) {
      if (poi.icon && poi.icon !== 'mdi-map-marker') {
        return poi.icon;
      }
      const pointType = poi.point_type_key || poi.name || 'default';
      switch (pointType) {
        case 'Pole namiotowe':
        case 'Miejsce biwakowania':
          return 'mdi-tent';
        case 'Przeszkoda':
        case 'Niebezpieczeństwo':
        case 'uwaga':
          return 'mdi-alert';
        case 'Jaz':
          return 'mdi-water';
        case 'most':
          return 'mdi-bridge';
        case 'przenoska':
          return 'mdi-arrow-up-down';
        case 'ujście':
          return 'mdi-call-split';
        case 'sklep':
          return 'mdi-store';
        default:
          return 'mdi-map-marker';
      }
    },

    getPoiIconColor(iconName) {
      const colorKey = POI_TYPE_COLOR_MAP[iconName] || 'primary';
      return colorKey;
    },

    onMarkerDragEnd(event) {
      const newLatLng = event.target.getLatLng();
      this.$emit('marker-moved', {
        id: this.poi.id,
        lat: newLatLng.lat,
        lng: newLatLng.lng
      });
    },
  }
}
</script>
<style scoped>
/* Point icon styling */
.point-icon {
  background-color: white;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.icon-with-stroke {
  filter: drop-shadow(0 0 2px rgba(19, 19, 19, 0.5));
  -webkit-text-stroke: 4px white;
  text-stroke: 4px white;
  paint-order: stroke fill;
}
</style>