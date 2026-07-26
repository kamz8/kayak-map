<template>
  <div class="map-container" :class="{ 'map-cursor-poi-mode': activeTool === 'poi', 'map-cursor-waterway-mode': activeTool === 'draw' && waterwaySnapEnabled, 'map-cursor-routing': isWaterwayRouting }">
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
import leafletCss from 'leaflet/dist/leaflet.css?inline';
import leafletDrawCss from 'leaflet-draw/dist/leaflet.draw.css?inline';
import L from 'leaflet';
import 'leaflet-draw';
// leaflet-draw 1.0.4 calls L.LineUtil._flat which is deprecated in Leaflet 1.9+
L.LineUtil._flat = L.LineUtil.isFlat;
import { LMap, LTileLayer, LFeatureGroup } from '@vue-leaflet/vue-leaflet';
import { mapState, mapGetters, mapActions } from 'vuex';
import { trailEditorGetters, trailEditorActions, trailEditorMutations } from '../store/trailEditor';
import { createTrailMarkerIcon } from '../utils/leafletIconUtils';
import { createLeafletRouteEditor } from '../utils/leafletRouteEditor';
import PoiEditorDialog from './PoiEditorDialog.vue';
import PoiMapMarker from './PoiMapMarker.vue';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

let leafletCssInjected = false;
function injectLeafletCss() {
  if (leafletCssInjected) return;
  const style = document.createElement('style');
  style.textContent = leafletCss + leafletDrawCss;
  document.head.appendChild(style);
  leafletCssInjected = true;
}

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

