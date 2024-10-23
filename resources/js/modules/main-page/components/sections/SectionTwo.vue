<template>
  <section class="section-two">
    <v-container class="py-16">
      <v-row align="start">
        <v-col cols="12" md="6">
          <!-- Tytuł -->
          <h2 class="custom-title text-white mb-16">
            <span class="title-line">Odkrywaj Sprawdzone Szlaki</span>
            <span class="title-line pl-5"></span>
          </h2>

          <!-- Lista benefitów -->
          <div class="benefits-list mb-16">
            <div v-for="(benefit, i) in benefits"
                 :key="i"
                 class="benefit-item d-flex align-center mb-8"
                 :class="{'slide-in': isVisible}"
                 :style="{ animationDelay: `${i * 200}ms` }"
            >
              <div class="benefit-icon-wrapper mr-6">
                <v-avatar
                    :color="benefit.color"
                    size="64"
                    class="elevation-3 benefit-avatar"
                >
                  <v-icon
                      color="white"
                      :icon="benefit.icon"
                      size="32"
                      class="benefit-icon"
                  />
                </v-avatar>
                <div class="benefit-ripple"></div>
              </div>
              <div class="benefit-content">
                <div class="text-h5 font-weight-bold mb-1 benefit-title">
                  {{ benefit.title }}
                </div>
                <div class="text-body-1 text-white-darken-1">
                  {{ benefit.description }}
                </div>
              </div>
            </div>
          </div>

          <!-- Statystyki -->
          <v-card
              class="stats-card pa-8 mb-8"
              elevation="0"
              :class="{'fade-in': isVisible}"
          >
            <v-row justify="space-around" align="center">
              <v-col
                  v-for="(stat, i) in statistics"
                  :key="i"
                  cols="4"
                  class="text-center"
              >
                <div class="stat-wrapper">
                  <div class="stat-icon-wrapper">
                    <v-icon
                        :icon="stat.icon"
                        :color="stat.iconColor"
                        size="36"
                        class="mb-4 stat-icon"
                        :class="{'bounce-in': isVisible}"
                    />
                    <div class="stat-highlight"></div>
                  </div>
                  <div class="slot-machine-wrapper">
                    <div
                        class="digit-wrapper"
                        v-for="(digit, index) in getDigits(stat.currentValue)"
                        :key="`${i}-${index}`"
                    >
                      <div
                          class="digit-column"
                          :style="{
                                                    transform: `translateY(-${digit * 10}%)`,
                                                    transitionDelay: `${index * 100}ms`
                                                }"
                      >
                        <div
                            v-for="n in 10"
                            :key="n"
                            class="digit"
                        >{{ n - 1 }}
                        </div>
                      </div>
                    </div>
                    <span class="suffix">{{ stat.suffix }}</span>
                  </div>
                  <div class="text-subtitle-1 mt-2">{{ stat.label }}</div>
                </div>
              </v-col>
            </v-row>
          </v-card>
        </v-col>

        <!-- Prawa kolumna z gridem zdjęć -->
        <v-col cols="12" md="6" class="d-flex justify-center align-start">
          <ImageGrid :image-tiles="exploreTiles"/>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>

import ImageGrid from "@/components/ImageGrid.vue";

