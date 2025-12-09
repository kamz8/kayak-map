import L from 'leaflet'

/**
 * Definicje kolorów dla POI (Point of Interest), mapowane na klucze kolorów Vuetify lub konkretne wartości HEX.
 * Ten obiekt jest używany w PoiMapMarker.vue do dynamicznego pobierania koloru z motywu Vuetify.
 * Zapewnia spójność wizualną z resztą aplikacji.
 */
export const POI_TYPE_COLOR_MAP = {
    // success/green
    'mdi-tent': 'success',
    'mdi-campfire': 'success',
    'mdi-map-marker-check': 'success',

    // error/red
    'mdi-alert': 'error',
    'mdi-alert-octagon': 'error',
    'mdi-alert-circle': 'error',
    'mdi-map-marker-alert': 'error',
    'mdi-map-marker-remove': 'error',

    // primary/blue
    'mdi-water': 'primary',
    'mdi-water-outline': 'primary',
    'mdi-map-marker': 'primary',
    'mdi-kayaking': 'primary',

    // warning/orange
    'mdi-arrow-up-down': 'warning', // Portage
    'mdi-call-split': 'warning', // Rozwidlenie
    'mdi-glass-mug-variant': 'warning', // Punkt gastronomiczny

    // neutral/brown/grey
    'mdi-bridge': 'brown',
    'mdi-gate': 'brown',
    'mdi-home-group': 'brown',
    'mdi-city': 'grey',

    // Info/Navigation points
    'mdi-hand-pointing-right': 'info',
    'mdi-help-circle-outline': 'info',
    'mdi-map-marker-plus': 'secondary', // Domyślny kolor dla dodawanych POI
}

/**
 * Funkcja pomocnicza do pobierania nazwy koloru Vuetify na podstawie nazwy ikony MDI.
 * Używana w PoiMapMarker.vue i PoiEditorDialog.vue.
 * @param {string} mdiIconName - Nazwa ikony MDI (np. 'mdi-tent').
 * @returns {string} Klucz koloru Vuetify (np. 'success', 'primary').
 */
export function getPointTypeColor(mdiIconName) {
    // Zwraca klucz koloru z mapy, domyślnie 'secondary'
    return POI_TYPE_COLOR_MAP[mdiIconName] || 'secondary'
}

/**
 * Funkcja do mapowania ikon MDI na ich kody Unicode.
 * Jest to niezbędne, jeśli chcemy osadzić symbole MDI bezpośrednio w SVG/Canvas.
 * @param {string} mdiIconName - Nazwa ikony MDI (np. 'mdi-map-marker-check').
 * @returns {string} Kod Unicode dla ikony.
 */
export function getMdiSymbol(mdiIconName) {
    const mdiUnicodeMap = {
        // Skrócona lista najczęściej używanych kodów Unicode dla ikon MDI
        'mdi-map-marker': '\uF34E',
        'mdi-map-marker-check': '\uF34F',
        'mdi-map-marker-alert': '\uF1552',
        'mdi-map-marker-plus': '\uF350',
        'mdi-map-marker-remove': '\uF1554',
        'mdi-tent': '\uF1555',
        'mdi-store': '\uF1DA',
        'mdi-home-group': '\uF1556',
        'mdi-water': '\uF1E0',
        'mdi-water-outline': '\uF1557',
        'mdi-kayaking': '\uF0C8C',
        'mdi-alert': '\uF02D',
        'mdi-alert-octagon': '\uF02E',
        'mdi-alert-circle': '\uF02C',
        'mdi-bridge': '\uF03B9',
        'mdi-gate': '\uF0635',
        'mdi-glass-mug-variant': '\uF1116',
        'mdi-hand-pointing-right': '\uF066E',
        'mdi-help-circle-outline': '\uF069B',
        'mdi-city': '\uF0433',
        'mdi-campfire': '\uF05CC',
        'mdi-arrow-up-down': '\uF022B',
        'mdi-call-split': '\uF03DA',

        // Domyślny symbol dla nieznanych ikon
        'default': '\uF34E', // mdi-map-marker
    }

    return mdiUnicodeMap[mdiIconName] || mdiUnicodeMap.default
}

/**
 * Tworzy ikonę POI używając Vuetify colorsystem i MDI icons.
 * @param {string} mdiIconName - Nazwa ikony MDI (np. 'mdi-tent').
 * @param {Object} vuetifyTheme - Obiekt motywu Vuetify ($vuetify.theme).
 * @param {Object} options - Opcje ikony.
 * @returns {L.DivIcon} Obiekt ikony Leaflet DivIcon.
 */
export function createPoiIcon(mdiIconName, vuetifyTheme, options = {}) {
    const { size = 32, isActive = false } = options

    // Get color key from map
    const colorKey = getPointTypeColor(mdiIconName)

    // Get actual color from Vuetify theme
    const color = vuetifyTheme.current.colors[colorKey] || vuetifyTheme.current.colors.primary

    // Convert mdi-icon-name to mdi-icon-name class format
    // Material Design Icons uses class format: mdi mdi-icon-name
    const iconClass = mdiIconName.replace('mdi-', '')

    const html = `
        <div class="poi-marker-wrapper" style="
            display: flex;
            align-items: center;
            justify-content: center;
            width: ${size}px;
            height: ${size}px;
        ">
            <i class="mdi mdi-${iconClass}"
               style="
                    color: ${color};
                    font-size: ${size}px;
                    ${isActive ? 'filter: drop-shadow(0 0 4px ' + color + ');' : ''}
                "
            ></i>
        </div>
    `

    return L.divIcon({
        className: 'poi-marker-icon',
        html: html,
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
        popupAnchor: [0, -size / 2]
    })
}

/**
 * Tworzy ikonę markera dla punktu startowego lub końcowego trasy.
 * Używa prostego znacznika z tekstem "S" lub "E".
 * @param {'start' | 'end'} type - Typ markera ('start' lub 'end').
 * @returns {L.DivIcon} Obiekt ikony Leaflet DivIcon.
 */
export function createTrailMarkerIcon(type) {
    const size = 30
    const color = type === 'start' ? '#4CAF50' : '#F44336' // Zielony dla Start, Czerwony dla End
    const text = type === 'start' ? 'S' : 'E'

    const html = `
        <div style="
            background-color: ${color};
            color: white;
            width: ${size}px;
            height: ${size}px;
            line-height: ${size}px;
            text-align: center;
            border-radius: 50%;
            border: 3px solid white;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        ">
            ${text}
        </div>
    `

    return L.divIcon({
        className: type === 'start' ? 'start-marker' : 'end-marker',
        html: html,
        iconSize: [size, size],
        iconAnchor: [size / 2, size], // Kotwica na dół znacznika
        popupAnchor: [0, -size] // Popup nad znacznikiem
    })
}