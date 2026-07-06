<template>
    <v-sheet class="editor-toolbar v-card" elevation="0" rounded>
        <div class="toolbar-container">
            <!-- Left: Drawing Tools -->
            <div class="toolbar-section">
                <!-- Draw Track -->
                <v-tooltip text="Planuj/edytuj trasę (P)" location="bottom">
                    <template #activator="{ props }">
                        <ui-button
                            v-bind="props"
                            size="sm"
                            density="comfortable"
                            variant="default"
                            :active="activeTool === 'draw'"
                            @click="handleDrawTool"
                            class="tool-button ui-interactive"
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </ui-button>
                    </template>
                </v-tooltip>

                <!-- Add POI -->
                <v-tooltip text="Dodaj punkt POI (O)" location="bottom">
                    <template #activator="{ props }">
                        <v-btn
                            v-bind="props"
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            :active="activeTool === 'poi'"
                            @click="handlePoiTool"
                            class="tool-button ui-interactive"
                        >
                            <v-icon>mdi-map-marker-outline</v-icon>
                        </v-btn>
                    </template>
                </v-tooltip>

                <v-divider vertical class="mx-1" />

                <!-- Snap to River -->
                <v-tooltip text="Automatyczny routing (S)" location="bottom">
                    <template #activator="{ props }">
                        <v-btn
                            v-bind="props"
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            :color="activeTool === 'snap' ? 'primary' : undefined"
                            :disabled="!hasTrack"
                            @click="handleSnapTool"
                            class="tool-button ui-interactive"
                        >
                            <v-icon>mdi-map-marker-path</v-icon>
                        </v-btn>
                    </template>
                </v-tooltip>

                <!-- Clear Track -->
                <v-tooltip text="Usuń trasę" location="bottom">
                    <template #activator="{ props }">
                        <v-btn
                            v-bind="props"
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            color="error"
                            :disabled="!hasTrack"
                            @click="handleClear"
                            class="tool-button ui-interactive"
                        >
                            <v-icon>mdi-delete-outline</v-icon>
                        </v-btn>
                    </template>
                </v-tooltip>
            </div>

            <!-- Center: Track Info -->
            <div class="toolbar-section track-info">
                <div class="info-item" v-if="hasTrack">
                    <v-icon size="small" color="primary">mdi-vector-polyline</v-icon>
                    <span class="info-label ui-text">{{ trackCoordinates.length }} pkt</span>
                </div>

                <div class="info-item" v-if="trackLength > 0">
                    <v-icon size="small" color="primary">mdi-map-marker-distance</v-icon>
                    <span class="info-label ui-text">{{ trackLength.toFixed(2) }} km</span>
                </div>

                <div class="info-item" v-if="poiCount > 0">
                    <v-icon size="small" color="primary">mdi-map-marker</v-icon>
                    <span class="info-label ui-text">{{ poiCount }} POI</span>
                </div>

                <v-chip
                    v-if="unsavedChanges"
                    size="x-small"
                    color="warning"
                    variant="flat"
                    prepend-icon="mdi-content-save-alert"
                    class="status-chip"
                >
                    Niezapisane
                </v-chip>

                <v-chip
                    v-if="snapProgress !== null"
                    size="x-small"
                    color="info"
                    variant="flat"
                    prepend-icon="mdi-progress-wrench"
                    class="status-chip"
                >
                    Snap: {{ snapProgress }}%
                </v-chip>
            </div>

            <!-- Right: Actions -->
            <div class="toolbar-section">
                <!-- Undo -->
                <v-tooltip text="Cofnij (Ctrl+Z)" location="bottom">
                    <template #activator="{ props }">
                        <v-btn
                            v-bind="props"
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            :disabled="!canUndo"
                            @click="handleUndo"
                            class="tool-button ui-interactive"
                        >
                            <v-icon>mdi-undo</v-icon>
                        </v-btn>
                    </template>
                </v-tooltip>

                <!-- Redo -->
                <v-tooltip text="Ponów (Ctrl+Shift+Z)" location="bottom">
                    <template #activator="{ props }">
                        <v-btn
                            v-bind="props"
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            :disabled="!canRedo"
                            @click="handleRedo"
                            class="tool-button ui-interactive"
                        >
                            <v-icon>mdi-redo</v-icon>
                        </v-btn>
                    </template>
                </v-tooltip>

                <v-divider vertical class="mx-1" />

                <!-- Save -->
                <v-tooltip text="Zapisz trasę (Ctrl+S)" location="bottom">
                    <template #activator="{ props }">
                        <v-btn
                            v-bind="props"
                            size="x-small"
                            density="comfortable"
                            variant="flat"
                            color="primary"
                            :disabled="!isValid || isSaving"
                            :loading="isSaving"
                            @click="handleSave"
                            class="tool-button save-button ui-interactive"
                        >
                            <v-icon>mdi-content-save</v-icon>
                        </v-btn>
                    </template>
                </v-tooltip>
            </div>
        </div>

        <!-- Snap Options Dialog -->
        <v-dialog v-model="showSnapDialog" max-width="500">
            <v-card class="v-card">
                <v-card-title class="text-h6 ui-heading">
                    <v-icon start>mdi-vector-radius</v-icon>
                    Snap do rzeki
                </v-card-title>

                <v-card-text>
                    <v-select
                        label="Rzeka"
                        :items="availableRivers"
                        v-model="selectedRiver"
                        prepend-icon="mdi-river"
                        density="comfortable"
                        variant="outlined"
                    />

                    <v-slider
                        label="Tolerancja (m)"
                        v-model="snapTolerance"
                        min="10"
                        max="500"
                        step="10"
                        thumb-label
                        prepend-icon="mdi-ruler"
                        density="comfortable"
                        color="primary"
                    >
                        <template v-slot:append>
                            <span class="text-caption ui-text-muted">{{ snapTolerance }}m</span>
                        </template>
                    </v-slider>

                    <v-checkbox
                        v-model="autoSimplify"
                        label="Automatyczne uproszczenie trasy"
                        color="primary"
                        density="comfortable"
                    />
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        variant="text"
                        @click="showSnapDialog = false"
                        size="small"
                        class="ui-interactive"
                    >
                        Anuluj
                    </v-btn>
                    <v-btn
                        color="primary"
                        @click="applySnap"
                        :loading="isSnapping"
                        size="small"
                        class="ui-interactive"
                    >
                        Zastosuj Snap
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-sheet>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import { trailEditorGetters, trailEditorActions, trailEditorMutations } from '../store/trailEditor.js'
import UiButton from "@ui/UiButton.vue";

