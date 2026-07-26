import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import UiBreadcrumb from '@ui/UiBreadcrumb.vue'
import { createRouter, createMemoryHistory } from 'vue-router'

describe('UiBreadcrumb Component', () => {
  let router

  beforeEach(() => {
    // Create router for testing
    router = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: '/dashboard',
          meta: {
            breadcrumbs: [
              { text: 'Dashboard', to: '/dashboard' }
            ]
          }
        },
        {
          path: '/dashboard/trails',
          meta: {
            breadcrumbs: [
              { text: 'Dashboard', to: '/dashboard' },
              { text: 'Szlaki' }
            ]
          }
        }
      ]
    })
  })

  describe('Rendering', () => {
    it('should render breadcrumbs from items prop', () => {
      const items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { text: 'Linki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      expect(wrapper.find('.ui-breadcrumb').exists()).toBe(true)
      expect(wrapper.findAll('.ui-breadcrumb-item')).toHaveLength(3)
    })

    it('should render breadcrumb text correctly', () => {
      const items = [
        { text: 'Dashboard' },
        { text: 'Szlaki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      const breadcrumbItems = wrapper.findAll('.ui-breadcrumb-item')
      expect(breadcrumbItems[0].text()).toContain('Dashboard')
      expect(breadcrumbItems[1].text()).toContain('Szlaki')
    })

    it('should render clickable links for items with "to" property', () => {
      const items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { text: 'Linki' } // Last item - not clickable
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      const links = wrapper.findAll('a.ui-breadcrumb-link')
      expect(links).toHaveLength(2) // Only first two are links

      const spans = wrapper.findAll('span.ui-breadcrumb-text')
      expect(spans).toHaveLength(1) // Last one is span
    })

    it('should hide items with empty text using v-show', () => {
      const items = [
        { text: 'Dashboard', to: '/dashboard' },
        { key: 'trail', text: '', to: '' }, // Empty placeholder
        { text: 'Linki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      const breadcrumbItems = wrapper.findAll('.ui-breadcrumb-item')
      expect(breadcrumbItems).toHaveLength(3)

      // Check v-show attribute (hidden item still in DOM)
      const hiddenItem = breadcrumbItems[1]
      expect(hiddenItem.attributes('style')).toContain('display: none')
    })
  })

  describe('Muted Styling', () => {
    it('should apply muted class to items with muted property', () => {
      const items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Trail Name', to: '/trail/123', muted: true },
        { text: 'Linki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      const mutedLink = wrapper.find('a.ui-breadcrumb-link--muted')
      expect(mutedLink.exists()).toBe(true)
      expect(mutedLink.text()).toBe('Trail Name')
    })

    it('should apply muted class to non-clickable items', () => {
      const items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Muted Text', muted: true }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      const mutedText = wrapper.find('span.ui-breadcrumb-text--muted')
      expect(mutedText.exists()).toBe(true)
    })
  })

  describe('Separators', () => {
    it('should render separators between items', () => {
      const items = [
        { text: 'Dashboard' },
        { text: 'Szlaki' },
        { text: 'Linki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      const separators = wrapper.findAll('.ui-breadcrumb-separator')
      expect(separators).toHaveLength(2) // n-1 separators
    })

    it('should not render separator after last item', () => {
      const items = [
        { text: 'Dashboard' },
        { text: 'Szlaki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      const lastItem = wrapper.findAll('.ui-breadcrumb-item')[1]
      expect(lastItem.find('.ui-breadcrumb-separator').exists()).toBe(false)
    })

    it('should use custom separator icon', () => {
      const items = [
        { text: 'Dashboard' },
        { text: 'Szlaki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: {
          items,
          separatorIcon: 'mdi-arrow-right'
        },
        global: { plugins: [router] }
      })

      const separator = wrapper.find('.ui-breadcrumb-separator')
      expect(separator.exists()).toBe(true)
    })
  })

  describe('Home Icon', () => {
    it('should show home icon when showHome is true', () => {
      const items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: {
          items,
          showHome: true
        },
        global: { plugins: [router] }
      })

      const homeIcon = wrapper.find('.ui-breadcrumb-home-icon')
      expect(homeIcon.exists()).toBe(true)
    })

    it('should not show home icon when showHome is false', () => {
      const items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: {
          items,
          showHome: false
        },
        global: { plugins: [router] }
      })

      const homeIcon = wrapper.find('.ui-breadcrumb-home-icon')
      expect(homeIcon.exists()).toBe(false)
    })

    it('should use custom home icon', () => {
      const items = [{ text: 'Dashboard' }]

      const wrapper = mount(UiBreadcrumb, {
        props: {
          items,
          showHome: true,
          homeIcon: 'mdi-home-variant'
        },
        global: { plugins: [router] }
      })

      const homeIcon = wrapper.find('.ui-breadcrumb-home-icon')
      expect(homeIcon.exists()).toBe(true)
    })
  })

  describe('Variants and Sizes', () => {
    it('should apply default variant class', () => {
      const items = [{ text: 'Dashboard' }]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      expect(wrapper.find('.ui-breadcrumb--default').exists()).toBe(true)
    })

    it('should apply subtle variant class', () => {
      const items = [{ text: 'Dashboard' }]

      const wrapper = mount(UiBreadcrumb, {
        props: {
          items,
          variant: 'subtle'
        },
        global: { plugins: [router] }
      })

      expect(wrapper.find('.ui-breadcrumb--subtle').exists()).toBe(true)
    })

    it('should apply size classes', () => {
      const items = [{ text: 'Dashboard' }]

      const wrapperSm = mount(UiBreadcrumb, {
        props: { items, size: 'sm' },
        global: { plugins: [router] }
      })
      expect(wrapperSm.find('.ui-breadcrumb--sm').exists()).toBe(true)

      const wrapperLg = mount(UiBreadcrumb, {
        props: { items, size: 'lg' },
        global: { plugins: [router] }
      })
      expect(wrapperLg.find('.ui-breadcrumb--lg').exists()).toBe(true)
    })
  })

  describe('Fallback to route.meta.breadcrumbs', () => {
    it('should use route.meta.breadcrumbs when items prop not provided', async () => {
      await router.push('/dashboard/trails')
      await router.isReady()

      const wrapper = mount(UiBreadcrumb, {
        props: {},
        global: {
          plugins: [router]
        }
      })

      // Wait for Vue reactivity to update
      await nextTick()

      // Should render breadcrumbs from route meta
      const breadcrumbItems = wrapper.findAll('.ui-breadcrumb-item')
      expect(breadcrumbItems.length).toBeGreaterThan(0)
    })

    it('should prefer items prop over route.meta.breadcrumbs', async () => {
      await router.push('/dashboard/trails')

      const customItems = [
        { text: 'Custom' },
        { text: 'Breadcrumbs' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items: customItems },
        global: {
          plugins: [router]
        }
      })

      expect(wrapper.text()).toContain('Custom')
      expect(wrapper.text()).toContain('Breadcrumbs')
    })
  })

  describe('Dynamic Updates Integration', () => {
    it('should render dynamic breadcrumb updates', () => {
      const items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { key: 'trail', text: 'Wda - Pomorze', to: '/trail/123', muted: true },
        { text: 'Linki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      expect(wrapper.text()).toContain('Wda - Pomorze')

      const mutedLink = wrapper.find('a.ui-breadcrumb-link--muted')
      expect(mutedLink.exists()).toBe(true)
      expect(mutedLink.text()).toBe('Wda - Pomorze')
    })

    it('should handle placeholder that gets updated', async () => {
      let items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { key: 'trail', text: '', to: '', muted: true }, // Empty placeholder
        { text: 'Linki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      // Initially hidden
      let visibleItems = wrapper.findAll('.ui-breadcrumb-item:not([style*="display: none"])')
      expect(visibleItems).toHaveLength(3) // Dashboard, Szlaki, Linki

      // Update with trail name
      items = [
        { text: 'Dashboard', to: '/dashboard' },
        { text: 'Szlaki', to: '/dashboard/trails' },
        { key: 'trail', text: 'Trail Name', to: '/trail/1', muted: true },
        { text: 'Linki' }
      ]

      await wrapper.setProps({ items })

      // Now visible
      visibleItems = wrapper.findAll('.ui-breadcrumb-item:not([style*="display: none"])')
      expect(visibleItems).toHaveLength(4)
      expect(wrapper.text()).toContain('Trail Name')
    })
  })

  describe('Edge Cases', () => {
    it('should handle empty items array', () => {
      const wrapper = mount(UiBreadcrumb, {
        props: { items: [] },
        global: { plugins: [router] }
      })

      expect(wrapper.find('.ui-breadcrumb').exists()).toBe(true)
      expect(wrapper.findAll('.ui-breadcrumb-item')).toHaveLength(0)
    })

    it('should handle single breadcrumb item', () => {
      const items = [{ text: 'Dashboard' }]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      expect(wrapper.findAll('.ui-breadcrumb-item')).toHaveLength(1)
      expect(wrapper.findAll('.ui-breadcrumb-separator')).toHaveLength(0)
    })

    it('should handle items without keys', () => {
      const items = [
        { text: 'Dashboard' },
        { text: 'Szlaki' }
      ]

      const wrapper = mount(UiBreadcrumb, {
        props: { items },
        global: { plugins: [router] }
      })

      expect(wrapper.findAll('.ui-breadcrumb-item')).toHaveLength(2)
    })
  })
})
