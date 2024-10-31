<template>
    <div
        class="photo-swiper"
        @mouseover="showArrows = true"
        @mouseleave="showArrows = false"
    >
        <v-carousel
            v-model="currentSlide"
            :height="height"
            :show-arrows="false"
            :continuous="continuous"
            :interval="interval"
            :direction="direction"
            :theme="theme"
            hide-delimiter-background
            hide-delimiters
        >
            <!-- Slajdy ze zdjęciami -->
            <template v-if="images.length">
                <v-carousel-item
                    v-for="(image, index) in sortedImages"
                    :key="image.id || index"
                    @click="handleImageClick(image, index)"
                >
                    <v-img
                        :src="image.path || placeholderImage"
                        :lazy-src="image.path"
                        :alt="image.alt || 'Zdjęcie'"
                        cover
                        class="fill-height"
                    >
                        <slot
                            name="overlay"
                            :image="image"
                            :index="index"
                        ></slot>
                    </v-img>
                </v-carousel-item>
            </template>

            <!-- Placeholder dla braku zdjęć -->
            <template v-else>
                <v-carousel-item>
                    <v-img
                        :src="placeholderImage"
                        cover
                        class="fill-height"
                    >
                        <div class="d-flex flex-column align-center justify-center fill-height bg-grey-darken-3 bg-opacity-25">
                            <v-icon
                                :size="iconSize"
                                color="grey-lighten-1"
                            >
                                mdi-image-off
                            </v-icon>
                            <span class="text-grey-lighten-1 mt-2">{{ noImagesText }}</span>
                        </div>
                    </v-img>
                </v-carousel-item>
            </template>

            <!-- Przyciski nawigacji -->
            <template v-if="images.length > 1 && showArrows">
                <v-btn
                    variant="text"
                    density="comfortable"
                    icon="mdi-chevron-left"
                    class="custom-arrow left-arrow v-btn--density-default"
                    @click.stop="prevSlide"
                />
                <v-btn
                    variant="text"
                    density="comfortable"
                    icon="mdi-chevron-right"
                    class="custom-arrow right-arrow v-btn--density-default"
                    @click.stop="nextSlide"
                />
            </template>
        </v-carousel>

        <!-- Własna nawigacja punktowa -->
        <div
            v-if="showDots && images.length > 1"
            class="custom-controls"
        >
            <div
                v-for="(_, index) in images"
                :key="index"
                class="custom-dot"
                :class="getDotClass(index)"
                @click.stop="currentSlide = index"
            />
        </div>

        <!-- Slot dla dodatkowych elementów -->
        <slot
            name="additional"
            :current-image="currentImage"
        ></slot>
    </div>
</template>

<script>
import appConfig from '@/config/appConfig.js';

export default {
    name: 'PhotoSwiper',
    props: {
        images: {
            type: Array,
            default: () => []
        },
        height: {
            type: [Number, String],
            default: 200
        },
        noImagesText: {
            type: String,
            default: 'Brak dostępnych zdjęć'
        },
        continuous: {
            type: Boolean,
            default: true
        },
        interval: {
            type: Number,
            default: 0,
            validator: (v) => (true)
        },
        showDots: {
            type: Boolean,
            default: true
        },
        sortByMain: {
            type: Boolean,
            default: true
        },
        direction: {
            type: String,
            default: 'horizontal',
            validator: (value) => ['horizontal', 'vertical'].includes(value)
        },
        theme: {
            type: String,
            default: undefined
        },
        iconSize: {
            type: String,
            default: 'large'
        }
    },

    data() {
        return {
            currentSlide: 0,
            showArrows: false
        };
    },

    computed: {
        sortedImages() {
            if (!this.sortByMain) return this.images;

            return [...this.images].sort((a, b) => {
                if (a.is_main && !b.is_main) return -1;
                if (!a.is_main && b.is_main) return 1;
                return (a.order || 0) - (b.order || 0);
            });
        },

        currentImage() {
            return this.sortedImages[this.currentSlide];
        }
    },

    methods: {
        prevSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.images.length) % this.images.length;
            this.$emit('slide-change', this.currentSlide);
        },

        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.images.length;
            this.$emit('slide-change', this.currentSlide);
        },

        getDotClass(index) {
            const totalSlides = this.images.length;
            const diff = (index - this.currentSlide + totalSlides) % totalSlides;

            if (diff === 0) return 'dot-large';
            if (diff === 1 || diff === totalSlides - 1) return 'dot-small';
            if (diff === 2 || diff === totalSlides - 2) return 'dot-medium';
            if (diff === 3 || diff === totalSlides - 3) return 'dot-large';
            if (diff === 4 || diff === totalSlides - 4) return 'dot-x-small';
            return 'dot-hidden';
        },

        handleImageClick(image, index) {
            this.$emit('image-click', {image, index});
        }
    },

    watch: {
        currentSlide(newValue) {
            this.$emit('update:current-slide', newValue);
        }
    },

    emits: ['slide-change', 'image-click', 'update:current-slide']
};
</script>

<style scoped>
.photo-swiper {
    position: relative;
    width: 100%;
}

.custom-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    background-color: rgba(255, 255, 255, 0.5) !important;
    transition: opacity 0.3s ease;
}

.left-arrow {
    left: 4px;
}

.right-arrow {
    right: 4px;
}

.custom-controls {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 3px;
    z-index: 1;
}

.custom-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.5);
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

:deep(.v-window__controls) {
    display: none;
}
</style>
