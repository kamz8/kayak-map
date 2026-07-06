/**
 * Vuex Store Module for Trail Map Editor
 * Manages track coordinates, start/end points, zoom level, and undo/redo history
 */

import { calculateTrackLength } from '../utils/coordinateUtils.js'
import apiClient from "@dashboard/plugins/axios.js";

// Constants
export const MAP_LAYERS = {
    DEFAULT: 'default',
    TERRAIN: 'terrain',
    SATELLITE: 'satellite'
}

export const ZOOM_LIMITS = {
    MIN: 2,
    MAX: 18,
    DEFAULT: 12
}

const state = () => ({
    // Trail data
    trailId: null,
    trackCoordinates: [],
    startPoint: null,
    endPoint: null,

    // Map settings
    zoomLevel: ZOOM_LIMITS.DEFAULT,
    centerPoint: [52.0, 19.0],
    currentLayer: MAP_LAYERS.DEFAULT,

    // Editor state
    unsavedChanges: false,
    isLoading: false,
    isSaving: false,
    activeTool: null, // 'draw', 'edit', 'poi', 'select', null

    // Map action requests (flags)
    requestLocate: 0, // Increment to trigger locate
    requestZoomIn: 0, // Increment to trigger zoom in
    requestZoomOut: 0, // Increment to trigger zoom out

    // History for Undo/Redo
    history: [],
    historyIndex: -1,
    maxHistorySize: 20,

    // POI data
    poiPoints: [],
    availablePointTypes: [], // Fetched from API
    pointTypesLoaded: false,

    // POI editor mode
    poiEditMode: false,
    poiInEditMode: null // ID POI będącego w trybie edycji
})

// Getter names as constants for better maintenance
export const GETTERS = {
    IS_LOADING: 'isLoading',
    IS_SAVING: 'isSaving',
    UNSAVED_CHANGES: 'unsavedChanges',
    CURRENT_ZOOM: 'currentZoom',
    CURRENT_LAYER: 'currentLayer',
    ACTIVE_TOOL: 'activeTool',
    REQUEST_LOCATE: 'requestLocate',
    REQUEST_ZOOM_IN: 'requestZoomIn',
    REQUEST_ZOOM_OUT: 'requestZoomOut',
    TRACK_COORDINATES: 'trackCoordinates',
    START_POINT: 'startPoint',
    END_POINT: 'endPoint',
    CENTER_POINT: 'centerPoint',
    TRAIL_ID: 'trailId',
    HAS_TRACK: 'hasTrack',
    IS_VALID: 'isValid',
    CAN_UNDO: 'canUndo',
    CAN_REDO: 'canRedo',
    IS_MIN_ZOOM: 'isMinZoom',
    IS_MAX_ZOOM: 'isMaxZoom',
    TRACK_LENGTH: 'trackLength',
    POI_POINTS: 'poiPoints',
    POI_COUNT: 'poiCount',
    HAS_POI: 'hasPoi',
    AVAILABLE_POINT_TYPES: 'availablePointTypes',
    POINT_TYPES_LOADED: 'pointTypesLoaded',
    MAP_LAYERS: 'mapLayers',
    POI_EDIT_MODE: 'poiEditMode',
    POI_IN_EDIT_MODE: 'poiInEditMode'
}

