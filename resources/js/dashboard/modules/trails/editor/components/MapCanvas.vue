<template>
  <div class="map-container">
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

      <!-- Start Point Marker -->
      <l-marker
          v-if="startPoint"
          :lat-lng="startPoint"
          :icon="createTrailMarkerIcon('start')"
      >
        <l-popup>
          <div class="marker-popup">
            <strong>Punkt Startowy</strong>
            <p class="text-caption">{{ startPoint[0].toFixed(6) }}, {{ startPoint[1].toFixed(6) }}</p>
          </div>
        </l-popup>
      </l-marker>

      <!-- End Point Marker -->
      <l-marker
          v-if="endPoint"
          :lat-lng="endPoint"
          :icon="createTrailMarkerIcon('end')"
      >
        <l-popup>
          <div class="marker-popup">
            <strong>Punkt Końcowy</strong>
            <p class="text-caption">{{ endPoint[0].toFixed(6) }}, {{ endPoint[1].toFixed(6) }}</p>
          </div>
        </l-popup>
      </l-marker>

      <!-- POI Markers - Using the new PoiMapMarker component -->
      <PoiMapMarker
          v-for="poi in poiPoints"
          :key="poi.id"
          :poi="poi"
          :point-types="availablePointTypes"
          @edit-poi="handleEditPoi"
      />

      <!-- Trail Track Layer -->
      <l-polyline
          v-if="trackCoordinates.length"
          :lat-lngs="trackCoordinates"
          :color="'#1976D2'"
          :weight="5"
          :opacity="0.8"
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
        :poi="selectedPoi"
        @saved="handlePoiSaved"
        @cancelled="handlePoiCancelled"
        @delete-poi="handlePoiDelete"
    />

  </div>
</template>

<script>
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'
import 'leaflet-draw/dist/leaflet.draw.css'
import 'leaflet-draw'
import { LMap, LTileLayer, LMarker, LPolyline, LPopup, LFeatureGroup } from '@vue-leaflet/vue-leaflet'
import { mapState, mapGetters, mapActions } from 'vuex'
import { trailEditorGetters, trailEditorActions } from '../store/trailEditor'
import { trailEditorMutations } from '../store/trailEditor'
import { createTrailMarkerIcon } from '../utils/leafletIconUtils'
import PoiEditorDialog from './PoiEditorDialog.vue'
import PoiMapMarker from './PoiMapMarker.vue' // Import new component

// Leaflet icon configuration (fixes missing icon issue)
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
})

