/**
 * Vitest setup file
 * Runs before all tests
 */

import { config } from '@vue/test-utils'

// Mock Vue Router
config.global.mocks = {
  $route: {
    path: '/dashboard',
    meta: {}
  },
  $router: {
    push: vi.fn(),
    replace: vi.fn(),
    back: vi.fn()
  }
}

// Mock window.matchMedia for Vuetify
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
})
