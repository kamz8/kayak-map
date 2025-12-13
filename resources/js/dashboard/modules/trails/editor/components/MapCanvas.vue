<template>
  <div class="map-container" :class="{ 'map-cursor-poi-mode': activeTool === 'poi' }">
    <l-map
        ref="map"
        :use-global-leaflet="true"
        style="height: 100%; width: 100%;"
        :zoom="zoom"
        :center="center"
        :options="mapOptions"
        @ready="onMapReady"
        @update:zoom="updateZoom"
    >
      <l-tile-layer :url="tileLayerUrl" :attribution="attribution" />

      <!-- POI Markers - Using the new PoiMapMarker component -->
      <PoiMapMarker
          v-for="poi in poiPoints"
          :key="poi.id"
          :poi="poi"
          :point-types="availablePointTypes"
          @edit-poi="handleEditPoi"
          @marker-click="handlePoiMarkerClick"
      />

      <!-- Draw/Edit Control Layer (drawnItems) -->
      <l-feature-group ref="editableFeatures" />

    </l-map>

    <!-- Loading Overlay -->
    <div v-if="isLoading" class="loading-overlay">
      <v-progress-circular indeterminate color="primary" size="64" />
      <p class="mt-4">Ładowanie mapy...</p>
    </div>

    <!-- POI Editor Dialog -->
    <PoiEditorDialog
        :show="showPoiEditor"
        @update:show="showPoiEditor = $event"
        :poi="selectedPoi"
        @saved="handlePoiSaved"
        @cancelled="handlePoiCancelled"
    />

  </div>
</template>

<script>
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import 'leaflet-draw/dist/leaflet.draw.css';
import 'leaflet-draw';
import { LMap, LTileLayer, LFeatureGroup } from '@vue-leaflet/vue-leaflet';
import { mapState, mapGetters, mapActions } from 'vuex';
import { trailEditorGetters, trailEditorActions, trailEditorMutations } from '../store/trailEditor';
import { createTrailMarkerIcon } from '../utils/leafletIconUtils';
import PoiEditorDialog from './PoiEditorDialog.vue';
import PoiMapMarker from './PoiMapMarker.vue';

// Leaflet icon configuration
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

