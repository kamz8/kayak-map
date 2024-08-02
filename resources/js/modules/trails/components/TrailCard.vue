<template>
    <v-card class="mx-auto my-4" max-width="344" outlined>
        <div class="carousel-container" @mouseover="showArrows = true" @mouseleave="showArrows = false">
            <v-carousel
                v-model="currentSlide"
                height="200"
                hide-delimiter-background
                :show-arrows="false"
                hide-delimiters
            >
                <template v-if="sortedImages.length">
                <v-carousel-item
                    v-for="(image, index) in sortedImages"
                    :key="image.id"
                    class="white--text align-end"
                >
                    <v-img :src="(image) ?image.path : appConfig.placeholderImage" cover eager alt="Główne zdjęcie dla trasy"  :lazy-src="image.path">

                    </v-img>
                </v-carousel-item>
                </template>
                <template v-else>

                    <v-carousel-item
                        :src="appConfig.placeholderImage"
                        class="align-end"
                        cover
                    >
                        <div class="no-images text-grey-lighten-3 overlay">
                            <v-icon size="large">mdi-image-off</v-icon>
                            <span>Brak dostępnych zdjęć</span>
                        </div>
                    </v-carousel-item>
                </template>
                <template  v-if="sortedImages.length">
                    <v-btn
                        v-if="showArrows"
                        icon
                        small
                        density="comfortable"
                        class="custom-arrow left-arrow"
                        @click="prevSlide"
                    >
                        <v-icon>mdi-chevron-left</v-icon>
                    </v-btn>
                    <v-btn
                        v-if="showArrows"
                        icon
                        density="comfortable"
                        small
                        class="custom-arrow right-arrow"
                        @click="nextSlide"
                    >
                        <v-icon>mdi-chevron-right</v-icon>
                    </v-btn>
                </template>

            </v-carousel>

            <div class="custom-controls">
                <div
                    v-for="(_, index) in sortedImages"
                    :key="index"
                    class="custom-dot"
                    :class="getDotClass(index)"
                    @click="currentSlide = index"
                ></div>
            </div>
        </div>

        <v-card-title>{{ trail.trail_name }}</v-card-title>
        <v-card-subtitle>
            <v-rating v-model="trail.rating" readonly size="xs" density="compact"></v-rating>
        </v-card-subtitle>
        <v-card-subtitle>{{ formatAvgDuration(trail.trail_length) }} | {{ formatTrailLength(trail.trail_length) }} | {{ trail.difficulty }} |  <span v-tooltip:bottom="'Ocena malowniczości trasy'"><v-icon color="footer" size="xs" icon="mdi-forest" /> {{ trail.scenery }}</span>

        </v-card-subtitle>
        <v-card-subtitle>Rzeka {{ trail.river_name }}</v-card-subtitle>
<!--        <v-card-actions>
            <v-btn icon>
                <v-icon>mdi-star</v-icon>
            </v-btn>
            <v-btn icon>
                <v-icon>mdi-bookmark</v-icon>
            </v-btn>
        </v-card-actions>-->
        <span class="px-2"></span>
    </v-card>
</template>

<script>

import appConfig from "@/config/appConfig.js";

export default {
    name: 'TrailCard',
    props: {
        trail: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            currentSlide: 0,
            showArrows: false
        };
    },
    computed: {
        appConfig() {
            return appConfig
        },
        sortedImages() {
            return [...this.trail.images].sort((a, b) => {
                if (a.is_main && !b.is_main) return -1;
                if (!a.is_main && b.is_main) return 1;
                return a.order - b.order;
            });
        }
    },
    methods: {
        formatTrailLength(length) {
            return `${(length / 1000).toFixed(1)} km`;
        },
        getDotClass(index) {
            const totalSlides = this.sortedImages.length;
            const diff = (index - this.currentSlide + totalSlides) % totalSlides;
            if (diff === 0) return 'dot-large';
            if (diff === 1 || diff === totalSlides - 1) return 'dot-small';
            if (diff === 2 || diff === totalSlides - 2) return 'dot-medium';
            if (diff === 3 || diff === totalSlides - 3) return 'dot-large';
            if (diff === 4 || diff === totalSlides - 4) return 'dot-x-small';
            return 'dot-hidden';
        },
        prevSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.sortedImages.length) % this.sortedImages.length;
        },
        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.sortedImages.length;
        }
    }
};
</script>

<style scoped>
.carousel-container {
    position: relative;
}

.custom-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    background-color: #ffffff !important;
    opacity: 0.5;
    transition: opacity 0.8s ease;
}

.left-arrow {
    left: 5px;
}

.right-arrow {
    right: 5px;
}

.custom-controls {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    justify-content: center;
    align-items: center;
}

.custom-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.5);
    margin: 0 3px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.dot-large {
    transform: scale(1.5);
    background-color: white;
}

.dot-medium {
    transform: scale(1.25);
}

.dot-small {
    transform: scale(1);
}

.dot-x-small {
    transform: scale(0.5);
}

.dot-hidden {
    opacity: 0;
    width: 0;
    margin: 0;
}
.no-images {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
}

.no-images v-icon {
    margin-bottom: 8px;
}
</style>
