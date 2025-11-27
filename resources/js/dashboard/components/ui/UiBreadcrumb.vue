<template>
  <nav :class="breadcrumbClasses" aria-label="Breadcrumb">
    <ol class="ui-breadcrumb-list">
      <li
        v-for="(item, index) in items"
        v-show="item.text"
        :key="item.key || index"
        class="ui-breadcrumb-item"
        :class="{
          'ui-breadcrumb-item--active': index === items.length - 1,
          'ui-breadcrumb-item--clickable': item.to && index !== items.length - 1,
          'ui-breadcrumb-item--muted': item.muted
        }"
      >
        <!-- Home icon for first item if enabled -->
        <v-icon
          v-if="showHome && index === 0"
          :icon="homeIcon"
          size="small"
          class="ui-breadcrumb-home-icon"
        />

        <!-- Clickable breadcrumb -->
        <router-link
          v-if="item.to && index !== items.length - 1"
          :to="item.to"
          class="ui-breadcrumb-link"
          :class="{ 'ui-breadcrumb-link--muted': item.muted }"
        >
          {{ item.text }}
        </router-link>

        <!-- Non-clickable breadcrumb (current page) -->
        <span
          v-else
          class="ui-breadcrumb-text"
          :class="{
            'ui-breadcrumb-text--current': index === items.length - 1,
            'ui-breadcrumb-text--muted': item.muted
          }"
        >
          {{ item.text }}
        </span>
        
        <!-- Separator -->
        <v-icon
          v-if="index < items.length - 1"
          :icon="separatorIcon"
          size="small"
          class="ui-breadcrumb-separator"
        />
      </li>
    </ol>
  </nav>
</template>

<script>
import { cn } from '@/dashboard/lib/utils'

export default {
  name: 'UiBreadcrumb',
  props: {
    items: {
      type: Array,
      required: true,
      validator(items) {
        return Array.isArray(items) && items.every(item => 
          item && typeof item === 'object' && item.text
        )
      }
    },
    variant: {
      type: String,
      default: 'default',
      validator: (v) => ['default', 'subtle'].includes(v)
    },
    size: {
      type: String,
      default: 'default',
      validator: (v) => ['sm', 'default', 'lg'].includes(v)
    },
    showHome: {
      type: Boolean,
      default: false
    },
    homeIcon: {
      type: String,
      default: 'mdi-home'
    },
    separatorIcon: {
      type: String,
      default: 'mdi-chevron-right'
    },
    class: String
  },
  computed: {
    breadcrumbClasses() {
      return cn(
        'ui-breadcrumb',
        `ui-breadcrumb--${this.variant}`,
        `ui-breadcrumb--${this.size}`,
        this.class
      )
    }
  }
}
</script>

<style scoped>
.ui-breadcrumb {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: hsl(var(--v-theme-on-surface-variant));
}

.ui-breadcrumb-list {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.ui-breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.ui-breadcrumb-home-icon {
  color: hsl(var(--v-theme-on-surface-variant));
  opacity: 0.7;
  margin-right: 4px;
}

.ui-breadcrumb-link {
  color: hsl(var(--v-theme-on-surface-variant));
  text-decoration: none;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease-in-out;
  font-weight: 400;
}

.ui-breadcrumb-link:hover {
  color: hsl(var(--v-theme-on-surface));
  background: hsl(var(--v-theme-surface-variant) / 0.5);
  text-decoration: none;
}

.ui-breadcrumb-text {
  padding: 4px 8px;
  font-weight: 400;
}

.ui-breadcrumb-text--current {
  color: hsl(var(--v-theme-on-surface));
  font-weight: 500;
}

.ui-breadcrumb-separator {
  color: hsl(var(--v-theme-on-surface-variant));
  opacity: 0.5;
  margin: 0 2px;
}

/* Muted styles */
.ui-breadcrumb-link--muted {
  opacity: 0.6;
  font-weight: 400;
}

.ui-breadcrumb-link--muted:hover {
  opacity: 0.8;
}

.ui-breadcrumb-text--muted {
  opacity: 0.6;
}

/* Variant styles */
.ui-breadcrumb--subtle {
  font-size: 13px;
}

.ui-breadcrumb--subtle .ui-breadcrumb-link,
.ui-breadcrumb--subtle .ui-breadcrumb-text {
  padding: 2px 6px;
}

/* Size variants */
.ui-breadcrumb--sm {
  font-size: 12px;
}

.ui-breadcrumb--sm .ui-breadcrumb-link,
.ui-breadcrumb--sm .ui-breadcrumb-text {
  padding: 2px 6px;
}

.ui-breadcrumb--lg {
  font-size: 16px;
}

.ui-breadcrumb--lg .ui-breadcrumb-link,
.ui-breadcrumb--lg .ui-breadcrumb-text {
  padding: 6px 10px;
}

/* Responsive design */
@media (max-width: 768px) {
  .ui-breadcrumb {
    font-size: 13px;
  }
  
  .ui-breadcrumb-list {
    gap: 2px;
  }
  
  .ui-breadcrumb-link,
  .ui-breadcrumb-text {
    padding: 2px 4px;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .ui-breadcrumb-link {
    border: 1px solid transparent;
  }
  
  .ui-breadcrumb-link:hover {
    border-color: hsl(var(--v-theme-primary));
  }
}
</style>