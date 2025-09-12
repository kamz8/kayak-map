<template>
  <v-btn
    v-bind="vuetifyProps"
    :disabled="disabled"
    :loading="loading"
    :class="buttonClasses"
    @click="$emit('click', $event)"
  >
    <template v-if="$slots.prepend" #prepend>
      <slot name="prepend" />
    </template>

    <slot />

    <template v-if="$slots.append" #append>
      <slot name="append" />
    </template>
  </v-btn>
</template>

<script>
import { designTokens } from '@/dashboard/design-system/tokens'
import { cn } from '@/dashboard/lib/utils'

export default {
  name: 'UiButton',
  emits: ['click'],
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: (v) => ['default', 'destructive', 'outline', 'secondary', 'ghost', 'link'].includes(v)
    },
    size: {
      type: String,
      default: 'default',
      validator: (v) => ['sm', 'default', 'lg', 'icon'].includes(v)
    },
    disabled: Boolean,
    loading: Boolean,
    class: String
  },
  computed: {
    vuetifyProps() {
      const variantProps = designTokens.variants.button[this.variant] || {}
      const sizeProps = designTokens.sizes[this.size] || {}

      return { ...variantProps, ...sizeProps }
    },

    buttonClasses() {
      return cn(
        'ui-button',
        `ui-button--${this.variant}`,
        `ui-button--${this.size}`,
        this.class
      )
    }
  }
}
</script>

<style scoped>
.ui-button {
  font-weight: 500;
  text-transform: none;
  letter-spacing: 0;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.ui-button--ghost :deep(.v-btn__content) {
  color: hsl(var(--v-theme-primary));
}

.ui-button--ghost :deep(.v-btn__overlay) {
  opacity: 0;
}

.ui-button--ghost:hover :deep(.v-btn__overlay) {
  opacity: 0.08;
}

.ui-button--link {
  text-decoration: underline;
  text-underline-offset: 4px;
}

.ui-button--link :deep(.v-btn__overlay) {
  opacity: 0;
}

.ui-button--link:hover {
  text-decoration: none;
}

.ui-button--destructive :deep(.v-btn__content) {
  font-weight: 500;
}

.ui-button--outline {
  background: transparent !important;
}

.ui-button--secondary :deep(.v-btn__content) {
  color: hsl(var(--v-theme-secondary-darken-1));
}

/* Icon button specific styles */
.ui-button--icon {
  min-width: auto;
}
</style>