injectLeafletCss();

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
      routeEditor: null,
      mapOptions: {
        zoomControl: false,
        attributionControl: false,
      },
      showPoiEditor: false,
      selectedPoi: null,
      isDrawing: false,
      isWaterwayRouting: false,
      startMarkerLayer: null,
      endMarkerLayer: null,
      trackLayer: null,
      routePreviewLayer: null,
    };
  },
  computed: {
    ...mapState('trailEditor', {
      zoom: 'zoomLevel',
      center: 'centerPoint',
      currentLayer: 'currentLayer',
      trackCoordinates: 'trackCoordinates',
      routePreviewCoordinates: 'routePreviewCoordinates',
      startPoint: 'startPoint',
      endPoint: 'endPoint',
      poiPoints: 'poiPoints',
      availablePointTypes: 'availablePointTypes',
      requestLocate: 'requestLocate',
      requestZoomIn: 'requestZoomIn',
      requestZoomOut: 'requestZoomOut',
      activeTool: 'activeTool',
      waterwaySnapEnabled: 'waterwaySnapEnabled',
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
    routePreviewCoordinates() {
      this.rebuildEditableLayers();
    },
    startPoint() {
      this.rebuildEditableLayers();
    },
    endPoint() {
      this.rebuildEditableLayers();
    },
    activeTool() {
      this.syncActiveTool();
    },
    waterwaySnapEnabled() {
      this.syncActiveTool();
    }
  },
  beforeUnmount() {
    this.destroyMap();
  },
  methods: {
    ...mapActions('trailEditor', {
      addFeature: trailEditorActions.ADD_FEATURE,
      setZoom: trailEditorActions.SET_ZOOM,
    }),

    createTrailMarkerIcon,

    onMapReady(mapObject) {
      this.map = mapObject;
      this.setupMapListeners();
      this.routeEditor = createLeafletRouteEditor({
        L,
        map: this.map,
        featureGroup: this.editableLayers,
        getTrackLayer: () => this.trackLayer,
        onRouteCreated: this.syncRouteCoordinates,
        onRouteEdited: this.syncRouteCoordinates,
        onDrawStateChanged: (isDrawing) => {
          this.isDrawing = isDrawing;
        },
      });
      this.$nextTick(() => {
        this.map.invalidateSize();
        this.rebuildEditableLayers();
        this.fitMapToTrack();
        this.syncActiveTool();
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
        if (this.routeEditor) {
            this.routeEditor.disable();
        }

        this.editableLayers.clearLayers();

        // Re-create track polyline
        if (this.trackCoordinates.length > 0) {
            this.trackLayer = L.polyline(this.trackCoordinates, {
                color: '#1976D2',
                weight: 5,
                opacity: 0.8,
            })
            .on('edit', () => {
                const coords = this.trackLayer.getLatLngs().map(latlng => [latlng.lat, latlng.lng]);
                this.syncRouteCoordinates(coords);
            })
            .addTo(this.editableLayers);
        }

        if (this.routePreviewCoordinates.length > 0) {
            this.routePreviewLayer = L.polyline(this.routePreviewCoordinates, {
                color: '#2E7D32',
                weight: 5,
                opacity: 0.9,
                dashArray: '8 8',
            }).addTo(this.editableLayers);
        }

        // Re-create start marker
        if (this.startPoint) {
            this.startMarkerLayer = L.marker(this.startPoint, {
                icon: this.createTrailMarkerIcon('start'),
                draggable: true, // Make it draggable
            })
            .on('dragend', this.handleStartMarkerDragEnd)
            .bindPopup('<strong>Punkt Startowy</strong>')
            .addTo(this.editableLayers);
        }

        // Re-create end marker
        if (this.endPoint) {
            this.endMarkerLayer = L.marker(this.endPoint, {
                icon: this.createTrailMarkerIcon('end'),
                draggable: true, // Make it draggable
            })
            .on('dragend', this.handleEndMarkerDragEnd)
            .bindPopup('<strong>Punkt Końcowy</strong>')
            .addTo(this.editableLayers);
        }

        if (this.activeTool === 'draw') {
            this.$nextTick(() => this.syncActiveTool());
        }
    },

    syncActiveTool() {
      if (!this.routeEditor) {
        return;
      }

      if (this.activeTool === 'draw') {
        if (this.waterwaySnapEnabled) {
          this.routeEditor.disable();
          this.isDrawing = false;
          return;
        }
        this.routeEditor.enablePlanningMode(this.trackCoordinates.length > 0);
        return;
      }

      this.routeEditor.disable();
    },

    syncRouteCoordinates(coords) {
      if (!Array.isArray(coords) || coords.length < 2) {
        return;
      }

      this.$store.commit(`trailEditor/${trailEditorMutations.UPDATE_TRACK_COORDINATES}`, coords);
      this.$store.commit(`trailEditor/${trailEditorMutations.SET_START_POINT}`, coords[0]);
      this.$store.commit(`trailEditor/${trailEditorMutations.SET_END_POINT}`, coords[coords.length - 1]);
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
        this.syncRouteCoordinates(coords);
      } else if (layerType === 'marker') {
        const { lat, lng } = layer.getLatLng();
        this.openPoiEditorForNew(lat, lng);
      }
    },
    
    handleDrawEdited(e) {
        e.layers.eachLayer(layer => {
            if (layer instanceof L.Polyline) {
                const coords = layer.getLatLngs().map(latlng => [latlng.lat, latlng.lng]);
                this.syncRouteCoordinates(coords);
            } else if (layer instanceof L.Marker) {
                const newLatLng = layer.getLatLng();
                const newCoords = [...this.trackCoordinates];

                if (layer === this.startMarkerLayer && newCoords.length > 0) {
                    newCoords[0] = [newLatLng.lat, newLatLng.lng];
                } else if (layer === this.endMarkerLayer && newCoords.length > 0) {
                    newCoords[newCoords.length - 1] = [newLatLng.lat, newLatLng.lng];
                }
                this.syncRouteCoordinates(newCoords);
            }
        });
        this.$notify('Trasa została zaktualizowana', 'info');
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
            this.$notify('Trasa została usunięta', 'info');
        }
    },

    handleMapClick(e) {
      if (this.activeTool === 'draw' && this.waterwaySnapEnabled) {
        if (!this.isWaterwayRouting) {
          this.handleWaterwaySnapClick(e.latlng.lat, e.latlng.lng);
        }
        return;
      }
      if (this.activeTool === 'poi' && !this.isDrawing) {
        this.openPoiEditorForNew(e.latlng.lat, e.latlng.lng);
      }
    },

    async handleWaterwaySnapClick(lat, lng) {
      this.isWaterwayRouting = true;
      try {
        const result = await this.$store.dispatch(`trailEditor/${trailEditorActions.ROUTE_WAYPOINT}`, { lat, lng });
        if (!result?.firstPoint) {
          this.$notify('Segment dodany po rzece', 'success');
        }
      } catch (error) {
        const apiMessage = error.response?.data?.message || error.message;
        const isRateLimit = apiMessage?.includes('429') || apiMessage?.includes('rate_limited');
        this.$notify(
          isRateLimit
            ? 'Overpass API jest przeciążone – spróbuj ponownie za chwilę lub użyj "Snap do rzeki" żeby zcache\'ować dane'
            : 'Błąd routingu: ' + apiMessage,
          'error'
        );
      } finally {
        this.isWaterwayRouting = false;
      }
    },

    handleStartMarkerDragEnd(event) {
      const latLng = event.target.getLatLng();
      const newCoords = [...this.trackCoordinates];

      if (newCoords.length === 0) {
        return;
      }

      newCoords[0] = [latLng.lat, latLng.lng];
      this.syncRouteCoordinates(newCoords);
    },

    handleEndMarkerDragEnd(event) {
      const latLng = event.target.getLatLng();
      const newCoords = [...this.trackCoordinates];

      if (newCoords.length === 0) {
        return;
      }

      newCoords[newCoords.length - 1] = [latLng.lat, latLng.lng];
      this.syncRouteCoordinates(newCoords);
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
          this.$notify('Lokalizacja znaleziona', 'success');
          L.circle(e.latlng, e.accuracy).addTo(this.map);
        });
        this.map.once('locationerror', (e) => {
          this.$notify('Nie udało się ustalić lokalizacji: ' + e.message, 'error');
        });
      }
    },

    handlePoiMarkerClick({ lat, lng }) {
        this.$store.commit(`trailEditor/${trailEditorMutations.SET_CENTER_POINT}`, [lat, lng]);
    },

    destroyMap() {
      if (this.map) {
        if (this.routeEditor) {
          this.routeEditor.destroy();
          this.routeEditor = null;
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

.map-cursor-waterway-mode :deep(.leaflet-container) {
  cursor: crosshair !important;
}

.map-cursor-routing :deep(.leaflet-container) {
  cursor: wait !important;
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

</style>
