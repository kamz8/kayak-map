<template>
    <v-container fluid class="pa-0">
        <!-- Sekcja Hero z logo -->
        <LiquidGlassParallax
            image-src="storage/assets/river-hero.jpg"
            :height="550"
            :blur-strength="8"
            :glass-opacity="0.5"
            class="d-flex align-center"
        >
            <v-container>
                <v-row class="justify-center">
                    <v-col cols="12" class="text-center">
                        <v-avatar size="240" class="mb-6 hero-logo">
                            <v-img
                                cover
                                src="/storage/assets/logo.svg"
                                alt="Logo Kayak Map"
                            />
                        </v-avatar>
                        <h1 class="text-h2 font-weight-bold mb-4 text-white hero-title">Wartki Nurt</h1>
                        <p class="text-h5 text-white hero-subtitle">Wspólna pasja do kajakarstwa</p>
                    </v-col>
                </v-row>
            </v-container>
        </LiquidGlassParallax>
        <!-- O projekcie -->
        <v-container class="my-12">
            <v-row>
                <v-col cols="12" class="text-center mb-8">
                    <h2 class="text-h3 font-weight-bold text-river-blue section-title">O projekcie</h2>
                    <v-divider class="my-4 mx-auto" width="100" color="river-blue"></v-divider>
                    <p class="text-body-1 mb-6 fade-in-text" style="max-width: 800px; margin: 0 auto;">
                        Wartki Nurt to dziecko dwóch programistów-pasjonatów kajakarstwa, którzy postanowili
                        stworzyć narzędzie, z którego sami chcieliby korzystać podczas planowania spływów.
                    </p>
                </v-col>

                <v-col cols="12" md="6" class="pr-md-8">
                    <h3 class="text-h5 font-weight-bold mb-4 text-river-blue">Geneza projektu</h3>
                    <p class="text-body-1 mb-4 fade-in-text">
                        Projekt jest ewolucją nieistniejącego już serwisu kajak.org.pl, z którego zaczerpnęliśmy
                        cenne dane i doświadczenia. Wielkie podziękowania dla <strong>Bartosza Sawickiego</strong>
                        za udostępnienie tych zasobów i inspirację do kontynuowania jego dzieła.
                    </p>
                    <p class="text-body-1 mb-4 fade-in-text">
                        Jako pasjonaci kajakarstwa, często spotykaliśmy się z tym samym problemem - rozproszone
                        i nieaktualne informacje o szlakach kajakowych. Przewodniki papierowe szybko się dezaktualizują,
                        a w internecie trudno znaleźć wiarygodne i kompleksowe źródło.
                    </p>
                </v-col>

                <v-col cols="12" md="6">
                    <v-img
                        src="/storage/assets/problem-spot.png"
                        alt="Problem na szlaku kajakowym"
                        height="250"
                        cover
                        class="rounded-lg hover-scale"
                    ></v-img>
                    <p class="text-caption text-center mt-2">Przykład niebezpiecznego miejsca oznaczonego w systemie</p>
                </v-col>

                <v-col cols="12" class="mt-6">
                    <h3 class="text-h5 font-weight-bold mb-4 text-river-blue">Cele projektu</h3>
                    <p class="text-body-1 mb-4 fade-in-text">
                        Wartki Nurt powstał, aby:
                    </p>
                    <v-list density="comfortable" class="transparent mb-4">
                        <v-list-item
                            v-for="(goal, index) in projectGoals"
                            :key="index"
                            prepend-icon="mdi-check-circle"
                            class="goal-item"
                            :style="{ 'animation-delay': `${index * 0.2}s` }"
                        >
                            <v-list-item-title>{{ goal }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                    <p class="text-body-1 fade-in-text">
                        Wierzymy, że dobra mapa to podstawa udanego spływu. Naszym celem jest tworzenie
                        narzędzia, z którego sami chcielibyśmy korzystać.
                    </p>
                </v-col>
            </v-row>
        </v-container>

        <!-- Roadmapa rozwoju -->
        <RoadmapComponent></RoadmapComponent>
        <!-- Dane i źródła -->
        <v-container class="my-12">
            <v-row>
                <v-col cols="12" class="text-center mb-8">
                    <h2 class="text-h3 font-weight-bold text-river-blue section-title">Dane i źródła</h2>
                    <v-divider class="my-4 mx-auto" width="100" color="river-blue"></v-divider>
                </v-col>

                <v-col cols="12" md="6" class="pr-md-8">
                    <h3 class="text-h5 font-weight-bold mb-4 text-river-blue">Dziedzictwo kajak.org.pl</h3>
                    <p class="text-body-1 mb-4 fade-in-text">
                        Podstawowe dane o szlakach kajakowych pochodzą z nieistniejącego już serwisu
                        <strong>kajak.org.pl</strong>. Dzięki życzliwości <strong>Bartosza Sawickiego</strong>
                        mogliśmy przejąć te cenne informacje i kontynuować jego dzieło.
                    </p>
                    <p class="text-body-1 mb-4 fade-in-text">
                        Serwis ten przez lata służył społeczności kajakarskiej, gromadząc dane o trasach
                        w całej Polsce. Nasze zadanie to nie tylko zachowanie tych informacji, ale także
                        ich rozwinięcie i unowocześnienie.
                    </p>
                </v-col>

                <v-col cols="12" md="6">
                    <v-card variant="outlined" class="pa-6 thanks-card">
                        <v-card-title class="text-h6">
                            <v-icon icon="mdi-heart" color="red" class="mr-2 pulse-animation"></v-icon>
                            Podziękowania
                        </v-card-title>
                        <v-card-text>
                            <p class="text-body-1 mb-3 fade-in-text">
                                Specjalne podziękowania dla <strong>Bartosza Sawickiego</strong>
                                za udostępnienie danych z kajak.org.pl i inspirację do stworzenia Wartki Nurt.
                            </p>
                            <p class="text-body-2 text-grey-darken-1 fade-in-text">
                                Bez jego pracy i życzliwości ten projekt nie mógłby powstać.
                            </p>
                        </v-card-text>
                    </v-card>
                </v-col>

                <v-col cols="12" class="mt-6">
                    <h3 class="text-h5 font-weight-bold mb-4 text-river-blue">Współczesne źródła danych</h3>
                    <v-row>
                        <v-col
                            v-for="(source, index) in dataSources"
                            :key="index"
                            cols="12"
                            md="4"
                        >
                            <v-card
                                variant="outlined"
                                class="pa-4 h-100 data-source-card"
                                :style="{ 'animation-delay': `${index * 0.2}s` }"
                            >
                                <v-card-title class="text-h6">
                                    <v-icon :icon="source.icon" :color="source.color" class="mr-2"></v-icon>
                                    {{ source.title }}
                                </v-card-title>
                                <v-card-text>
                                    {{ source.description }}
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>
        </v-container>

        <!-- Jak działamy -->
        <v-sheet color="grey-lighten-4" class="py-12">
            <v-container>
                <v-row>
                    <v-col cols="12" class="text-center mb-8">
                        <h2 class="text-h3 font-weight-bold text-river-blue section-title">Jak działamy?</h2>
                        <v-divider class="my-4 mx-auto" width="100" color="river-blue"></v-divider>
                    </v-col>

                    <v-col
                        v-for="(step, i) in workflow"
                        :key="i"
                        cols="12"
                        md="4"
                        class="text-center px-6"
                    >
                        <v-card
                            variant="flat"
                            height="100%"
                            class="workflow-card"
                            :style="{ 'animation-delay': `${i * 0.3}s` }"
                        >
                            <v-card-text>
                                <v-avatar size="80" :color="step.color" class="mb-4 workflow-avatar">
                                    <v-icon :icon="step.icon" size="48" color="white"></v-icon>
                                </v-avatar>
                                <h3 class="text-h5 mb-2">{{ step.title }}</h3>
                                <p class="text-body-1">{{ step.description }}</p>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-sheet>

        <!-- Technologie -->
        <v-container class="my-12">
            <v-row>
                <v-col cols="12" class="text-center mb-8">
                    <h2 class="text-h3 font-weight-bold text-river-blue section-title">Wykorzystane technologie</h2>
                    <v-divider class="my-4 mx-auto" width="100" color="river-blue"></v-divider>
                    <p class="text-body-1 mx-auto mb-8 fade-in-text" style="max-width: 800px;">
                        Wartki Nurt powstał w oparciu o sprawdzone, otwarte technologie, które gwarantują niezawodność
                        i swobodę rozwoju bez licencyjnych ograniczeń.
                    </p>
                </v-col>

                <!-- Logo technologii -->
                <v-col cols="12" class="mb-8">
                    <v-row class="justify-center">
                        <v-col
                            v-for="(tech, index) in technologies"
                            :key="tech.name"
                            cols="6"
                            sm="3"
                            md="2"
                            class="text-center"
                        >
                            <v-card
                                variant="flat"
                                class="pa-4 tech-card"
                                :class="{ 'tech-card-active': selectedTech === tech.name }"
                                @click="selectedTech = tech.name"
                                :style="{ 'animation-delay': `${index * 0.1}s` }"
                            >
                                <v-img
                                    :src="tech.logo"
                                    :alt="tech.name"
                                    height="50"
                                    contain
                                    class="mb-2"
                                ></v-img>
                                <div class="text-subtitle-2 font-weight-medium">{{ tech.name }}</div>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-col>

                <!-- Opis wybranej technologii -->
                <v-col cols="12">
                    <v-card class="pa-6 elevation-2 tech-detail-card">
                        <v-row>
                            <v-col cols="12" md="8">
                                <h3 class="text-h4 font-weight-bold mb-4 text-river-blue">
                                    {{ getCurrentTech.name }} - {{ getCurrentTech.subtitle }}
                                </h3>
                                <div class="text-body-1 mb-4 fade-in-text" v-html="getCurrentTech.description"></div>

                                <v-chip-group class="mb-4">
                                    <v-chip
                                        v-for="(feature, index) in getCurrentTech.features"
                                        :key="feature"
                                        color="blue-lighten-4"
                                        size="small"
                                        class="feature-chip"
                                        :style="{ 'animation-delay': `${index * 0.1}s` }"
                                    >
                                        {{ feature }}
                                    </v-chip>
                                </v-chip-group>
                            </v-col>

                            <v-col cols="12" md="4" class="text-center">
                                <v-img
                                    :src="getCurrentTech.illustration"
                                    :alt="getCurrentTech.name"
                                    height="200"
                                    contain
                                    class="rounded-lg hover-scale"
                                ></v-img>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-col>

                <!-- Dlaczego Open Source -->
                <v-col cols="12">
                    <v-alert
                        type="info"
                        variant="tonal"
                        class="mt-8 open-source-alert"
                        icon="mdi-open-source-initiative"
                    >
                        <template v-slot:title>
                            <span class="text-h6">Dlaczego open source?</span>
                        </template>
                        <p class="mb-2 fade-in-text">
                            Wybór wyłącznie otwartych technologii daje nam pełną kontrolę nad rozwojem,
                            gwarancję długoterminowej dostępności i możliwość udostępnienia aplikacji za darmo
                            społeczności kajakarskiej.
                        </p>
                        <p class="mb-0 fade-in-text">
                            Wszystkie narzędzia mają aktywne społeczności deweloperów zapewniające regularne
                            aktualizacje bezpieczeństwa i nowe funkcjonalności.
                        </p>
                    </v-alert>
                </v-col>
            </v-row>
        </v-container>
    </v-container>
</template>

<script>
import LiquidGlassParallax from "@/components/UIKit/LiquidGlassParallax.vue";
import RoadmapComponent from "@/modules/main-page/components/AboutProject/Roadmap.vue";

export default {
    name: 'AboutPage',
    components: {RoadmapComponent, LiquidGlassParallax},
    data() {
        return {
            selectedTech: 'Vue.js',
            projectGoals: [
                'Zbierać w jednym miejscu aktualne informacje o szlakach',
                'Oznaczać niebezpieczne miejsca i przeszkody',
                'Dzielić się wiedzą i doświadczeniem kajakarzy'
            ],
            workflow: [
                {
                    title: 'Zbieranie danych',
                    description: 'Weryfikujemy trasy na podstawie własnych spływów i zgłoszeń społeczności',
                    icon: 'mdi-map-search',
                    color: 'blue'
                },
                {
                    title: 'Oznaczanie miejsc',
                    description: 'Naniesienie na mapę niebezpiecznych miejsc, portali i punktów informacyjnych',
                    icon: 'mdi-map-marker',
                    color: 'green'
                },
                {
                    title: 'Udostępnianie',
                    description: 'Publikacja danych w formie przyjaznej dla kajakarzy',
                    icon: 'mdi-share-variant',
                    color: 'orange'
                }
            ],
            dataSources: [
                {
                    title: 'Domeny publiczne',
                    description: 'Wykorzystujemy dostępne w domenie publicznej dane o szlakach wodnych, które następnie weryfikujemy i aktualizujemy.',
                    icon: 'mdi-database',
                    color: 'blue'
                },
                {
                    title: 'Analiza AI',
                    description: 'Sztuczna inteligencja pomaga nam analizować i kategoryzować duże zbiory danych o trasach kajakowych.',
                    icon: 'mdi-robot',
                    color: 'green'
                },
                {
                    title: 'Społeczność',
                    description: 'Dane wzbogacamy o informacje przekazywane przez doświadczonych kajakarzy i lokalnych przewodników.',
                    icon: 'mdi-account-group',
                    color: 'orange'
                }
            ],
            roadmapMilestones: [
                {
                    date: 'Koniec 2023',
                    title: 'Pierwszy pomysł',
                    description: 'Początkowe plany powstania projektu i analiza potrzeb społeczności kajakarskiej',
                    icon: 'mdi-lightbulb',
                    color: '#FFC107',
                    status: 'completed',
                    features: ['Analiza rynku', 'Badanie potrzeb', 'Koncepcja projektu']
                },
                {
                    date: 'Czerwiec 2024',
                    title: 'Początek realizacji',
                    description: 'Kamil traci pracę i postanawia w pełni zaangażować się w realizację projektu',
                    icon: 'mdi-rocket-launch',
                    color: '#2196F3',
                    status: 'completed',
                    features: ['Wybór technologii', 'Architektura systemu', 'Pierwszy kod']
                },
                {
                    date: 'Wakacje 2024',
                    title: 'Pierwszy prototyp',
                    description: 'Powstaje działający prototyp z podstawowymi funkcjonalnościami mapowymi',
                    icon: 'mdi-wrench',
                    color: '#4CAF50',
                    status: 'completed',
                    features: ['Mapa Leaflet', 'Import GPX', 'Podstawowe UI']
                },
                {
                    date: 'Grudzień 2024',
                    title: 'Wartki Nurt w sieci',
                    description: 'Projekt zyskuje nazwę Wartki Nurt, a prototyp trafia na serwer produkcyjny',
                    icon: 'mdi-web',
                    color: '#3F51B5',
                    status: 'completed',
                    features: ['Nazwa projektu', 'Serwer produkcyjny', 'Pierwsi użytkownicy']
                },
                {
                    date: '2025',
                    title: 'Nowe wyzwania',
                    description: 'Rozbudowa funkcjonalności i zwiększenie bazy danych o szlaki',
                    icon: 'mdi-trending-up',
                    color: '#9C27B0',
                    status: 'in-progress',
                    features: ['Więcej tras', 'Optymalizacja', 'Społeczność']
                },
                {
                    date: 'Q2 2025',
                    title: 'Integracja GPS',
                    description: 'Dodanie obsługi urządzeń GPS i nawigacji offline',
                    icon: 'mdi-crosshairs-gps',
                    color: '#00BCD4',
                    status: 'planned',
                    features: ['Nawigacja GPS', 'Import z urządzeń', 'Tracking tras']
                },
                {
                    date: 'Q3 2025',
                    title: 'Monitoring poziomu wody',
                    description: 'System ostrzeżeń o poziomie wody i warunkach na rzekach',
                    icon: 'mdi-waves',
                    color: '#03A9F4',
                    status: 'planned',
                    features: ['API hydrologiczne', 'Ostrzeżenia', 'Prognoza warunków']
                },
                {
                    date: 'Q4 2025',
                    title: 'Aplikacja mobilna',
                    description: 'Natywna aplikacja mobilna z możliwością pracy offline',
                    icon: 'mdi-cellphone',
                    color: '#009688',
                    status: 'planned',
                    features: ['React Native', 'Offline maps', 'Synchronizacja']
                },
                {
                    date: '2026',
                    title: 'Społeczność i oceny',
                    description: 'System ocen, komentarzy i dashboard dla społeczności',
                    icon: 'mdi-account-group',
                    color: '#FF9800',
                    status: 'planned',
                    features: ['System ocen', 'Komentarze', 'Dashboard użytkownika']
                }
            ],
            technologies: [
                { name: 'Vue.js', logo: 'https://vuejs.org/images/logo.png' },
                { name: 'Vuetify', logo: 'https://cdn.vuetifyjs.com/docs/images/brand-kit/v-logo.svg' },
                { name: 'Laravel', logo: 'https://laravel.com/img/logomark.min.svg' },
                { name: 'Leaflet', logo: 'https://leafletjs.com/docs/images/logo.png' },
                { name: 'MySQL', logo: 'https://www.mysql.com/common/logos/logo-mysql-170x115.png' }
            ],
            techDetails: {
                'Vue.js': {
                    name: 'Vue.js 3',
                    subtitle: 'Reaktywny frontend',
                    description: `
                        Interfejs użytkownika napędza Vue.js w najnowszej wersji, który automatycznie aktualizuje
                        mapę w czasie rzeczywistym podczas filtrowania tras czy zmiany regionów.
                        <br><br>
                        Vuetify dostarcza gotowe komponenty Material Design dostosowujące się do różnych rozmiarów
                        ekranów - kluczowe dla mobile-first design. Vuex zarządza stanem aplikacji,
                        centralizując dane o trasach i preferencjach użytkownika.
                    `,
                    features: ['Composition API', 'Reactive data', 'Component-based', 'Mobile-first'],
                    illustration: 'https://vuejs.org/images/logo.png'
                },
                'Vuetify': {
                    name: 'Vuetify',
                    subtitle: 'Material Design UI',
                    description: `
                        Biblioteka komponentów UI oparta na Material Design Google. Dostarcza gotowe,
                        responsywne komponenty jak przyciski, formularze czy nawigację, które automatycznie
                        dostosowują się do różnych urządzeń.
                        <br><br>
                        Znacznie przyspiesza rozwój aplikacji - nie musimy projektować wszystkiego od zera,
                        koncentrując się na funkcjonalnościach specyficznych dla kajakarstwa.
                    `,
                    features: ['Material Design', 'Responsive', 'Accessibility', 'Theming'],
                    illustration: 'https://cdn.vuetifyjs.com/docs/images/brand-kit/v-logo.svg'
                },
                'Laravel': {
                    name: 'Laravel 11',
                    subtitle: 'Solidny backend',
                    description: `
                        Fundament aplikacji stanowi Laravel, który obsługuje przetwarzanie plików GPX,
                        zarządzanie bazą danych tras i generowanie API dla frontendu.
                        <br><br>
                        System kolejkowania zadań asynchronicznie przetwarza duże zbiory danych - import 200 tras
                        nie blokuje działania aplikacji. Wbudowane mechanizmy zabezpieczeń chronią dane użytkowników.
                    `,
                    features: ['Job Queues', 'API Resources', 'Security', 'Database migrations'],
                    illustration: 'https://laravel.com/img/logomark.min.svg'
                },
                'Leaflet': {
                    name: 'Leaflet.js',
                    subtitle: 'Interaktywna mapa',
                    description: `
                        Lekka biblioteka open source do wizualizacji map, która doskonale radzi sobie
                        z wyświetlaniem setek tras bez utraty płynności.
                        <br><br>
                        Pozwala na łatwe nakładanie warstw - od podstawowych map OpenStreetMap po niestandardowe
                        markery oznaczające niebezpieczne miejsca czy punkty wodowania. Wszystko bez opłat licencyjnych.
                    `,
                    features: ['Lightweight', 'Plugin ecosystem', 'Mobile support', 'Custom markers'],
                    illustration: 'https://leafletjs.com/docs/images/logo.png'
                },
                'MySQL': {
                    name: 'MySQL',
                    subtitle: 'Baza danych przestrzennych',
                    description: `
                        Trasy przechowujemy wykorzystując rozszerzenia przestrzenne MySQL w formatach
                        GEOMETRY i GEOGRAPHY, co usprawnia zapytania o trasy w określonym obszarze.
                        <br><br>
                        Hierarchiczna struktura regionów (kraj/województwo/miasto) z automatycznie generowanymi
                        slug'ami zapewnia SEO-friendly URL-e typu <code>/poland/dolnoslaskie/wroclaw</code>.
                    `,
                    features: ['Spatial extensions', 'Geospatial queries', 'GEOMETRY types', 'Performance'],
                    illustration: 'https://www.mysql.com/common/logos/logo-mysql-170x115.png'
                }
            }
        }
    },
    methods: {
        getChipColor(status) {
            switch(status) {
                case 'completed': return 'green-lighten-4'
                case 'in-progress': return 'purple-lighten-4'
                case 'planned': return 'blue-lighten-4'
                default: return 'grey-lighten-4'
            }
        }
    },
    computed: {
        getCurrentTech() {
            return this.techDetails[this.selectedTech] || this.techDetails['Vue.js']
        }
    }
}
</script>

<style scoped>
/* Podstawowe kolory */
.text-river-blue {
    color: #1976D2;
}

/* Animacje wejścia */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Hero sekcja */
.hero-logo {
    animation: scaleIn 1s ease-out;
}

.hero-title {
    animation: fadeInUp 1s ease-out 0.3s both;
}

.hero-subtitle {
    animation: fadeInUp 1s ease-out 0.6s both;
}

/* Sekcje */
.section-title {
    animation: fadeInUp 0.8s ease-out;
}

.fade-in-text {
    animation: fadeInUp 0.8s ease-out;
}

/* Cele projektu */
.goal-item {
    animation: fadeInLeft 0.8s ease-out both;
}

/* Desktop Roadmapa - wieloliniowa */
.roadmap-multiline {
    position: relative;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.roadmap-row {
    position: relative;
    height: 250px;
    margin-bottom: 40px;
}

.roadmap-line-horizontal {
    position: absolute;
    top: 50%;
    left: 50px;
    right: 50px;
    height: 3px;
    background: #1976D2;
    transform: translateY(-50%);
    z-index: 1;
}

.roadmap-line-horizontal::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: repeating-linear-gradient(
        90deg,
        transparent 0px,
        transparent 8px,
        white 8px,
        white 12px
    );
}

.roadmap-item-multiline {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: fadeInUp 0.8s ease-out both;
    z-index: 3;
    width: 200px;
}

.roadmap-item-multiline:nth-child(2) { left: 50px; }
.roadmap-item-multiline:nth-child(3) { left: calc(25% - 50px); }
.roadmap-item-multiline:nth-child(4) { left: calc(50% - 100px); }
.roadmap-item-multiline:nth-child(5) { left: calc(75% - 150px); }
.roadmap-item-multiline:nth-child(6) { right: 50px; }

.item-bottom {
    flex-direction: column-reverse;
}

.timeline-card {
    width: 200px;
    margin: 15px 0;
    transition: all 0.3s ease;
    border-radius: 8px;
    background: white;
}

.timeline-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.card-bottom {
    margin-bottom: 15px;
    margin-top: 0;
}

.timeline-dot {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    z-index: 4;
    transition: all 0.3s ease;
}

.timeline-dot:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
}

