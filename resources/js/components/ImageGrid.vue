<template>
    <div class="image-grid-container">
        <div class="image-grid">
            <div
                v-for="(tile, index) in sortedTiles"
                :key="index"
                class="grid-item"
                :class="[`grid-item--${tile.size}`]"
            >
                <v-img
                    :src="tile.image"
                    :lazy-src="tile.image"
                    class="grid-image"
                    :class="{'fall-in': isVisible}"
                    cover
                    :aspect-ratio="tile.aspectRatio"
                >
                    <template v-slot:placeholder>
                        <div class="placeholder-loader">
                            <v-progress-circular
                                indeterminate
                                color="white"
                            ></v-progress-circular>
                        </div>
                    </template>
                    <div class="image-overlay" />
                </v-img>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ImageGrid',
    props: {
        imageTiles: {
            type: Array,
            required: true,
            validator: (tiles) => {
                return tiles.every(tile =>
                    tile.size &&
                    tile.image &&
                    tile.aspectRatio &&
                    ['dominant', 'horizontal', 'vertical', 'small'].includes(tile.size)
                )
            }
        }
    },
    data() {
        return {
            isVisible: false
        }
    },
    computed: {
        sortedTiles() {
            return [...this.imageTiles].sort((a, b) => (a.order || 0) - (b.order || 0))
        }
    },
    mounted() {
        this.setupIntersectionObserver()
    },
    methods: {
        setupIntersectionObserver() {
            const observer = new IntersectionObserver(
                (entries) => {
                    if (entries[0].isIntersecting && !this.isVisible) {
                        this.isVisible = true
                        observer.disconnect()
                    }
                },
                { threshold: 0.2 }
            )
            observer.observe(this.$el)
        }
    }
}
</script>

<style scoped>
.image-grid-container {
    width: 100%;
    padding: 16px;
    overflow: hidden;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 16px;
    width: 100%;
}

.grid-item {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.grid-item--dominant {
    grid-column: span 12;
    grid-row: span 2;
}

.grid-item--horizontal {
    grid-column: span 8;
    grid-row: span 2;
}

.grid-item--vertical {
    grid-column: span 4;
    grid-row: span 3;
}

.grid-item--small {
    grid-column: span 4;
    grid-row: span 1;
}

.grid-image {
    width: 100%;
    height: 100%;
    opacity: 0;
    transform: translateY(-100%);
    transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.grid-image.fall-in {
    opacity: 1;
    transform: translateY(0);
}

.image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.2), transparent);
    opacity: 0;
    transition: all 0.5s ease;
}

.placeholder-loader {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.1);
}

.grid-item:hover {
    transform: scale(1.02);
}

.grid-item:hover .image-overlay {
    opacity: 1;
}

/* Sekwencyjne animacje */
.grid-item:nth-child(1) .grid-image.fall-in { transition-delay: 0ms; }
.grid-item:nth-child(2) .grid-image.fall-in { transition-delay: 200ms; }
.grid-item:nth-child(3) .grid-image.fall-in { transition-delay: 400ms; }
.grid-item:nth-child(4) .grid-image.fall-in { transition-delay: 500ms; }
.grid-item:nth-child(5) .grid-image.fall-in { transition-delay: 600ms; }
.grid-item:nth-child(6) .grid-image.fall-in { transition-delay: 700ms; }
.grid-item:nth-child(7) .grid-image.fall-in { transition-delay: 800ms; }

@media (max-width: 960px) {
    .image-grid {
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
    }

    .grid-item--dominant {
        grid-column: span 6;
    }

    .grid-item--horizontal {
        grid-column: span 6;
    }

    .grid-item--vertical {
        grid-column: span 3;
        grid-row: span 2;
    }

    .grid-item--small {
        grid-column: span 3;
    }
}
</style>

