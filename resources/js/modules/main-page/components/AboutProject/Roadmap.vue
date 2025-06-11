<template>
    <v-sheet color="grey-lighten-4" class="py-12">
        <v-container fluid>
            <v-row>
                <v-col cols="12" class="text-center mb-8">
                    <h2 class="text-h3 font-weight-bold text-river-blue section-title">Roadmapa rozwoju</h2>
                    <v-divider class="my-4 mx-auto" width="100" color="river-blue"></v-divider>
                    <p class="text-body-1 mb-6 fade-in-text" style="max-width: 800px; margin: 0 auto;">
                        Poznaj historię projektu i planowane funkcjonalności na przyszłość
                    </p>
                </v-col>

                <!-- Desktop Timeline - meandrujący layout -->
                <v-col cols="12" class="d-none d-md-block px-6">
                    <div class="roadmap-meandering">
                        <svg class="roadmap-path-svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid meet">
                            <!-- Przerwana linia łącząca wszystkie punkty -->
                            <path
                                :d="roadmapPath"
                                stroke="#1976D2"
                                stroke-width="3"
                                stroke-dasharray="8,8"
                                fill="none"
                            />
                        </svg>

                        <div
                            v-for="(milestone, i) in roadmapMilestones"
                            :key="i"
                            class="roadmap-item-meandering"
                            :class="[milestone.status]"
                            :style="{
                                'animation-delay': `${i * 0.15}s`,
                                'left': `${getMilestonePosition(i).x}px`,
                                'top': `${getMilestonePosition(i).y}px`
                            }"
                        >
                            <!-- Karta -->
                            <v-card
                                class="timeline-card-meandering elevation-4"
                                :class="{ 'card-top': getMilestonePosition(i).cardTop }"
                            >
                                <v-card-title class="text-subtitle-2 pb-1">
                                    <div class="timeline-date" :class="milestone.status">{{ milestone.date }}</div>
                                    <div class="timeline-title">{{ milestone.title }}</div>
                                </v-card-title>
                                <v-card-text class="py-2">
                                    <p class="text-caption mb-0">{{ milestone.description }}</p>
                                </v-card-text>
                                <div class="timeline-progress" v-if="milestone.status === 'in-progress'">
                                    <v-progress-linear
                                        :model-value="75"
                                        color="purple"
                                        height="2"
                                    ></v-progress-linear>
                                </div>
                            </v-card>

                            <!-- Łącznik do linii -->
                            <div
                                class="timeline-connector-meandering"
                                :class="{ 'connector-top': getMilestonePosition(i).cardTop }"
                            ></div>

                            <!-- Kropka na linii -->
                            <div class="timeline-dot-meandering" :style="{ backgroundColor: milestone.color }">
                                <v-icon :icon="milestone.icon" color="white" size="16"></v-icon>
                            </div>
                        </div>
                    </div>
                </v-col>

                <!-- Mobile Timeline -->
                <v-col cols="12" class="d-md-none">
                    <div class="roadmap-mobile">
                        <div
                            v-for="(milestone, i) in roadmapMilestones"
                            :key="i"
                            class="roadmap-mobile-item"
                            :class="milestone.status"
                            :style="{ 'animation-delay': `${i * 0.2}s` }"
                        >
                            <div class="roadmap-mobile-content">
                                <div class="roadmap-mobile-dot" :style="{ backgroundColor: milestone.color }">
                                    <v-icon :icon="milestone.icon" color="white" size="14"></v-icon>
                                </div>
                                <v-card class="roadmap-mobile-card elevation-3 ml-3">
                                    <v-card-title class="text-subtitle-2 pb-1">
                                        <span class="timeline-date" :class="milestone.status">{{ milestone.date }}</span>
                                        <br>
                                        {{ milestone.title }}
                                    </v-card-title>
                                    <v-card-text class="py-2">
                                        <p class="text-caption mb-0">{{ milestone.description }}</p>
                                    </v-card-text>
                                </v-card>
                            </div>
                            <div v-if="i < roadmapMilestones.length - 1" class="roadmap-mobile-line-dashed"></div>
                        </div>
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </v-sheet>
</template>

