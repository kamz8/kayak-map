<template>
  <v-card
    :class="cardClasses"
    :elevation="elevation"
    v-bind="$attrs"
  >
    <v-card-title v-if="title || $slots.title" class="ui-card-title">
      <slot name="title">
        <span class="ui-card-title-text">{{ title }}</span>
      </slot>
    </v-card-title>

    <v-card-subtitle v-if="subtitle || $slots.subtitle" class="ui-card-subtitle">
      <slot name="subtitle">{{ subtitle }}</slot>
    </v-card-subtitle>

    <v-card-text v-if="$slots.default" class="ui-card-content">
      <slot />
    </v-card-text>

    <v-card-actions v-if="$slots.actions" class="ui-card-actions">
      <slot name="actions" />
    </v-card-actions>
  </v-card>
</template>

<script>
import { cn } from '@/dashboard/lib/utils'

export default {
  name: 'UiCard',
  props: {
    title: String,
    subtitle: String,
    variant: {
      type: String,
      default: 'default',
      validator: (v) => ['default', 'outlined', 'elevated'].includes(v)
    },
    class: String,
  },
  computed: {
    elevation() {
      return this.variant === 'elevated' ? 2 : 0
    },

    cardClasses() {
      return cn(
        'ui-card',
        `ui-card--${this.variant}`,
        this.class
      )
    }
  }
}
</script>

<style scoped>
.ui-card {
  border-radius: 8px;
  overflow: hidden;
  transition: box-shadow 0.2s ease-in-out;
}

.ui-card--default {
  border: 1px solid hsl(var(--v-theme-surface-variant));
  background: hsl(var(--v-theme-surface));
}

.ui-card--outlined {
  border: 1px solid hsl(var(--v-theme-surface-variant));
  background: hsl(var(--v-theme-surface));
}

.ui-card--elevated {
  border: none;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

.ui-card-title {
  font-size: 18px;
  font-weight: 600;
  padding: 24px 24px 0 24px;
  line-height: 1.25;
}

.ui-card-title-text {
  color: hsl(var(--v-theme-on-surface));
}

.ui-card-subtitle {
  font-size: 14px;
  font-weight: 400;
  color: hsl(var(--v-theme-on-surface-variant));
  padding: 8px 24px 0 24px;
  line-height: 1.4;
}

.ui-card-content {
  padding: 24px;
  color: hsl(var(--v-theme-on-surface));
}

.ui-card-content:first-child {
  padding-top: 24px;
}

.ui-card-actions {
  padding: 16px 24px 24px 24px;
  gap: 8px;
}

.ui-card-actions:first-child {
  padding-top: 24px;
}

/* When title exists, reduce top padding of content */
.ui-card-title + .ui-card-content {
  padding-top: 16px;
}

/* When subtitle exists, reduce top padding of content */
.ui-card-subtitle + .ui-card-content {
  padding-top: 16px;
}

/* Hover effect for interactive cards */
.ui-card:hover.ui-card--elevated {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}
</style>
