<template>
    <div class="trail-map-editor">
        <!-- Editor Toolbar -->
        <EditorToolbar
            @save-success="handleSaveSuccess"
            @save-error="handleSaveError"
        />

        <!-- Map Canvas -->
        <MapCanvas />

        <!-- Unsaved Changes Dialog -->
        <UnsavedChangesDialog
            v-model="showUnsavedDialog"
            @confirm="confirmNavigation"
            @cancel="cancelNavigation"
        />
    </div>
</template>

<script>
import EditorToolbar from './EditorToolbar.vue'
import MapCanvas from './MapCanvas.vue'
import UnsavedChangesDialog from './UnsavedChangesDialog.vue'

export default {
    name: 'MapEditor',
    components: {
        EditorToolbar,
        MapCanvas,
        UnsavedChangesDialog
    },

    data() {
        return {
            showUnsavedDialog: false,
            pendingNavigation: null
        }
    },

    mounted() {
        this.initializeEditor()
        this.setupNavigationGuard()
    },

    beforeUnmount() {
        this.cleanup()
    },

    methods: {
        initializeEditor() {
            console.log('🗺️ Trail Map Editor initialized')
        },

        setupNavigationGuard() {
            this.$router.beforeEach((to, from, next) => {
                if (from.name === this.$route.name && this.$store.state.trailEditor.unsavedChanges) {
                    this.showUnsavedDialog = true
                    this.pendingNavigation = next
                    return false // Prevent navigation
                }
                next()
            })
        },

        handleSaveSuccess() {
            console.log('✅ Trail saved successfully')
            this.$notify('Szlak został zapisany pomyślnie', 'success')
        },

        handleSaveError(error) {
            console.error('❌ Save error:', error)
            this.$notify('Błąd podczas zapisywania szlaku: ' + error.message, 'error')
        },

        confirmNavigation() {
            this.showUnsavedDialog = false
            if (this.pendingNavigation) {
                this.pendingNavigation()
                this.pendingNavigation = null
            }
        },

        cancelNavigation() {
            this.showUnsavedDialog = false
            this.pendingNavigation = null
        },

        cleanup() {
            this.$store.dispatch('trailEditor/clearEditor')
            console.log('🧹 Trail Map Editor cleanup complete')
        }
    }
}
</script>

<style scoped>
.trail-map-editor {
    height: 100vh;
    display: flex;
    flex-direction: column;
    background: rgb(var(--v-theme-background));
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .trail-map-editor {
        height: calc(100vh - 64px);
    }
}
</style>