<script>
export default {
    name: 'RoadmapComponent',
    data() {
        return {
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
            ]
        }
    },
    methods: {
        getChipColor(status) {
            switch (status) {
                case 'completed':
                    return 'green-lighten-4'
                case 'in-progress':
                    return 'purple-lighten-4'
                case 'planned':
                    return 'blue-lighten-4'
                default:
                    return 'grey-lighten-4'
            }
        },
        getMilestonePosition(index) {
            // Używa tej samej listy pozycji co ścieżka SVG
            return this.milestonePositions[index] || {x: 100, y: 100, cardTop: false}
        }
    },
    computed: {
        milestonePositions() {
            // JEDNA lista pozycji używana wszędzie
            return [
                {x: 50, y: 150, cardTop: false},   // Koniec 2023
                {x: 250, y: 350, cardTop: true},    // Czerwiec 2024
                {x: 500, y: 120, cardTop: false},   // Wakacje 2024
                {x: 760, y: 180, cardTop: true},   // Grudzień 2024
                {x: 890, y: 420, cardTop: true},    // 2025
                {x: 50, y: 580, cardTop: false},   // Q2 2025
                {x: 400, y: 650, cardTop: true},    // Q3 2025
                {x: 650, y: 570, cardTop: false},   // Q4 2025
                {x: 900, y: 650, cardTop: true}     // 2026
            ]
        },
        roadmapPath() {
            const positions = this.milestonePositions.slice(0, this.roadmapMilestones.length)
            if (positions.length < 2) return ''

            // Dodajemy 110px do każdej pozycji X, aby linia przechodziła przez środek karty
            const adjustedPositions = positions.map(pos => ({
                ...pos,
                x: pos.x + 110
            }))

            // Funkcja do tworzenia płynnej krzywej przez trzy punkty
            const createSmoothCurve = (p0, p1, p2, index, total) => {
                // Wektor kierunkowy do punktu
                let dx1 = p1.x - p0.x
                let dy1 = p1.y - p0.y

                // Wektor kierunkowy od punktu
                let dx2, dy2
                if (p2) {
                    dx2 = p2.x - p1.x
                    dy2 = p2.y - p1.y
                } else {
                    // Dla ostatniego punktu użyj kierunku z poprzedniego segmentu
                    dx2 = dx1
                    dy2 = dy1
                }

                // Normalizuj wektory
                const len1 = Math.sqrt(dx1 * dx1 + dy1 * dy1)
                const len2 = Math.sqrt(dx2 * dx2 + dy2 * dy2)

                if (len1 > 0) {
                    dx1 /= len1
                    dy1 /= len1
                }
                if (len2 > 0) {
                    dx2 /= len2
                    dy2 /= len2
                }

                // Oblicz tangens (średni kierunek) w punkcie
                let tangentX = (dx1 + dx2) / 2
                let tangentY = (dy1 + dy2) / 2

                // Dla pierwszego i ostatniego punktu użyj tylko jednego kierunku
                if (index === 0) {
                    tangentX = dx2
                    tangentY = dy2
                } else if (index === total - 1) {
                    tangentX = dx1
                    tangentY = dy1
                }

                // Normalizuj tangens
                const tangentLen = Math.sqrt(tangentX * tangentX + tangentY * tangentY)
                if (tangentLen > 0) {
                    tangentX /= tangentLen
                    tangentY /= tangentLen
                }

                // Siła kontrolna proporcjonalna do odległości
                const controlDist = Math.min(len1, len2 || len1) * 0.3

                // Punkty kontrolne wzdłuż tangensa
                const cp1 = {
                    x: p1.x - tangentX * controlDist,
                    y: p1.y - tangentY * controlDist
                }
                const cp2 = {
                    x: p1.x + tangentX * controlDist,
                    y: p1.y + tangentY * controlDist
                }

                return { cp1, cp2 }
            }

            // Funkcja do dodania meandrów
            const addMeander = (p1, p2, strength = 0.25) => {
                const dx = p2.x - p1.x
                const dy = p2.y - p1.y
                const distance = Math.sqrt(dx * dx + dy * dy)

                // Kierunek prostopadły
                const perpX = -dy / distance
                const perpY = dx / distance

                // Offset meandrowania zależny od pozycji
                const offset = Math.sin(p1.x * 0.01 + p1.y * 0.01) * distance * strength

                return {
                    x: offset * perpX,
                    y: offset * perpY
                }
            }

            // Rozpocznij ścieżkę
            let path = `M ${adjustedPositions[0].x} ${adjustedPositions[0].y}`

            // Generuj płynną ścieżkę przez wszystkie punkty
            for (let i = 0; i < adjustedPositions.length - 1; i++) {
                const p0 = adjustedPositions[i - 1] || adjustedPositions[i]
                const p1 = adjustedPositions[i]
                const p2 = adjustedPositions[i + 1]
                const p3 = adjustedPositions[i + 2]

                // Oblicz punkty kontrolne dla płynnej krzywej
                const { cp2: startCp } = createSmoothCurve(p0, p1, p2, i, adjustedPositions.length)
                const { cp1: endCp } = createSmoothCurve(p1, p2, p3, i + 1, adjustedPositions.length)

                // Dodaj meandry do punktów kontrolnych
                const meander1 = addMeander(p1, p2, 0.2)
                const meander2 = addMeander(p1, p2, -0.15)

                // Zmodyfikowane punkty kontrolne z meandrami
                const cp1 = {
                    x: startCp.x + meander1.x,
                    y: startCp.y + meander1.y
                }
                const cp2 = {
                    x: endCp.x + meander2.x,
                    y: endCp.y + meander2.y
                }

                // Dodaj krzywą Beziera
                path += ` C ${cp1.x} ${cp1.y}, ${cp2.x} ${cp2.y}, ${p2.x} ${p2.y}`
            }

            return path
        },
        roadmapPathAdvanced() {
            const positions = this.milestonePositions.slice(0, this.roadmapMilestones.length)
            if (positions.length < 2) return ''

            // Catmull-Rom spline dla ultra-płynnej krzywej
            const catmullRomSpline = (p0, p1, p2, p3, t) => {
                const t2 = t * t
                const t3 = t2 * t

                const v0 = (p2 - p0) * 0.5
                const v1 = (p3 - p1) * 0.5

                return (2 * p1 - 2 * p2 + v0 + v1) * t3 +
                    (-3 * p1 + 3 * p2 - 2 * v0 - v1) * t2 +
                    v0 * t + p1
            }

            // Generuj płynną ścieżkę z meandrami
            let path = `M ${positions[0].x} ${positions[0].y}`

            for (let i = 0; i < positions.length - 1; i++) {
                const p0 = positions[Math.max(0, i - 1)]
                const p1 = positions[i]
                const p2 = positions[i + 1]
                const p3 = positions[Math.min(positions.length - 1, i + 2)]

                // Odległość między punktami
                const distance = Math.sqrt(
                    Math.pow(p2.x - p1.x, 2) +
                    Math.pow(p2.y - p1.y, 2)
                )

                // Liczba segmentów proporcjonalna do odległości
                const segments = Math.max(3, Math.floor(distance / 50))

                // Generuj punkty pośrednie używając Catmull-Rom
                const curvePoints = []
                for (let j = 0; j <= segments; j++) {
                    const t = j / segments

                    // Podstawowa pozycja ze spline'a
                    const x = catmullRomSpline(p0.x, p1.x, p2.x, p3.x, t)
                    const y = catmullRomSpline(p0.y, p1.y, p2.y, p3.y, t)

                    // Dodaj delikatne meandrowanie
                    const meander = Math.sin(t * Math.PI * 2 + i) * distance * 0.05
                    const angle = Math.atan2(p2.y - p1.y, p2.x - p1.x) + Math.PI / 2

                    curvePoints.push({
                        x: x + Math.cos(angle) * meander,
                        y: y + Math.sin(angle) * meander
                    })
                }

                // Konwertuj punkty na ścieżkę Beziera
                for (let j = 1; j < curvePoints.length; j++) {
                    const prev = curvePoints[j - 1]
                    const curr = curvePoints[j]
                    const next = curvePoints[j + 1] || curr

                    // Tangent w poprzednim punkcie
                    const dx1 = curr.x - prev.x
                    const dy1 = curr.y - prev.y

                    // Tangent w bieżącym punkcie
                    const dx2 = next.x - prev.x
                    const dy2 = next.y - prev.y

                    // Punkty kontrolne
                    const cp1x = prev.x + dx1 * 0.33
                    const cp1y = prev.y + dy1 * 0.33
                    const cp2x = curr.x - dx2 * 0.16
                    const cp2y = curr.y - dy2 * 0.16

                    path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${curr.x} ${curr.y}`
                }
            }

            return path
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

/* Sekcje */
.section-title {
    animation: fadeInUp 0.8s ease-out;
}

.fade-in-text {
    animation: fadeInUp 0.8s ease-out;
}

/* Desktop Roadmapa - meandrujący layout */
.roadmap-meandering {
    position: relative;
    width: 100%;
    max-width: 1200px;
    height: 800px;
    margin: 0 auto;
    padding: 20px;
}

.roadmap-path-svg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    pointer-events: none;
}

.roadmap-item-meandering {
    position: absolute;
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: fadeInUp 0.8s ease-out both;
    z-index: 3;
    transform: translate(-50%, -50%);
}

.roadmap-item-meandering.card-top {
    flex-direction: column-reverse;
}

.timeline-card-meandering {
    width: 220px;
    margin: 20px 0;
    transition: all 0.3s ease;
    border-radius: 8px;
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.timeline-card-meandering:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.timeline-card-meandering.card-top {
    margin-bottom: 20px;
    margin-top: 0;
}

.timeline-dot-meandering {
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

.timeline-dot-meandering:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
}

.timeline-connector-meandering {
    width: 2px;
    height: 15px;
    background: #1976D2;
    z-index: 2;
}

.timeline-connector-meandering.connector-top {
    order: -1;
}

.timeline-date {
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.timeline-title {
    font-weight: 600;
    font-size: 0.85rem;
    line-height: 1.2;
    margin-top: 2px;
}

.timeline-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
}

.feature-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
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

/* Status opacity */
.roadmap-item-meandering.completed {
    opacity: 1;
}

.roadmap-item-meandering.in-progress {
    opacity: 0.95;
}

.roadmap-item-meandering.planned {
    opacity: 0.8;
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

/* Responsywność dla meandrującej roadmapy */
@media (max-width: 1200px) {
    .roadmap-meandering {
        max-width: 1000px;
        height: 700px;
    }

    .timeline-card-meandering {
        width: 200px;
        font-size: 0.9rem;
    }
}

@media (max-width: 992px) {
    .roadmap-meandering {
        max-width: 800px;
        height: 600px;
    }

    .timeline-card-meandering {
        width: 180px;
    }

    .timeline-dot-meandering {
        width: 32px;
        height: 32px;
    }
}

@media (max-width: 768px) {
    .roadmap-meandering {
        display: none !important;
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
}

/* Scroll animations */
@media (prefers-reduced-motion: no-preference) {
    .fade-in-text,
    .roadmap-item-meandering,
    .roadmap-mobile-item {
        opacity: 0;
        animation-fill-mode: both;
    }

    /* Trigger animations on scroll using Intersection Observer */
    .fade-in-text.visible,
    .roadmap-item-meandering.visible,
    .roadmap-mobile-item.visible {
        animation-play-state: running;
    }
}
</style>