export default {
  name: 'MapCanvas',
  components: {
    LMap,
    LTileLayer,
    LFeatureGroup,
    PoiEditorDialog,
    PoiMapMarker,
  },
  data() {
    return {
      map: null,
      drawControl: null,
      mapOptions: {
        zoomControl: false,
        attributionControl: false,
      },
      showPoiEditor: false,
      selectedPoi: null,
      isDrawing: false,
      startMarkerLayer: null,
      endMarkerLayer: null,
      trackLayer: null,
      tempPoiMarker: null, // New: To store a temporary POI marker for placement
    };
  },
  computed: {
    ...mapState('trailEditor', {
      zoom: 'zoomLevel',
      center: 'centerPoint',
      currentLayer: 'currentLayer',
      trackCoordinates: 'trackCoordinates',
      startPoint: 'startPoint',
      endPoint: 'endPoint',
      poiPoints: 'poiPoints',
      availablePointTypes: 'availablePointTypes',
      requestLocate: 'requestLocate',
      requestZoomIn: 'requestZoomIn',
      requestZoomOut: 'requestZoomOut',
      activeTool: 'activeTool',
    }),
    ...mapGetters('trailEditor', {
      mapLayers: trailEditorGetters.MAP_LAYERS,
    }),
    isLoading() {
      return this.$store.state.trailEditor.isLoading || !this.map;
    },
    tileLayerUrl() {
      return this.mapLayers[this.currentLayer]?.url || this.mapLayers.default.url;
    },
    attribution() {
      return this.mapLayers[this.currentLayer]?.attribution || this.mapLayers.default.attribution;
    },
    editableLayers() {
        return this.$refs.editableFeatures?.leafletObject;
    }
  },
  watch: {
    requestLocate() { this.locateUser(); },
    requestZoomIn() {
      if (this.map) {
        this.map.zoomIn();
        this.$nextTick(() => this.setZoom(this.map.getZoom()));
      }
    },
    requestZoomOut() {
      if (this.map) {
        this.map.zoomOut();
        this.$nextTick(() => this.setZoom(this.map.getZoom()));
      }
    },
    trackCoordinates() {
      this.rebuildEditableLayers();
    },
    startPoint() {
      this.rebuildEditableLayers();
    },
    endPoint() {
      this.rebuildEditableLayers();
    }
  },
  methods: {
    ...mapActions('trailEditor', {
      addFeature: trailEditorActions.ADD_FEATURE,
      setZoom: trailEditorActions.SET_ZOOM,
    }),
    ...mapActions('ui', ['showMessage']),

    createTrailMarkerIcon,

    onMapReady(mapObject) {
      this.map = mapObject;
      this.setupDrawControl();
      this.setupMapListeners();
      this.$nextTick(() => {
        this.map.invalidateSize();
        this.rebuildEditableLayers();
        this.fitMapToTrack();
      });
      setTimeout(() => this.map.invalidateSize(), 100);
      this.$emit('map-ready', mapObject);
    },

    updateZoom(zoom) {
      this.setZoom(zoom);
    },

    fitMapToTrack() {
      if (this.map && this.trackCoordinates.length > 0) {
        const bounds = L.latLngBounds(this.trackCoordinates);
        try {
          this.map.fitBounds(bounds, { padding: [20, 20] });
        } catch (e) {
          console.error('Error fitting map bounds:', e);
        }
      }
    },

    rebuildEditableLayers() {
        if (!this.editableLayers) return;
        this.editableLayers.clearLayers();

        // Re-create track polyline
        if (this.trackCoordinates.length > 0) {
            this.trackLayer = L.polyline(this.trackCoordinates, {
                color: '#1976D2',
                weight: 5,
                opacity: 0.8,
            }).addTo(this.editableLayers);
        }

        // Re-create start marker
        if (this.startPoint) {
            this.startMarkerLayer = L.marker(this.startPoint, {
                icon: this.createTrailMarkerIcon('start'),
                draggable: true, // Make it draggable
            })
            .bindPopup('<strong>Punkt Startowy</strong>')
            .addTo(this.editableLayers);
        }

        // Re-create end marker
        if (this.endPoint) {
            this.endMarkerLayer = L.marker(this.endPoint, {
                icon: this.createTrailMarkerIcon('end'),
                draggable: true, // Make it draggable
            })
            .bindPopup('<strong>Punkt Końcowy</strong>')
            .addTo(this.editableLayers);
        }
    },

    setupDrawControl() {
      if (!this.editableLayers) {
        console.warn('⚠️ editableFeatures ref not ready yet');
        return;
      }
      this.drawControl = new L.Control.Draw({
        edit: {
          featureGroup: this.editableLayers,
          remove: true,
        },
        draw: {
          polyline: {
            shapeOptions: { color: '#1976D2', weight: 5, opacity: 0.8 },
            allowIntersection: false,
          },
          marker: { icon: this.createTrailMarkerIcon('poi') },
          polygon: false,
          circle: false,
          rectangle: false,
          circlemarker: false,
        },
      });
      // this.map.addControl(this.drawControl); // Disabled: Do not add the control to the map
    },
    
    setupMapListeners() {
      this.map.on(L.Draw.Event.CREATED, this.handleDrawCreated);
      this.map.on(L.Draw.Event.EDITED, this.handleDrawEdited);
      this.map.on(L.Draw.Event.DELETED, this.handleDrawDeleted);
      this.map.on(L.Draw.Event.DRAWSTART, () => this.isDrawing = true);
      this.map.on(L.Draw.Event.DRAWSTOP, () => this.isDrawing = false);
      this.map.on('click', this.handleMapClick);
    },

    handleDrawCreated(e) {
      const { layerType, layer } = e;
      if (layerType === 'polyline') {
        const coords = layer.getLatLngs().map(latlng => [latlng.lat, latlng.lng]);
        this.$store.commit(`trailEditor/${trailEditorMutations.UPDATE_TRACK_COORDINATES}`, coords);
        this.$store.commit(`trailEditor/${trailEditorMutations.SET_START_POINT}`, coords[0]);
        this.$store.commit(`trailEditor/${trailEditorMutations.SET_END_POINT}`, coords[coords.length - 1]);
      } else if (layerType === 'marker') {
        const { lat, lng } = layer.getLatLng();
        this.openPoiEditorForNew(lat, lng);
      }
    },
    
    handleDrawEdited(e) {
        e.layers.eachLayer(layer => {
            if (layer instanceof L.Polyline) {
                const coords = layer.getLatLngs().map(latlng => [latlng.lat, latlng.lng]);
                this.$store.commit(`trailEditor/${trailEditorMutations.UPDATE_TRACK_COORDINATES}`, coords);
                this.$store.commit(`trailEditor/${trailEditorMutations.SET_START_POINT}`, coords[0]);
                this.$store.commit(`trailEditor/${trailEditorMutations.SET_END_POINT}`, coords[coords.length - 1]);
            } else if (layer instanceof L.Marker) {
                const newLatLng = layer.getLatLng();
                const newCoords = [...this.trackCoordinates];

                if (layer === this.startMarkerLayer && newCoords.length > 0) {
                    newCoords[0] = [newLatLng.lat, newLatLng.lng];
                    this.$store.commit(`trailEditor/${trailEditorMutations.SET_START_POINT}`, newCoords[0]);
                } else if (layer === this.endMarkerLayer && newCoords.length > 0) {
                    newCoords[newCoords.length - 1] = [newLatLng.lat, newLatLng.lng];
                    this.$store.commit(`trailEditor/${trailEditorMutations.SET_END_POINT}`, newCoords[newCoords.length - 1]);
                }
                this.$store.commit(`trailEditor/${trailEditorMutations.UPDATE_TRACK_COORDINATES}`, newCoords);
            }
        });
        this.showMessage({ type: 'info', message: 'Trasa została zaktualizowana' });
    },

    handleDrawDeleted(e) {
        let trackCleared = false;
        e.layers.eachLayer(layer => {
            if (layer instanceof L.Polyline) {
                trackCleared = true;
            } else if (layer === this.startMarkerLayer) {
                this.$store.commit(`trailEditor/${trailEditorMutations.SET_START_POINT}`, null);
            } else if (layer === this.endMarkerLayer) {
                this.$store.commit(`trailEditor/${trailEditorMutations.SET_END_POINT}`, null);
            }
        });

        if (trackCleared) {
            this.$store.commit(`trailEditor/${trailEditorMutations.CLEAR_TRACK}`);
            this.showMessage({ type: 'info', message: 'Trasa została usunięta' });
        }
    },

    handleMapClick(e) {
      if (this.activeTool === 'poi' && !this.isDrawing) {
        this.openPoiEditorForNew(e.latlng.lat, e.latlng.lng);
      }
    },

    openPoiEditorForNew(lat, lng) {
      this.selectedPoi = {
        id: `temp-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`, // Tymczasowy ID
        name: 'Nowy Punkt',
        description: '',
        latitude: lat,
        longitude: lng,
        point_type_id: this.availablePointTypes[0]?.id || 1,
      };
      this.showPoiEditor = true;
    },
    
    handleEditPoi(poi) {
      this.selectedPoi = { ...poi };
      this.showPoiEditor = true;
    },
    
    handlePoiSaved(poiData) {
      // Sprawdź czy to nowy punkt (ma tymczasowy ID)
      const idStr = String(poiData.id || '')
      const isNewPoi = idStr.startsWith('temp-')

      // Wzbogać dane POI o brakujące pola (icon, point_type, images)
      const enrichedPoiData = {
        ...poiData,
        // Dla nowych punktów ustaw id na null (będzie nadane przez serwer)
        id: isNewPoi ? null : poiData.id,
        // Znajdź typ punktu
        point_type: this.availablePointTypes.find(pt => pt.id === poiData.point_type_id),
        // Ustaw domyślną ikonę
        icon: this.availablePointTypes.find(pt => pt.id === poiData.point_type_id)?.icon || 'mdi-map-marker',
        // Dodaj puste tablice dla obrazów jeśli to nowy punkt
        main_image: poiData.main_image || null,
        images: poiData.images || [],
        // Dodaj domyślne wartości dla nowych punktów
        at_length: poiData.at_length || 0,
        order: poiData.order || 0
      }

      // Wybierz mutation type - dla nowych punktów ADD, dla edycji UPDATE
      const mutationType = isNewPoi ? 'ADD_POI' : 'UPDATE_POI'

      // Commit the POI mutation (ADD_POI and UPDATE_POI already set unsavedChanges internally)
      this.$store.commit(`trailEditor/${mutationType}`, enrichedPoiData)

      this.showPoiEditor = false
      this.selectedPoi = null
    },

    handlePoiCancelled() {
      this.showPoiEditor = false;
      this.selectedPoi = null;
    },

    locateUser() {
      if (this.map) {
        this.map.locate({ setView: true, maxZoom: 16, enableHighAccuracy: true });
        this.map.once('locationfound', (e) => {
          this.showMessage({ type: 'success', message: 'Lokalizacja znaleziona' });
          L.circle(e.latlng, e.accuracy).addTo(this.map);
        });
        this.map.once('locationerror', (e) => {
          this.showMessage({ type: 'error', message: 'Nie udało się ustalić lokalizacji: ' + e.message });
        });
      }
    },

    handlePoiMarkerClick({ lat, lng }) {
        this.$store.commit(`trailEditor/${trailEditorMutations.SET_CENTER_POINT}`, [lat, lng]);
    },

    beforeUnmount() {
      if (this.map) {
        if (this.drawControl) {
          this.map.removeControl(this.drawControl);
        }
        this.map.off();
        this.map.remove();
        this.map = null;
      }
    },
  }
}
</script>