export default {
  name: 'MapCanvas',
  components: {
    LMap,
    LTileLayer,
    LMarker,
    LPolyline,
    LPopup,
    LFeatureGroup,
    PoiEditorDialog,
    PoiMapMarker, // Register new component
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
      // Local state for Draw/Edit operations that are not yet in Vuex
      isDrawing: false,
    }
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
      availablePointTypes: 'availablePointTypes', // Required to pass to POI marker
      requestLocate: 'requestLocate',
      requestZoomIn: 'requestZoomIn',
      requestZoomOut: 'requestZoomOut',
      activeTool: 'activeTool',
    }),
    ...mapGetters('trailEditor', {
      mapLayers: trailEditorGetters.MAP_LAYERS,
    }),
    isLoading() {
      // Assume map loading is part of the overall editor loading state
      return this.$store.state.trailEditor.isLoading || !this.map
    },
    tileLayerUrl() {
      return this.mapLayers[this.currentLayer]?.url || this.mapLayers.default.url
    },
    attribution() {
      return this.mapLayers[this.currentLayer]?.attribution || this.mapLayers.default.attribution
    }
  },
  watch: {
    requestLocate() {
      this.locateUser()
    },
    requestZoomIn() {
      // React to zoom in request
      if (this.map) {
        this.map.zoomIn()
        // Update store with new zoom level
        this.$nextTick(() => {
          this.setZoom(this.map.getZoom())
        })
      }
    },
    requestZoomOut() {
      // React to zoom out request
      if (this.map) {
        this.map.zoomOut()
        // Update store with new zoom level
        this.$nextTick(() => {
          this.setZoom(this.map.getZoom())
        })
      }
    },
    activeTool: {
      immediate: true,
      handler(newTool) {
        if (this.map) {
          this.updateDrawControl(newTool)
        }
      }
    },
    trackCoordinates(newCoords) {
      // Use this watcher to update map bounds
      if (this.map && newCoords.length > 0) {
        this.fitMapToTrack()
      }
    }
  },
  methods: {
    ...mapActions('trailEditor', {
      addFeature: trailEditorActions.ADD_FEATURE,
      setZoom: trailEditorActions.SET_ZOOM,
      // ... other actions
    }),
    ...mapActions('ui', ['showMessage']),

    createTrailMarkerIcon, // Make helper function available in components

    /**
     * Initializes the map when it is ready.
     * @param {L.Map} mapObject - The Leaflet map object.
     */
    onMapReady(mapObject) {
      this.map = mapObject
      this.setupDrawControl()
      this.setupMapListeners()

      // Emit ready event for TrailMapEditorComponent
      this.$emit('map-ready', mapObject)
    },

    /**
     * Updates the zoom level in Vuex.
     * @param {number} zoom - The current zoom level.
     */
    updateZoom(zoom) {
      this.setZoom(zoom)
    },

    /**
     * Fits the map view to the bounds of the drawn track.
     */
    fitMapToTrack() {
      if (this.map && this.trackCoordinates.length > 0) {
        const bounds = L.latLngBounds(this.trackCoordinates)
        // Use try-catch in case bounds are invalid (e.g., single point)
        try {
          this.map.fitBounds(bounds, { padding: [20, 20] })
        } catch (e) {
          console.error('Error fitting map bounds:', e)
        }
      }
    },

    /**
     * Sets up the Leaflet.draw control for drawing/editing.
     */
    setupDrawControl() {
      // Validate that editableFeatures ref exists
      if (!this.$refs.editableFeatures) {
        console.warn('⚠️ editableFeatures ref not ready yet')
        return
      }

      // Feature group for editable items (track and POIs)
      const editableFeatures = this.$refs.editableFeatures.leafletObject

      // Initialize Draw control
      this.drawControl = new L.Control.Draw({
        edit: {
          featureGroup: editableFeatures,
          remove: true,
        },
        draw: {
          polyline: {
            shapeOptions: {
              color: '#1976D2',
              weight: 5,
              opacity: 0.8,
            },
            allowIntersection: false,
          },
          marker: {
            icon: this.createTrailMarkerIcon('poi'),
          },
          polygon: false,
          circle: false,
          rectangle: false,
          circlemarker: false,
        },
      })

      // Initially hide the control
      this.map.addControl(this.drawControl)
      this.drawControl._container.style.display = 'none'

      // Add existing track to the editable features group
      if (this.trackCoordinates.length) {
        const polyline = L.polyline(this.trackCoordinates, { color: '#1976D2' })
        editableFeatures.addLayer(polyline)
      }
    },

    /**
     * Sets up map event listeners (clicks, drawing).
     */
    setupMapListeners() {
      // Draw Events
      this.map.on(L.Draw.Event.CREATED, this.handleDrawCreated)
      this.map.on(L.Draw.Event.EDITED, this.handleDrawEdited)
      this.map.on(L.Draw.Event.DELETED, this.handleDrawDeleted)
      this.map.on(L.Draw.Event.DRAWSTART, this.handleDrawStart)
      this.map.on(L.Draw.Event.DRAWSTOP, this.handleDrawStop)

      // Map click event (for adding POI)
      this.map.on('click', this.handleMapClick)
    },

    /**
     * Updates the visibility and mode of the Draw control based on activeTool.
     * @param {string} tool - The active tool ('draw', 'edit', 'poi', 'select', null).
     */
    updateDrawControl(tool) {
      if (!this.drawControl) {
        return
      }

      // Show/hide the Draw control based on active tool
      const shouldShow = ['draw', 'edit', 'poi'].includes(tool)
      this.drawControl._container.style.display = shouldShow ? 'block' : 'none'

      // Note: We let users manually click the Leaflet.draw toolbar buttons
      // This is simpler and more reliable than programmatically triggering modes
    },

    // --- Draw Event Handlers ---

    handleDrawStart(e) {
      this.isDrawing = true
    },

    handleDrawStop(e) {
      this.isDrawing = false
    },

    handleDrawCreated(e) {
      const type = e.layerType
      const layer = e.layer
      const editableFeatures = this.$refs.editableFeatures.leafletObject

      // Add new layer to the group
      editableFeatures.addLayer(layer)

      if (type === 'polyline') {
        const coords = layer.getLatLngs().map(latlng => [latlng.lat, latlng.lng])
        this.$store.commit(`trailEditor/${trailEditorMutations.SET_TRACK_COORDINATES}`, coords)
        this.$store.commit(`trailEditor/${trailEditorMutations.SET_START_END_POINTS}`, {
          start: coords[0],
          end: coords[coords.length - 1],
        })
        this.$store.commit(`trailEditor/${trailEditorMutations.SET_UNSAVED_CHANGES}`, true)
      } else if (type === 'marker') {
        // Drawing a new POI
        const latlng = layer.getLatLng()
        this.openPoiEditorForNew(latlng.lat, latlng.lng)
        // Remove temporary marker added by Leaflet.draw
        editableFeatures.removeLayer(layer)
      }

      // Disable drawing mode after completion
      this.updateDrawControl(this.activeTool)
    },

    handleDrawEdited(e) {
      e.layers.eachLayer(layer => {
        if (layer instanceof L.Polyline) {
          const coords = layer.getLatLngs().map(latlng => [latlng.lat, latlng.lng])
          this.$store.commit(`trailEditor/${trailEditorMutations.SET_TRACK_COORDINATES}`, coords)
          this.$store.commit(`trailEditor/${trailEditorMutations.SET_START_END_POINTS}`, {
            start: coords[0],
            end: coords[coords.length - 1],
          })
          this.$store.commit(`trailEditor/${trailEditorMutations.SET_UNSAVED_CHANGES}`, true)
        }
      })
      this.showMessage({ type: 'info', message: 'Trasa została zaktualizowana' })
    },

    handleDrawDeleted(e) {
      e.layers.eachLayer(layer => {
        if (layer instanceof L.Polyline) {
          this.$store.commit(`trailEditor/${trailEditorMutations.CLEAR_TRACK}`)
          this.$store.commit(`trailEditor/${trailEditorMutations.SET_UNSAVED_CHANGES}`, true)
          this.showMessage({ type: 'info', message: 'Trasa została usunięta' })
        }
      })
    },

    /**
     * Handles map click in POI mode.
     */
    handleMapClick(e) {
      if (this.activeTool === 'poi' && !this.isDrawing) {
        // Add new POI
        this.openPoiEditorForNew(e.latlng.lat, e.latlng.lng)
      }
    },


    // --- POI Editor Handlers ---

    /**
     * Opens the editor for a new POI.
     * @param {number} lat - Latitude.
     * @param {number} lng - Longitude.
     */
    openPoiEditorForNew(lat, lng) {
      // Default values for new POI
      this.selectedPoi = {
        id: null,
        name: 'Nowy Punkt',
        description: '',
        latitude: lat,
        longitude: lng,
        point_type_id: this.availablePointTypes.length > 0 ? this.availablePointTypes[0].id : 1, // Default to first type or 1
      }
      this.showPoiEditor = true
    },

    /**
     * Opens the editor for an existing POI.
     * This is called by the new PoiMapMarker component.
     * @param {Object} poi - The POI object.
     */
    handleEditPoi(poi) {
      // Clone to avoid direct Vuex state modification before saving
      this.selectedPoi = { ...poi }
      this.showPoiEditor = true
    },

    /**
     * Called after POI editor changes are saved.
     */
    handlePoiSaved(poiData) {
      this.showPoiEditor = false
      this.selectedPoi = null
      this.$store.commit(`trailEditor/${trailEditorMutations.SET_UNSAVED_CHANGES}`, true)
      // Marker update will happen automatically due to poiPoints reactivity
    },

    /**
     * Called after POI editor is cancelled.
     */
    handlePoiCancelled() {
      this.showPoiEditor = false
      this.selectedPoi = null
    },

    /**
     * Called after a POI deletion request from the dialog.
     */
    handlePoiDelete(poi) {
      if (poi && poi.id) {
        this.$store.commit(`trailEditor/${trailEditorMutations.DELETE_POI}`, poi.id)
        this.$store.commit(`trailEditor/${trailEditorMutations.SET_UNSAVED_CHANGES}`, true)
        this.showMessage({ type: 'success', message: 'Punkt został usunięty' })
      }
      this.showPoiEditor = false
      this.selectedPoi = null
    },

    // --- Geolocation Handler ---

    locateUser() {
      if (this.map) {
        this.map.locate({
          setView: true,
          maxZoom: 16,
          enableHighAccuracy: true,
        })
        this.map.once('locationfound', (e) => {
          this.showMessage({ type: 'success', message: 'Lokalizacja znaleziona' })
          // Optionally: add location marker
          L.circle(e.latlng, e.accuracy).addTo(this.map)
        })
        this.map.once('locationerror', (e) => {
          this.showMessage({ type: 'error', message: 'Nie udało się ustalić lokalizacji: ' + e.message })
        })
      }
    },

    // --- Cleanup ---

    beforeUnmount() {
      if (this.map) {
        // Remove Draw control
        if (this.drawControl) {
          this.map.removeControl(this.drawControl)
        }

        // Remove event listeners
        this.map.off(L.Draw.Event.CREATED, this.handleDrawCreated)
        this.map.off(L.Draw.Event.EDITED, this.handleDrawEdited)
        this.map.off(L.Draw.Event.DELETED, this.handleDrawDeleted)
        this.map.off('click', this.handleMapClick)

        // Remove map instance (optional but recommended)
        this.map.remove()
        this.map = null
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