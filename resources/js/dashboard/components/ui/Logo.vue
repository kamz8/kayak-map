<template>
  <div class="logo-container d-flex align-center">
    <img 
      :src="logoSrc" 
      alt="Wartki Nurt" 
      class="logo-image"
      :class="logoClasses"
    />
    <span v-if="showText" class="logo-text ms-3">{{ text }}</span>
  </div>
</template>

<script>
export default {
  name: 'Logo',
  props: {
    size: {
      type: [String, Number],
      default: 36
    },
    showText: {
      type: Boolean,
      default: true
    },
    text: {
      type: String,
      default: 'Wartki Nurt'
    },
    src: {
      type: String,
      default: '/storage/assets/logo.svg'
    }
  },
  computed: {
    logoSrc() {
      return this.src
    },
    logoClasses() {
      return {
        'logo-dark-theme': this.$vuetify.theme.current.dark,
        'logo-light-theme': !this.$vuetify.theme.current.dark
      }
    }
  }
}
</script>

<style scoped>
.logo-container {
  user-select: none;
}

.logo-image {
  height: v-bind('typeof size === "number" ? size + "px" : size');
  object-fit: contain;
  transition: filter 0.2s ease;
}

/* Dark theme - invert logo colors */
.logo-dark-theme {
  filter: invert(1) brightness(1);
}

/* Light theme - no filter */
.logo-light-theme {
  filter: none;
}

.logo-text {
  color: currentColor;
  font-size: 16px;
  font-weight: 600;
  letter-spacing: -0.01em;
  white-space: nowrap;
}
</style>