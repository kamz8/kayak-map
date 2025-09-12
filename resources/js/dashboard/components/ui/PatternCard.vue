<template>
  <v-sheet
    :class="[
      'pattern-card',
      `pattern-card--${variant}`,
      {
        'pattern-card--clickable': clickable,
        'pattern-card--elevated': elevated
      }
    ]"
    :color="backgroundColor"
    :theme="theme"
    :rounded="rounded"
    :elevation="elevated ? elevation : 0"
    v-bind="$attrs"
    @click="handleClick"
  >
    <!-- Pattern SVG Overlay -->
    <svg 
      class="pattern-svg" 
      fill="none"
      :class="`pattern-svg--${theme}`"
    >
      <defs>
        <pattern 
          :id="`pattern-${patternId}`" 
          x="0" 
          y="0" 
          width="8" 
          height="8" 
          patternUnits="userSpaceOnUse"
        >
          <path 
            d="M-1 5L5 -1M3 9L8.5 3.5" 
            :stroke-width="patternStrokeWidth"
            :stroke="patternStroke"
          />
        </pattern>
      </defs>
      <rect 
        stroke="none" 
        :fill="`url(#pattern-${patternId})`" 
        width="100%" 
        height="100%" 
      />
    </svg>

    <!-- Content Slot -->
    <div class="pattern-card__content" :class="`pattern-card__content--${contentPosition}`">
      <slot />
    </div>
  </v-sheet>
</template>

<script>
export default {
  name: 'PatternCard',
  inheritAttrs: false,
  props: {
    // Vuetify props
    theme: {
      type: String,
      default: 'dark'
    },
    color: {
      type: String,
      default: null
    },
    rounded: {
      type: [String, Number, Boolean],
      default: 'xl'
    },
    elevation: {
      type: [String, Number],
      default: 0
    },
    
    // Custom props
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'primary', 'secondary', 'success', 'warning', 'error'].includes(value)
    },
    patternStrokeWidth: {
      type: [String, Number],
      default: 0.5
    },
    clickable: {
      type: Boolean,
      default: false
    },
    elevated: {
      type: Boolean,
      default: false
    },
    contentPosition: {
      type: String,
      default: 'center',
      validator: (value) => ['center', 'top', 'bottom', 'left', 'right'].includes(value)
    },
    minHeight: {
      type: [String, Number],
      default: '200px'
    }
  },
  computed: {
    patternId() {
      return Math.random().toString(36).substr(2, 9)
    },
    backgroundColor() {
      if (this.color) return this.color
      
      const variants = {
        default: this.theme === 'dark' ? '#1a1a1a' : '#ffffff',
        primary: this.theme === 'dark' ? '#1e293b' : '#e3f2fd',
        secondary: this.theme === 'dark' ? '#374151' : '#f5f5f5',
        success: this.theme === 'dark' ? '#064e3b' : '#e8f5e9',
        warning: this.theme === 'dark' ? '#92400e' : '#fff8e1',
        error: this.theme === 'dark' ? '#7f1d1d' : '#ffebee'
      }
      
      return variants[this.variant]
    },
    patternStroke() {
      const strokes = {
        dark: {
          default: 'rgba(255, 255, 255, 0.1)',
          primary: 'rgba(59, 130, 246, 0.2)',
          secondary: 'rgba(156, 163, 175, 0.2)',
          success: 'rgba(34, 197, 94, 0.2)',
          warning: 'rgba(245, 158, 11, 0.2)',
          error: 'rgba(239, 68, 68, 0.2)'
        },
        light: {
          default: 'rgba(0, 0, 0, 0.1)',
          primary: 'rgba(25, 118, 210, 0.15)',
          secondary: 'rgba(107, 114, 128, 0.15)',
          success: 'rgba(22, 163, 74, 0.15)',
          warning: 'rgba(217, 119, 6, 0.15)',
          error: 'rgba(220, 38, 38, 0.15)'
        }
      }
      
      return strokes[this.theme]?.[this.variant] || strokes[this.theme].default
    }
  },
  methods: {
    handleClick(event) {
      if (this.clickable) {
        this.$emit('click', event)
      }
    }
  }
}
</script>

<style scoped>
.pattern-card {
  position: relative;
  overflow: hidden;
  border: 1px solid;
  transition: all 0.2s ease;
}

.pattern-card--default {
  border-color: rgba(255, 255, 255, 0.1);
}

.pattern-card--primary {
  border-color: rgba(59, 130, 246, 0.3);
}

.pattern-card--secondary {
  border-color: rgba(156, 163, 175, 0.3);
}

.pattern-card--success {
  border-color: rgba(34, 197, 94, 0.3);
}

.pattern-card--warning {
  border-color: rgba(245, 158, 11, 0.3);
}

.pattern-card--error {
  border-color: rgba(239, 68, 68, 0.3);
}

.pattern-card--clickable {
  cursor: pointer;
}

.pattern-card--clickable:hover {
  transform: translateY(-2px);
}

.pattern-card--elevated {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.pattern-card--elevated:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.pattern-svg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.pattern-card__content {
  position: relative;
  z-index: 2;
  width: 100%;
  height: 100%;
  min-height: v-bind(minHeight);
  display: flex;
  padding: 24px;
}

.pattern-card__content--center {
  align-items: center;
  justify-content: center;
  flex-direction: column;
}

.pattern-card__content--top {
  align-items: flex-start;
  justify-content: flex-start;
  flex-direction: column;
}

.pattern-card__content--bottom {
  align-items: flex-end;
  justify-content: flex-end;
  flex-direction: column;
}

.pattern-card__content--left {
  align-items: center;
  justify-content: flex-start;
  flex-direction: row;
}

.pattern-card__content--right {
  align-items: center;
  justify-content: flex-end;
  flex-direction: row;
}

/* Theme-specific adjustments */
.v-theme--light .pattern-card--default {
  border-color: rgba(0, 0, 0, 0.12);
}

.v-theme--light .pattern-card--primary {
  border-color: rgba(25, 118, 210, 0.3);
}

.v-theme--light .pattern-card--secondary {
  border-color: rgba(107, 114, 128, 0.3);
}

.v-theme--light .pattern-card--success {
  border-color: rgba(22, 163, 74, 0.3);
}

.v-theme--light .pattern-card--warning {
  border-color: rgba(217, 119, 6, 0.3);
}

.v-theme--light .pattern-card--error {
  border-color: rgba(220, 38, 38, 0.3);
}
</style>