<style scoped>
.map-container {
  position: relative;
  width: 100%;
  height: 100%;
}

/* Loading Overlay */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

/* Marker Popups - Styles for standard Start/End markers, POI has its own style */
.marker-popup {
  padding: 8px;
  min-width: 200px;
}

.marker-popup strong {
  display: block;
  margin-bottom: 4px;
  font-size: 14px;
}

.marker-popup .text-caption {
  color: rgba(var(--v-theme-on-surface), 0.7);
  font-size: 12px;
}

/* Custom marker icons - style for icons generated by LeafletIconUtils */
:deep(.start-marker .leaflet-v-icon) {
  color: rgb(var(--v-theme-success)) !important;
  text-shadow: 0 0 5px rgba(0, 0, 0, 0.4);
}

:deep(.end-marker .leaflet-v-icon) {
  color: rgb(var(--v-theme-error)) !important;
  text-shadow: 0 0 5px rgba(0, 0, 0, 0.4);
}

:deep(.poi-marker .leaflet-v-icon) {
  /* Color is set dynamically in leafletIconUtils */
  text-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
}

/* Style for Leaflet.draw control to match the interface */
:deep(.leaflet-control-container .leaflet-draw-toolbar) {
  box-shadow: none;
  border-radius: 8px;
  margin-top: 10px;
}

