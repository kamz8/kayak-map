import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useBreadcrumbs } from '@dashboard/composables/useBreadcrumbs'
import { defineComponent, getCurrentInstance } from 'vue'
import { mount } from '@vue/test-utils'
import { createStore } from 'vuex'

describe('useBreadcrumbs Composable', () => {
  let store
  let wrapper
  let breadcrumbs

  // Test component that uses the composable
  const TestComponent = defineComponent({
    template: '<div>Test</div>',
    setup() {
      breadcrumbs = useBreadcrumbs()
      return { breadcrumbs }
    }
  })

  beforeEach(() => {
    // Create fresh Vuex store with breadcrumbs module
    store = createStore({
      modules: {
        breadcrumbs: {
          namespaced: true,
          state: () => ({
            updates: {}
          }),
          getters: {
            updates: state => state.updates,
            hasUpdates: state => Object.keys(state.updates).length > 0
          },
          mutations: {
            UPDATE_BREADCRUMB_BY_KEY(state, { key, updates }) {
              state.updates = {
                ...state.updates,
                [key]: updates
              }
            },
            CLEAR_KEY(state, key) {
              const newUpdates = { ...state.updates }
              delete newUpdates[key]
              state.updates = newUpdates
            },
            CLEAR_UPDATES(state) {
              state.updates = {}
            }
          },
          actions: {
            updateBreadcrumbByKey: vi.fn(({ commit }, payload) => {
              commit('UPDATE_BREADCRUMB_BY_KEY', payload)
            }),
            clearKey: vi.fn(({ commit }, key) => {
              commit('CLEAR_KEY', key)
            }),
            clearUpdates: vi.fn(({ commit }) => {
              commit('CLEAR_UPDATES')
            })
          }
        }
      }
    })

    // Mount component with store
    wrapper = mount(TestComponent, {
      global: {
        plugins: [store]
      }
    })
  })

  describe('Composable Availability', () => {
    it('should return composable functions', () => {
      expect(breadcrumbs).toBeDefined()
      expect(breadcrumbs.updateBreadcrumbByKey).toBeTypeOf('function')
      expect(breadcrumbs.clearKey).toBeTypeOf('function')
      expect(breadcrumbs.clearUpdates).toBeTypeOf('function')
    })

    it('should return fallback functions when store not available', () => {
      // Mount without store
      const ComponentWithoutStore = defineComponent({
        template: '<div>Test</div>',
        setup() {
          return { breadcrumbs: useBreadcrumbs() }
        }
      })

      const wrapperNoStore = mount(ComponentWithoutStore)
      const breadcrumbsNoStore = wrapperNoStore.vm.breadcrumbs

      expect(breadcrumbsNoStore.updateBreadcrumbByKey).toBeTypeOf('function')
      expect(breadcrumbsNoStore.clearKey).toBeTypeOf('function')
      expect(breadcrumbsNoStore.clearUpdates).toBeTypeOf('function')

      // Should not throw errors
      expect(() => breadcrumbsNoStore.updateBreadcrumbByKey('test', {})).not.toThrow()
    })
  })

  describe('updateBreadcrumbByKey', () => {
    it('should dispatch updateBreadcrumbByKey action', () => {
      const spy = vi.spyOn(store, 'dispatch')

      breadcrumbs.updateBreadcrumbByKey('trail', {
        text: 'Trail Name',
        to: '/trail/123'
      })

      expect(spy).toHaveBeenCalledWith('breadcrumbs/updateBreadcrumbByKey', {
        key: 'trail',
        updates: {
          text: 'Trail Name',
          to: '/trail/123'
        }
      })
    })

    it('should update store state', () => {
      breadcrumbs.updateBreadcrumbByKey('trail', {
        text: 'Test Trail',
        to: '/test'
      })

      const storeUpdates = store.getters['breadcrumbs/updates']
      expect(storeUpdates).toEqual({
        trail: {
          text: 'Test Trail',
          to: '/test'
        }
      })
    })

    it('should handle multiple updates', () => {
      breadcrumbs.updateBreadcrumbByKey('trail', { text: 'Trail' })
      breadcrumbs.updateBreadcrumbByKey('section', { text: 'Section' })

      const storeUpdates = store.getters['breadcrumbs/updates']
      expect(storeUpdates).toEqual({
        trail: { text: 'Trail' },
        section: { text: 'Section' }
      })
    })

    it('should handle muted property', () => {
      breadcrumbs.updateBreadcrumbByKey('trail', {
        text: 'Trail Name',
        to: '/trail/123',
        muted: true
      })

      const storeUpdates = store.getters['breadcrumbs/updates']
      expect(storeUpdates.trail.muted).toBe(true)
    })
  })

  describe('clearKey', () => {
    it('should dispatch clearKey action', () => {
      const spy = vi.spyOn(store, 'dispatch')

      breadcrumbs.clearKey('trail')

      expect(spy).toHaveBeenCalledWith('breadcrumbs/clearKey', 'trail')
    })

    it('should remove key from store', () => {
      // Add some updates
      breadcrumbs.updateBreadcrumbByKey('trail', { text: 'Trail' })
      breadcrumbs.updateBreadcrumbByKey('section', { text: 'Section' })

      // Clear one key
      breadcrumbs.clearKey('trail')

      const storeUpdates = store.getters['breadcrumbs/updates']
      expect(storeUpdates).toEqual({
        section: { text: 'Section' }
      })
    })
  })

  describe('clearUpdates', () => {
    it('should dispatch clearUpdates action', () => {
      const spy = vi.spyOn(store, 'dispatch')

      breadcrumbs.clearUpdates()

      expect(spy).toHaveBeenCalledWith('breadcrumbs/clearUpdates')
    })

    it('should clear all updates from store', () => {
      // Add some updates
      breadcrumbs.updateBreadcrumbByKey('trail', { text: 'Trail' })
      breadcrumbs.updateBreadcrumbByKey('section', { text: 'Section' })

      expect(store.getters['breadcrumbs/hasUpdates']).toBe(true)

      // Clear all
      breadcrumbs.clearUpdates()

      expect(store.getters['breadcrumbs/hasUpdates']).toBe(false)
      expect(store.getters['breadcrumbs/updates']).toEqual({})
    })
  })

  describe('Integration Scenarios', () => {
    it('should handle trail links page scenario', () => {
      // Initial: empty updates
      expect(store.getters['breadcrumbs/hasUpdates']).toBe(false)

      // Component fetches trail data and updates breadcrumb
      breadcrumbs.updateBreadcrumbByKey('trail', {
        text: 'Wda - Pomorze',
        to: '/dashboard/trails/123/edit'
      })

      const updates = store.getters['breadcrumbs/updates']
      expect(updates.trail).toEqual({
        text: 'Wda - Pomorze',
        to: '/dashboard/trails/123/edit'
      })

      // Navigate away - clear updates
      breadcrumbs.clearUpdates()
      expect(store.getters['breadcrumbs/hasUpdates']).toBe(false)
    })

    it('should handle section links page scenario', () => {
      // Update both trail and section
      breadcrumbs.updateBreadcrumbByKey('trail', {
        text: 'Wda - Pomorze',
        to: '/dashboard/trails/123/edit',
        muted: true
      })

      breadcrumbs.updateBreadcrumbByKey('section', {
        text: 'Sekcja A',
        muted: true
      })

      const updates = store.getters['breadcrumbs/updates']
      expect(updates).toEqual({
        trail: {
          text: 'Wda - Pomorze',
          to: '/dashboard/trails/123/edit',
          muted: true
        },
        section: {
          text: 'Sekcja A',
          muted: true
        }
      })
    })
  })
})