.timeline-connector {
    width: 2px;
    height: 15px;
    background: #1976D2;
    z-index: 2;
}

.connector-bottom {
    order: -1;
}

.timeline-date {
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.timeline-title {
    font-weight: 600;
    font-size: 0.9rem;
    line-height: 1.2;
}

.timeline-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
}

/* Łącznik między liniami */
.inter-line-connector {
    position: absolute;
    top: -20px;
    right: 50px;
    width: 3px;
    height: 40px;
    background: #1976D2;
    background-image: repeating-linear-gradient(
        180deg,
        transparent 0px,
        transparent 6px,
        white 6px,
        white 10px
    );
    z-index: 2;
}

.inter-line-connector::before {
    content: '';
    position: absolute;
    bottom: -10px;
    left: -5px;
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 10px solid #1976D2;
}

/* Status kolory dla dat */
.timeline-date.completed {
    color: #2E7D32;
}

.timeline-date.in-progress {
    color: #7B1FA2;
}

.timeline-date.planned {
    color: #1565C0;
}

/* Mobile Roadmapa - zaktualizowana */
.roadmap-mobile {
    position: relative;
    max-width: 500px;
    margin: 0 auto;
    padding-left: 25px;
}

.roadmap-mobile-item {
    position: relative;
    margin-bottom: 25px;
    animation: fadeInLeft 0.8s ease-out both;
}

