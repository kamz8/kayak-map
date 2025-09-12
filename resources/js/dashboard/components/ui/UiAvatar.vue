<template>
  <div class="ui-avatar-wrapper">
    <v-avatar
      :size="size"
      :color="avatarColor"
      class="ui-avatar"
      :class="avatarClasses"
    >
      <!-- Image avatar -->
      <v-img
        v-if="src && !imageError"
        :src="src"
        :alt="alt"
        @error="handleImageError"
        cover
      />
      
      <!-- Fallback with initials -->
      <span
        v-else
        class="ui-avatar-initials"
        :class="initialsClasses"
      >
        {{ displayInitials }}
      </span>
    </v-avatar>
    
    <!-- Upload button overlay (optional) -->
    <div
      v-if="uploadable"
      class="ui-avatar-upload-overlay"
      @click="$emit('upload')"
    >
      <v-icon size="16" color="white">mdi-camera</v-icon>
    </div>
  </div>
</template>

<script>
import { designTokens } from '../../design-system/tokens.js'

export default {
  name: 'UiAvatar',
  emits: ['upload'],
  props: {
    src: {
      type: String,
      default: null
    },
    alt: {
      type: String,
      default: 'Avatar'
    },
    size: {
      type: [String, Number],
      default: 48
    },
    name: {
      type: String,
      default: ''
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'primary', 'secondary'].includes(value)
    },
    uploadable: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      imageError: false
    }
  },
  computed: {
    displayInitials() {
      if (!this.name) return '??'
      
      const words = this.name.trim().split(' ')
      if (words.length >= 2) {
        // Imię Nazwisko -> IN
        return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase()
      } else if (words.length === 1) {
        // Pojedyncze słowo -> pierwsze 2 litery
        return words[0].substring(0, 2).toUpperCase()
      }
      return '??'
    },
    
    avatarColor() {
      const variants = designTokens.variants.avatar
      return variants[this.variant]?.color || 'primary'
    },
    
    avatarClasses() {
      return [
        `ui-avatar--${this.variant}`,
        {
          'ui-avatar--uploadable': this.uploadable
        }
      ]
    },
    
    initialsClasses() {
      const sizeNum = typeof this.size === 'string' ? parseInt(this.size) : this.size
      return [
        {
          'text-h6': sizeNum >= 80,
          'text-subtitle1': sizeNum >= 60 && sizeNum < 80,
          'text-body1': sizeNum >= 40 && sizeNum < 60,
          'text-body2': sizeNum >= 24 && sizeNum < 40,
          'text-caption': sizeNum < 24
        }
      ]
    }
  },
  methods: {
    handleImageError() {
      this.imageError = true
    }
  },
  watch: {
    src() {
      // Reset error state when src changes
      this.imageError = false
    }
  }
}
</script>

<style scoped>
.ui-avatar-wrapper {
  position: relative;
  display: inline-block;
}

.ui-avatar {
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.ui-avatar--default {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.ui-avatar--primary {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.ui-avatar--secondary {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
}

.ui-avatar--uploadable {
  cursor: pointer;
}

.ui-avatar--uploadable:hover {
  border-color: rgba(255, 255, 255, 0.3);
  transform: scale(1.02);
}

.ui-avatar-initials {
  font-weight: 600;
  color: white;
  user-select: none;
}

.ui-avatar-upload-overlay {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 28px;
  height: 28px;
  background: #2563eb;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 2px solid white;
  transition: all 0.2s ease;
}

.ui-avatar-upload-overlay:hover {
  background: #1d4ed8;
  transform: scale(1.1);
}

/* Size-specific adjustments */
.ui-avatar-wrapper .v-avatar--size-default .ui-avatar-upload-overlay {
  width: 24px;
  height: 24px;
  bottom: 0;
  right: 0;
}

.ui-avatar-wrapper .v-avatar--size-small .ui-avatar-upload-overlay {
  width: 20px;
  height: 20px;
  bottom: 1px;
  right: 1px;
}

.ui-avatar-wrapper .v-avatar--size-large .ui-avatar-upload-overlay {
  width: 32px;
  height: 32px;
  bottom: -4px;
  right: -4px;
}
</style>