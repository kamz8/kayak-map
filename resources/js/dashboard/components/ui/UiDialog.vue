<template>
  <v-dialog
    v-model="dialog"
    :max-width="maxWidth"
    :persistent="persistent"
    :scrollable="scrollable"
    class="ui-dialog"
  >
    <v-card class="ui-dialog-card">
      <!-- Header -->
      <v-card-title v-if="title || $slots.title" class="ui-dialog-title">
        <slot name="title">
          <div class="d-flex align-center">
            <v-icon
              v-if="icon"
              :icon="icon"
              :color="iconColor"
              class="me-3"
              size="24"
            />
            <span>{{ title }}</span>
          </div>
        </slot>

        <v-spacer />

        <v-btn
          v-if="closable"
          icon
          size="small"
          variant="text"
          class="ui-dialog-close"
          @click="handleClose"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <!-- Content -->
      <v-card-text v-if="$slots.default || text" class="ui-dialog-content">
        <slot>
          <div v-if="text" class="text-body-1">
            {{ text }}
          </div>
        </slot>
      </v-card-text>

      <!-- Actions -->
      <v-card-actions v-if="$slots.actions" class="ui-dialog-actions">
        <slot name="actions" />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'UiDialog',
  emits: ['update:modelValue', 'close'],
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    title: String,
    text: String,
    icon: String,
    iconColor: {
      type: String,
      default: 'primary'
    },
    maxWidth: {
      type: [String, Number],
      default: 500
    },
    persistent: {
      type: Boolean,
      default: false
    },
    scrollable: {
      type: Boolean,
      default: false
    },
    closable: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    dialog: {
      get() {
        return this.modelValue
      },
      set(value) {
        this.$emit('update:modelValue', value)
      }
    }
  },
  methods: {
    handleClose() {
      this.dialog = false
      this.$emit('close')
    }
  }
}
</script>

<style scoped>
/* Dialog overlay styling for consistent dark theme */
:deep(.ui-dialog .v-overlay__scrim) {
  background: rgba(0, 0, 0, 0.85) !important;
}

/* Dialog card styling */
:deep(.ui-dialog-card) {
  background: #1a1a1a !important;
  border: 1px solid #333333 !important;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8) !important;
  border-radius: 12px !important;
}

/* Title styling */
:deep(.ui-dialog-title) {
  background: #222222 !important;
  color: #ffffff !important;
  border-bottom: 1px solid #333333 !important;
  font-weight: 600 !important;
  padding: 20px 24px 16px 24px !important;
  font-size: 18px !important;
}

/* Content styling */
:deep(.ui-dialog-content) {
  color: #e0e0e0 !important;
  background: #1a1a1a !important;
  padding: 20px 24px !important;
  line-height: 1.6 !important;
}

/* Actions styling */
:deep(.ui-dialog-actions) {
  background: #1f1f1f !important;
  border-top: 1px solid #333333 !important;
  padding: 16px 24px !important;
  justify-content: flex-end !important;
  gap: 8px !important;
}

/* Close button styling */
:deep(.ui-dialog-close) {
  color: #9ca3af !important;
}

:deep(.ui-dialog-close:hover) {
  color: #ffffff !important;
  background: rgba(255, 255, 255, 0.1) !important;
}

/* Icon styling */
:deep(.ui-dialog-title .v-icon) {
  opacity: 0.9 !important;
}

/* Scrollable content */
:deep(.ui-dialog .v-card--variant-elevated) {
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8) !important;
}

/* Remove default margins */
:deep(.ui-dialog-content > *:first-child) {
  margin-top: 0 !important;
}

:deep(.ui-dialog-content > *:last-child) {
  margin-bottom: 0 !important;
}
</style>