export default {
    name: 'EditorToolbar',
    components: {UiButton},

    emits: ['save-success', 'save-error', 'tool-changed', 'snap-requested'],

    data() {
        return {
            showSnapDialog: false,
            selectedRiver: 'odra',
            snapTolerance: 50,
            autoSimplify: true,
            isSnapping: false,
            snapProgress: null,
            availableRivers: [
                { title: 'Odra', value: 'odra' },
                { title: 'Wisła', value: 'wisla' },
                { title: 'Warta', value: 'warta' },
                { title: 'Bug', value: 'bug' }
            ]
        }
    },

    computed: {
        ...mapGetters('trailEditor', {
            trackCoordinates: trailEditorGetters.TRACK_COORDINATES,
            hasTrack: trailEditorGetters.HAS_TRACK,
            isValid: trailEditorGetters.IS_VALID,
            canUndo: trailEditorGetters.CAN_UNDO,
            canRedo: trailEditorGetters.CAN_REDO,
            trackLength: trailEditorGetters.TRACK_LENGTH,
            unsavedChanges: trailEditorGetters.UNSAVED_CHANGES,
            isSaving: trailEditorGetters.IS_SAVING,
            hasPoi: trailEditorGetters.HAS_POI,
            poiCount: trailEditorGetters.POI_COUNT,
            activeTool: trailEditorGetters.ACTIVE_TOOL
        })
    },

    methods: {
        ...mapActions('trailEditor', {
            saveTrack: trailEditorActions.SAVE_TRACK,
            clearAllFeatures: trailEditorActions.CLEAR_ALL_FEATURES
        }),

        // Tool Selection - Toggle behavior
        handleDrawTool() {
            const newTool = this.activeTool === 'draw' ? null : 'draw'
            console.log('🖊️ Plan/Edit route tool', newTool ? 'activated' : 'deactivated')
            this.$store.commit(`trailEditor/${trailEditorMutations.SET_ACTIVE_TOOL}`, newTool)
        },

        handleSnapTool() {
            if (!this.hasTrack) return
            console.log('🌊 Snap tool activated')
            this.showSnapDialog = true
        },

        handlePoiTool() {
            const newTool = this.activeTool === 'poi' ? null : 'poi'
            console.log('📍 POI tool', newTool ? 'activated' : 'deactivated')
            this.$store.commit(`trailEditor/${trailEditorMutations.SET_ACTIVE_TOOL}`, newTool)
        },

        // POI Actions
        handleDeletePoi() {
            if (confirm('Usunąć wszystkie punkty POI?')) {
                this.$store.commit(`trailEditor/${trailEditorMutations.CLEAR_POI}`)
                console.log('🗑️ POI cleared')
            }
        },

        // Snap Actions
        async applySnap() {
            this.isSnapping = true
            this.snapProgress = 0

            try {
                // Symulacja progresu
                const interval = setInterval(() => {
                    this.snapProgress += 10
                    if (this.snapProgress >= 100) {
                        clearInterval(interval)
                        this.finishSnap()
                    }
                }, 200)

            } catch (error) {
                console.error('Snap error:', error)
                this.isSnapping = false
                this.snapProgress = null
            }
        },

        finishSnap() {
            this.$emit('snap-requested', {
                river: this.selectedRiver,
                tolerance: this.snapTolerance,
                simplify: this.autoSimplify
            })

            this.isSnapping = false
            this.snapProgress = null
            this.showSnapDialog = false

            this.$store.dispatch('ui/showSuccess', 'Trasa została przyciągnięta do rzeki')
        },

        // Actions
        handleUndo() {
            this.$store.commit(`trailEditor/${trailEditorMutations.UNDO}`)
            console.log('↩️ Undo performed')
        },

        handleRedo() {
            this.$store.commit(`trailEditor/${trailEditorMutations.REDO}`)
            console.log('↪️ Redo performed')
        },

        handleClear() {
            if (confirm('Czy na pewno chcesz wyczyścić całą trasę? Ta operacja nie może być cofnięta.')) {
                this.$store.commit(`trailEditor/${trailEditorMutations.CLEAR_TRACK}`)
                this.$store.commit(`trailEditor/${trailEditorMutations.SET_ACTIVE_TOOL}`, null)
                console.log('🗑️ Track cleared')
            }
        },

        // Save
        async handleSave() {
            try {
                await this.saveTrack()
                this.$store.dispatch('ui/showSuccess', 'Trasa została zapisana pomyślnie')
                this.$emit('save-success')
                console.log('✅ Track saved successfully')
            } catch (error) {
                this.$store.dispatch('ui/showError', 'Błąd podczas zapisywania trasy: ' + error.message)
                this.$emit('save-error', error)
                console.error('❌ Save error:', error)
            }
        }
    }
}
</script>

