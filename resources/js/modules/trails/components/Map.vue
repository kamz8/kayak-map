<template>
    <l-map style="height: 100vh; width: 100%;" :zoom="zoom" :center="center">
        <l-tile-layer :url="url" :attribution="attribution" />
    </l-map>
</template>

<script>
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer } from "@vue-leaflet/vue-leaflet";

export default {
    name: "Map",
    components: {
        LMap,
        LTileLayer,
    },
    data() {
        return {
            zoom: 13,
            center: [51.505, -0.09],
            url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            attribution: '© OpenStreetMap contributors'
        };
    },
};
</script>

<style>
@import "leaflet/dist/leaflet.css";
</style>
