<template>
    <section class="section-one">
        <v-container>
            <v-row justify="center" class="text-center mb-8">
                <v-col cols="12" md="8">
                    <h2 class="text-h3 font-weight-bold mb-6">Odkryj Najlepsze Szlaki Kajakowe</h2>
                    <p class="text-h6 font-weight-regular">
                        Wybierz z ponad <span class="font-weight-bold">120 tras</span> w całej Polsce. Planuj wycieczki z przewodnikiem lub odkrywaj nowe szlaki samodzielnie.
                    </p>
                </v-col>
            </v-row>

            <v-row>
                <v-col v-for="(feature, i) in features" :key="i" cols="12" md="4">
                    <v-card elevation="2" class="feature-card pa-6" :class="{'on-hover': hover}" @mouseenter="hover = true" @mouseleave="hover = false">
                        <v-avatar :color="feature.color" size="64" class="mb-4">
                            <v-icon size="32" color="white">{{ feature.icon }}</v-icon>
                        </v-avatar>
                        <h3 class="text-h5 mb-4">{{ feature.title }}</h3>
                        <p class="text-body-1">{{ feature.description }}</p>
                        <v-btn
                            :color="feature.color"
                            variant="text"
                            class="mt-4"
                            @click="handleFeatureClick(feature)"
                        >
                            Dowiedz się więcej
                            <v-icon end>mdi-arrow-right</v-icon>
                        </v-btn>
                    </v-card>
                </v-col>
            </v-row>

            <v-row justify="center" class="mt-12">
                <v-col cols="12" sm="6" md="4" class="text-center">
                    <BaseBtnProgress
                        :progress="progress"
                        color="white"
                        size="x-large"
                    >
                        Rozpocznij Planowanie
                    </BaseBtnProgress>
                </v-col>
            </v-row>
        </v-container>
    </section>
</template>

<script>
import BaseBtnProgress from "@/components/BaseBtnProgress.vue";

export default {
    name: 'SectionOne',
    components: { BaseBtnProgress },
    data() {
        return {
            progress: 0,
            hover: false,
            features: [
                {
                    icon: 'mdi-map-search',
                    title: 'Inteligentne Wyszukiwanie',
                    description: 'Znajdź idealne trasy dopasowane do Twojego poziomu zaawansowania i preferencji.',
                    color: 'var(--dodger-blue)'
                },
                {
                    icon: 'mdi-compass',
                    title: 'Szczegółowa Nawigacja',
                    description: 'Dokładne mapy, punkty orientacyjne i wskazówki nawigacyjne na całej trasie.',
                    color: 'var(--gold)'
                },
                {
                    icon: 'mdi-weather-sunny',
                    title: 'Warunki na Szlaku',
                    description: 'Aktualne informacje o pogodzie, stanie wody i warunkach na trasie.',
                    color: 'var(--lime-green)'
                }
            ]
        }
    },
    methods: {
        handleFeatureClick(feature) {
            // Implementacja akcji dla każdej funkcji
            console.log(`Clicked feature: ${feature.title}`);
        }
    },
    mounted() {
        const progressInterval = setInterval(() => {
            if (this.progress < 100) {
                this.progress += 1;
            } else {
                clearInterval(progressInterval);
            }
        }, 50);
    }
}
</script>

<style scoped>
.section-one {
    background-color: var(--steel-blue);
    color: white;
    padding: 80px 0;
}

.feature-card {
    height: 100%;
    transition: all 0.3s ease;
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.feature-card.on-hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.15);
}

</style>
