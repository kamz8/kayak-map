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

            <!-- POI Markers -->
            <l-marker
                v-for="poi in poiPoints"
                :key="poi.id"
                :lat-lng="[poi.lat, poi.lng]"
                :icon="createPoiIcon(poi)"
                @click="selectPoi(poi)"
                @mouseover="highlightPoi(poi)"
                @mouseout="clearHighlightPoi"
            >
                <l-popup>
                    <div class="poi-popup">
                        <strong>{{ poi.name || 'Punkt POI' }}</strong>
                        <p class="text-caption">{{ poi.description || 'Brak opisu' }}</p>
                        <p class="text-caption mt-1">
                            <v-icon size="x-small">{{ poi.icon || 'mdi-map-marker' }}</v-icon>
                            {{ poi.lat.toFixed(6) }}, {{ poi.lng.toFixed(6) }}
                        </p>
                        <div class="mt-2">
                            <v-btn size="x-small" variant="text" color="error" @click="deletePoi(poi)">
                                <v-icon size="small">mdi-delete</v-icon>
                                Usuń
                            </v-btn>
                        </div>
                    </div>
                </l-popup>
            </l-marker>
        </l-map>

        <!-- Loading Overlay -->
        <div v-if="!geomanLoaded" class="loading-overlay">
            <v-progress-circular indeterminate color="primary" />
            <p class="text-caption mt-2">Ładowanie edytora mapy...</p>
        </div>
    </div>
</template>

<script>
import { LMap, LTileLayer, LMarker, LIcon, LPopup } from '@vue-leaflet/vue-leaflet'
import { createMdiMarkerIcon, createTrailMarkerIcon } from '../utils/leafletIconUtils.js'
import { mapGetters } from 'vuex'
import { trailEditorGetters } from '../store/trailEditor.js'