.roadmap-mobile-content {
    display: flex;
    align-items: flex-start;
}

.roadmap-mobile-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    border: 2px solid white;
    flex-shrink: 0;
    z-index: 3;
    position: relative;
}

.roadmap-mobile-card {
    flex: 1;
    transition: all 0.3s ease;
    border-radius: 6px;
}

.roadmap-mobile-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}

.roadmap-mobile-line-dashed {
    position: absolute;
    left: 13px;
    top: 28px;
    bottom: -25px;
    width: 2px;
    background: #1976D2;
    background-image: repeating-linear-gradient(
        180deg,
        transparent 0px,
        transparent 6px,
        white 6px,
        white 10px
    );
}

.roadmap-mobile-item:last-child .roadmap-mobile-line-dashed {
    display: none;
}

/* Technologie */
.tech-card {
    border: 2px solid transparent;
    transition: all 0.3s ease;
    cursor: pointer;
    animation: scaleIn 0.6s ease-out both;
}

.tech-card:hover {
    border-color: #1976D2;
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(25, 118, 210, 0.15);
}

.tech-card-active {
    border-color: #1976D2 !important;
    background-color: rgba(25, 118, 210, 0.05);
    transform: translateY(-3px);
}

.tech-detail-card {
    transition: all 0.3s ease;
}

