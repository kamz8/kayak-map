// src/mixins/MapMixin.js
export default {
    methods: {
        // Konwertuje współrzędne [longitude, latitude] na [latitude, longitude]
        convertCoordinates(coordinates) {
            return coordinates.map(([lng, lat]) => [lat, lng])
        }
    }
}
