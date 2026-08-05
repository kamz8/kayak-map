import { beforeEach, describe, expect, it, vi } from 'vitest'
import trailEditorModule, { ACTIONS, GETTERS, MUTATIONS } from '@dashboard/modules/trails/editor/store/trailEditor.js'
import apiClient from '@dashboard/plugins/axios.js'

vi.mock('@dashboard/plugins/axios.js', () => ({
  default: {
    post: vi.fn()
  }
}))

describe('Trail editor river route preview', () => {
  let state

  beforeEach(() => {
    vi.clearAllMocks()
    state = trailEditorModule.state()
    state.trailId = 42
    state.startPoint = [53.1, 18.1]
    state.endPoint = [53.2, 18.2]
  })

  it('generates route preview coordinates from backend path', async () => {
    apiClient.post.mockResolvedValue({
      data: {
        data: {
          path: [[18.1, 53.1], [18.2, 53.2]],
          distance_m: 123
        }
      }
    })

    const commit = (type, payload) => trailEditorModule.mutations[type](state, payload)

    const result = await trailEditorModule.actions[ACTIONS.GENERATE_RIVER_ROUTE]({ state, commit }, {
      snapToleranceMeters: 250
    })

    expect(apiClient.post).toHaveBeenCalledWith('/dashboard/trails/42/snap-river', {
        start: { lat: 53.1, lng: 18.1 },
        end: { lat: 53.2, lng: 18.2 },
        snap_tolerance_m: 250,
        mode: 'snap'
    })
    expect(result.distance_m).toBe(123)
    expect(trailEditorModule.getters[GETTERS.ROUTE_PREVIEW_COORDINATES](state)).toEqual([[53.1, 18.1], [53.2, 18.2]])
  })

  it('generates a complete auto route preview', async () => {
    apiClient.post.mockResolvedValue({
      data: {
        data: {
          path: [[18.1, 53.1], [18.2, 53.2]],
          distance_m: 123,
          routing: { engine: 'pgrouting', algorithm: 'astar' }
        }
      }
    })

    const commit = (type, payload) => trailEditorModule.mutations[type](state, payload)
    const result = await trailEditorModule.actions[ACTIONS.GENERATE_AUTO_RIVER_ROUTE]({ state, commit })

    expect(apiClient.post).toHaveBeenCalledWith('/dashboard/trails/42/auto-route', { mode: 'auto' })
    expect(result.routing.algorithm).toBe('astar')
    expect(trailEditorModule.getters[GETTERS.ROUTE_PREVIEW_COORDINATES](state)).toEqual([[53.1, 18.1], [53.2, 18.2]])
  })

  it('applies route preview to track and clears preview', async () => {
    trailEditorModule.mutations[MUTATIONS.SET_ROUTE_PREVIEW](state, [[53.1, 18.1], [53.2, 18.2]])

    const commit = (type, payload) => trailEditorModule.mutations[type](state, payload)

    await trailEditorModule.actions[ACTIONS.APPLY_ROUTE_PREVIEW]({ state, commit })

    expect(state.trackCoordinates).toEqual([[53.1, 18.1], [53.2, 18.2]])
    expect(state.routePreviewCoordinates).toEqual([])
    expect(state.unsavedChanges).toBe(true)
  })

})
