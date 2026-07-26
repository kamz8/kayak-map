<template>
    <div class="trail-map-editor">
        <!-- Map Editor Component -->
        <TrailMapEditorComponent
            :trail-id="trailId"
            @save-success="handleSaveSuccess"
            @save-error="handleSaveError"
            @back="handleBack"
        />

        <!-- Unsaved Changes Dialog -->
        <UnsavedChangesDialog
            v-model="showUnsavedDialog"
            @confirm="handleLeaveConfirm"
            @cancel="handleLeaveCancel"
        />
    </div>
</template>

<script>
import TrailMapEditorComponent from '../editor/components/TrailMapEditorComponent.vue'
import UnsavedChangesDialog from '../editor/components/UnsavedChangesDialog.vue'

export default {
    name: 'TrailMapEditor',
    components: {
        TrailMapEditorComponent,
        UnsavedChangesDialog
    },

    data() {
        return {
            showUnsavedDialog: false,
            pendingNavigation: null
        }
    },

    computed: {
        trailId() {
            return this.$route.params.id
        }
    },

    beforeRouteLeave(to, from, next) {
        // Check if there are unsaved changes in the editor
        const hasUnsavedChanges = this.$store.state.trailEditor?.unsavedChanges

        if (hasUnsavedChanges) {
            this.showUnsavedDialog = true
            this.pendingNavigation = next
        } else {
            next()
        }
    },

    mounted() {
        // Add browser refresh warning
        window.addEventListener('beforeunload', this.handleBeforeUnload)
    },

    beforeUnmount() {
        // Remove browser refresh warning
        window.removeEventListener('beforeunload', this.handleBeforeUnload)
    },

    methods: {
        handleBack() {
            this.$router.push({
                name: 'DashboardTrailsEdit',
                params: { id: this.trailId }
            })
        },

        handleSaveSuccess() {
            console.log('✅ Save successful from TrailMapEditor')
            this.$store.dispatch('ui/showMessage', {
                type: 'success',
                message: 'Szlak został zapisany pomyślnie'
            })
        },

        handleSaveError(error) {
            console.error('❌ Save error from TrailMapEditor:', error)
            this.$store.dispatch('ui/showMessage', {
                type: 'error',
                message: 'Błąd podczas zapisywania szlaku: ' + error.message
            })
        },

        handleBeforeUnload(e) {
            const hasUnsavedChanges = this.$store.state.trailEditor?.unsavedChanges
            if (hasUnsavedChanges) {
                e.preventDefault()
                e.returnValue = ''
            }
        },

        handleLeaveConfirm() {
            this.showUnsavedDialog = false
            if (this.pendingNavigation) {
                this.pendingNavigation()
                this.pendingNavigation = null
            }
        },

        handleLeaveCancel() {
            this.showUnsavedDialog = false
            if (this.pendingNavigation) {
                this.pendingNavigation(false)
                this.pendingNavigation = null
            }
        }
    }
}
</script>

<style scoped>
.trail-map-editor {
    width: 100%;
    height: calc(100vh - 164px);
    overflow: hidden;
    position: relative;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .trail-map-editor {
        height: calc(100vh - 64px);
    }
}
</style>
