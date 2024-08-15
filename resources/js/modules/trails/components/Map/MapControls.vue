<template>
    <div class="map-controls top-right-controls">
        <div class="layer-control" @mouseenter="showLayerOptions = true" @mouseleave="showLayerOptions = false">
            <v-btn icon="mdi-layers" density="comfortable" class="main-button" v-tooltip="'Warstwy mapy'"/>
            <transition name="fade">
                <div v-if="showLayerOptions" class="layer-options">
                    <v-btn icon="mdi-map" class="layer-button" @click="setTileLayer('default')" v-tooltip="'Mapa domyślna'"/>
                    <v-btn icon="mdi-terrain" class="layer-button" @click="setTileLayer('terrain')" v-tooltip="'Mapa terenu'"/>
                    <v-btn icon="mdi-satellite-variant" class="layer-button" @click="setTileLayer('satellite')"
                           v-tooltip="'Mapa satelitarna'"/>
                </div>
            </transition>
        </div>
    </div>
    <div class="map-controls bottom-right-controls">
        <v-btn icon="mdi-plus" density="comfortable" class="control-button" @click="zoomIn" v-tooltip="'Przybliż'"/>
        <v-btn icon="mdi-minus" density="comfortable" class="control-button" @click="zoomOut" v-tooltip="'Oddal'"/>
        <v-btn icon="mdi-crosshairs-gps" density="comfortable" class="control-button" @click="locate"
               v-tooltip="'Zlokalizuj mnie'"/>
    </div>



    <div class="map-controls">
        <div class="top-right-controls">
            <div class="layer-control" @mouseenter="showLayerOptions = true" @mouseleave="showLayerOptions = false">
                <v-btn icon="mdi-layers" density="comfortable" class="main-button" v-tooltip="'Warstwy mapy'"/>
                <transition name="fade">
                    <div v-if="showLayerOptions" class="layer-options">
                        <v-btn icon="mdi-map" class="layer-button" @click="$emit('change-layer', 'default')" v-tooltip="'Mapa domyślna'"/>
                        <v-btn icon="mdi-terrain" class="layer-button" @click="$emit('change-layer', 'terrain')" v-tooltip="'Mapa terenu'"/>
                        <v-btn icon="mdi-satellite-variant" class="layer-button" @click="$emit('change-layer', 'satellite')" v-tooltip="'Mapa satelitarna'"/>
                    </div>
                </transition>
            </div>
        </div>
        <div class="bottom-right-controls">
            <v-btn icon="mdi-plus" density="comfortable" class="control-button" @click="$emit('zoom-in')" v-tooltip="'Przybliż'"/>
            <v-btn icon="mdi-minus" density="comfortable" class="control-button" @click="$emit('zoom-out')" v-tooltip="'Oddal'"/>
            <v-btn icon="mdi-crosshairs-gps" density="comfortable" class="control-button" @click="$emit('locate')" v-tooltip="'Zlokalizuj mnie'"/>
        </div>
    </div>
</template>

<script>
export default {
    name: "MapControls",
    data() {
        return {
            showLayerOptions: false
        };
    },
    emits: ['change-layer', 'zoom-in', 'zoom-out', 'locate']
}
</script>

<style scoped>
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