export default {
    name: 'MapCanvas',
    components: {
        LMap,
        LTileLayer,
        LMarker,
        LIcon,
        LPopup
    },

    data() {
        return {
            mapInstance: null,
            geomanLayer: null,
            tileLayer: null,
            tileLayerUrl: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            attribution: '© OpenStreetMap contributors',
            mapOptions: {
                zoomControl: false,
                preferCanvas: true,
                maxZoom: 18,
                minZoom: 2
            },
            geomanLoaded: false,
            isDrawing: false,
            isEditing: false,
            activePoiMarker: null,
            highlightedPoiMarker: null,
            poiMarkers: [] // Store Leaflet marker instances
        }
    },

    computed: {
        ...mapGetters('trailEditor', {
            poiPoints: trailEditorGetters.POI_POINTS
        }),

        trackCoordinates() {
            return this.$store.state.trailEditor.trackCoordinates
        },

        startPoint() {
            return this.$store.state.trailEditor.startPoint
        },

        endPoint() {
            return this.$store.state.trailEditor.endPoint
        },

        zoom() {
            return this.$store.state.trailEditor.zoomLevel
        },

        center() {
            return this.$store.state.trailEditor.centerPoint
        },

        hasTrack() {
            return this.trackCoordinates.length > 0
        }
    },

    watch: {
        trackCoordinates(newCoords) {
            if (newCoords.length > 0 && !this.geomanLayer && this.mapInstance && this.geomanLoaded) {
                this.createEditablePolyline(newCoords)
                this.fitBoundsToTrack()
            }
        },

        // Watch POI points changes
        poiPoints: {
            handler(newPois) {
                console.log('📍 POI points changed:', newPois.length)
                // POI markers are rendered via template, no manual handling needed
            },
            deep: true
        },

        // Watch activeTool changes from Vuex
        '$store.state.trailEditor.activeTool'(newTool) {
            console.log('🔧 Active tool changed via Vuex:', newTool)
            this.handleToolChange(newTool)
        },

        // Watch requestLocate flag
        '$store.state.trailEditor.requestLocate'() {
            console.log('📍 Locate requested via Vuex')
            this.locateUser()
        },

        // Watch requestZoomIn flag
        '$store.state.trailEditor.requestZoomIn'() {
            console.log('➕ Zoom in requested via Vuex')
            this.zoomIn()
        },

        // Watch requestZoomOut flag
        '$store.state.trailEditor.requestZoomOut'() {
            console.log('➖ Zoom out requested via Vuex')
            this.zoomOut()
        },

        // Watch currentLayer changes
        '$store.state.trailEditor.currentLayer'(newLayer) {
            console.log('🗺️ Layer changed via Vuex:', newLayer)
            this.changeLayer(newLayer)
        }
    },

    async mounted() {
        // Dynamically load Leaflet-Geoman
        try {
            await import('@geoman-io/leaflet-geoman-free')
            await import('@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css')
            await import('leaflet/dist/leaflet.css')
            this.geomanLoaded = true
            console.log('✅ Leaflet-Geoman loaded')

            // Initialize Geoman if map is already ready
            if (this.mapInstance) {
                this.initializeGeoman()
            }
        } catch (error) {
            console.error('❌ Failed to load Leaflet-Geoman:', error)
            this.$store.dispatch('ui/showError', 'Nie udało się załadować edytora mapy')
        }
    },

    beforeUnmount() {
        this.cleanup()
    },

    methods: {
        // Zmodyfikuj metodę onMapReady aby dodać listenery zoomu
        zoomIn() {
            if (this.mapInstance) {
                this.mapInstance.zoomIn()
                const newZoom = this.mapInstance.getZoom()
                this.updateZoom(newZoom)
            }
        },

        zoomOut() {
            if (this.mapInstance) {
                this.mapInstance.zoomOut()
                const newZoom = this.mapInstance.getZoom()
                this.updateZoom(newZoom)
            }
        },

        updateZoom(newZoom) {
            // Dispatch action to update zoom in Vuex
            this.$store.dispatch('trailEditor/setZoom', newZoom)
            console.log('🗺️ Zoom updated via Vuex action:', newZoom)
        },

// Zmodyfikuj onMapReady:
        onMapReady() {
            this.mapInstance = this.$refs.map.leafletObject
            this.tileLayer = this.mapInstance._layers[Object.keys(this.mapInstance._layers)[0]]
            console.log('✅ Map ready')

            // Set initial zoom from Vuex
            if (this.zoom && this.mapInstance.getZoom() !== this.zoom) {
                this.mapInstance.setZoom(this.zoom)
            }

            // Add zoom change listener
            this.mapInstance.on('zoomend', () => {
                const newZoom = this.mapInstance.getZoom()
                this.updateZoom(newZoom)
            })

            // Initialize Geoman if loaded
            if (this.geomanLoaded) {
                this.initializeGeoman()
            }

            // Load existing track
            if (this.trackCoordinates.length > 0) {
                this.createEditablePolyline(this.trackCoordinates)
                this.fitBoundsToTrack()
            }
        },

        initializeGeoman() {
            if (!this.mapInstance || !this.mapInstance.pm) {
                console.error('❌ Leaflet.PM not available')
                return
            }

            try {
                // COMPLETELY DISABLE all default Geoman controls
                this.mapInstance.pm.addControls({
                    drawMarker: false,
                    drawPolyline: false,
                    drawRectangle: false,
                    drawPolygon: false,
                    drawCircle: false,
                    drawCircleMarker: false,
                    editMode: false,
                    dragMode: false,
                    cutPolygon: false,
                    removalMode: false,
                    rotateMode: false
                })

                // Remove controls from map
                this.mapInstance.pm.removeControls()

                this.setupGeomanEvents()
                console.log('✅ Leaflet-Geoman initialized (NO default controls)')
            } catch (error) {
                console.error('❌ Failed to initialize Geoman:', error)
            }
        },

        // Layer control
        changeLayer(layerType) {
            const layerUrls = {
                default: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                terrain: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
                satellite: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
            }

            if (this.tileLayer) {
                this.tileLayer.setUrl(layerUrls[layerType] || layerUrls.default)
            }
        },

        // User location
        locateUser() {
            if (!this.mapInstance) return

            this.mapInstance.locate({
                setView: true,
                maxZoom: 16,
                enableHighAccuracy: true
            })

            this.mapInstance.on('locationfound', (e) => {
                const radius = e.accuracy / 2
                window.L.circle(e.latlng, radius).addTo(this.mapInstance)
                this.$store.dispatch('ui/showSuccess', 'Lokalizacja znaleziona')
            })

            this.mapInstance.on('locationerror', (e) => {
                this.$store.dispatch('ui/showError', 'Nie udało się znaleźć lokalizacji: ' + e.message)
            })
        },

        // Editor Controls
        startDrawing() {
            if (!this.mapInstance || !this.mapInstance.pm) return

            if (this.isDrawing) {
                // Stop drawing
                this.mapInstance.pm.disableDraw()
                this.isDrawing = false
            } else {
                // Start drawing - clear existing track first
                if (this.geomanLayer) {
                    if (confirm('Usunąć istniejącą trasę i narysować nową?')) {
                        this.deleteTrack()
                    } else {
                        return
                    }
                }

                this.mapInstance.pm.enableDraw('Line', {
                    snappable: true,
                    snapDistance: 20,
                    allowSelfIntersection: true,
                    finishOn: 'dblclick'
                })
                this.isDrawing = true
                this.isEditing = false
                console.log('🖊️ Drawing mode enabled')
            }
        },

        toggleEdit() {
            if (!this.geomanLayer) return

            if (this.isEditing) {
                this.geomanLayer.pm.disable()
                this.isEditing = false
                console.log('✏️ Edit mode disabled')
            } else {
                this.geomanLayer.pm.enable({
                    allowSelfIntersection: true
                })
                this.isEditing = true
                this.isDrawing = false
                console.log('✏️ Edit mode enabled')
            }
        },

        // POI Mode
        activatePoiMode() {
            if (!this.mapInstance || !this.mapInstance.pm) return

            this.disableAllModes()
            this.mapInstance.pm.enableDraw('Marker', {
                snappable: true,
                finishOn: 'click'
            })

            console.log('📍 POI mode activated')
        },

        // New enable methods without toggle (for Vuex integration)
        enableDrawMode() {
            if (!this.mapInstance || !this.mapInstance.pm) return

            // Check if track exists
            if (this.geomanLayer) {
                if (confirm('Usunąć istniejącą trasę i narysować nową?')) {
                    this.deleteTrack()
                } else {
                    // User cancelled, deactivate tool
                    this.$store.commit('trailEditor/SET_ACTIVE_TOOL', null)
                    return
                }
            }

            this.mapInstance.pm.enableDraw('Line', {
                snappable: true,
                snapDistance: 20,
                allowSelfIntersection: true,
                finishOn: 'dblclick'
            })
            this.isDrawing = true
            console.log('🖊️ Drawing mode enabled')
        },

        enableEditMode() {
            if (!this.geomanLayer) {
                console.warn('⚠️ No track to edit')
                this.$store.commit('trailEditor/SET_ACTIVE_TOOL', null)
                return
            }

            this.geomanLayer.pm.enable({
                allowSelfIntersection: true
            })
            this.isEditing = true
            console.log('✏️ Edit mode enabled')
        },

        enablePoiMode() {
            if (!this.mapInstance || !this.mapInstance.pm) return

            this.mapInstance.pm.enableDraw('Marker', {
                snappable: true,
                finishOn: 'click'
            })
            console.log('📍 POI mode enabled')
        },

        // Handle tool change from Vuex
        handleToolChange(tool) {
            if (!this.mapInstance || !this.mapInstance.pm) {
                console.warn('⚠️ Map not ready yet, tool change deferred')
                return
            }

            // First disable all modes
            this.disableAllModes()

            // Then activate the requested tool
            switch (tool) {
                case 'draw':
                    this.enableDrawMode()
                    break
                case 'edit':
                    this.enableEditMode()
                    break
                case 'poi':
                    this.enablePoiMode()
                    break
                case 'select':
                case null:
                    // Already disabled by disableAllModes()
                    console.log('🔧 All tools deactivated')
                    break
                default:
                    console.warn('Unknown tool:', tool)
            }
        },

        deleteTrack() {
            if (!this.geomanLayer) return

            if (confirm('Czy na pewno chcesz usunąć trasę?')) {
                this.geomanLayer.remove()
                this.geomanLayer = null
                this.isEditing = false
                this.isDrawing = false
                this.$store.commit('trailEditor/CLEAR_TRACK')
                console.log('🗑️ Track deleted')
            }
        },

        createEditablePolyline(coordinates) {
            if (this.geomanLayer) {
                this.geomanLayer.remove()
            }

            this.geomanLayer = window.L.polyline(coordinates, {
                color: '#2196F3',
                weight: 4,
                opacity: 0.8,
                pmIgnore: false
            }).addTo(this.mapInstance)

            console.log('✅ Editable polyline created with', coordinates.length, 'points')
        },

        syncToStore() {
            if (!this.geomanLayer) return

            const latLngs = this.geomanLayer.getLatLngs()
            const coordinates = latLngs.map(ll => [ll.lat, ll.lng])

            this.$store.commit('trailEditor/UPDATE_TRACK_COORDINATES', coordinates)

            // Auto-update start/end points
            if (coordinates.length > 0) {
                this.$store.commit('trailEditor/SET_START_POINT', coordinates[0])
                this.$store.commit('trailEditor/SET_END_POINT', coordinates[coordinates.length - 1])
            }
        },

        fitBoundsToTrack() {
            if (!this.mapInstance || this.trackCoordinates.length < 2) return

            const bounds = window.L.latLngBounds(this.trackCoordinates)
            this.mapInstance.fitBounds(bounds, {padding: [50, 50]})
        },

        // Snap to river
        applySnapToRiver(config) {
            console.log('🌊 Applying snap to river:', config)
            this.$store.dispatch('ui/showInfo', 'Trwa przyciąganie do rzeki...')
        },

        // Disable all modes
        disableAllModes() {
            if (!this.mapInstance || !this.mapInstance.pm) return

            this.mapInstance.pm.disableDraw()
            this.mapInstance.pm.disableGlobalEditMode()
            this.mapInstance.pm.disableGlobalRemovalMode()

            if (this.geomanLayer && this.geomanLayer.pm) {
                this.geomanLayer.pm.disable()
            }

            this.isDrawing = false
            this.isEditing = false
        },

        // Export
        exportMap(format) {
            console.log('📤 Exporting map as:', format)
            this.$store.dispatch('ui/showInfo', `Eksportowanie mapy jako ${format}...`)
        },

        // POI Management
        createPoiIcon(poi) {
            const isActive = this.activePoiMarker?.id === poi.id
            const isHighlighted = this.highlightedPoiMarker?.id === poi.id

            return createMdiMarkerIcon(poi.icon || 'mdi-map-marker', {
                size: 32,
                isActive,
                isHighlighted
            })
        },

        selectPoi(poi) {
            this.activePoiMarker = poi
            console.log('📍 POI selected:', poi.name)
        },

        highlightPoi(poi) {
            this.highlightedPoiMarker = poi
        },

        clearHighlightPoi() {
            this.highlightedPoiMarker = null
        },

        deletePoi(poi) {
            if (confirm(`Czy na pewno chcesz usunąć punkt "${poi.name || 'POI'}"?`)) {
                this.$store.commit('trailEditor/REMOVE_POI', poi.id)
                this.$store.dispatch('ui/showSuccess', 'Punkt POI został usunięty')
                console.log('🗑️ POI deleted:', poi.id)
            }
        },

        cleanup() {
            if (this.geomanLayer) {
                if (this.geomanLayer.pm) {
                    this.geomanLayer.pm.disable()
                }
                this.geomanLayer.remove()
            }

            if (this.mapInstance) {
                if (this.mapInstance.pm) {
                    this.mapInstance.pm.removeControls()
                }
                this.mapInstance.off('pm:create')
                this.mapInstance.off('pm:edit')
                this.mapInstance.off('pm:vertexadded')
                this.mapInstance.off('pm:vertexremoved')
                this.mapInstance.off('pm:remove')
            }

            console.log('🧹 MapCanvas cleanup complete')
        },

// W sekcji setupGeomanEvents dodaj obsługę tworzenia linii:
        setupGeomanEvents() {
            if (!this.mapInstance) return

            // New polyline created
            this.mapInstance.on('pm:create', (e) => {
                console.log('🎯 PM Create event:', e.shape)

                if (e.shape === 'Line') {
                    console.log('📏 New line created')

                    // Remove existing track if present
                    if (this.geomanLayer) {
                        this.geomanLayer.remove()
                    }

                    this.geomanLayer = e.layer
                    this.isDrawing = false

                    // Sync to store immediately
                    this.syncToStore()

                    this.$store.dispatch('ui/showSuccess', 'Trasa została utworzona')

                    console.log('✅ New polyline created and synced to store')
                }

                if (e.shape === 'Marker') {
                    console.log('📍 New marker created')
                    // Remove the temporary marker created by Geoman
                    e.layer.remove()

                    // Handle POI marker creation with default icon
                    const latlng = e.layer.getLatLng()
                    const newPoi = {
                        id: Date.now(),
                        lat: latlng.lat,
                        lng: latlng.lng,
                        name: `POI ${this.$store.state.trailEditor.poiPoints.length + 1}`,
                        description: '',
                        icon: 'mdi-map-marker', // Default icon
                        point_type_id: null
                    }

                    this.$store.commit('trailEditor/ADD_POI', newPoi)
                    this.$store.dispatch('ui/showSuccess', 'Punkt POI został dodany')
                    console.log('✅ POI added:', newPoi)

                    // Deactivate POI mode after adding
                    this.$store.commit('trailEditor/SET_ACTIVE_TOOL', null)
                }
            })

            // Rest of the event handlers remain the same...
            this.mapInstance.on('pm:edit', () => {
                console.log('✏️ Polyline edited')
                this.syncToStore()
            })

            this.mapInstance.on('pm:vertexadded', () => {
                console.log('➕ Vertex added')
                this.syncToStore()
            })

            this.mapInstance.on('pm:vertexremoved', () => {
                console.log('➖ Vertex removed')
                this.syncToStore()
            })

            this.mapInstance.on('pm:remove', (e) => {
                console.log('🗑️ Layer removed:', e.shape)
                this.geomanLayer = null
                this.isEditing = false
                this.isDrawing = false

                if (e.shape === 'Line') {
                    this.$store.commit('trailEditor/CLEAR_TRACK')
                }
            })
        }
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

/* Marker Popups */
.marker-popup,
.poi-popup {
    padding: 8px;
    min-width: 200px;
}

.marker-popup strong,
.poi-popup strong {
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
}

.poi-popup .text-caption {
    color: rgba(var(--v-theme-on-surface), 0.7);
    font-size: 12px;
}

.poi-popup .mt-1 {
    margin-top: 4px;
}

.poi-popup .mt-2 {
    margin-top: 8px;
}

/* Custom marker icons */
:deep(.start-marker-icon),
:deep(.end-marker-icon) {
    background: white;
    border-radius: 50%;
    padding: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

:deep(.mdi-marker-icon),
:deep(.default-marker-icon),
:deep(.trail-start-marker-icon),
:deep(.trail-end-marker-icon) {
    cursor: pointer;
    transition: transform 0.2s ease;
}

:deep(.mdi-marker-icon:hover),
:deep(.default-marker-icon:hover) {
    transform: scale(1.1);
}
</style>