const getters = {
    [GETTERS.IS_LOADING]: (state) => state.isLoading,
    [GETTERS.IS_SAVING]: (state) => state.isSaving,
    [GETTERS.UNSAVED_CHANGES]: (state) => state.unsavedChanges,
    [GETTERS.CURRENT_ZOOM]: (state) => state.zoomLevel,
    [GETTERS.CURRENT_LAYER]: (state) => state.currentLayer,
    [GETTERS.ACTIVE_TOOL]: (state) => state.activeTool,
    [GETTERS.REQUEST_LOCATE]: (state) => state.requestLocate,
    [GETTERS.REQUEST_ZOOM_IN]: (state) => state.requestZoomIn,
    [GETTERS.REQUEST_ZOOM_OUT]: (state) => state.requestZoomOut,
    [GETTERS.TRACK_COORDINATES]: (state) => state.trackCoordinates,
    [GETTERS.START_POINT]: (state) => state.startPoint,
    [GETTERS.END_POINT]: (state) => state.endPoint,
    [GETTERS.CENTER_POINT]: (state) => state.centerPoint,
    [GETTERS.TRAIL_ID]: (state) => state.trailId,
    [GETTERS.POI_POINTS]: (state) => state.poiPoints,
    [GETTERS.AVAILABLE_POINT_TYPES]: (state) => state.availablePointTypes,
    [GETTERS.POINT_TYPES_LOADED]: (state) => state.pointTypesLoaded,
    [GETTERS.POI_EDIT_MODE]: (state) => state.poiEditMode,
    [GETTERS.POI_IN_EDIT_MODE]: (state) => state.poiInEditMode,

    // Map layers configuration
    [GETTERS.MAP_LAYERS]: () => ({
        default: {
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        },
        terrain: {
            url: 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
            attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a>'
        },
        satellite: {
            url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        }
    }),

    [GETTERS.HAS_TRACK]: (state) => state.trackCoordinates.length > 0,
    [GETTERS.IS_VALID]: (state) => state.trackCoordinates.length >= 2,
    [GETTERS.CAN_UNDO]: (state) => state.historyIndex > 0,
    [GETTERS.CAN_REDO]: (state) => state.historyIndex < state.history.length - 1,
    [GETTERS.IS_MIN_ZOOM]: (state) => state.zoomLevel <= ZOOM_LIMITS.MIN,
    [GETTERS.IS_MAX_ZOOM]: (state) => state.zoomLevel >= ZOOM_LIMITS.MAX,
    [GETTERS.TRACK_LENGTH]: (state) => calculateTrackLength(state.trackCoordinates),
    [GETTERS.POI_COUNT]: (state) => state.poiPoints.length,
    [GETTERS.HAS_POI]: (state) => state.poiPoints.length > 0
}

// Mutation types as constants
export const MUTATIONS = {
    SET_TRAIL_ID: 'SET_TRAIL_ID',
    UPDATE_TRACK_COORDINATES: 'UPDATE_TRACK_COORDINATES',
    SET_START_POINT: 'SET_START_POINT',
    SET_END_POINT: 'SET_END_POINT',
    SET_ZOOM_LEVEL: 'SET_ZOOM_LEVEL',
    SET_CENTER_POINT: 'SET_CENTER_POINT',
    SET_CURRENT_LAYER: 'SET_CURRENT_LAYER',
    SET_ACTIVE_TOOL: 'SET_ACTIVE_TOOL',
    REQUEST_LOCATE: 'REQUEST_LOCATE',
    REQUEST_ZOOM_IN: 'REQUEST_ZOOM_IN',
    REQUEST_ZOOM_OUT: 'REQUEST_ZOOM_OUT',
    CLEAR_TRACK: 'CLEAR_TRACK',
    CLEAR_POI: 'CLEAR_POI',
    ADD_POI: 'ADD_POI',
    REMOVE_POI: 'REMOVE_POI',
    UPDATE_POI: 'UPDATE_POI',
    SET_POINT_TYPES: 'SET_POINT_TYPES',
    SET_POI_POINTS: 'SET_POI_POINTS',
    RESET_UNSAVED_CHANGES: 'RESET_UNSAVED_CHANGES',
    SET_LOADING: 'SET_LOADING',
    SET_SAVING: 'SET_SAVING',
    UNDO: 'UNDO',
    REDO: 'REDO',
    RESET_HISTORY: 'RESET_HISTORY',
    SET_POI_EDIT_MODE: 'SET_POI_EDIT_MODE',
    SET_POI_IN_EDIT_MODE: 'SET_POI_IN_EDIT_MODE'
}

