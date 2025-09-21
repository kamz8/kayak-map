<template>
  <UiDialog
    v-model="dialog"
    :title="title"
    :icon="icon || 'mdi-help-circle'"
    :icon-color="iconColor"
    :max-width="maxWidth"
    :persistent="persistent"
    :closable="false"
  >
    <!-- Content -->
    <div class="text-body-1">
      {{ message }}
    </div>

    <div v-if="details" class="text-body-2 text-medium-emphasis mt-3">
      {{ details }}
    </div>

    <!-- Custom content -->
    <slot />

    <!-- Actions -->
    <template #actions>
      <UiButton
        :color="cancelColor"
        :variant="cancelVariant"
        @click="handleCancel"
      >
        {{ cancelText }}
      </UiButton>

      <UiButton
        :color="confirmColor"
        :variant="confirmVariant"
        :loading="loading"
        @click="handleConfirm"
      >
        {{ confirmText }}
      </UiButton>
    </template>
  </UiDialog>
</template>

<script>
import UiDialog from './UiDialog.vue'
import UiButton from './UiButton.vue'

export default {
  name: 'ConfirmDialog',
  components: {
    UiDialog,
    UiButton
  },
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
      default: 'error'
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

