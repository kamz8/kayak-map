<template>
  <v-chip
    v-bind="vuetifyProps"
    :class="badgeClasses"
    :closable="closable"
    @click:close="$emit('close')"
  >
    <slot />
  </v-chip>
</template>

<script>
import { designTokens } from '@/dashboard/design-system/tokens'
import { cn } from '@/dashboard/lib/utils'

export default {
  name: 'UiBadge',
  emits: ['close'],
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: (v) => ['default', 'secondary', 'destructive', 'success', 'warning', 'outline'].includes(v)
    },
    size: {
      type: String,
      default: 'default',
      validator: (v) => ['sm', 'default', 'lg'].includes(v)
    },
    closable: Boolean,
    class: String
  },
  computed: {
    vuetifyProps() {
      const variantProps = designTokens.variants.badge[this.variant] || {}
      const sizeProps = designTokens.sizes[this.size] || {}

      return { ...variantProps, ...sizeProps }
    },

    badgeClasses() {
      return cn(
        'ui-badge',
        `ui-badge--${this.variant}`,
        `ui-badge--${this.size}`,
        this.class
      )
    }
  }
}
</script>

<style scoped>
.ui-badge {
  font-weight: 500;
  font-size: 12px;
  transition: all 0.2s ease-in-out;
}

/* Default variant */
.ui-badge--default {
  background: hsl(var(--v-theme-primary)) !important;
  color: hsl(var(--v-theme-on-primary)) !important;
}

/* Secondary variant */
.ui-badge--secondary {
  background: hsl(var(--v-theme-secondary)) !important;
  color: hsl(var(--v-theme-on-secondary)) !important;
}

/* Destructive variant */
.ui-badge--destructive {
  background: hsl(var(--v-theme-error)) !important;
  color: hsl(var(--v-theme-on-error)) !important;
}

/* Success variant */
.ui-badge--success {
  background: hsl(var(--v-theme-success)) !important;
  color: hsl(var(--v-theme-on-success)) !important;
}

/* Warning variant */
.ui-badge--warning {
  background: hsl(var(--v-theme-warning)) !important;
  color: hsl(var(--v-theme-on-warning)) !important;
}

/* Outline variant */
.ui-badge--outline {
  background: transparent !important;
  color: hsl(var(--v-theme-primary)) !important;
  border: 1px solid hsl(var(--v-theme-primary)) !important;
}

/* Size variants */
.ui-badge--sm {
  font-size: 11px;
  height: 20px !important;
}

.ui-badge--sm :deep(.v-chip__content) {
  padding: 0 8px;
}

.ui-badge--default {
  font-size: 12px;
  height: 24px !important;
}

.ui-badge--default :deep(.v-chip__content) {
  padding: 0 10px;
}

.ui-badge--lg {
  font-size: 13px;
  height: 28px !important;
}

.ui-badge--lg :deep(.v-chip__content) {
  padding: 0 12px;
}

/* Remove default Vuetify chip styles we don't want */
.ui-badge :deep(.v-chip__content) {
  font-weight: 500;
  line-height: 1;
}

/* Hover effects */
.ui-badge:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.ui-badge--outline:hover {
  background: hsl(var(--v-theme-primary) / 0.05) !important;
}
</style>