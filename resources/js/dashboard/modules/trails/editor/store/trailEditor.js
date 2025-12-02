/**
 * Vuex Store Module for Trail Map Editor
 * Manages track coordinates, start/end points, zoom level, and undo/redo history
 */

import axios from 'axios'
import { calculateTrackLength } from '../utils/coordinateUtils.js'

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
    poiPoints: []
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
    HAS_POI: 'hasPoi'
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
    RESET_UNSAVED_CHANGES: 'RESET_UNSAVED_CHANGES',
    SET_LOADING: 'SET_LOADING',
    SET_SAVING: 'SET_SAVING',
    UNDO: 'UNDO',
    REDO: 'REDO',
    RESET_HISTORY: 'RESET_HISTORY'
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
        const index = state.poiPoints.findIndex(p => p.id === poiId)
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
    }
}

// Action types as constants
export const ACTIONS = {
    LOAD_TRAIL: 'loadTrail',
    SAVE_TRACK: 'saveTrack',
    ZOOM_IN: 'zoomIn',
    ZOOM_OUT: 'zoomOut',
    SET_ZOOM: 'setZoom',
    CHANGE_LAYER: 'changeLayer',
    HANDLE_MAP_CLICK: 'handleMapClick',
    ADD_FEATURE: 'addFeature',
    CLEAR_ALL_FEATURES: 'clearAllFeatures',
    CLEAR_EDITOR: 'clearEditor'
}

const actions = {
    async [ACTIONS.LOAD_TRAIL]({ commit }, trailId) {
        commit(MUTATIONS.SET_LOADING, true)

        try {
            const response = await axios.get(`/api/v1/dashboard/trails/${trailId}`)
            const trail = response.data.data

            commit(MUTATIONS.SET_TRAIL_ID, trailId)

            if (trail.river_track?.track_points) {
                const coordinates = trail.river_track.track_points.map(point =>
                    Array.isArray(point)
                        ? [parseFloat(point[0]), parseFloat(point[1])]
                        : [parseFloat(point.lat), parseFloat(point.lng)]
                )

                commit(MUTATIONS.UPDATE_TRACK_COORDINATES, coordinates)

                if (coordinates.length > 0) {
                    commit(MUTATIONS.SET_START_POINT, coordinates[0])
                    commit(MUTATIONS.SET_END_POINT, coordinates[coordinates.length - 1])
                }
            }

            if (trail.start_lat && trail.start_lng) {
                commit(MUTATIONS.SET_CENTER_POINT, [
                    parseFloat(trail.start_lat),
                    parseFloat(trail.start_lng)
                ])
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

    async [ACTIONS.SAVE_TRACK]({ state, commit, getters }) {
        if (!getters[GETTERS.IS_VALID]) {
            throw new Error('Track must have at least 2 points')
        }

        commit(MUTATIONS.SET_SAVING, true)

        try {
            await new Promise(resolve => setTimeout(resolve, 1000))

            const payload = {
                trailId: state.trailId,
                track_points: state.trackCoordinates.map(([lat, lng]) => [lng, lat]),
                start_lat: state.startPoint[0],
                start_lng: state.startPoint[1],
                end_lat: state.endPoint[0],
                end_lng: state.endPoint[1],
                track_length: getters[GETTERS.TRACK_LENGTH],
                saved_at: new Date().toISOString()
            }

            localStorage.setItem(`trail_track_${state.trailId}`, JSON.stringify(payload))
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
        console.log('🗺️ Map click at:', event.latlng)
    },

    async [ACTIONS.ADD_FEATURE]({ commit }, feature) {
        console.log('➕ Feature added:', feature)
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