const mutations = {
    [MUTATIONS.SET_TRAIL_ID](state, trailId) {
        state.trailId = trailId
    },

    [MUTATIONS.UPDATE_TRACK_COORDINATES](state, coordinates) {
        state.trackCoordinates = coordinates
        state.unsavedChanges = true

        if (state.historyIndex < state.history.length - 1) {
            state.history = state.history.slice(0, state.historyIndex + 1)
        }

        state.history.push({
            trackCoordinates: [...coordinates],
            startPoint: state.startPoint ? [...state.startPoint] : null,
            endPoint: state.endPoint ? [...state.endPoint] : null,
            poiPoints: [...state.poiPoints]
        })

        if (state.history.length > state.maxHistorySize) {
            state.history.shift()
        } else {
            state.historyIndex++
        }
    },

    [MUTATIONS.SET_START_POINT](state, point) {
        state.startPoint = point
    },

    [MUTATIONS.SET_END_POINT](state, point) {
        state.endPoint = point
    },

    [MUTATIONS.SET_ZOOM_LEVEL](state, zoom) {
        state.zoomLevel = Math.max(ZOOM_LIMITS.MIN, Math.min(ZOOM_LIMITS.MAX, zoom))
    },

    [MUTATIONS.SET_CENTER_POINT](state, center) {
        state.centerPoint = center
    },

    [MUTATIONS.SET_CURRENT_LAYER](state, layer) {
        state.currentLayer = layer
    },

    [MUTATIONS.SET_ACTIVE_TOOL](state, tool) {
        state.activeTool = tool
    },

    [MUTATIONS.REQUEST_LOCATE](state) {
        state.requestLocate++
    },

    [MUTATIONS.REQUEST_ZOOM_IN](state) {
        state.requestZoomIn++
    },

    [MUTATIONS.REQUEST_ZOOM_OUT](state) {
        state.requestZoomOut++
    },

    [MUTATIONS.CLEAR_TRACK](state) {
        state.trackCoordinates = []
        state.startPoint = null
        state.endPoint = null
        state.unsavedChanges = true
    },

    [MUTATIONS.CLEAR_POI](state) {
        state.poiPoints = []
        state.unsavedChanges = true
    },

    [MUTATIONS.ADD_POI](state, poi) {
        state.poiPoints.push(poi)
        state.unsavedChanges = true
    },

    [MUTATIONS.REMOVE_POI](state, poiId) {
        const index = state.poiPoints.findIndex(p => String(p.id) === String(poiId))
        if (index !== -1) {
            state.poiPoints.splice(index, 1)
            state.unsavedChanges = true
        }
    },

    [MUTATIONS.UPDATE_POI](state, updatedPoi) {
        const index = state.poiPoints.findIndex(p => p.id === updatedPoi.id)
        if (index !== -1) {
            state.poiPoints.splice(index, 1, updatedPoi)
            state.unsavedChanges = true
        }
    },

    [MUTATIONS.SET_POINT_TYPES](state, pointTypes) {
        state.availablePointTypes = pointTypes
        state.pointTypesLoaded = true
    },

    [MUTATIONS.SET_POI_POINTS](state, poiPoints) {
        state.poiPoints = poiPoints
    },

    [MUTATIONS.RESET_UNSAVED_CHANGES](state) {
        state.unsavedChanges = false
    },

    [MUTATIONS.SET_LOADING](state, loading) {
        state.isLoading = loading
    },

    [MUTATIONS.SET_SAVING](state, saving) {
        state.isSaving = saving
    },

    [MUTATIONS.UNDO](state) {
        if (state.historyIndex > 0) {
            state.historyIndex--
            const historyState = state.history[state.historyIndex]
            state.trackCoordinates = [...historyState.trackCoordinates]
            state.startPoint = historyState.startPoint ? [...historyState.startPoint] : null
            state.endPoint = historyState.endPoint ? [...historyState.endPoint] : null
            state.poiPoints = [...historyState.poiPoints]
            state.unsavedChanges = true
        }
    },

    [MUTATIONS.REDO](state) {
        if (state.historyIndex < state.history.length - 1) {
            state.historyIndex++
            const historyState = state.history[state.historyIndex]
            state.trackCoordinates = [...historyState.trackCoordinates]
            state.startPoint = historyState.startPoint ? [...historyState.startPoint] : null
            state.endPoint = historyState.endPoint ? [...historyState.endPoint] : null
            state.poiPoints = [...historyState.poiPoints]
            state.unsavedChanges = true
        }
    },

    [MUTATIONS.RESET_HISTORY](state) {
        state.history = []
        state.historyIndex = -1
    },

    [MUTATIONS.SET_POI_EDIT_MODE](state, isEditMode) {
        state.poiEditMode = isEditMode
    },

    [MUTATIONS.SET_POI_IN_EDIT_MODE](state, poiId) {
        state.poiInEditMode = poiId
    }
}

// ======== POI Edit Mode ========

// Action types as constants
export const ACTIONS = {
    LOAD_TRAIL: 'loadTrail',
    SAVE_TRACK: 'saveTrack',
    FETCH_POINT_TYPES: 'fetchPointTypes',
    ZOOM_IN: 'zoomIn',
    ZOOM_OUT: 'zoomOut',
    SET_ZOOM: 'setZoom',
    CHANGE_LAYER: 'changeLayer',
    HANDLE_MAP_CLICK: 'handleMapClick',
    ADD_FEATURE: 'addFeature',
    CLEAR_ALL_FEATURES: 'clearAllFeatures',
    CLEAR_EDITOR: 'clearEditor',
    TOGGLE_POI_EDIT_MODE: 'togglePoiEditMode',
    START_POI_EDIT: 'startPoiEdit',
    STOP_POI_EDIT: 'stopPoiEdit',
    UPDATE_POI_POSITION: 'updatePoiPosition',
    DELETE_POI: 'deletePoi'
}

