/**
 * Coordinate utilities for Trail Map Editor
 * Handles conversion between Leaflet [lat, lng] and GeoJSON [lng, lat] formats
 */

/**
 * Convert Leaflet format [lat, lng] to GeoJSON format [lng, lat]
 * @param {Array<Array<number>>} coordinates - Array of [lat, lng] pairs
 * @returns {Array<Array<number>>} Array of [lng, lat] pairs
 */
export const toGeoJSON = (coordinates) => {
  return coordinates.map(([lat, lng]) => [lng, lat])
}

/**
 * Convert GeoJSON format [lng, lat] to Leaflet format [lat, lng]
 * @param {Array<Array<number>>} coordinates - Array of [lng, lat] pairs
 * @returns {Array<Array<number>>} Array of [lat, lng] pairs
 */
export const toLeaflet = (coordinates) => {
  return coordinates.map(([lng, lat]) => [lat, lng])
}

/**
 * Validate latitude value
 * @param {number} lat - Latitude value
 * @returns {boolean} True if valid
 */
export const isValidLatitude = (lat) => {
  return typeof lat === 'number' && lat >= -90 && lat <= 90
}

/**
 * Validate longitude value
 * @param {number} lng - Longitude value
 * @returns {boolean} True if valid
 */
export const isValidLongitude = (lng) => {
  return typeof lng === 'number' && lng >= -180 && lng <= 180
}

/**
 * Validate single coordinate pair [lat, lng]
 * @param {Array<number>} coord - Coordinate pair [lat, lng]
 * @returns {boolean} True if valid
 */
export const isValidCoordinate = (coord) => {
  if (!Array.isArray(coord) || coord.length !== 2) return false
  const [lat, lng] = coord
  return isValidLatitude(lat) && isValidLongitude(lng)
}

/**
 * Validate track (minimum 2 points, all valid coordinates)
 * @param {Array<Array<number>>} coordinates - Array of coordinate pairs
 * @returns {Object} { isValid: boolean, errors: string[] }
 */
export const validateTrack = (coordinates) => {
  const errors = []

  if (!Array.isArray(coordinates)) {
    errors.push('Track coordinates must be an array')
    return { isValid: false, errors }
  }

  if (coordinates.length < 2) {
    errors.push('Trasa musi zawierać co najmniej 2 punkty')
  }

  if (coordinates.length > 10000) {
    errors.push('Trasa nie może zawierać więcej niż 10000 punktów')
  }

  coordinates.forEach((coord, index) => {
    if (!isValidCoordinate(coord)) {
      errors.push(`Nieprawidłowe współrzędne w punkcie ${index + 1}`)
    }
  })

  return {
    isValid: errors.length === 0,
    errors
  }
}

/**
 * Format coordinate to fixed precision
 * @param {number} value - Coordinate value
 * @param {number} precision - Number of decimal places (default 6)
 * @returns {number} Formatted value
 */
export const formatCoordinate = (value, precision = 6) => {
  return parseFloat(value.toFixed(precision))
}

/**
 * Normalize coordinates (round to 6 decimal places)
 * @param {Array<Array<number>>} coordinates - Array of coordinate pairs
 * @returns {Array<Array<number>>} Normalized coordinates
 */
export const normalizeCoordinates = (coordinates) => {
  return coordinates.map(([lat, lng]) => [
    formatCoordinate(lat),
    formatCoordinate(lng)
  ])
}

/**
 * Calculate distance between two points using Haversine formula
 * @param {number} lat1 - Latitude of first point
 * @param {number} lng1 - Longitude of first point
 * @param {number} lat2 - Latitude of second point
 * @param {number} lng2 - Longitude of second point
 * @returns {number} Distance in kilometers
 */
export const calculateDistance = (lat1, lng1, lat2, lng2) => {
  const R = 6371 // Earth radius in km

  const toRad = (degrees) => degrees * Math.PI / 180

  const dLat = toRad(lat2 - lat1)
  const dLng = toRad(lng2 - lng1)

  const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2)

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))

  return R * c
}

/**
 * Calculate total track length
 * @param {Array<Array<number>>} coordinates - Array of [lat, lng] pairs
 * @returns {number} Total length in kilometers
 */
export const calculateTrackLength = (coordinates) => {
  if (coordinates.length < 2) return 0

  let totalDistance = 0

  for (let i = 0; i < coordinates.length - 1; i++) {
    const [lat1, lng1] = coordinates[i]
    const [lat2, lng2] = coordinates[i + 1]
    totalDistance += calculateDistance(lat1, lng1, lat2, lng2)
  }

  return totalDistance
}
