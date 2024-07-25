<template>
    <div style="display: none">
        <slot></slot>
    </div>
</template>

<script>
import "leaflet.markercluster/dist/MarkerCluster.css";
import "leaflet.markercluster/dist/MarkerCluster.Default.css";
import L from 'leaflet';
import 'leaflet.markercluster';

export default {
    name: 'LMarkerCluster',
    props: {
        options: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            markerClusterGroup: null,
        };
    },
    mounted() {
        this.markerClusterGroup = L.markerClusterGroup(this.options);
        if (this.$parent.mapObject) {
            this.$parent.mapObject.addLayer(this.markerClusterGroup);
        }
    },
    methods: {
        addLayer(layer) {
            if (this.markerClusterGroup) {
                this.markerClusterGroup.addLayer(layer);
            }
        },
        removeLayer(layer) {
            if (this.markerClusterGroup) {
                this.markerClusterGroup.removeLayer(layer);
            }
        },
    },
    render() {
        return this.$slots.default ? this.$slots.default() : null;
    },
};
</script>

<style scoped>
/* Styles remain the same */
</style>
