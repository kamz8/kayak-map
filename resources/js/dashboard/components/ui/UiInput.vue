<template>
  <v-text-field
    :model-value="modelValue"
    v-bind="vuetifyProps"
    :error="!!errorMessage"
    :error-messages="errorMessage"
    :class="inputClasses"
    :placeholder="placeholder"
    :type="type"
    :readonly="readonly"
    :disabled="disabled"
    @update:model-value="$emit('update:modelValue', $event)"
    @blur="$emit('blur', $event)"
    @focus="$emit('focus', $event)"
    @keydown="$emit('keydown', $event)"
  >
    <template v-if="$slots.prepend" #prepend>
      <slot name="prepend" />
    </template>

    <template v-if="$slots['prepend-inner']" #prepend-inner>
      <slot name="prepend-inner" />
    </template>

    <template v-if="$slots['append-inner']" #append-inner>
      <slot name="append-inner" />
    </template>

    <template v-if="$slots.append" #append>
      <slot name="append" />
    </template>
  </v-text-field>
</template>

<script>
import { designTokens } from '@/dashboard/design-system/tokens'
import { cn } from '@/dashboard/lib/utils'

export default {
  name: 'UiInput',
  emits: ['update:modelValue', 'blur', 'focus', 'keydown'],
  props: {
    modelValue: [String, Number],
    variant: {
      type: String,
      default: 'default',
      validator: (v) => ['default', 'filled', 'underlined'].includes(v)
    },
    size: {
      type: String,
      default: 'default',
      validator: (v) => ['sm', 'default', 'lg'].includes(v)
    },
    type: {
      type: String,
      default: 'text'
    },
    placeholder: String,
    errorMessage: String,
    disabled: Boolean,
    readonly: Boolean,
    class: String
  },
  computed: {
    vuetifyProps() {
      const variantProps = designTokens.variants.input[this.variant] || {}
      const sizeProps = designTokens.sizes[this.size] || {}

      return { ...variantProps, ...sizeProps }
    },

    inputClasses() {
      return cn(
        'ui-input',
        `ui-input--${this.variant}`,
        `ui-input--${this.size}`,
        this.class
      )
    }
  }
}
</script>

<style scoped>
.ui-input {
  transition: all 0.2s ease-in-out;
}

.ui-input :deep(.v-field) {
  border-radius: 6px;
  transition: all 0.2s ease-in-out;
}

.ui-input :deep(.v-field__field) {
  font-size: 14px;
}

.ui-input :deep(.v-field__input) {
  color: hsl(var(--v-theme-on-surface));
  font-weight: 400;
}

.ui-input :deep(.v-field__input)::placeholder {
  color: hsl(var(--v-theme-on-surface-variant));
  opacity: 0.7;
}

/* Default variant styling */
.ui-input--default :deep(.v-field) {
  border: 1px solid hsl(var(--v-theme-surface-variant));
  background: hsl(var(--v-theme-surface));
}

.ui-input--default :deep(.v-field:hover) {
  border-color: hsl(var(--v-theme-primary));
}

.ui-input--default :deep(.v-field--focused) {
  border-color: hsl(var(--v-theme-primary));
  box-shadow: 0 0 0 2px hsl(var(--v-theme-primary) / 0.2);
}

.ui-input--default :deep(.v-field--error) {
  border-color: hsl(var(--v-theme-error));
}

/* Filled variant */
.ui-input--filled :deep(.v-field) {
  background: hsl(var(--v-theme-surface-variant));
  border: none;
}

/* Underlined variant */
.ui-input--underlined :deep(.v-field) {
  background: transparent;
  border: none;
  border-bottom: 1px solid hsl(var(--v-theme-surface-variant));
  border-radius: 0;
}

/* Size variants */
.ui-input--sm :deep(.v-field__input) {
  font-size: 13px;
  min-height: 32px;
}

.ui-input--lg :deep(.v-field__input) {
  font-size: 16px;
  min-height: 44px;
}

/* Disabled state */
.ui-input :deep(.v-field--disabled) {
  opacity: 0.6;
}

/* Error state styling */
.ui-input :deep(.v-messages__message) {
  color: hsl(var(--v-theme-error));
  font-size: 12px;
  margin-top: 4px;
}
</style>