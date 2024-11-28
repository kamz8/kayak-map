<template>
    <section class="section-three">
        <v-container>
            <v-row align="center">
                <v-col cols="12" md="6" class="text-center text-md-left">
                    <h2 class="text-h3 font-weight-bold mb-6">
                        Zabierz Przewodnik ze Sobą
                    </h2>
                    <p class="text-h6 mb-8">
                        Nasza aplikacja mobilna to Twój niezbędny kompan podczas każdej kajakowej przygody.
                        Działająca offline nawigacja, szczegółowe mapy i wskazówki zawsze pod ręką.
                    </p>

                    <v-row class="mb-8">
                        <v-col v-for="(feature, i) in appFeatures" :key="i" cols="12" sm="6">
                            <v-icon :color="feature.color" size="32" class="mb-2">
                                {{ feature.icon }}
                            </v-icon>
                            <h3 class="text-h6 mb-2">{{ feature.title }}</h3>
                            <p class="text-body-2">{{ feature.description }}</p>
                        </v-col>
                    </v-row>

                    <v-btn
                        color="success"
                        size="x-large"
                        prepend-icon="mdi-google-play"
                        class="mr-4"
                    >
                        Pobierz z Google Play
                    </v-btn>
                </v-col>

                <v-col cols="12" md="6" class="d-flex justify-center">
                    <v-img
                        src="./map.jpg"
                        class="app-preview"
                        max-width="300"
                        contain
                    ></v-img>
                </v-col>
            </v-row>
        </v-container>
    </section>
</template>

<script>
export default {
    name: 'SectionThree',
    data() {
        return {
            appFeatures: [
                {
                    icon: 'mdi-map-marker-off',
                    title: 'Tryb Offline',
                    description: 'Pobierz mapy i korzystaj z nawigacji bez dostępu do internetu.',
                    color: 'var(--dark-green)'
                },
                {
                    icon: 'mdi-bell-ring',
                    title: 'Powiadomienia',
                    description: 'Otrzymuj alerty o warunkach na trasie i ważnych punktach.',
                    color: 'var(--steel-blue)'
                },
                {
                    icon: 'mdi-route',
                    title: 'Śledzenie Trasy',
                    description: 'Zapisuj swoje trasy i udostępniaj je innym kajakarzom.',
                    color: 'var(--dodger-blue)'
                },
                {
                    icon: 'mdi-camera',
                    title: 'Dziennik Podróży',
                    description: 'Twórz fotogalerię i zapisuj wspomnienia z wypraw.',
                    color: 'var(--sandy-brown)'
                }
            ]
        }
    }
}
</script>

<style scoped>
.section-three {
    background-color: var(--wheat);
    padding: 80px 0;
    color: var(--dark-green);
}

.app-preview {
    position: relative;
}

@media (min-width: 960px) {
    .app-preview {
        transform: perspective(1000px) rotateY(-15deg) rotateX(5deg);
        transition: transform 0.3s ease;
    }

    .app-preview:hover {
        transform: perspective(1000px) rotateY(-5deg) rotateX(2deg);
    }
}
</style>
```

