import { describe, it, expect, beforeEach } from 'vitest'
import breadcrumbsModule from '@dashboard/store/modules/breadcrumbs'

describe('Breadcrumbs Vuex Module', () => {
  let state

  beforeEach(() => {
    // Fresh state for each test
    state = breadcrumbsModule.state()
  })

  describe('State', () => {
    it('should initialize with empty updates object', () => {
      expect(state.updates).toEqual({})
    })
  })

  describe('Getters', () => {
    describe('updates', () => {
      it('should return updates object', () => {
        state.updates = { trail: { text: 'Test Trail' } }
        const result = breadcrumbsModule.getters.updates(state)
        expect(result).toEqual({ trail: { text: 'Test Trail' } })
      })

      it('should return empty object when no updates', () => {
        const result = breadcrumbsModule.getters.updates(state)
        expect(result).toEqual({})
      })
    })

    describe('hasUpdates', () => {
      it('should return true when updates exist', () => {
        state.updates = { trail: { text: 'Test' } }
        const result = breadcrumbsModule.getters.hasUpdates(state)
        expect(result).toBe(true)
      })

      it('should return false when no updates', () => {
        const result = breadcrumbsModule.getters.hasUpdates(state)
        expect(result).toBe(false)
      })
    })
  })

  describe('Mutations', () => {
    describe('UPDATE_BREADCRUMB_BY_KEY', () => {
      it('should add new update for a key', () => {
        breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
          key: 'trail',
          updates: { text: 'Trail Name', to: '/trail/123' }
        })

        expect(state.updates).toEqual({
          trail: { text: 'Trail Name', to: '/trail/123' }
        })
      })

      it('should update existing key', () => {
        state.updates = { trail: { text: 'Old Name' } }

        breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
          key: 'trail',
          updates: { text: 'New Name', to: '/new' }
        })

        expect(state.updates.trail).toEqual({
          text: 'New Name',
          to: '/new'
        })
      })

      it('should merge updates for a key', () => {
        state.updates = { trail: { text: 'Trail', to: '/old' } }

        breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
          key: 'trail',
          updates: { text: 'Updated Trail' }
        })

        expect(state.updates.trail).toEqual({
          text: 'Updated Trail',
          to: '/old'
        })
      })

      it('should handle multiple keys', () => {
        breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
          key: 'trail',
          updates: { text: 'Trail' }
        })

        breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
          key: 'section',
          updates: { text: 'Section' }
        })

        expect(state.updates).toEqual({
          trail: { text: 'Trail' },
          section: { text: 'Section' }
        })
      })
    })

    describe('CLEAR_KEY', () => {
      it('should remove specific key from updates', () => {
        state.updates = {
          trail: { text: 'Trail' },
          section: { text: 'Section' }
        }

        breadcrumbsModule.mutations.CLEAR_KEY(state, 'trail')

        expect(state.updates).toEqual({
          section: { text: 'Section' }
        })
      })

      it('should handle clearing non-existent key', () => {
        state.updates = { trail: { text: 'Trail' } }

        breadcrumbsModule.mutations.CLEAR_KEY(state, 'nonexistent')

        expect(state.updates).toEqual({
          trail: { text: 'Trail' }
        })
      })

      it('should result in empty object when clearing last key', () => {
        state.updates = { trail: { text: 'Trail' } }

        breadcrumbsModule.mutations.CLEAR_KEY(state, 'trail')

        expect(state.updates).toEqual({})
      })
    })

    describe('CLEAR_UPDATES', () => {
      it('should clear all updates', () => {
        state.updates = {
          trail: { text: 'Trail' },
          section: { text: 'Section' }
        }

        breadcrumbsModule.mutations.CLEAR_UPDATES(state)

        expect(state.updates).toEqual({})
      })

      it('should handle clearing already empty updates', () => {
        breadcrumbsModule.mutations.CLEAR_UPDATES(state)

        expect(state.updates).toEqual({})
      })
    })
  })

  describe('Actions', () => {
    describe('updateBreadcrumbByKey', () => {
      it('should commit UPDATE_BREADCRUMB_BY_KEY mutation', () => {
        const commit = vi.fn()
        const payload = {
          key: 'trail',
          updates: { text: 'Trail Name' }
        }

        breadcrumbsModule.actions.updateBreadcrumbByKey({ commit }, payload)

        expect(commit).toHaveBeenCalledWith('UPDATE_BREADCRUMB_BY_KEY', payload)
      })
    })

    describe('clearKey', () => {
      it('should commit CLEAR_KEY mutation', () => {
        const commit = vi.fn()
        const key = 'trail'

        breadcrumbsModule.actions.clearKey({ commit }, key)

        expect(commit).toHaveBeenCalledWith('CLEAR_KEY', key)
      })
    })

    describe('clearUpdates', () => {
      it('should commit CLEAR_UPDATES mutation', () => {
        const commit = vi.fn()

        breadcrumbsModule.actions.clearUpdates({ commit })

        expect(commit).toHaveBeenCalledWith('CLEAR_UPDATES')
      })
    })
  })

  describe('Integration', () => {
    it('should handle complete update cycle', () => {
      const state = breadcrumbsModule.state()

      // Add update
      breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
        key: 'trail',
        updates: { text: 'Trail 1' }
      })
      expect(breadcrumbsModule.getters.hasUpdates(state)).toBe(true)

      // Update same key
      breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
        key: 'trail',
        updates: { text: 'Trail 1 Updated', to: '/trail/1' }
      })
      expect(state.updates.trail.text).toBe('Trail 1 Updated')

      // Add another key
      breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
        key: 'section',
        updates: { text: 'Section A' }
      })
      expect(Object.keys(state.updates)).toHaveLength(2)

      // Clear one key
      breadcrumbsModule.mutations.CLEAR_KEY(state, 'section')
      expect(Object.keys(state.updates)).toHaveLength(1)

      // Clear all
      breadcrumbsModule.mutations.CLEAR_UPDATES(state)
      expect(breadcrumbsModule.getters.hasUpdates(state)).toBe(false)
    })
  })
})
