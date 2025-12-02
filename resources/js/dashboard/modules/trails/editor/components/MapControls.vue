<template>
    <div class="map-controls">
        <!-- Top Right: Layer Control -->
        <div class="control-group layer-control-group">
            <div class="layer-control" @mouseenter="showLayerOptions = true" @mouseleave="showLayerOptions = false">
                <v-btn
                    size="x-small"
                    density="comfortable"
                    variant="flat"
                    class="control-button main-button ui-interactive"
                    v-tooltip="'Warstwy mapy'"
                >
                    <v-icon size="small">mdi-layers</v-icon>
                </v-btn>
                <transition name="ui-fade">
                    <div v-if="showLayerOptions" class="layer-options">
                        <v-btn
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            class="control-button layer-button ui-interactive"
                            @click="handleChangeLayer('default')"
                            v-tooltip="'Mapa domyślna'"
                            :color="currentLayer === 'default' ? 'primary' : undefined"
                        >
                            <v-icon size="small">mdi-map</v-icon>
                        </v-btn>
                        <v-btn
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            class="control-button layer-button ui-interactive"
                            @click="handleChangeLayer('terrain')"
                            v-tooltip="'Mapa terenu'"
                            :color="currentLayer === 'terrain' ? 'primary' : undefined"
                        >
                            <v-icon size="small">mdi-terrain</v-icon>
                        </v-btn>
                        <v-btn
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            class="control-button layer-button ui-interactive"
                            @click="handleChangeLayer('satellite')"
                            v-tooltip="'Mapa satelitarna'"
                            :color="currentLayer === 'satellite' ? 'primary' : undefined"
                        >
                            <v-icon size="small">mdi-satellite-variant</v-icon>
                        </v-btn>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Bottom Right: Zoom & Locate Controls -->
        <div class="control-group zoom-control-group">
            <div class="zoom-controls v-card">
                <!-- Zoom In -->
                <v-btn
                    size="x-small"
                    density="comfortable"
                    variant="flat"
                    class="control-button zoom-button ui-interactive"
                    @click="handleZoomIn"
                    v-tooltip="'Przybliż'"
                    :disabled="isMaxZoom"
                >
                    <v-icon size="small">mdi-plus</v-icon>
                </v-btn>

                <!-- Zoom Level Indicator -->
                <div class="zoom-indicator">
                    <span class="zoom-level ui-text">{{ currentZoom }}</span>
                </div>

                <!-- Zoom Out -->
                <v-btn
                    size="x-small"
                    density="comfortable"
                    variant="flat"
                    class="control-button zoom-button ui-interactive"
                    @click="handleZoomOut"
                    v-tooltip="'Oddal'"
                    :disabled="isMinZoom"
                >
                    <v-icon size="small">mdi-minus</v-icon>
                </v-btn>
            </div>

            <!-- Locate Me -->
            <div class="locate-control">
                <v-btn
                    size="x-small"
                    density="comfortable"
                    variant="flat"
                    class="control-button locate-button ui-interactive"
                    @click="handleLocate"
                    v-tooltip="'Zlokalizuj mnie'"
                >
                    <v-icon size="small">mdi-crosshairs-gps</v-icon>
                </v-btn>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { trailEditorGetters } from '../store/trailEditor.js'

export default {
    name: "MapControls",

    data() {
        return {
            showLayerOptions: false
        };
    },

    computed: {
        ...mapGetters('trailEditor', {
            currentZoom: trailEditorGetters.CURRENT_ZOOM,
            isMinZoom: trailEditorGetters.IS_MIN_ZOOM,
            isMaxZoom: trailEditorGetters.IS_MAX_ZOOM,
            currentLayer: trailEditorGetters.CURRENT_LAYER
        })
    },

    methods: {
        handleZoomIn() {
            this.$store.commit('trailEditor/REQUEST_ZOOM_IN')
            console.log('➕ Zoom in requested via Vuex')
        },

        handleZoomOut() {
            this.$store.commit('trailEditor/REQUEST_ZOOM_OUT')
            console.log('➖ Zoom out requested via Vuex')
        },

        handleChangeLayer(layerType) {
            this.$store.commit('trailEditor/SET_CURRENT_LAYER', layerType)
            this.showLayerOptions = false
            console.log('🗺️ Layer change requested via Vuex:', layerType)
        },

        handleLocate() {
            this.$store.commit('trailEditor/REQUEST_LOCATE')
            console.log('📍 Locate requested via Vuex')
        }
    }
}
</script>

<style scoped>
.map-controls {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    pointer-events: none;
}

