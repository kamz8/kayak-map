<template>
    <DraggableCard
        class="link-card"
        variant="outlined"
        elevation="2"
        v-bind="$attrs"
    >
        <!-- Drag Handle -->
        <template #drag-handle>
            <v-icon
                class="drag-handle"
                color="grey"
            >
                mdi-drag-vertical
            </v-icon>
        </template>

        <!-- Card Title with Delete Button -->
        <template #title>
            <div class="card-title-content">
                <span class="link-title">{{ metadata.title || 'Nowy link' }}</span>
                <v-btn
                    icon="mdi-minus"
                    size="small"
                    color="error"
                    variant="text"
                    @click="handleDelete"
                />
            </div>
        </template>

        <!-- Content -->
        <v-row>
            <!-- URL and Title - Side by Side -->
            <v-col cols="12" md="6">
                <FormField
                    :model-value="link.url"
                    type="text"
                    label="URL"
                    placeholder="https://example.com"
                    required
                    :rules="urlRules"
                    @update:model-value="updateField('url', $event)"
                />
            </v-col>

            <v-col cols="12" md="6">
                <FormField
                    :model-value="metadata.title"
                    type="text"
                    label="Tytuł"
                    placeholder="Nazwa linku"
                    required
                    :rules="titleRules"
                    @update:model-value="updateMetadata('title', $event)"
                />
            </v-col>

            <!-- Description - Full Width Below -->
            <v-col cols="12">
                <FormField
                    :model-value="metadata.description"
                    type="textarea"
                    label="Opis"
                    placeholder="Dodatkowe informacje o linku"
                    :rows="2"
                    :counter="150"
                    :maxlength="150"
                    :counter-template="(value, max) => `${value} / ${max} znaków`"
                    @update:model-value="updateMetadata('description', $event)"
                />
            </v-col>

            <!-- Icon Selector -->
            <v-col cols="12" md="6">
                <v-select
                    :model-value="metadata.icon"
                    :items="iconOptions"
                    label="Ikona serwisu (opcjonalnie)"
                    clearable
                    density="compact"
                    variant="outlined"
                    @update:model-value="updateMetadata('icon', $event)"
                >
                    <template #prepend-inner v-if="metadata.icon">
                        <v-icon>{{ metadata.icon }}</v-icon>
                    </template>
                </v-select>
            </v-col>
        </v-row>
    </DraggableCard>
</template>

<script>
import FormField from '@/dashboard/components/ui/FormField.vue'
import DraggableCard from '@/dashboard/components/ui/UiDraggableCard.vue'

export default {
    name: 'LinkCard',

    components: {
        FormField,
        DraggableCard
    },

    props: {
        link: {
            type: Object,
            required: true
        }
    },

    emits: ['update:link', 'delete'],

    data() {
        return {
            iconOptions: [
                { title: 'YouTube', value: 'mdi-youtube' },
                { title: 'Facebook', value: 'mdi-facebook' },
                { title: 'Instagram', value: 'mdi-instagram' },
                { title: 'Wikipedia', value: 'mdi-wikipedia' },
                { title: 'Google Maps', value: 'mdi-google-maps' },
                { title: 'Website', value: 'mdi-web' }
            ]
        }
    },

    computed: {
        metadata() {
            try {
                if (this.link.meta_data) {
                    return typeof this.link.meta_data === 'string'
                        ? JSON.parse(this.link.meta_data)
                        : this.link.meta_data
                }
            } catch (e) {
                console.error('Failed to parse meta_data:', e)
            }
            return { title: '', description: '', icon: '' }
        },

        urlRules() {
            return [
                v => !!v || 'URL jest wymagany',
                v => {
                    try {
                        new URL(v)
                        return true
                    } catch {
                        return 'Wprowadź poprawny URL'
                    }
                }
            ]
        },

        titleRules() {
            return [
                v => !!v || 'Tytuł jest wymagany',
                v => (v && v.length <= 255) || 'Tytuł może mieć maksymalnie 255 znaków'
            ]
        }
    },

    methods: {
        updateField(field, value) {
            const updatedLink = {
                ...this.link,
                [field]: value
            }
            this.$emit('update:link', updatedLink)
        },

        updateMetadata(field, value) {
            const updatedMetadata = {
                ...this.metadata,
                [field]: value
            }

            const updatedLink = {
                ...this.link,
                meta_data: JSON.stringify(updatedMetadata)
            }

            this.$emit('update:link', updatedLink)
        },

        handleDelete() {
            this.$emit('delete', this.link)
        }
    },
}
</script>

<style scoped>
.link-card {
    margin-bottom: 16px;
    border: 1px solid rgb(255, 255, 255) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.link-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12) !important;
}

/* Dragging state */
.link-card.sortable-drag {
    transform: scale(1.05) rotate(2deg);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2) !important;
}

/* Ghost element during drag */
.link-card.sortable-ghost {
    opacity: 0.4;
    background: rgba(0, 0, 0, 0.05);
}

/* Chosen element */
.link-card.sortable-chosen {
    opacity: 1;
}

.card-title-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.link-title {
    font-weight: 600;
    flex: 1;
}

.drag-handle {
    cursor: grab;
    transition: color 0.2s ease;
}

.drag-handle:hover {
    color: rgb(var(--v-theme-primary));
}

.drag-handle:active {
    cursor: grabbing;
}

/* Bez przezroczystości - pełna karta jako duszek */
.draggable-ghost {
    opacity: 0.8;
    transform: scale(0.95);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.draggable-chosen {
    transform: scale(1.02);
}

.draggable-drag {
    transform: rotate(3deg);
}
</style>
