<template>
    <l-marker
        v-for="trail in trails"
        :key="trail.id"
        :lat-lng="[trail.start_lat, trail.start_lng]"
        :icon="createMarkerIcon(trail)"
        @click="selectTrail(trail)"
        @mouseover="highlightTrail(trail)"
        @mouseout="clearHighlightTrail"
    >
        <l-popup :options="popupOptions" @close="handlePopupClose">
            <trail-popup :trail="trail" @view-details="viewTrailDetails" />
        </l-popup>
    </l-marker>
</template>

<script>
import { LMarker, LPopup } from '@vue-leaflet/vue-leaflet';
import TrailPopup from '../TrailPopup.vue';
import { Icon } from "leaflet";

export default {
    name: "MapMarkers",
    components: {
        LMarker,
        LPopup,
        TrailPopup
    },
    props: {
        trails: {
            type: Array,
            required: true
        },
        activeTrail: {
            type: Object,
            default: null
        },
        highlightedTrail: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            popupOptions: {
                closeButton: false,
                className: 'custom-popup'
            }
        };
    },
    emits: ['select-trail', 'highlight-trail', 'clear-highlight-trail', 'view-trail-details', 'clear-active-trail'],
    methods: {
        createMarkerIcon(trail) {
            const isActive = this.activeTrail && this.activeTrail.id === trail.id;
            const isHighlighted = this.highlightedTrail && this.highlightedTrail.id === trail.id;

            return new Icon({
                iconUrl: 'data:image/svg+xml;base64,' + btoa(`
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5z" fill="${isActive ? this.$vuetify.theme.current.colors['marker-active'] : isHighlighted ? this.$vuetify.theme.current.colors['marker-highlighted'] : this.$vuetify.theme.current.colors.anchor}" stroke="white" stroke-width="2"/>
          </svg>
        `),
                iconSize: [42, 42],
                iconAnchor: [21, 42],
            });
        },
        selectTrail(trail) {
            this.$emit('select-trail', trail);
        },
        highlightTrail(trail) {
            this.$emit('highlight-trail', trail);
        },
        clearHighlightTrail() {
            this.$emit('clear-highlight-trail');
        },
        viewTrailDetails(trailId) {
            this.$emit('view-trail-details', trailId);
        },
        handlePopupClose() {
            this.$emit('clear-active-trail');
        }
    }
}
</script>

<style>
.custom-popup .leaflet-popup-content-wrapper {
    padding: 0;
    overflow: hidden;
}

.custom-popup .leaflet-popup-content {
    margin: 0;
    width: 300px !important;
}

.custom-popup .leaflet-popup-tip-container {
    display: none;
}
</style>