.control-group {
    position: absolute;
    pointer-events: auto;
}

/* Layer Control - Top Right */
.layer-control-group {
    top: 80px; /* Below the toolbar */
    right: 20px;
}

/* Zoom Controls - Bottom Right */
.zoom-control-group {
    bottom: 20px;
    right: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.layer-control {
    position: relative;
    display: inline-block;
}

/* Zoom Controls */
.zoom-controls {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: rgb(var(--v-theme-surface));
    backdrop-filter: blur(10px);
    border-radius: 8px;
    padding: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(var(--v-border-color), 0.3);
    gap: 2px;
}

.zoom-button {
    border-radius: 6px !important;
    min-width: 32px !important;
    width: 32px !important;
    height: 28px !important;
    background: rgb(var(--v-theme-surface)) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid rgba(var(--v-border-color), 0.3) !important;
    transition: all 0.2s ease-in-out !important;
}

.zoom-button:hover:not(.v-btn--disabled) {
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-1px);
    background: rgba(var(--v-theme-primary), 0.05) !important;
    border-color: rgb(var(--v-theme-primary)) !important;
}

.zoom-button:active:not(.v-btn--disabled) {
    transform: translateY(0);
}

.zoom-button.v-btn--disabled {
    opacity: 0.4;
    background: rgba(var(--v-theme-on-surface), 0.05) !important;
    cursor: not-allowed;
}

/* Zoom Indicator */
.zoom-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 24px;
    background: rgba(var(--v-theme-surface-variant), 0.8);
    border-radius: 4px;
    border: 1px solid rgba(var(--v-border-color), 0.2);
    margin: 2px 0;
}

.zoom-level {
    font-size: 11px;
    font-weight: 600;
    color: rgb(var(--v-theme-on-surface-variant));
    user-select: none;
}

/* Locate Control */
.locate-control {
    display: flex;
    justify-content: center;
}

.locate-button {
    border-radius: 6px !important;
    min-width: 32px !important;
    width: 32px !important;
    height: 32px !important;
    background: rgb(var(--v-theme-surface)) !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid rgba(var(--v-border-color), 0.3) !important;
    transition: all 0.2s ease-in-out !important;
}

.locate-button:hover {
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-1px);
    background: rgba(var(--v-theme-primary), 0.05) !important;
    border-color: rgb(var(--v-theme-primary)) !important;
}

.locate-button:active {
    transform: translateY(0);
}

/* Layer Options */
.layer-options {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 6px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    background: rgb(var(--v-theme-surface));
    backdrop-filter: blur(10px);
    border-radius: 8px;
    padding: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(var(--v-border-color), 0.3);
    z-index: 1001;
}

.control-button {
    border-radius: 6px !important;
    min-width: 32px !important;
    width: 32px !important;
    height: 32px !important;
    background: rgb(var(--v-theme-surface)) !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid rgba(var(--v-border-color), 0.3) !important;
    transition: all 0.2s ease-in-out !important;
}

.control-button:hover {
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-1px);
    background: rgba(var(--v-theme-primary), 0.05) !important;
    border-color: rgb(var(--v-theme-primary)) !important;
}

.main-button,
.layer-button {
    border-radius: 6px !important;
}

.layer-button {
    width: 32px !important;
    height: 32px !important;
}

.layer-button.v-btn--active {
    background: rgba(var(--v-theme-primary), 0.1) !important;
    border-color: rgb(var(--v-theme-primary)) !important;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .layer-control-group {
        top: 70px;
        right: 12px;
    }

    .zoom-control-group {
        bottom: 12px;
        right: 12px;
    }

    .control-button,
    .zoom-button,
    .locate-button {
        min-width: 30px !important;
        width: 30px !important;
        height: 30px !important;
    }

    .zoom-button {
        height: 26px !important;
    }

    .zoom-indicator {
        width: 30px;
        height: 22px;
    }

    .zoom-level {
        font-size: 10px;
    }

    .layer-button {
        width: 30px !important;
        height: 30px !important;
    }
}

@media (max-width: 480px) {
    .layer-control-group {
        top: 65px;
        right: 8px;
    }

    .zoom-control-group {
        bottom: 8px;
        right: 8px;
    }

    .control-button,
    .zoom-button,
    .locate-button {
        min-width: 28px !important;
        width: 28px !important;
        height: 28px !important;
    }

    .zoom-button {
        height: 24px !important;
    }

    .zoom-indicator {
        width: 28px;
        height: 20px;
    }

    .zoom-level {
        font-size: 9px;
    }

    .layer-button {
        width: 28px !important;
        height: 28px !important;
    }
}
</style>
