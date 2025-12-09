<template>
  <l-marker
      :lat-lng="[poi.lat, poi.lng]"
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
      <div class="poi-mini-popup">
        <div class="poi-card-content">
          <div class="d-flex flex-column pa-2">
            <!-- Nazwa z ikoną -->
            <div class="d-flex align-center font-weight-bold mb-1">
              <v-icon :color="getPoiIconColor(getPoiIconName(poi))" size="small" class="mr-2">
                {{ getPoiIconName(poi) }}
              </v-icon>
              <span>{{ poi.name || 'Punkt POI' }}</span>
            </div>

            <!-- Współrzędne -->
            <span class="text-caption text-grey-darken-2">
              {{ poi.lat.toFixed(6) }}, {{ poi.lng.toFixed(6) }}
            </span>

            <!-- Opis jeśli istnieje -->
            <p v-if="poi.description" class="text-caption mt-1 mb-0">
              {{ truncatedDescription }}
            </p>
          </div>
        </div>

        <v-btn
            size="x-small"
            color="primary"
            variant="text"
            class="text-none edit-details-btn"
            @click="$emit('edit-poi', poi)"
        >
          Edytuj
        </v-btn>
      </div>
    </l-popup>
  </l-marker>
</template>

<script>
import { LMarker, LPopup, LIcon } from '@vue-leaflet/vue-leaflet'
import { POI_TYPE_COLOR_MAP } from '../utils/leafletIconUtils'

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
    }
  },

  emits: ['edit-poi'],

  data() {
    return {
      popupOptions: {
        closeButton: false,
        className: 'custom-popup'
      }
    }
  },

  computed: {
    truncatedDescription() {
      if (!this.poi.description) return ''
      if (this.poi.description.length <= 100) return this.poi.description
      return this.poi.description.substring(0, 100) + '...'
    }
  },

  methods: {
    /**
     * Pobiera ikonę MDI dla typu POI
     * @param {Object} poi - Obiekt POI
     * @returns {string} Nazwa ikony MDI (np. 'mdi-tent')
     */
    getPoiIconName(poi) {
      // Debug - sprawdź jakie dane są w POI
      console.log('POI data:', poi)
      console.log('POI icon:', poi.icon)
      console.log('POI point_type_key:', poi.point_type_key)
      console.log('POI name:', poi.name)

      // Jeśli poi.icon jest ustawiony i NIE jest domyślny, użyj go
      if (poi.icon && poi.icon !== 'mdi-map-marker') {
        console.log('Using poi.icon:', poi.icon)
        return poi.icon
      }

      // W przeciwnym razie użyj point_type_key lub name do określenia ikony
      const pointType = poi.point_type_key || poi.name || 'default'
      console.log('Using pointType:', pointType)

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
        default:
          return 'mdi-map-marker'
      }
    },

    /**
     * Pobiera kolor ikony dla typu POI na podstawie nazwy ikony MDI
     * @param {string} iconName - Nazwa ikony MDI (np. 'mdi-water')
     * @returns {string} Kolor dla v-icon (Vuetify color)
     */
    getPoiIconColor(iconName) {
      // Użyj mapowania z leafletIconUtils.js
      const colorKey = POI_TYPE_COLOR_MAP[iconName] || 'primary'
      console.log('Icon:', iconName, '→ Color:', colorKey)
      return colorKey
    }
  }
}
</script>

<style scoped>
.poi-mini-popup {
  width: 220px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0px 3px 1px -2px rgba(0, 0, 0, 0.2),
  0px 2px 2px 0px rgba(0, 0, 0, 0.14),
  0px 1px 5px 0px rgba(0, 0, 0, 0.12);
  padding: 0;
  overflow: hidden;
}

.poi-card-content {
  min-height: 60px;
}

.edit-details-btn {
  width: 100%;
  margin-top: 0;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 0 0 8px 8px;
}

/* Nadpisanie stylów Leaflet Popup */
:deep(.custom-popup .leaflet-popup-content-wrapper) {
  padding: 0;
  border-radius: 8px;
  box-shadow: none;
}

:deep(.custom-popup .leaflet-popup-content) {
  margin: 0;
  width: auto !important;
}

:deep(.custom-popup .leaflet-popup-tip-container) {
  display: none;
}

/* Point icon styling - jak w SingleTrailMap */
.point-icon {
  background-color: white;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.icon-with-stroke {
  filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.5));
  -webkit-text-stroke: 2px white;
  text-stroke: 2px white;
  paint-order: stroke fill;
}
</style>