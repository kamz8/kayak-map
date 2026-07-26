<template>
    <div class="trail-map-editor-component">
        <!-- Map Container -->
        <div class="map-container">
            <!-- Loading Overlay -->
            <div v-if="isLoading" class="loading-overlay">
                <v-progress-circular indeterminate color="primary" size="64" />
                <p>Ładowanie trasy...</p>
            </div>

            <!-- Map Canvas with Controls -->
            <div v-show="!isLoading" class="map-with-controls">
                <MapCanvas
                    ref="mapCanvas"
                    @map-ready="handleMapReady"
                    @zoom-changed="handleZoomChanged"
                />

                <!-- Map Controls Overlay -->
                <div class="map-controls-overlay">
                    <MapControls
                        @locate="locateUser"
                    />
                </div>
            </div>

            <!-- Floating Toolbar -->
            <div class="floating-toolbar">
                <EditorToolbar
                    :trail-name="trailName"
                    @save="handleSave"
                    @back="$emit('back')"
                    @export="handleExport"
                    @snap-requested="handleSnapRequested"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { trailEditorGetters, trailEditorActions } from '../store/trailEditor.js'

import MapCanvas from './MapCanvas.vue'
import MapControls from './MapControls.vue'
import EditorToolbar from './EditorToolbar.vue'
import apiClient from '@/dashboard/plugins/axios'

export default {
    name: 'TrailMapEditorComponent',
    components: {
        MapCanvas,
        MapControls,
        EditorToolbar
    },

    props: {
        trailId: {
            type: [String, Number],
            required: true
        }
    },

    data() {
        return {
            trailName: '',
            mapInstance: null
        }
    },

    computed: {
        ...mapGetters('trailEditor', {
            isLoading: trailEditorGetters.IS_LOADING,
            trackCoordinates: trailEditorGetters.TRACK_COORDINATES,
            hasTrack: trailEditorGetters.HAS_TRACK
        })
    },

    mounted() {
        this.loadTrailData()
    },

    beforeUnmount() {
        this.cleanup()
    },

    methods: {
        ...mapActions('trailEditor', {
            loadTrail: trailEditorActions.LOAD_TRAIL,
            saveTrack: trailEditorActions.SAVE_TRACK,
            changeLayer: trailEditorActions.CHANGE_LAYER,
            clearEditor: trailEditorActions.CLEAR_EDITOR
        }),

        async loadTrailData() {
            try {
                await this.loadTrail(this.trailId)

                // Fetch trail name for display
                const response = await apiClient.get(`/dashboard/trails/${this.trailId}`)
                this.trailName = response.data.data.trail_name

                // Update page header with trail name
                if (this.$route.meta.pageHeader) {
                    this.$route.meta.pageHeader.title = `Edytor trasy - Szlak #${this.trailName}`
                }

                // Update breadcrumb with trail name
                const trailBreadcrumb = this.$route.meta.breadcrumbs?.find(b => b.key === 'trail')
                if (trailBreadcrumb) {
                    trailBreadcrumb.text = this.trailName
                    trailBreadcrumb.to = `/dashboard/trails/${this.trailId}/edit`
                }

                console.log('✅ Trail loaded:', this.trailName)

            } catch (error) {
                console.error('❌ Failed to load trail:', error)
                this.$notify('Nie udało się załadować trasy', 'error')
                this.$emit('load-error', error)
            }
        },

        handleMapReady(mapInstance) {
            this.mapInstance = mapInstance
            console.log('🗺️ Map ready in editor component')

        },

        handleZoomChanged(zoomLevel) {
            // Zoom changes are now handled by Vuex automatically
            console.log('🔄 Zoom changed to:', zoomLevel)
        },

        // Map control methods
        locateUser() {
            if (this.mapInstance && this.mapInstance.locateUser) {
                this.mapInstance.locateUser()
            }
        },

        // Tool handling
        // Snap functionality
        handleSnapRequested(config) {
            if (this.mapInstance && this.mapInstance.applySnapToRiver) {
                this.mapInstance.applySnapToRiver(config)
            }
        },

        // Save handler
        async handleSave() {
            try {
                await this.saveTrack()
                this.$emit('save-success')
            } catch (error) {
                this.$emit('save-error', error)
            }
        },

        // Export handler
        handleExport(format) {
            if (this.mapInstance && this.mapInstance.exportMap) {
                this.mapInstance.exportMap(format)
            }
        },

        cleanup() {
            if (this.mapInstance && this.mapInstance.cleanup) {
                this.mapInstance.cleanup()
            }
            this.clearEditor()
            console.log('🧹 Trail Map Editor Component cleanup complete')
        }
    }
}
</script>

<style scoped>
.trail-map-editor-component {
    width: 100%;
    height: 100%;
    position: relative;
}

.map-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.map-with-controls {
    position: relative;
    width: 100%;
    height: 100%;
}

/* Map Controls - Now properly positioned within the component */
.map-controls-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 500;
    pointer-events: none;
}

/* Floating Toolbar */
.floating-toolbar {
    position: absolute;
    top: 8px;
    left: 16px;
    right: 16px;
    z-index: 1000;
    max-width: 1200px;
    margin: 0 auto;
    pointer-events: auto;
}

/* Loading Overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    background: rgba(var(--v-theme-surface), 0.95);
    z-index: 2000;
}

.loading-overlay p {
    color: rgb(var(--v-theme-on-surface-variant));
    font-size: 16px;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .floating-toolbar {
        top: 4px;
        left: 8px;
        right: 8px;
    }
}

@media (max-width: 480px) {
    .floating-toolbar {
        top: 2px;
        left: 4px;
        right: 4px;
    }
}
</style>
