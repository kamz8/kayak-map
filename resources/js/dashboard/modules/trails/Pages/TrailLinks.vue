<template>
    <div class="trail-links">
        <!-- Header -->
        <div class="page-header mb-6">
            <h1 class="ui-heading">{{ trailName || 'Szlak' }}</h1>
            <p class="text-subtitle-1 text-medium-emphasis">Zarządzanie linkami</p>

        </div>

        <!-- Two Column Layout -->
        <v-row>
            <!-- Left Column - Links List -->
            <v-col cols="12" lg="8">
                <!-- Links List with Drag & Drop -->
                <div v-if="links.length > 0" ref="linksContainer" class="links-list">
                    <LinkCard
                        v-for="(link, index) in links"
                        :key="link.id"
                        :link="link"
                        @update:link="updateLink(index, $event)"
                        @delete="confirmDelete"
                    />
                </div>

                <!-- Empty State -->
                <v-card v-else class="text-center pa-8" elevation="0" variant="outlined">
                    <v-icon size="64" color="grey-lighten-1">mdi-link-variant-off</v-icon>
                    <p class="text-h6 mt-4 mb-2">Brak linków</p>
                    <p class="text-body-2 text-medium-emphasis">
                        Dodaj pierwszy link do tego szlaku
                    </p>
                </v-card>

                <!-- Add Link Button -->
                <v-sheet class="mt-4 pa-4 d-flex justify-center" elevation="0" rounded>
                    <ui-button
                        color="primary"
                        prepend-icon="mdi-plus"
                        @click="addNewLink"
                        :disabled="links.length >= maxLinks"
                        :title="links.length >= maxLinks ? `Maksymalnie ${maxLinks} linków` : ''"
                    >
                        Dodaj link
                        <template #append>
                            <v-chip
                                size="small"
                                variant="outlined"
                                class="ml-2"
                            >
                                {{ links.length }}/{{ maxLinks }}
                            </v-chip>
                        </template>
                    </ui-button>
                </v-sheet>
            </v-col>

            <!-- Right Column - Actions -->
            <v-col cols="12" lg="4">
                <v-card class="mb-6">
                    <v-card-title>Akcje</v-card-title>
                    <v-card-text>
                        <div class="d-flex flex-column gap-3">
                            <ui-button
                                color="success"
                                block
                                prepend-icon="mdi-content-save"
                                :loading="loading"
                                @click="saveAllLinks"
                                :disabled="links.length === 0"
                            >
                                Zapisz wszystkie
                            </ui-button>

                            <ui-button
                                variant="outlined"
                                block
                                prepend-icon="mdi-arrow-left"
                                @click="$router.back()"
                            >
                                Powrót
                            </ui-button>
                        </div>
                    </v-card-text>
                </v-card>

                <!-- Info Card -->
                <v-card>
                    <v-card-title>Informacje</v-card-title>
                    <v-card-text>
                        <div class="text-body-2">
                            <p class="mb-2">
                                <v-icon size="small" class="mr-1">mdi-drag-vertical</v-icon>
                                Przeciągnij aby zmienić kolejność
                            </p>
                            <p>
                                <v-icon size="small" class="mr-1">mdi-information</v-icon>
                                Wybierz ikonę dla popularnych serwisów
                            </p>
                            <p class="mb-0">
                                <v-icon size="small" class="mr-1">mdi-link</v-icon>
                                Maksymalnie {{ maxLinks }} linków na szlak
                            </p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            v-model="showDeleteDialog"
            title="Usuń link"
            :message="deleteMessage"
            confirm-text="Usuń"
            confirm-color="error"
            @confirm="handleDelete"
            @cancel="cancelDelete"
        />

        <!-- Loading Overlay -->
        <v-overlay v-model="loading" class="align-center justify-center">
            <v-progress-circular indeterminate size="64"/>
        </v-overlay>
    </div>
</template>

<script>
import draggable from 'vuedraggable'
import LinkCard from '../components/LinkCard.vue'
import ConfirmDialog from '@/dashboard/components/ui/ConfirmDialog.vue'
import apiClient from '@/dashboard/plugins/axios'
import { mapActions } from 'vuex'
import UiButton from "@ui/UiButton.vue"
import { useBreadcrumbs } from '@/dashboard/composables/useBreadcrumbs'

