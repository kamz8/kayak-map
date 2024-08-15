<script>
import { defineComponent, onMounted, onBeforeUnmount, h } from 'vue';
import L from 'leaflet';
import 'leaflet.markercluster';

export default defineComponent({
    name: 'LMarkerCluster',
    props: {
        options: {
            type: Object,
            default: () => ({})
        }
    },
    setup(props, { slots }) {
        let markerClusterGroup = null;

        onMounted(() => {
            const leafletRef = this.$parent.leafletObject;
            if (!leafletRef) {
                console.error('No Leaflet reference found');
                return;
            }

            markerClusterGroup = L.markerClusterGroup(props.options);
            leafletRef.addLayer(markerClusterGroup);
        });

        onBeforeUnmount(() => {
            if (markerClusterGroup) {
                markerClusterGroup.clearLayers();
                this.$parent.leafletObject.removeLayer(markerClusterGroup);
            }
        });

        return () => h('div', slots.default?.());
    }
});
</script>
