<template>
  <v-select
    :model-value="modelValue"
    v-bind="vuetifyProps"
    :items="items"
    :item-title="itemTitle"
    :item-value="itemValue"
    :error="!!errorMessage"
    :error-messages="errorMessage"
    :class="selectClasses"
    :placeholder="placeholder"
    :readonly="readonly"
    :disabled="disabled"
    :multiple="multiple"
    :chips="chips"
    :clearable="clearable"
    @update:model-value="$emit('update:modelValue', $event)"
    @blur="$emit('blur', $event)"
    @focus="$emit('focus', $event)"
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

    <template v-if="$slots.item" #item="slotProps">
      <slot name="item" v-bind="slotProps" />
    </template>

    <template v-if="$slots.selection" #selection="slotProps">
      <slot name="selection" v-bind="slotProps" />
    </template>

    <template v-if="$slots['no-data']" #no-data>
      <slot name="no-data" />
    </template>
  </v-select>
</template>

<script>
import { designTokens } from '@/dashboard/design-system/tokens'
import { cn } from '@/dashboard/lib/utils'

export default {
  name: 'UiSelect',
  emits: ['update:modelValue', 'blur', 'focus'],
  props: {
    modelValue: [String, Number, Array, Object],
    items: {
      type: Array,
      required: true
    },
    itemTitle: {
      type: String,
      default: 'title'
    },
    itemValue: {
      type: String,
      default: 'value'
    },
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
    placeholder: String,
    label: String,
    errorMessage: String,
    disabled: Boolean,
    readonly: Boolean,
    multiple: Boolean,
    chips: Boolean,
    clearable: Boolean,
    class: String
  },
  computed: {
    vuetifyProps() {
      const variantProps = designTokens.variants.input[this.variant] || {}
      const sizeProps = designTokens.sizes[this.size] || {}

      // Remove 'size' prop as v-select doesn't support it, only use density
      const { size: _, ...filteredSizeProps } = sizeProps

      return {
        ...variantProps,
        ...filteredSizeProps,
        label: this.label
      }
    },

    selectClasses() {
      return cn(
        'ui-select',
        `ui-select--${this.variant}`,
        `ui-select--${this.size}`,
        this.class
      )
    }
  }
}
</script>

<style scoped>
.ui-select {
  transition: all 0.2s ease-in-out;
}

.ui-select--default {
  /* Default variant styles */
}

.ui-select--filled {
  /* Filled variant styles */
}

.ui-select--underlined {
  /* Underlined variant styles */
}

/* Size variants */
.ui-select--sm {
  /* Small size styles */
}

.ui-select--default {
  /* Default size styles */
}

.ui-select--lg {
  /* Large size styles */
}
</style>