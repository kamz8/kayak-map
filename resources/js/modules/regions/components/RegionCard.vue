<template>
    <v-card
        rounded="xl"
        class="h-100 region-card d-flex flex-column"
        elevation="2"
    >
        <v-img
            :src="region.image"
            cover
            class="region-card-img"
            :alt="'Zdjęcie główne pokzaujące region:'+region.name"
            lazy-src="/storage/assets/region-placeholder.webp"
        >
            <template v-slot:placeholder>
                <v-row
                    class="fill-height ma-0 region-card-img"
                    align="center"
                    justify="center"


                >
                    <v-progress-circular
                        indeterminate
                        color="grey-lighten-5"
                    />
                </v-row>
            </template>

            <!-- Metoda 1: Używając analizy obrazu -->
            <v-chip
                v-if="useImageAnalysis"
                class="region-type-chip ma-4"
                :color="getTypeColor"
                :class="{ 'light-image': isLightImage }"
                size="small"
                label
                elevation="2"
            >
                <v-icon start size="small">
                    {{ getTypeIcon }}
                </v-icon>
                {{ getTypeLabel }}
            </v-chip>

            <!-- Metoda 2: Używając CSS mix-blend-mode -->
            <div v-else class="chip-wrapper">
                <v-chip
                    class="region-type-chip ma-4"
                    :color="getTypeColor"
                    size="small"
                    label
                    elevation="2"
                >
                    <v-icon start size="small">
                        {{ getTypeIcon }}
                    </v-icon>
                    {{ getTypeLabel }}
                </v-chip>
            </div>
        </v-img>

        <v-card-text class="flex-grow-1 d-flex flex-column pt-4">
            <h3 class="text-h6 font-weight-bold mb-4">{{ region.name }}</h3>

            <!-- Informacja o szlakach -->
            <div class="mt-auto">
                <v-icon size="small" color="primary" class="mr-1">
                    mdi-map-marker-path
                </v-icon>
                <span class="text-body-2">{{ region.trailsCount }} szlaków</span>
            </div>
        </v-card-text>

        <!-- Sekcja akcji -->
        <v-card-actions class="pa-4 pt-0">
            <v-spacer></v-spacer>
            <v-btn
                variant="text"
                color="primary"
                class="px-0"
                append-icon="mdi-arrow-right"
                @click="viewDetails"
            >
                Zobacz więcej
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
export default {
    name: 'RegionCard',

    props: {
        region: {
            type: Object,
            required: true
        },
        outlined: {
            type: Boolean,
            default: false
        },
        useImageAnalysis: {
            type: Boolean,
            default: false // Domyślnie używamy CSS mix-blend-mode
        }
    },
    data() {
        return {
            isLightImage: false
        }
    },
    computed: {
        getTypeIcon() {
            const typeIcons = {
                'geographic_area': 'mdi-pine-tree',
                'state': 'mdi-map',
                'city': 'mdi-city',
                'country': 'mdi-earth'
            }
            return typeIcons[this.region.type] || 'mdi-help-circle'
        },

        getTypeLabel() {
            const typeLabels = {
                'geographic_area': 'Park Narodowy',
                'state': 'Województwo',
                'city': 'Miasto',
                'country': 'Kraj'
            }
            return typeLabels[this.region.type] || 'Inny'
        },

        getTypeColor() {
            const typeColors = {
                'geographic_area': 'success', // zielony
                'state': 'primary',          // niebieski
                'city': 'secondary',         // jasny niebieski
                'country': 'river-blue'      // ciemny niebieski
            }
            return typeColors[this.region.type] || 'info'
        }
    },

    methods: {
        onImageLoad(e) {
            if (this.useImageAnalysis) {
                this.analyzeImageBrightness(e.target)
            }
        },

        analyzeImageBrightness(imgElement) {
            const canvas = document.createElement('canvas')
            const ctx = canvas.getContext('2d')
            canvas.width = imgElement.width
            canvas.height = imgElement.height

            ctx.drawImage(imgElement, 0, 0)

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)
            const data = imageData.data

            let brightness = 0

            // Analizujemy tylko część obrazu gdzie znajduje się chip
            const topArea = Math.floor(canvas.height * 0.2) // Górne 20% obrazu
            const samplesCount = canvas.width * topArea * 4

            for (let i = 0; i < samplesCount; i += 4) {
                const r = data[i]
                const g = data[i + 1]
                const b = data[i + 2]
                // Formuła na jasność: https://www.w3.org/TR/AERT/#color-contrast
                brightness += (r * 299 + g * 587 + b * 114) / 1000
            }

            const averageBrightness = brightness / (samplesCount / 4)
            this.isLightImage = averageBrightness > 128
        },
        viewDetails() {
            this.$router.push(`/regions/${this.region.id}`)
        }
    }
}
</script>

<style scoped>
.region-card {
    display: flex;
    flex-direction: column;
}

:deep(.v-card-text) {
    display: flex;
    flex-direction: column;
}

.region-type-chip {
    position: absolute !important;
    right: 0;
    top: 0;
    font-weight: 600 !important;
    background: #00000082 !important;
}
.region-card-img{
    height: 200px;
    max-height: 200px
}

/* Hover efekt dla chipów */
.region-type-chip:hover {
    opacity: 0.95;
}

/* Metoda 1: Style dla analizy obrazu */
.region-type-chip {
    position: absolute !important;
    right: 0;
    top: 0;
    font-weight: 600 !important;
}

.region-type-chip.light-image {
    background-color: rgba(255, 255, 255, 0.9) !important;
    color: rgba(0, 0, 0, 0.87) !important;
}

/* Metoda 2: Style dla mix-blend-mode */
.chip-wrapper {
    position: relative;
    mix-blend-mode: difference;
}

:deep(.v-card-text) {
    display: flex;
    flex-direction: column;
}

.region-type-chip:hover {
    opacity: 0.95;
}
</style>
