<template>
    <UiCard
        :class="draggableClasses"
        :variant="variant"
        :title="title"
        :subtitle="subtitle"
        v-bind="$attrs"
    >
        <!-- Drag Handle Slot -->
        <template v-if="$slots['drag-handle']" #title>
            <div class="draggable-card-header">
                <div class="draggable-handle-wrapper">
                    <slot name="drag-handle" />
                </div>
                <div class="draggable-title-content">
                    <slot name="title">
                        <span class="ui-card-title-text">{{ title }}</span>
                    </slot>
                </div>
            </div>
        </template>

        <!-- Default slot for content -->
        <slot />

        <!-- Actions slot -->
        <template v-if="$slots.actions" #actions>
            <slot name="actions" />
        </template>
    </UiCard>
</template>

<script>
import UiCard from './UiCard.vue'

export default {
    name: 'DraggableCard',
    components: {
        UiCard
    },
    props: {
        title: String,
        subtitle: String,
        variant: {
            type: String,
            default: 'default',
            validator: (v) => ['default', 'outlined', 'elevated'].includes(v)
        },
        class: String,
        dragging: Boolean
    },
    computed: {
        draggableClasses() {
            return [
                'draggable-card',
                this.dragging ? 'draggable-card--dragging' : '',
                this.class
            ].filter(Boolean).join(' ')
        }
    }
}
</script>

<style scoped>
.draggable-card {
    cursor: grab;
    transition: all 0.2s ease-in-out;
    user-select: none;
}

.draggable-card--dragging {
    cursor: grabbing;
    opacity: 0.8;
    transform: rotate(2deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.draggable-card-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    width: 100%;
}

.draggable-handle-wrapper {
    flex-shrink: 0;
    margin-top: 2px;
}

.draggable-title-content {
    flex: 1;
    min-width: 0;
}

/* Hover effect */
.draggable-card:hover:not(.draggable-card--dragging) {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>