export default {
  name: 'SectionTwo',
  components: {ImageGrid},
  data() {
    return {
      isVisible: false,
      benefits: [
        {
          icon: 'mdi-check-decagram',
          title: 'Zweryfikowane Trasy',
          description: 'Każda trasa jest sprawdzona i dokładnie opisana przez nasz zespół.',
          color: '#FFD700'
        },
        {
          icon: 'mdi-compass',
          title: 'Precyzyjna Nawigacja',
          description: 'Dokładne mapy i wskazówki dla każdego odcinka trasy.',
          color: '#32CD32'
        },
        {
          icon: 'mdi-map-marker-radius',
          title: 'Lokalne Atrakcje',
          description: 'Poznaj ciekawe miejsca i atrakcje w pobliżu szlaku.',
          color: '#4682B4'
        }
      ],
      statistics: [
        {
          value: 165,
          currentValue: 0,
          label: 'Sprawdzonych Tras',
          suffix: '+',
          icon: 'mdi-map-check',
          iconColor: '#32CD32'
        },
        {
          value: 3,
          currentValue: 0,
          label: 'Kraje',
          suffix: '',
          icon: 'mdi-earth',
          iconColor: '#4682B4'
        },
        {
          value: 75,
          currentValue: 0,
          label: 'Krain Geograficznych',
          suffix: '+',
          icon: 'mdi-terrain',
          iconColor: '#FFD700'
        }
      ],
      exploreTiles: [
        {
          size: 'dominant',
          image: '/storage/assets/explore/explore-1.jpg',
          aspectRatio: 16 / 9,
          order: 1
        },
        {
          size: 'small',
          image: '/storage/assets/explore/explore-2.jpg',
          aspectRatio: 16 / 9,
          order: 5
        },
        {
          size: 'vertical',
          image: '/storage/assets/explore/explore-7.jpg',
          aspectRatio: 2 / 3,
          order: 3
        },
        {
          size: 'small',
          image: '/storage/assets/explore/explore-4.jpg',
          aspectRatio: 4 / 3,
          order: 4
        },
        {
          size: 'horizontal',
          image: '/storage/assets/explore/explore-5.jpg',
          aspectRatio: 4 / 3,
          order: 2
        },
        {
          size: 'small',
          image: '/storage/assets/explore/explore-6.jpg',
          aspectRatio: 4 / 3,
          order: 6
        },
        {
          size: 'small',
          image: '/storage/assets/explore/explore-3.jpg',
          aspectRatio: 4 / 3,
          order: 7
        }
      ]

    }
  },
  mounted() {
    this.setupIntersectionObserver()
  },
  methods: {
    getDigits(number) {
      return String(number).padStart(3, '0').split('')
    },
    setupIntersectionObserver() {
      const observer = new IntersectionObserver(
          (entries) => {
            if (entries[0].isIntersecting && !this.isVisible) {
              this.isVisible = true
              this.animateNumbers()
              observer.disconnect()
            }
          },
          {threshold: 0.2}
      )
      observer.observe(this.$el)
    },
    animateNumbers() {
      this.statistics.forEach((stat) => {
        const duration = 1000
        const steps = 60
        const increment = stat.value / steps
        let current = 0

        const interval = setInterval(() => {
          if (current >= stat.value) {
            stat.currentValue = stat.value
            clearInterval(interval)
          } else {
            current += increment
            stat.currentValue = Math.round(current)
          }
        }, duration / steps)
      })
    }
  }
}
</script>

<style scoped>
.section-two {
  background-color: var(--sky-blue);
  color: white;
  overflow: hidden;
}

.custom-title {
  font-size: clamp(2rem, 5vw, 3.75rem);
  font-weight: bold;
  line-height: 1.2;
  margin: 0;
  padding: 0;
}

.title-line {
  display: block;
  overflow: visible;
  position: relative;
}

.title-line:after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: white;
  transition: width 0.5s ease;
}

.custom-title:hover .title-line:after {
  width: 100%;
}

.stats-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.benefit-icon-wrapper {
  position: relative;
  cursor: pointer;
}

.benefit-ripple {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: transparent;
  transition: all 0.3s ease;
}

.benefit-icon-wrapper:hover .benefit-ripple {
  background: rgba(255, 255, 255, 0.1);
  transform: translate(-50%, -50%) scale(1.2);
}

.benefit-icon-wrapper:hover .benefit-avatar {
  transform: scale(1.1);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.benefit-avatar {
  transition: all 0.3s ease;
  z-index: 1;
}

.benefit-icon {
  transition: transform 0.3s ease;
}

.benefit-icon-wrapper:hover .benefit-icon {
  transform: rotate(360deg) scale(1.2);
}

.slot-machine-wrapper {
  display: inline-flex;
  align-items: center;
  height: 5rem;
  overflow: hidden;
  position: relative;
}

.digit-wrapper {
  width: 2.5rem;
  height: 5rem;
  overflow: hidden;
  position: relative;
}

.digit-column {
  transition: transform 2s cubic-bezier(0.23, 1, 0.32, 1);
}

.digit {
  height: 5rem;
  font-size: 4rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}

.suffix {
  font-size: 3rem;
  font-weight: bold;
  margin-left: 0.5rem;
}

.image-container {
  position: relative;
  overflow: hidden;
  border-radius: 32px;
}

.feature-image {
  transition: transform 0.5s ease;
  opacity: 0;
  transform: translateY(30px);
}

.feature-image.zoom-in {
  opacity: 1;
  transform: translateY(0);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, rgba(0, 0, 0, 0.2), transparent);
  opacity: 0;
  transition: all 0.3s ease;
}

.image-container:hover .image-overlay {
  opacity: 1;
}

.image-container:hover .feature-image {
  transform: scale(1.05);
}

.benefit-item {
  opacity: 0;
  transform: translateX(-50px);
}

.slide-in {
  animation: slideIn 0.8s ease-out forwards;
}

.stat-icon {
  opacity: 0;
  transform: scale(0);
}

.bounce-in {
  animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-50px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes bounceIn {
  0% {
    opacity: 0;
    transform: scale(0);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.2);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Responsywność */
@media (max-width: 960px) {
  .digit {
    font-size: 3rem;
    height: 4rem;
  }

  .digit-wrapper {
    width: 2rem;
    height: 4rem;
  }

  .suffix {
    font-size: 2.5rem;
  }

  .custom-title {
    font-size: 2.5rem;
  }

  .feature-image {
    margin-top: 2rem;
    border-radius: 24px;
  }
}

</style>
