<template>
  <div class="section-links">
    <!-- Header -->
    <div class="page-header mb-6">
      <h1 class="text-h4">{{ sectionName || 'Sekcja szlaku' }}</h1>
      <p class="text-subtitle-1 text-medium-emphasis">Zarządzanie linkami</p>
    </div>

    <!-- Two Column Layout -->
    <v-row>
      <!-- Left Column - Links List -->
      <v-col cols="12" lg="8">
        <!-- Links List with Drag & Drop -->
        <div v-if="links.length > 0" ref="linksContainer" class="links-list">
          <LinkCard
            v-for="link in links"
            :key="link.id"
            :link="link"
            @delete="confirmDelete"
          />
        </div>

        <!-- Empty State -->
        <v-card v-else class="text-center pa-8" elevation="0" variant="outlined">
          <v-icon size="64" color="grey-lighten-1">mdi-link-variant-off</v-icon>
          <p class="text-h6 mt-4 mb-2">Brak linków</p>
          <p class="text-body-2 text-medium-emphasis">
            Dodaj pierwszy link do tej sekcji
          </p>
        </v-card>

        <!-- Add Link Button - Below Links -->
        <v-sheet class="mt-4 pa-4 d-flex justify-center" elevation="0" rounded>
          <v-btn
            color="primary"
            size="large"
            prepend-icon="mdi-plus"
            @click="addNewLink"
          >
            Dodaj link
          </v-btn>
        </v-sheet>
      </v-col>

      <!-- Right Column - Actions -->
      <v-col cols="12" lg="4">
        <v-card class="mb-6">
          <v-card-title>Akcje</v-card-title>
          <v-card-text>
            <div class="d-flex flex-column gap-3">
              <v-btn
                color="success"
                block
                prepend-icon="mdi-content-save"
                :loading="loading"
                @click="saveAllLinks"
              >
                Zapisz wszystkie
              </v-btn>

              <v-btn
                variant="outlined"
                block
                prepend-icon="mdi-arrow-left"
                @click="$router.back()"
              >
                Powrót
              </v-btn>
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
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDialog
      v-model="showDeleteDialog"
      title="Usuń link"
      :message="getDeleteMessage()"
      confirm-text="Usuń"
      confirm-color="error"
      @confirm="handleDelete"
      @cancel="cancelDelete"
    />

    <!-- Loading Overlay -->
    <v-overlay v-model="loading" class="align-center justify-center">
      <v-progress-circular indeterminate size="64" />
    </v-overlay>
  </div>
</template>

<script>
import { useSortable } from '@vueuse/integrations/useSortable'
import LinkCard from '../components/LinkCard.vue'
import ConfirmDialog from '@/dashboard/components/ui/ConfirmDialog.vue'
import apiClient from '@/dashboard/plugins/axios'
import { mapActions } from 'vuex'

export default {
  name: 'SectionLinks',

  components: {
    LinkCard,
    ConfirmDialog
  },

  data() {
    return {
      links: [],
      sectionName: '',
      loading: false,
      showDeleteDialog: false,
      linkToDelete: null
    }
  },

  mounted() {
    this.fetchLinks()
    this.initDragAndDrop()
  },

  methods: {
    ...mapActions('ui', ['showSuccess', 'showError']),

    initDragAndDrop() {
      this.$nextTick(() => {
        if (this.$refs.linksContainer) {
          useSortable(this.$refs.linksContainer, this.links, {
            animation: 300,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onUpdate: (e) => {
              console.log('Link reordered:', e)
              // Note: Order persistence will be implemented when backend adds 'order' column
            }
          })
        }
      })
    },

    async fetchLinks() {
      this.loading = true
      try {
        const response = await apiClient.get(
          `/dashboard/trails/${this.$route.params.id}/sections/${this.$route.params.sectionId}/links`
        )
        this.links = response.data.data || response.data

        // Get section name
        if (response.data.section) {
          this.sectionName = response.data.section.name
        }
      } catch (error) {
        console.error('Failed to fetch links:', error)
        this.showError('Błąd podczas pobierania linków')
      } finally {
        this.loading = false
      }
    },

    addNewLink() {
      const newLink = {
        id: `temp-${Date.now()}`,
        url: '',
        meta_data: JSON.stringify({
          title: '',
          description: '',
          icon: ''
        }),
        isNew: true
      }
      this.links.push(newLink)

      // Reinitialize drag & drop after adding new link
      this.$nextTick(() => {
        this.initDragAndDrop()
      })
    },

    async saveAllLinks() {
      this.loading = true
      const savePromises = []

      for (const link of this.links) {
        if (link.isNew) {
          savePromises.push(this.createLink(link))
        } else if (link.modified) {
          savePromises.push(this.updateLink(link))
        }
      }

      try {
        await Promise.all(savePromises)
        this.showSuccess('Wszystkie linki zostały zapisane')
        await this.fetchLinks()
      } catch (error) {
        console.error('Failed to save links:', error)
        this.showError('Błąd podczas zapisywania linków')
      } finally {
        this.loading = false
      }
    },

    async createLink(link) {
      const response = await apiClient.post(
        `/dashboard/trails/${this.$route.params.id}/sections/${this.$route.params.sectionId}/links`,
        {
          url: link.url,
          meta_data: link.meta_data
        }
      )
      return response.data
    },

    async updateLink(link) {
      const response = await apiClient.put(
        `/dashboard/trails/${this.$route.params.id}/sections/${this.$route.params.sectionId}/links/${link.id}`,
        {
          url: link.url,
          meta_data: link.meta_data
        }
      )
      return response.data
    },

    confirmDelete(link) {
      this.linkToDelete = link
      this.showDeleteDialog = true
    },

    getDeleteMessage() {
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
    },

    async handleDelete() {
      if (!this.linkToDelete) return

      this.loading = true
      try {
        if (!this.linkToDelete.isNew) {
          await apiClient.delete(
            `/dashboard/trails/${this.$route.params.id}/sections/${this.$route.params.sectionId}/links/${this.linkToDelete.id}`
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
.section-links {
  width: 100%;
  padding: 24px;
}

.page-header {
  margin-bottom: 32px;
}

.links-list {
  margin-bottom: 24px;
}
</style>
