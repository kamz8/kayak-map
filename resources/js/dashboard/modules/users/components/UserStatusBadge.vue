<template>
  <UiBadge :variant="statusConfig.variant" :size="size">
    <v-icon
      v-if="statusConfig.icon"
      :icon="statusConfig.icon"
      size="12"
      class="me-1"
    />
    {{ statusConfig.label }}
  </UiBadge>
</template>

<script>
import { UiBadge } from '@/dashboard/components/ui'

const STATUS_CONFIG = {
  active: {
    variant: 'success',
    label: 'Aktywny',
    icon: 'mdi-check-circle'
  },
  inactive: {
    variant: 'secondary',
    label: 'Nieaktywny',
    icon: 'mdi-pause-circle'
  },
  unverified: {
    variant: 'warning',
    label: 'Niezweryfikowany',
    icon: 'mdi-email-alert'
  },
  deleted: {
    variant: 'destructive',
    label: 'Usunięty',
    icon: 'mdi-delete'
  }
}

export default {
  name: 'UserStatusBadge',
  components: {
    UiBadge
  },
  props: {
    status: {
      type: String,
      required: true,
      validator: (value) => Object.keys(STATUS_CONFIG).includes(value)
    },
    size: {
      type: String,
      default: 'sm',
      validator: (value) => ['sm', 'default', 'lg'].includes(value)
    },
    showIcon: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    statusConfig() {
      const config = STATUS_CONFIG[this.status] || STATUS_CONFIG.active

      return {
        ...config,
        icon: this.showIcon ? config.icon : null
      }
    }
  }
}
</script>