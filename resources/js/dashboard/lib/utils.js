/**
 * Utility functions for UI Kit
 * Simplified version without external dependencies
 */

import { getVariantProps } from '@/dashboard/design-system/tokens'

/**
 * Merge class names (simplified clsx + twMerge equivalent)
 * @param  {...any} classes
 * @returns {string}
 */
export function cn(...classes) {
  return classes
    .filter(Boolean)
    .join(' ')
    .replace(/\s+/g, ' ')
    .trim()
}

/**
 * Get Vuetify props from design tokens
 * @param {string} component - Component name (button, input, etc.)
 * @param {string} variant - Variant name
 * @param {string} size - Size name
 * @returns {Object}
 */
export function getVuetifyProps(component, variant, size = 'default') {
  return getVariantProps(component, variant, size)
}

/**
 * Create component classes with BEM-like naming
 * @param {string} component - Component name
 * @param {string} variant - Variant name  
 * @param {string} size - Size name
 * @param {string} customClass - Additional classes
 * @returns {string}
 */
export function createComponentClasses(component, variant, size, customClass = '') {
  return cn(
    `ui-${component}`,
    variant && `ui-${component}--${variant}`,
    size && size !== 'default' && `ui-${component}--${size}`,
    customClass
  )
}

/**
 * Validate component variant
 * @param {string} variant - Variant to validate
 * @param {Array} allowedVariants - Array of allowed variants
 * @param {string} defaultVariant - Default variant if invalid
 * @returns {string}
 */
export function validateVariant(variant, allowedVariants, defaultVariant = 'default') {
  return allowedVariants.includes(variant) ? variant : defaultVariant
}

/**
 * Validate component size
 * @param {string} size - Size to validate
 * @param {Array} allowedSizes - Array of allowed sizes
 * @param {string} defaultSize - Default size if invalid
 * @returns {string}
 */
export function validateSize(size, allowedSizes, defaultSize = 'default') {
  return allowedSizes.includes(size) ? size : defaultSize
}

/**
 * Deep merge objects (for theme configuration)
 * @param {Object} target 
 * @param {Object} source 
 * @returns {Object}
 */
export function deepMerge(target, source) {
  const result = { ...target }
  
  for (const key in source) {
    if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
      result[key] = deepMerge(result[key] || {}, source[key])
    } else {
      result[key] = source[key]
    }
  }
  
  return result
}

/**
 * Convert hex color to RGB
 * @param {string} hex - Hex color code
 * @returns {string}
 */
export function hexToRgb(hex) {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
  return result ? 
    `${parseInt(result[1], 16)} ${parseInt(result[2], 16)} ${parseInt(result[3], 16)}` : 
    null
}

/**
 * Generate CSS custom properties from design tokens
 * @param {Object} tokens - Design tokens object
 * @param {string} prefix - CSS variable prefix
 * @returns {Object}
 */
export function generateCSSVars(tokens, prefix = '--ui') {
  const vars = {}
  
  function processTokens(obj, path = '') {
    for (const [key, value] of Object.entries(obj)) {
      const varName = `${prefix}${path}-${key}`.replace(/[A-Z]/g, letter => `-${letter.toLowerCase()}`)
      
      if (typeof value === 'object' && value !== null) {
        processTokens(value, `${path}-${key}`)
      } else {
        vars[varName] = value
      }
    }
  }
  
  processTokens(tokens)
  return vars
}

/**
 * Debounce function
 * @param {Function} func 
 * @param {number} wait 
 * @param {boolean} immediate 
 * @returns {Function}
 */
export function debounce(func, wait, immediate = false) {
  let timeout
  
  return function executedFunction(...args) {
    const later = () => {
      timeout = null
      if (!immediate) func(...args)
    }
    
    const callNow = immediate && !timeout
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
    
    if (callNow) func(...args)
  }
}

/**
 * Check if element is in viewport
 * @param {HTMLElement} element 
 * @returns {boolean}
 */
export function isInViewport(element) {
  const rect = element.getBoundingClientRect()
  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  )
}

/**
 * Format number with locale
 * @param {number} value 
 * @param {string} locale 
 * @returns {string}
 */
export function formatNumber(value, locale = 'pl-PL') {
  if (typeof value !== 'number') return '0'
  return new Intl.NumberFormat(locale).format(value)
}

/**
 * Generate unique ID
 * @param {string} prefix 
 * @returns {string}
 */
export function generateId(prefix = 'ui') {
  return `${prefix}-${Math.random().toString(36).substring(2, 9)}`
}