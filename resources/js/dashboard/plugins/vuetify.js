import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import { designTokens } from '@/dashboard/design-system/tokens'

// shadcn/ui inspired theme using design tokens
const kayakLightTheme = {
  dark: false,
  colors: {
    // Primary colors from design tokens
    primary: designTokens.colors.primary,
    'primary-darken-1': '#1d4ed8',
    'primary-lighten-1': '#3b82f6',
    
    // Secondary colors
    secondary: designTokens.colors.secondary,
    'secondary-darken-1': '#475569',
    'secondary-lighten-1': '#94a3b8',
    
    // Semantic colors
    error: designTokens.colors.destructive,
    success: designTokens.colors.success,
    warning: designTokens.colors.warning,
    info: designTokens.colors.primary,
    
    // Surface and background
    background: designTokens.colors.background,
    surface: designTokens.colors.card,
    'surface-variant': designTokens.colors['surface-variant'],
    'on-surface': designTokens.colors.foreground,
    'on-surface-variant': designTokens.colors['on-surface-variant'],
    
    // Additional semantic colors
    'on-primary': designTokens.colors['primary-foreground'],
    'on-secondary': designTokens.colors['secondary-foreground'],
    'on-error': designTokens.colors['destructive-foreground'],
    'on-success': designTokens.colors['success-foreground'],
    'on-warning': designTokens.colors['warning-foreground']
  },
  variables: {
    // Border and input colors
    'border-color': designTokens.colors.border,
    'border-opacity': 1,
    
    // High emphasis text
    'high-emphasis-opacity': 0.87,
    'medium-emphasis-opacity': 0.60,
    'disabled-opacity': 0.38,
    
    // Theme specific variables
    'theme-kbd': designTokens.colors.muted,
    'theme-on-kbd': designTokens.colors['muted-foreground'],
    'theme-code': designTokens.colors['surface-variant'],
    'theme-on-code': designTokens.colors.foreground
  }
}

const kayakDarkTheme = {
  dark: true,
  colors: {
    // Adjusted colors for dark theme
    primary: '#3b82f6',
    'primary-darken-1': '#2563eb',
    'primary-lighten-1': '#60a5fa',
    
    secondary: '#64748b',
    'secondary-darken-1': '#475569',
    'secondary-lighten-1': '#94a3b8',
    
    error: '#ef4444',
    success: '#10b981',
    warning: '#f59e0b',
    info: '#3b82f6',
    
    background: '#0a0a0a',
    surface: '#111111',
    'surface-variant': '#1a1a1a',
    'on-surface': '#ffffff',
    'on-surface-variant': '#94a3b8',
    
    'on-primary': '#ffffff',
    'on-secondary': '#ffffff',
    'on-error': '#ffffff',
    'on-success': '#ffffff',
    'on-warning': '#ffffff'
  },
  variables: {
    'border-color': '#1f1f1f',
    'border-opacity': 1,
    'high-emphasis-opacity': 0.87,
    'medium-emphasis-opacity': 0.60,
    'disabled-opacity': 0.38
  }
}

export default createVuetify({
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi }
  },
  theme: {
    defaultTheme: 'kayakLightTheme',
    themes: {
      kayakLightTheme,
      kayakDarkTheme
    }
  },
  defaults: {
    // Global component defaults for consistency
    VBtn: {
      style: 'text-transform: none; font-weight: 500;',
    },
    VCard: {
      elevation: 0,
      variant: 'outlined'
    },
    VTextField: {
      variant: 'outlined',
      density: 'compact',
      color: 'primary'
    },
    VSelect: {
      variant: 'outlined',
      density: 'compact', 
      color: 'primary'
    },
    VTextarea: {
      variant: 'outlined',
      density: 'compact',
      color: 'primary'
    },
    VChip: {
      size: 'small'
    }
  }
})