export default {
    name: 'TrailLinks',

    components: {
        UiButton,
        LinkCard,
        ConfirmDialog,
        draggable
    },

    setup() {
        const { updateBreadcrumbByKey } = useBreadcrumbs()
        return { updateBreadcrumbByKey }
    },

    data() {
        return {
            links: [],
            trailName: '',
            loading: false,
            showDeleteDialog: false,
            linkToDelete: null,
            maxLinks: 10,
            dragAndDropInitialized: false
        }
    },

    computed: {
        deleteMessage() {
            if (!this.linkToDelete) return 'Czy na pewno chcesz usunąć ten link?'

            try {
                const metaData = typeof this.linkToDelete.meta_data === 'string'
                    ? JSON.parse(this.linkToDelete.meta_data)
                    : this.linkToDelete.meta_data

                const title = metaData?.title || 'ten link'
                return `Czy na pewno chcesz usunąć link: ${title}?`
            } catch {
                return 'Czy na pewno chcesz usunąć ten link?'
            }
        }
    },

    mounted() {
        this.fetchLinks()
    },

    methods: {
        ...mapActions('ui', ['showSuccess', 'showError', 'showInfo', 'showWarning']),

        parseMetaData(metaData) {
            if (typeof metaData === 'object' && metaData !== null) {
                return metaData
            }

            if (typeof metaData === 'string') {
                try {
                    return JSON.parse(metaData)
                } catch (e) {
                    console.error('Failed to parse meta_data:', e)
                }
            }

            return { title: '', description: '', icon: '' }
        },

        formatLinkForBackend(link) {
            return {
                ...link,
                meta_data: typeof link.meta_data === 'string'
                    ? link.meta_data
                    : JSON.stringify(link.meta_data)
            }
        },

        async fetchLinks() {
            this.loading = true
            try {
                const response = await apiClient.get(
                    `/dashboard/trails/${this.$route.params.id}/links`
                )

                this.links = (response.data.data || response.data).map(link => ({
                    ...link,
                    meta_data: this.parseMetaData(link.meta_data)
                }))

                if (response.data.trail) {
                    this.trailName = response.data.trail.trail_name

                    // Update only the trail breadcrumb item
                    this.updateBreadcrumbByKey('trail', {
                        text: response.data.trail.trail_name,
                        to: `/dashboard/trails/${this.$route.params.id}/edit`
                    })
                }
            } catch (error) {
                console.error('Failed to fetch links:', error)
                this.showError('Błąd podczas pobierania linków')
            } finally {
                this.loading = false
            }
        },

        addNewLink() {
            if (this.links.length >= this.maxLinks) {
                this.showWarning(`Nie można dodać więcej niż ${this.maxLinks} linków`)
                return
            }

            const newLink = {
                id: `temp-${Date.now()}`,
                url: 'https://',
                meta_data: JSON.stringify({
                    title: '',
                    description: '',
                    icon: 'mdi-web'
                }),
                isNew: true,
                modified: true
            }

            this.links.push(newLink)
        },

        updateLink(index, updatedLink) {
            const linkWithModification = {
                ...updatedLink,
                modified: !updatedLink.isNew
            }
            this.links.splice(index, 1, linkWithModification)
        },

        async saveAllLinks() {
            const linksToSave = this.links.filter(link => {
                if (link.isNew) return true
                return link.modified === true
            })

            if (linksToSave.length === 0) {
                this.showSuccess('Brak zmian do zapisania')
                return
            }

            const invalidLinks = linksToSave.filter(link => {
                if (!link.url || link.url === 'https://') {
                    return true
                }

                try {
                    const metaData = typeof link.meta_data === 'string'
                        ? JSON.parse(link.meta_data)
                        : link.meta_data
                    return !metaData.title
                } catch {
                    return true
                }
            })

            if (invalidLinks.length > 0) {
                this.showError('Uzupełnij URL i tytuł we wszystkich linkach')
                return
            }

            this.loading = true
            const savePromises = []

            for (const link of linksToSave) {
                if (link.isNew) {
                    savePromises.push(this.createLink(link))
                } else {
                    savePromises.push(this.updateLinkRequest(link))
                }
            }

            try {
                await Promise.all(savePromises)
                this.showSuccess(`Zapisano ${linksToSave.length} linków`)
                await this.fetchLinks()
            } catch (error) {
                console.error('Failed to save links:', error)
                this.showError('Błąd podczas zapisywania linków')
            } finally {
                this.loading = false
            }
        },

        async createLink(link) {
            const formattedLink = this.formatLinkForBackend(link)
            const response = await apiClient.post(
                `/dashboard/trails/${this.$route.params.id}/links`,
                formattedLink
            )
            return response.data
        },

        async updateLinkRequest(link) {
            const formattedLink = this.formatLinkForBackend(link)
            const response = await apiClient.put(
                `/dashboard/trails/${this.$route.params.id}/links/${link.id}`,
                formattedLink
            )
            return response.data
        },

        confirmDelete(link) {
            this.linkToDelete = link
            this.showDeleteDialog = true
        },

        async handleDelete() {
            if (!this.linkToDelete) return

            this.loading = true
            try {
                if (!this.linkToDelete.isNew) {
                    await apiClient.delete(
                        `/dashboard/trails/${this.$route.params.id}/links/${this.linkToDelete.id}`
                    )
                }

                this.links = this.links.filter(l => l.id !== this.linkToDelete.id)
                this.showSuccess('Link został usunięty')
            } catch (error) {
                console.error('Failed to delete link:', error)
                this.showError('Błąd podczas usuwania linku')
            } finally {
                this.loading = false
                this.showDeleteDialog = false
                this.linkToDelete = null
            }
        },

        cancelDelete() {
            this.showDeleteDialog = false
            this.linkToDelete = null
        }
    }
}
</script>

<style scoped>
.trail-links {
    width: 100%;
    padding: 24px;
}

.page-header {
    margin-bottom: 32px;
}

.links-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}
</style>
