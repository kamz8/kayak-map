import L from 'leaflet';
import 'leaflet.markercluster';

export default L.MarkerClusterGroup.extend({
    options: {
        chunkedLoading: true,
        chunkInterval: 200,
        chunkDelay: 50,
        chunkProgress: null,
        maxClusterRadius: 80,
        spiderfyOnMaxZoom: false,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        removeOutsideVisibleBounds: true,
    },

    initialize: function (options) {
        L.MarkerClusterGroup.prototype.initialize.call(this, options);
        this._virtualMarkers = [];
    },

    addLayers: function (layers) {
        this._virtualMarkers = this._virtualMarkers.concat(layers);
        this._refreshClusters();
    },

    clearLayers: function () {
        this._virtualMarkers = [];
        return L.MarkerClusterGroup.prototype.clearLayers.call(this);
    },

    _refreshClusters: function () {
        if (!this._map) return;

        const bounds = this._map.getBounds().pad(0.2);
        const visibleMarkers = this._virtualMarkers.filter(marker =>
            bounds.contains(marker.getLatLng())
        );

        L.MarkerClusterGroup.prototype.clearLayers.call(this);
        L.MarkerClusterGroup.prototype.addLayers.call(this, visibleMarkers);
    },

    onAdd: function (map) {
        this._map = map;
        this._refreshClusters();
        this._map.on('moveend', this._refreshClusters, this);
        return L.MarkerClusterGroup.prototype.onAdd.call(this, map);
    },

    onRemove: function (map) {
        this._map.off('moveend', this._refreshClusters, this);
        this._map = null;
        return L.MarkerClusterGroup.prototype.onRemove.call(this, map);
    }
});