/* Workflow karty */
.workflow-card {
    transition: all 0.3s ease;
    animation: fadeInUp 0.8s ease-out both;
}

.workflow-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

.workflow-avatar {
    transition: all 0.3s ease;
}

.workflow-card:hover .workflow-avatar {
    transform: scale(1.1) rotate(5deg);
}

/* Data sources */
.data-source-card {
    transition: all 0.3s ease;
    animation: fadeInUp 0.8s ease-out both;
}

.data-source-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

/* Feature chips */
.feature-chip {
    animation: scaleIn 0.6s ease-out both;
    transition: all 0.3s ease;
}

.feature-chip:hover {
    transform: scale(1.05);
}

.feature-chip-mobile {
    animation: fadeInLeft 0.6s ease-out both;
}

/* Thanks card */
.thanks-card {
    transition: all 0.3s ease;
}

.thanks-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

/* Pulse animation for heart */
.pulse-animation {
    animation: pulse 2s infinite;
}

/* Hover effects */
.hover-scale {
    transition: all 0.3s ease;
}

.hover-scale:hover {
    transform: scale(1.05);
}

/* Open source alert */
.open-source-alert {
    animation: fadeInUp 0.8s ease-out both;
}

/* Responsywność */
@media (max-width: 960px) {
    .hero-title {
        font-size: 2rem !important;
    }

    .section-title {
        font-size: 1.8rem !important;
    }

    .roadmap-mobile-card .v-card-title {
        font-size: 1rem !important;
    }
}

@media (max-width: 600px) {
    .roadmap-mobile {
        padding-left: 20px;
    }

    .roadmap-mobile-dot {
        width: 24px;
        height: 24px;
    }

    .roadmap-mobile-line-dashed {
        left: 11px;
    }

    .hero-title {
        font-size: 1.75rem !important;
    }

    .hero-subtitle {
        font-size: 1.25rem !important;
    }

    .tech-card {
        padding: 12px !important;
    }
}

/* Scroll animations */
@media (prefers-reduced-motion: no-preference) {
    .fade-in-text,
    .goal-item,
    .roadmap-item-meandering,
    .roadmap-mobile-item,
    .workflow-card,
    .data-source-card,
    .tech-card,
    .feature-chip {
        opacity: 0;
        animation-fill-mode: both;
    }

    /* Trigger animations on scroll using Intersection Observer */
    .fade-in-text.visible,
    .goal-item.visible,
    .roadmap-item-meandering.visible,
    .roadmap-mobile-item.visible,
    .workflow-card.visible,
    .data-source-card.visible,
    .tech-card.visible,
    .feature-chip.visible {
        animation-play-state: running;
    }
}
</style>