:deep(.leaflet-draw-toolbar .leaflet-draw-actions) {
  margin-top: 4px;
}

:deep(.leaflet-control-container .leaflet-draw-toolbar a) {
  background-image: none !important; /* Removes default background images */
  border: 1px solid rgba(var(--v-border-color), 0.1);
  background-color: rgb(var(--v-theme-surface));
  color: rgb(var(--v-theme-on-surface));
  height: 32px;
  width: 32px;
  line-height: 32px;
  text-align: center;
  padding: 0;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px; /* Use larger fonts instead of icons */
}

:deep(.leaflet-draw-toolbar a:hover) {
  background-color: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
}

:deep(.leaflet-draw-toolbar a.leaflet-draw-toolbar-button-enabled) {
  background-color: rgb(var(--v-theme-primary)) !important;
  color: rgb(var(--v-theme-on-primary)) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

:deep(.leaflet-draw-toolbar .leaflet-draw-edit-remove) {
  color: rgb(var(--v-theme-error)) !important;
}

:deep(.leaflet-draw-toolbar .leaflet-draw-edit-edit) {
  color: rgb(var(--v-theme-info)) !important;
}

/* Insert mdi-pencil icon for Draw-Polyline button */
:deep(.leaflet-draw-draw-polyline:before) {
  content: "\F34F" !important; /* mdi-pencil */
  font-family: 'Material Design Icons';
  font-size: 20px;
}

/* Insert mdi-pencil-outline icon for Edit-Edit button */
:deep(.leaflet-draw-edit-edit:before) {
  content: "\F638" !important; /* mdi-pencil-outline */
  font-family: 'Material Design Icons',serif;
  font-size: 20px;
}

/* Insert mdi-close-circle-outline icon for Edit-Remove button */
:deep(.leaflet-draw-edit-remove:before) {
  content: "\F0553" !important; /* mdi-delete-outline */
  font-family: 'Material Design Icons';
  font-size: 20px;
}

/* Hide original labels built into Leaflet.draw */
:deep(.leaflet-draw-toolbar a:after) {
  display: none;
}
</style>