const actions = {
    async [ACTIONS.LOAD_TRAIL]({ commit }, trailId) {
        commit(MUTATIONS.SET_LOADING, true)

        try {
            const response = await apiClient.get(`/dashboard/trails/${trailId}?with=riverTrack,points.pointType,points.images`)
            const trail = response.data.data

            commit(MUTATIONS.SET_TRAIL_ID, trailId)

            if (trail.river_track?.track_points) {
                // Parse track_points if it's a JSON string
                let trackPoints = trail.river_track.track_points

                if (typeof trackPoints === 'string') {
                    try {
                        trackPoints = JSON.parse(trackPoints)
                    } catch (e) {
                        console.error('❌ Failed to parse track_points JSON:', e)
                        trackPoints = null
                    }
                }

                // Handle GeoJSON format: { type: "LineString", coordinates: [[lng, lat], ...] }
                if (trackPoints && typeof trackPoints === 'object' && trackPoints.type === 'LineString') {
                    const coordinates = trackPoints.coordinates.map(([lng, lat]) => [
                        parseFloat(lat),
                        parseFloat(lng)
                    ])

                    commit(MUTATIONS.UPDATE_TRACK_COORDINATES, coordinates)
                }
                // Handle array format: [{lat, lng}, ...] or [[lat, lng], ...]
                else if (Array.isArray(trackPoints) && trackPoints.length > 0) {
                    const coordinates = trackPoints.map(point =>
                      Array.isArray(point)
                        ? [parseFloat(point[0]), parseFloat(point[1])]
                        : [parseFloat(point.lat), parseFloat(point.lng)]
                    )

                    commit(MUTATIONS.UPDATE_TRACK_COORDINATES, coordinates)
                } else {
                    console.warn('⚠️ track_points format not recognized:', trackPoints)
                }
            }

            // Set start point from database
            if (trail.start_lat && trail.start_lng) {
                const startPoint = [
                    parseFloat(trail.start_lat),
                    parseFloat(trail.start_lng)
                ]
                commit(MUTATIONS.SET_START_POINT, startPoint)
                commit(MUTATIONS.SET_CENTER_POINT, startPoint)
            }

            // Set end point from database
            if (trail.end_lat && trail.end_lng) {
                const endPoint = [
                    parseFloat(trail.end_lat),
                    parseFloat(trail.end_lng)
                ]
                commit(MUTATIONS.SET_END_POINT, endPoint)
            }

            // Load POI points if available
            if (trail.points && Array.isArray(trail.points)) {
                const poiPoints = trail.points.map(point => {
                    const mainImage = point.images?.find(img => img.pivot.is_main) || point.images?.[0] || null;
                    return {
                        id: point.id,
                        point_type_id: point.point_type_id,
                        point_type: point.point_type,
                        name: point.name,
                        description: point.description || '',
                        lat: parseFloat(point.lat),
                        lng: parseFloat(point.lng),
                        icon: point.icon || 'mdi-map-marker',
                        at_length: point.at_length || 0,
                        order: point.order || 0,
                        main_image: mainImage,
                        images: point.images || []
                    }
                })

                commit(MUTATIONS.SET_POI_POINTS, poiPoints)
            } else {
                // Clear POI if no points in response
                commit(MUTATIONS.SET_POI_POINTS, [])
            }

            commit(MUTATIONS.RESET_UNSAVED_CHANGES)
            commit(MUTATIONS.RESET_HISTORY)

        } catch (error) {
            console.error('Failed to load trail:', error)
            throw error
        } finally {
            commit(MUTATIONS.SET_LOADING, false)
        }
    },

    async [ACTIONS.FETCH_POINT_TYPES]({ commit, state }) {
        // Skip if already loaded
        if (state.pointTypesLoaded) {
            return state.availablePointTypes
        }

        try {
            const response = await apiClient.get('/dashboard/points/types')
            const pointTypes = response.data.data

            commit(MUTATIONS.SET_POINT_TYPES, pointTypes)

            return pointTypes
        } catch (error) {
            console.error('❌ Failed to fetch point types:', error)
            throw error
        }
    },

    async [ACTIONS.SAVE_TRACK]({ state, commit, getters }) {
        if (!getters[GETTERS.IS_VALID]) {
            throw new Error('Track must have at least 2 points')
        }

        commit(MUTATIONS.SET_SAVING, true)

        try {
            const payload = {
                track_points: state.trackCoordinates.map(([lat, lng]) => [lng, lat]),
                start_lat: state.startPoint[0],
                start_lng: state.startPoint[1],
                end_lat: state.endPoint[0],
                end_lng: state.endPoint[1],
                trail_length: Math.round(getters[GETTERS.TRACK_LENGTH] * 1000),
                poi_points: state.poiPoints.map(poi => ({
                    id: poi.id,
                    point_type_id: poi.point_type_id,
                    name: poi.name,
                    description: poi.description,
                    lat: poi.lat,
                    lng: poi.lng,
                    icon: poi.icon,
                    at_length: poi.at_length,
                    order: poi.order
                })),
                saved_at: new Date().toISOString()
            }

            localStorage.setItem(`trail_track_${state.trailId}`, JSON.stringify({
                trailId: state.trailId,
                ...payload
            }))

            await apiClient.put(`/dashboard/trails/${state.trailId}`, {
                track_points: payload.track_points,
                start_lat: payload.start_lat,
                start_lng: payload.start_lng,
                end_lat: payload.end_lat,
                end_lng: payload.end_lng,
                trail_length: payload.trail_length
            })

            commit(MUTATIONS.RESET_UNSAVED_CHANGES)

            return true

        } catch (error) {
            console.error('❌ Failed to save track:', error)
            throw error
        } finally {
            commit(MUTATIONS.SET_SAVING, false)
        }
    },

    async [ACTIONS.ZOOM_IN]({ commit, state, getters }) {
        if (!getters[GETTERS.IS_MAX_ZOOM]) {
            const newZoom = state.zoomLevel + 1
            commit(MUTATIONS.SET_ZOOM_LEVEL, newZoom)
            return newZoom
        }
        return state.zoomLevel
    },

    async [ACTIONS.ZOOM_OUT]({ commit, state, getters }) {
        if (!getters[GETTERS.IS_MIN_ZOOM]) {
            const newZoom = state.zoomLevel - 1
            commit(MUTATIONS.SET_ZOOM_LEVEL, newZoom)
            return newZoom
        }
        return state.zoomLevel
    },

    async [ACTIONS.SET_ZOOM]({ commit }, zoomLevel) {
        commit(MUTATIONS.SET_ZOOM_LEVEL, zoomLevel)
        return zoomLevel
    },

    async [ACTIONS.CHANGE_LAYER]({ commit }, layerType) {
        commit(MUTATIONS.SET_CURRENT_LAYER, layerType)
        return layerType
    },

    async [ACTIONS.HANDLE_MAP_CLICK]({ commit }, event) {
        // Handle map click event
    },

    async [ACTIONS.ADD_FEATURE]({ commit }, feature) {
        // Handle feature addition
    },

    async [ACTIONS.CLEAR_ALL_FEATURES]({ commit }) {
        commit(MUTATIONS.CLEAR_TRACK)
        commit(MUTATIONS.CLEAR_POI)
    },

    async [ACTIONS.CLEAR_EDITOR]({ commit }) {
        commit(MUTATIONS.CLEAR_TRACK)
        commit(MUTATIONS.CLEAR_POI)
        commit(MUTATIONS.RESET_HISTORY)
        commit(MUTATIONS.SET_ZOOM_LEVEL, ZOOM_LIMITS.DEFAULT)
        commit(MUTATIONS.SET_CURRENT_LAYER, MAP_LAYERS.DEFAULT)
    },

    async [ACTIONS.TOGGLE_POI_EDIT_MODE]({ commit, state }) {
        commit(MUTATIONS.SET_POI_EDIT_MODE, !state.poiEditMode)
    },

    async [ACTIONS.START_POI_EDIT]({ commit }, poiId) {
        commit(MUTATIONS.SET_POI_EDIT_MODE, true)
        commit(MUTATIONS.SET_POI_IN_EDIT_MODE, poiId)
    },

    async [ACTIONS.STOP_POI_EDIT]({ commit }) {
        commit(MUTATIONS.SET_POI_EDIT_MODE, false)
        commit(MUTATIONS.SET_POI_IN_EDIT_MODE, null)
    },

    async [ACTIONS.UPDATE_POI_POSITION]({ commit, state }, { poiId, lat, lng }) {
        const updatedPoi = {
            ...state.poiPoints.find(p => p.id === poiId),
            lat,
            lng
        }
        commit(MUTATIONS.UPDATE_POI, updatedPoi)
    },

    async [ACTIONS.DELETE_POI]({ commit }, poiId) {
        commit(MUTATIONS.REMOVE_POI, poiId)
    }
}

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
}

// Export helpers for components
export const trailEditorGetters = GETTERS
export const trailEditorMutations = MUTATIONS
export const trailEditorActions = ACTIONS
