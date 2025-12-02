import { Icon } from 'leaflet'

/**
 * Map of MDI icon names to their SVG paths
 */
const MDI_ICONS = {
    'mdi-tent': mdiTent,
    'mdi-dam': mdiDam,
    'mdi-bridge': mdiBridge,
    'mdi-hand-pointing-right': mdiHandPointingRight,
    'mdi-store': mdiStore,
    'mdi-home-group': mdiHomeGroup,
    'mdi-water-outline': mdiWaterOutline,
    'mdi-alert': mdiAlert,
    'mdi-water': mdiWater,
    'mdi-kayaking': mdiKayaking,
    'mdi-alert-octagon': mdiAlertOctagon,
    'mdi-medical-bag': mdiMedicalBag,
    'mdi-glass-mug-variant': mdiGlassMugVariant,
    'mdi-gate': mdiGate,
    'mdi-help-circle-outline': mdiHelpCircleOutline,
    'mdi-city': mdiCity
}

/**
 * Color mapping for different point types
 */
const POINT_TYPE_COLORS = {
    // Warning/Danger points
    'mdi-alert': '#FF5252',
    'mdi-alert-octagon': '#D32F2F',
    'mdi-dam': '#FF6F00',
    'mdi-gate': '#F57C00',

    // Water/Kayaking points
    'mdi-water': '#2196F3',
    'mdi-water-outline': '#42A5F5',
    'mdi-kayaking': '#00BCD4',
    'mdi-bridge': '#607D8B',

    // Service/Facility points
    'mdi-tent': '#4CAF50',
    'mdi-store': '#9C27B0',
    'mdi-home-group': '#795548',
    'mdi-glass-mug-variant': '#FF9800',
    'mdi-medical-bag': '#E91E63',

    // Info/Navigation points
    'mdi-hand-pointing-right': '#3F51B5',
    'mdi-help-circle-outline': '#00BCD4',
    'mdi-city': '#9E9E9E',

    // Default
    'default': '#757575'
}

/**
 * Creates a Leaflet Icon from MDI icon name
 * @param {string} mdiIconName - MDI icon name (e.g., 'mdi-tent')
 * @param {Object} options - Icon options
 * @param {string} options.color - Icon fill color (overrides default)
 * @param {string} options.backgroundColor - Background circle color
 * @param {number} options.size - Icon size in pixels
 * @param {boolean} options.isActive - Whether the marker is active/selected
 * @param {boolean} options.isHighlighted - Whether the marker is highlighted
 * @returns {Icon} Leaflet Icon instance
 */
export function createMdiMarkerIcon(mdiIconName, options = {}) {
    const {
        color = POINT_TYPE_COLORS[mdiIconName] || POINT_TYPE_COLORS.default,
        backgroundColor = '#FFFFFF',
        size = 32,
        isActive = false,
        isHighlighted = false
    } = options

    const iconPath = MDI_ICONS[mdiIconName]

    if (!iconPath) {
        console.warn(`MDI icon "${mdiIconName}" not found, using default`)
        return createDefaultMarkerIcon(options)
    }

    // Adjust colors based on state
    let finalColor = color
    let strokeColor = '#FFFFFF'
    let strokeWidth = 2

    if (isActive) {
        strokeColor = '#FF9800' // Orange for active
        strokeWidth = 3
    } else if (isHighlighted) {
        strokeColor = '#FFC107' // Amber for highlighted
        strokeWidth = 2.5
    }

    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${size}" width="${size}" height="${size}">
            <!-- Background circle -->
            <circle
                cx="${size / 2}"
                cy="${size / 2}"
                r="${size / 2 - strokeWidth}"
                fill="${backgroundColor}"
                stroke="${strokeColor}"
                stroke-width="${strokeWidth}"
                opacity="0.95"
            />

            <!-- MDI Icon -->
            <g transform="translate(${size * 0.2}, ${size * 0.2}) scale(${size * 0.025})">
                <path d="${iconPath}" fill="${finalColor}"/>
            </g>
        </svg>
    `

    return new Icon({
        iconUrl: 'data:image/svg+xml;base64,' + btoa(svg),
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
        popupAnchor: [0, -size / 2],
        className: 'mdi-marker-icon'
    })
}

/**
 * Creates a default marker icon (pin style) for when MDI icon is not available
 */
function createDefaultMarkerIcon(options = {}) {
    const {
        color = '#757575',
        size = 32,
        isActive = false,
        isHighlighted = false
    } = options

    let strokeColor = '#FFFFFF'
    let strokeWidth = 2

    if (isActive) {
        strokeColor = '#FF9800'
        strokeWidth = 3
    } else if (isHighlighted) {
        strokeColor = '#FFC107'
        strokeWidth = 2.5
    }

    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 36" width="${size}" height="${size * 1.5}">
            <path
                d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5z"
                fill="${color}"
                stroke="${strokeColor}"
                stroke-width="${strokeWidth}"
            />
        </svg>
    `

    return new Icon({
        iconUrl: 'data:image/svg+xml;base64,' + btoa(svg),
        iconSize: [size, size * 1.5],
        iconAnchor: [size / 2, size * 1.5],
        popupAnchor: [0, -size * 1.5],
        className: 'default-marker-icon'
    })
}

/**
 * Creates a trail start/end marker icon
 */
export function createTrailMarkerIcon(type = 'start', options = {}) {
    const {
        size = 32,
        isActive = false,
        isHighlighted = false
    } = options

    const color = type === 'start' ? '#4CAF50' : '#F44336' // Green for start, Red for end
    let strokeColor = '#FFFFFF'
    let strokeWidth = 2

    if (isActive) {
        strokeColor = '#FF9800'
        strokeWidth = 3
    } else if (isHighlighted) {
        strokeColor = '#FFC107'
        strokeWidth = 2.5
    }

    const label = type === 'start' ? 'S' : 'E'

    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 36" width="${size}" height="${size * 1.5}">
            <!-- Pin shape -->
            <path
                d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7z"
                fill="${color}"
                stroke="${strokeColor}"
                stroke-width="${strokeWidth}"
            />

            <!-- Label circle -->
            <circle cx="12" cy="9" r="5" fill="${strokeColor}" opacity="0.9"/>

            <!-- Label text -->
            <text
                x="12"
                y="9"
                text-anchor="middle"
                dominant-baseline="central"
                font-family="Arial, sans-serif"
                font-size="8"
                font-weight="bold"
                fill="${color}"
            >${label}</text>
        </svg>
    `

    return new Icon({
        iconUrl: 'data:image/svg+xml;base64,' + btoa(svg),
        iconSize: [size, size * 1.5],
        iconAnchor: [size / 2, size * 1.5],
        popupAnchor: [0, -size * 1.5],
        className: `trail-${type}-marker-icon`
    })
}

/**
 * Get color for a point type icon
 */
export function getPointTypeColor(mdiIconName) {
    return POINT_TYPE_COLORS[mdiIconName] || POINT_TYPE_COLORS.default
}

/**
 * Check if MDI icon exists in our map
 */
export function hasMdiIcon(mdiIconName) {
    return !!MDI_ICONS[mdiIconName]
}