<style scoped>
.editor-toolbar {
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    height: 48px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.toolbar-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 12px;
    gap: 16px;
    height: 100%;
}

.toolbar-section {
    display: flex;
    align-items: center;
    gap: 8px;
    height: 100%;
}

/* Tool Buttons */
.tool-button {
    border-radius: 6px !important;
    min-width: 32px !important;
    width: 32px !important;
    height: 32px !important;
    background: rgb(var(--v-theme-surface)) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    font-size: 14px;
}

.tool-button:hover {
    background: rgba(var(--v-theme-primary), 0.05) !important;
    border-color: rgb(var(--v-theme-primary)) !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
}

.tool-button.v-btn--active {
    background: rgba(var(--v-theme-primary), 0.1) !important;
    border-color: rgb(var(--v-theme-primary)) !important;
}

.save-button {
    background: rgb(var(--v-theme-primary)) !important;
    color: white !important;
}

.save-button:disabled {
    background: rgba(var(--v-theme-primary), 0.3) !important;
}

/* Track Info - Center */
.track-info {
    flex: 1;
    justify-content: center;
    min-width: 0;
    gap: 12px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 6px;
    background: rgba(var(--v-theme-on-surface), 0.05);
    border: 1px solid rgba(var(--v-border-color), 0.1);
    height: 28px;
}

.info-label {
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    color: rgb(var(--v-theme-on-surface));
}

.status-chip {
    height: 28px;
}

/* Mobile Responsiveness */
@media (max-width: 960px) {
    .toolbar-container {
        flex-wrap: wrap;
        height: auto;
        min-height: 48px;
    }

    .track-info {
        order: 3;
        width: 100%;
        justify-content: flex-start;
        margin-top: 6px;
    }

    .editor-toolbar {
        height: auto;
        min-height: 48px;
    }
}

@media (max-width: 600px) {
    .toolbar-container {
        padding: 4px 8px;
        gap: 12px;
    }

    .toolbar-section {
        gap: 6px;
    }

    .tool-button {
        min-width: 32px !important;
        width: 32px !important;
        height: 32px !important;
    }

    .info-item {
        padding: 4px 8px;
        height: 26px;
    }

    .info-label {
        font-size: 12px;
    }
}

/* Dodatkowe style dla mniejszych przycisków */
:deep(.v-btn--size-x-small) {
    --v-btn-height: 36px;
}

:deep(.v-btn--density-comfortable) {
    height: 36px;
}
</style>
