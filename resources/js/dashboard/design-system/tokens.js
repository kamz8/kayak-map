/**
 * Design Tokens - Kayak Map Dashboard UI Kit
 * Inspired by shadcn/ui, built for Vuetify
 */

export const designTokens = {
  // Color palette - shadcn/ui inspired
  colors: {
    // Primary colors
    primary: '#2563eb',        // blue-600
    'primary-foreground': '#ffffff',
    
    // Secondary colors  
    secondary: '#64748b',      // slate-500
    'secondary-foreground': '#ffffff',
    
    // Destructive (error) colors
    destructive: '#dc2626',    // red-600
    'destructive-foreground': '#ffffff',
    
    // Success colors
    success: '#059669',        // emerald-600
    'success-foreground': '#ffffff',
    
    // Warning colors
    warning: '#d97706',        // amber-600
    'warning-foreground': '#ffffff',
    
    // Neutral colors
    muted: '#64748b',          // slate-500
    'muted-foreground': '#475569', // slate-600
    
    // Border and surface
    border: '#e2e8f0',         // slate-200
    input: '#ffffff',
    ring: '#3b82f6',           // blue-500
    background: '#ffffff',
    foreground: '#0f172a',     // slate-900
    
    // Card colors
    card: '#ffffff',
    'card-foreground': '#0f172a',
    
    // Surface variants
    'surface-variant': '#f8fafc', // slate-50
    'on-surface-variant': '#64748b'
  },
  
  // Component variant mappings to Vuetify
  variants: {
    button: {
      default: {
        variant: 'elevated',
        color: 'primary'
      },
      destructive: {
        variant: 'elevated', 
        color: 'error'
      },
      outline: {
        variant: 'outlined',
        color: 'primary'
      },
      secondary: {
        variant: 'tonal',
        color: 'secondary'
      },
      ghost: {
        variant: 'text',
        color: 'primary'
      },
      link: {
        variant: 'plain',
        color: 'primary'
      }
    },
    
    avatar: {
      default: {
        color: 'primary'
      },
      primary: {
        color: 'primary' 
      },
      secondary: {
        color: 'secondary'
      }
    },
    
    input: {
      default: {
        variant: 'outlined',
        density: 'compact'
      },
      filled: {
        variant: 'filled',
        density: 'compact'
      },
      underlined: {
        variant: 'underlined',
        density: 'compact'
      }
    },
    
    badge: {
      default: {
        color: 'primary',
        variant: 'elevated'
      },
      secondary: {
        color: 'secondary',
        variant: 'tonal'
      },
      destructive: {
        color: 'error',
        variant: 'elevated'
      },
      success: {
        color: 'success',
        variant: 'elevated'
      },
      warning: {
        color: 'warning',
        variant: 'elevated'
      },
      outline: {
        color: 'primary',
        variant: 'outlined'
      }
    },
    
    card: {
      default: {
        elevation: 0,
        variant: 'outlined'
      },
      elevated: {
        elevation: 2,
        variant: 'elevated'  
      },
      outlined: {
        elevation: 0,
        variant: 'outlined'
      }
    }
  },
  
  // Size mappings
  sizes: {
    sm: {
      size: 'small',
      density: 'compact'
    },
    default: {
      size: 'default', 
      density: 'default'
    },
    lg: {
      size: 'large',
      density: 'comfortable'
    },
    icon: {
      size: 'small',
      density: 'compact'
    }
  },
  
  // Border radius scale
  borderRadius: {
    sm: '4px',
    default: '6px',
    md: '8px',
    lg: '12px',
    xl: '16px'
  },
  
  // Spacing scale (matches Vuetify)
  spacing: {
    0: '0px',
    1: '4px',
    2: '8px', 
    3: '12px',
    4: '16px',
    5: '20px',
    6: '24px',
    8: '32px',
    10: '40px',
    12: '48px',
    16: '64px',
    20: '80px'
  },
  
  // Typography scale
  typography: {
    'text-xs': {
      fontSize: '12px',
      lineHeight: '16px'
    },
    'text-sm': {
      fontSize: '14px', 
      lineHeight: '20px'
    },
    'text-base': {
      fontSize: '16px',
      lineHeight: '24px'
    },
    'text-lg': {
      fontSize: '18px',
      lineHeight: '28px' 
    },
    'text-xl': {
      fontSize: '20px',
      lineHeight: '28px'
    }
  },
  
  // Shadow system
  shadows: {
    xs: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
    sm: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
    default: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
    md: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
    lg: '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
    xl: '0 25px 50px -12px rgb(0 0 0 / 0.25)'
  }
}

// Helper function to get variant props
export function getVariantProps(component, variant, size = 'default') {
  const variantProps = designTokens.variants[component]?.[variant] || {}
  const sizeProps = designTokens.sizes[size] || {}
  
  return { ...variantProps, ...sizeProps }
}

// Helper function to get color value
export function getColor(colorName) {
  return designTokens.colors[colorName] || colorName
}

// Export individual token categories for easier access
export const colors = designTokens.colors
export const variants = designTokens.variants  
export const sizes = designTokens.sizes
export const borderRadius = designTokens.borderRadius
export const spacing = designTokens.spacing
export const typography = designTokens.typography
export const shadows = designTokens.shadows