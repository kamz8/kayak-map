<template>
  <v-card class="stats-card" :color="color" :variant="variant">
    <v-card-text class="d-flex align-center">
      <!-- Icon -->
      <v-avatar
        :color="iconColor || color"
        :variant="iconVariant"
        :size="iconSize"
        class="me-4"
      >
        <v-icon :icon="icon" :color="iconTextColor" />
      </v-avatar>

      <!-- Content -->
      <div class="flex-grow-1">
        <div class="text-overline text-medium-emphasis mb-1">
          {{ title }}
        </div>
        <div class="text-h4 font-weight-bold" :class="valueColor">
          {{ formattedValue }}
        </div>
        <div v-if="subtitle" class="text-caption text-medium-emphasis">
          {{ subtitle }}
        </div>
      </div>

      <!-- Trend indicator -->
      <div v-if="trend" class="text-end">
        <v-chip
          :color="trendColor"
          :prepend-icon="trendIcon"
          size="small"
          variant="flat"
        >
          {{ Math.abs(trend) }}%
        </v-chip>
        <div class="text-caption text-medium-emphasis mt-1">
          {{ trendLabel }}
        </div>
      </div>
    </v-card-text>

    <!-- Progress bar -->
    <v-progress-linear
      v-if="progress !== undefined"
      :model-value="progress"
      :color="progressColor || color"
      height="4"
    />
  </v-card>
</template>

<script>
export default {
  name: 'StatsCard',
  props: {
    title: {
      type: String,
      required: true
    },
    value: {
      type: [String, Number],
      required: true
    },
    subtitle: String,
    icon: {
      type: String,
      required: true
    },
    color: {
      type: String,
      default: 'primary'
    },
    variant: {
      type: String,
      default: 'flat'
    },
    iconColor: String,
    iconVariant: {
      type: String,
      default: 'tonal'
    },
    iconSize: {
      type: [String, Number],
      default: 48
    },
    iconTextColor: {
      type: String,
      default: 'white'
    },
    valueColor: String,
    trend: Number,
    trendLabel: {
      type: String,
      default: 'vs poprzedni miesiąc'
    },
    progress: Number,
    progressColor: String
  },
  computed: {
    formattedValue() {
      if (typeof this.value === 'number') {
        return new Intl.NumberFormat('pl-PL').format(this.value)
      }
      return this.value
    },
    trendColor() {
      if (!this.trend) return 'grey'
      return this.trend > 0 ? 'success' : 'error'
    },
    trendIcon() {
      if (!this.trend) return 'mdi-minus'
      return this.trend > 0 ? 'mdi-trending-up' : 'mdi-trending-down'
    }
  }
}
</script>

<style scoped>
.stats-card {
  transition: all 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}
</style>