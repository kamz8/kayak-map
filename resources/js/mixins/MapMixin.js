// src/mixins/MapMixin.js
export default {
    methods: {
        // convert coordinates from [longitude, latitude] to [latitude, longitude]
        convertCoordinates(coordinates) {
            return coordinates.map(([lng, lat]) => [lat, lng])
        },
        getPointIcon(pointType) {
            switch (pointType) {
                case 'Pole namiotowe':
                    return 'mdi-tent'
                case 'Miejsce biwakowania':
                    return 'mdi-tent'
                case 'biwak':
                    return 'mdi-campfire'
                case 'Przeszkoda':
                case 'Niebezpieczeństwo':
                case 'uwaga':
                    return 'mdi-alert'
                case 'Jaz':
                    return 'mdi-water'
                case 'most':
                    return 'mdi-bridge'
                case 'przenoska':
                    return 'mdi-arrow-up-down'
                case 'ujście':
                    return 'mdi-call-split'
                case 'sklep':
                    return 'mdi-store'
                case 'Inny':
                case 'Other':
                default:
                    return 'mdi-map-marker'
            }
        },
        getPointColor(pointType) {
            switch (pointType) {
                case 'Pole namiotowe':
                case 'Miejsce biwakowania':
                    return 'green'
                case 'Przeszkoda':
                case 'Niebezpieczeństwo':
                case 'uwaga':
                    return 'red'
                case 'Jaz':
                    return 'blue'
                case 'most':
                    return 'brown'
                case 'przenoska':
                    return 'orange'
                case 'ujście':
                    return 'purple'
                case 'sklep':
                    return 'amber'
                case 'Inny':
                case 'Other':
                default:
                    return 'teal'
            }
        },

        isValidLocation(point) {
            return point &&
                typeof point === 'object' &&
                point.lat !== undefined &&
                point.lng !== undefined &&
                point.lat !== "-1.0000000" &&
                point.lng !== "-1.0000000" &&
                point.lat !== null &&
                point.lng !== null;
        }
    }
}
