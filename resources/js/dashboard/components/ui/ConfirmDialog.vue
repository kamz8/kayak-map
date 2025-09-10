<template>
  <v-dialog
    v-model="dialog"
    :max-width="maxWidth"
    :persistent="persistent"
  >
    <v-card>
      <!-- Header -->
      <v-card-title class="d-flex align-center">
        <v-icon
          v-if="icon"
          :icon="icon"
          :color="iconColor"
          class="me-3"
          size="28"
        />
        <span>{{ title }}</span>
      </v-card-title>

      <!-- Content -->
      <v-card-text class="pb-4">
        <div class="text-body-1">
          {{ message }}
        </div>
        
        <div v-if="details" class="text-body-2 text-medium-emphasis mt-3">
          {{ details }}
        </div>

        <!-- Custom content -->
        <slot />
      </v-card-text>

      <!-- Actions -->
      <v-card-actions class="px-6 pb-4">
        <v-spacer />
        
        <v-btn
          :color="cancelColor"
          :variant="cancelVariant"
          @click="handleCancel"
        >
          {{ cancelText }}
        </v-btn>
        
        <v-btn
          :color="confirmColor"
          :variant="confirmVariant"
          :loading="loading"
          @click="handleConfirm"
        >
          {{ confirmText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'ConfirmDialog',
  emits: ['confirm', 'cancel', 'update:modelValue'],
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'Potwierdź akcję'
    },
    message: {
      type: String,
      required: true
    },
    details: String,
    icon: String,
    iconColor: {
      type: String,
      default: 'warning'
    },
    confirmText: {
      type: String,
      default: 'Potwierdź'
    },
    cancelText: {
      type: String,
      default: 'Anuluj'
    },
    confirmColor: {
      type: String,
      default: 'error'
    },
    cancelColor: {
      type: String,
      default: 'grey'
    },
    confirmVariant: {
      type: String,
      default: 'flat'
    },
    cancelVariant: {
      type: String,
      default: 'text'
    },
    maxWidth: {
      type: [String, Number],
      default: 400
    },
    persistent: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
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
    handleConfirm() {
      this.$emit('confirm')
    },
    handleCancel() {
      this.$emit('cancel')
      this.dialog = false
    }
  }
}
</script>