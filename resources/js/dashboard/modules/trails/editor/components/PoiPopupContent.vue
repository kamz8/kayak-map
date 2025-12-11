<template>
  <v-card width="320" flat color="white" class="poi-popup-card">
    <v-row no-gutters>
      <v-col cols="4">
        <v-img
            :src="poi.main_image ? poi.main_image.path : '/storage/assets/trailsplaceholder.webp'"
            height="100%"
            cover
            class="rounded-s-lg"
        >
          <template v-slot:placeholder>
            <div class="d-flex align-center justify-center fill-height bg-grey-lighten-2">
              <v-icon color="grey" size="x-large">mdi-image-off-outline</v-icon>
            </div>
          </template>
        </v-img>
      </v-col>

      <v-col cols="8">
        <div class="d-flex flex-column pa-2 text-black" style="height: 100%;">
          <div class="d-flex align-start justify-space-between">
            <h3 class="text-subtitle-1 font-weight-bold">{{ poi.name }}</h3>
            <v-chip
                v-if="poi.point_type"
                :color="getPoiIconColor(getPoiIconName(poi))"
                size="x-small"
                variant="tonal"
                class="ml-2 flex-shrink-0"
            >
              {{ poi.point_type.type }}
            </v-chip>
          </div>

          <p v-if="poi.description" class="text-caption mt-1 mb-0 flex-grow-1">
            {{ truncatedDescription }}
          </p>

          <v-btn
              size="x-small"
              color="primary"
              variant="text"
              class="text-none edit-details-btn align-self-end"
              @click="$emit('edit-poi', poi)"
          >
            Edytuj
          </v-btn>
        </div>
      </v-col>
    </v-row>
  </v-card>
</template>

<script>
import { POI_TYPE_COLOR_MAP } from '../utils/leafletIconUtils';

export default {
  name: 'PoiPopupContent',
  props: {
    poi: {
      type: Object,
      required: true,
    },
  },
  emits: ['edit-poi'],
  computed: {
    truncatedDescription() {
      if (!this.poi.description) return '';
      if (this.poi.description.length <= 80) return this.poi.description;
      return this.poi.description.substring(0, 80) + '...';
    },
  },
  methods: {
    getPoiIconName(poi) {
      if (poi.icon && poi.icon !== 'mdi-map-marker') {
        return poi.icon;
      }
      const pointType = poi.point_type?.type || poi.name || 'default';
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
  }
};
</script>

<style scoped>
.poi-popup-card {
  overflow: hidden;
  background: #ffffff;
}

.edit-details-btn {
  padding: 0;
  min-width: auto;
  margin-top: auto;
}
</style>
