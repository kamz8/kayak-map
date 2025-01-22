<template>
    <div class="hero-wrapper" ref="heroSection">
        <v-parallax
            src="./storage/assets/regionparalax.jpg"
            :height="parallaxHeight"
        >
            <div class="gradient-overlay"></div>
            <v-container tag="header" class="fill-height py-16">
                <!-- Nagłówek -->
                <v-row>
                    <v-col cols="12" class="text-center">
                        <h1 class="text-h3 text-sm-h2 font-weight-bold text-white mb-4 font-poppins">
                            Odkryj ponad {{ stats.totalTrails }} szlaków kajakowych
                        </h1>
                        <p class="text-h6 text-sm-h5 font-weight-regular text-white mb-16 font-inter">
                            w {{ countries.length }} krajach Europy
                        </p>
                    </v-col>
                </v-row>

                <!-- Karty krajów w slide group -->
                <div class="slider-wrapper">
                    <v-slide-group
                        v-model="selectedCard"
                        class="mx-auto"
                        :selected-class="'bg-primary'"
                        :show-arrows="!$vuetify.display.mobile"
                        center-active
                        mandatory
                        touch
                        :mobile="$vuetify.display.mobile"
                    >
                        <template v-slot:prev>
                            <v-btn
                                variant="text"
                                icon="mdi-chevron-left"
                                color="transparent"
                                size="x-large"
                                class="nav-arrow prev-arrow"
                            ></v-btn>
                        </template>

                        <template v-slot:next>
                            <v-btn
                                variant="text"
                                icon="mdi-chevron-right"
                                color="white"
                                size="x-large"
                                rounded
                                class="nav-arrow next-arrow"
                            ></v-btn>
                        </template>

                        <v-slide-group-item
                            v-for="(country, index) in countries"
                            :key="country.id"
                            v-slot="{ isSelected, toggle }"
                            :value="index"
                            ref="countrySlider"
                        >
                            <div class="ma-2 card-wrapper">
                                <CountryCard
                                    :country="country"
                                    :class="{ 'selected-card': isSelected }"
                                    @click="handleCardClick(country, toggle)"
                                />
                            </div>
                        </v-slide-group-item>
                    </v-slide-group>

                    <!-- Wskaźnik przewijania na mobile -->
                    <div v-if="$vuetify.display.smAndDown" class="text-center mt-4">
                        <div class="d-flex justify-center">
                            <span
                                v-for="(_, index) in countries"
                                :key="index"
                                class="mx-1 swipe-indicator"
                                :class="{ 'active': selectedCard === index }"
                            ></span>
                        </div>
                    </div>
                </div>
                <v-spacer class="py-1"></v-spacer>
<!--                <v-row tag="footer">
                    <v-col cols="12">
                        <p class="text-h6 text-sm-h6 text-center text-capitalize font-weight-regular text-teal-lighten-4 mb-16">
                            Kliknij w interesujący ciebie kraj i odkryj jego szlaki
                        </p>
                    </v-col>
                </v-row>-->
            </v-container>
        </v-parallax>
    </div>
</template>

<script>
import CountryCard from './CountryCard.vue'
import {useGoTo} from "vuetify";

export default {
    name: 'HeroSection',

    components: {
        CountryCard
    },

    props: {
        stats: {
            type: Object,
            required: true,
            default: () => ({
                totalTrails: 0,
                totalRegions: 0
            })
        },
        countries: {
            type: Array,
            required: true,
            default: () => []
        }
    },
    setup () {
      const goTo = useGoTo()
      return { goTo }
    },
    data() {
        return {
            selectedCard: 1,
            touchStartX: 0,
            touchEndX: 0,
            swipeThreshold: 50
        }
    },

    computed: {
        parallaxHeight() {
            return this.$vuetify.display.smAndDown ? 1000 : 800
        }
    },

    methods: {
        handleCardClick(country, toggle) {
            if (toggle) toggle()
            this.$emit('select-country', country)

          this.$nextTick(() => {
            this.goTo('#region-section', {
              duration: 300,
              offset: this.$vuetify.display.mobile ? 80 : 100,
              easing: 'easeInOutCubic'
            })
          })
        },

        handleTouchStart(event) {
            this.touchStartX = event.touches[0].clientX
        },

        handleTouchMove(event) {
            this.touchEndX = event.touches[0].clientX
        },

        handleTouchEnd() {
            const diff = this.touchStartX - this.touchEndX

            if (Math.abs(diff) > this.swipeThreshold) {
                if (diff > 0 && this.selectedCard < this.countries.length - 1) {
                    this.selectedCard++
                } else if (diff < 0 && this.selectedCard > 0) {
                    this.selectedCard--
                }
            }
        }
    }
}
</script>

<style scoped>
.hero-wrapper {
    position: relative;
    font-family: "Inter", "Poppins", sans-serif;
}

.hero-wrapper [class='h-*']{
    font-family: "Poppins", sans-serif;
}

:deep(.nav-arrow) {
    height: 64px !important;
    width: 64px !important;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 3;
    background: transparent !important;
}

:deep(.nav-arrow .v-icon) {
    font-size: 48px;
    font-weight: bold;
}

:deep(.prev-arrow) {
    left: -32px;
}

:deep(.next-arrow) {
    right: -32px;
}

.slider-wrapper {
    position: relative;
    width: calc(100% - 64px);
    margin: 0 auto;
    overflow: visible;
}

:deep(.v-slide-group) {
    overflow: visible;
}

:deep(.v-slide-group__content) {
    overflow: visible;
    justify-content: center;
    padding-top: 1em;
    transition: transform 0.3s ease-out;
}

:deep(.v-slide-group__container) {
    overflow: visible;
}

.card-wrapper {
    overflow: visible;
    touch-action: pan-y pinch-zoom;
}

.gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.7), rgba(0,0,0,0.4));
    z-index: 1;
}

:deep(.v-parallax) {
    position: relative;
}

:deep(.v-container) {
    position: relative;
    z-index: 2;
}

:deep(.v-slide-group__prev),
:deep(.v-slide-group__next) {
    margin: 0;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 3;
}

:deep(.v-slide-group__prev) {
    left: -48px;
}

:deep(.v-slide-group__next) {
    right: -48px;
}

:deep(.v-slide-group__prev-icon),
:deep(.v-slide-group__next-icon) {
    font-size: 32px;
    color: white;
}

.selected-card {
    transform: translateY(-8px);
    transition: transform 0.3s ease;
}

.swipe-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.5);
}

.swipe-indicator.active {
    background-color: white;
    width: 24px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

@media (max-width: 959.98px) {
    .slider-wrapper {
        width: 100%;
    }

    :deep(.v-slide-group),
    :deep(.v-slide-group__content),
    :deep(.v-slide-group__container),
    .slider-wrapper,
    .card-wrapper {
        overflow-x: hidden;
    }
}
</style>
