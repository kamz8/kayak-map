<template>
    <v-container
        fluid
        class="d-flex flex-column align-center justify-center fill-height position-relative overflow-hidden"
    >
        <!-- Dodajemy fale przed piaskiem -->
        <div class="waves-container">
            <div class="wave wave1"></div>
            <div class="wave wave2"></div>
            <div class="wave wave3"></div>
        </div>
        <!-- Piasek w tle -->
        <div class="sand-background"></div>

        <!-- Efekt fali na piasku -->
        <div class="sand-wave"></div>

        <!-- Ikona kajaka -->
        <v-icon
            :color="theme.current.value.colors['river-blue']"
            size="120"
            class="mb-8 animate-paddle position-relative"
        >
            mdi-kayaking
        </v-icon>

        <!-- Numer błędu -->
        <h1
            class="text-h2 font-weight-regular mb-4 position-relative"
            :style="{ color: theme.current.value.colors['river-surface'] }"
        >
            404
        </h1>

        <!-- Główny nagłówek -->
        <h2
            class="text-h3 font-weight-regular mb-4 text-center position-relative"
            :style="{ color: theme.current.value.colors['river-blue'] }"
        >
            Ups! Wpadliśmy na mieliznę
        </h2>

        <!-- Tekst pomocniczy -->
        <p
            class="text-body-1 text-center mb-8 position-relative"
            style="max-width: 600px"
            :style="{ color: theme.current.value.colors['river-blue-light'] }"
        >
            Ta strona to jak ten jeden próg na rzece -
            wszyscy o nim słyszeli, ale nikt go nie znalazł.
            Może wrócimy na główny nurt?
        </p>

        <!-- Przycisk -->
        <v-btn
            :color="theme.current.value.colors['river-blue']"
            size="large"
            class="text-capitalize px-8 white--text position-relative"
            rounded
            to="/"
            elevation="2"
        >
            Wracamy na główny szlak
        </v-btn>
    </v-container>
</template>

<script setup>
import { useTheme } from 'vuetify'

const theme = useTheme()
</script>

<style scoped>
.fill-height {
    min-height: 100vh;
    background: linear-gradient(
        180deg,
        rgba(92, 130, 153, 0.1) 0%,
        rgba(38, 62, 74, 0.05) 100%
    );
}

/* Tło z piaskiem */
.sand-background {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40vh;
    background: linear-gradient(
        180deg,
        #f0e6d2 0%,
        #e6d5b8 60%,
        #d4c4a7 100%
    );
    opacity: 0.6;
    z-index: 0;
}

/* Tekstura piasku */
.sand-background::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
        radial-gradient(circle at 50% 50%, rgba(0,0,0,0.05) 0%, transparent 1%),
        radial-gradient(circle at 30% 30%, rgba(0,0,0,0.05) 0%, transparent 1%),
        radial-gradient(circle at 70% 70%, rgba(0,0,0,0.05) 0%, transparent 1%);
    background-size: 20px 20px;
    animation: sandShift 10s linear infinite;
}

/* Fala na piasku */
.sand-wave {
    position: absolute;
    bottom: 40vh;
    left: 0;
    right: 0;
    height: 50px;
    background: linear-gradient(
        180deg,
        transparent,
        rgba(240, 230, 210, 0.6) 40%,
        rgba(240, 230, 210, 0.8) 100%
    );
    clip-path: polygon(
        0% 100%,
        5% 95%,
        10% 98%,
        15% 95%,
        20% 97%,
        25% 94%,
        30% 98%,
        35% 95%,
        40% 97%,
        45% 94%,
        50% 98%,
        55% 95%,
        60% 97%,
        65% 94%,
        70% 98%,
        75% 95%,
        80% 97%,
        85% 94%,
        90% 98%,
        95% 95%,
        100% 97%,
        100% 100%
    );
}

/* Animacja przesuwania tekstury piasku */
@keyframes sandShift {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 20px 20px;
    }
}

.animate-paddle {
    animation: paddle 4s ease-in-out infinite;
    transform-origin: center;
    z-index: 1;
}

@keyframes paddle {
    0% {
        transform: rotate(0deg) translateY(0px);
    }
    25% {
        transform: rotate(-5deg) translateY(-5px);
    }
    75% {
        transform: rotate(5deg) translateY(5px);
    }
    100% {
        transform: rotate(0deg) translateY(0px);
    }
}

/* Cień pod kajakiem */
.animate-paddle::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 10px;
    background: rgba(0,0,0,0.1);
    border-radius: 50%;
    filter: blur(4px);
    animation: shadowMove 4s ease-in-out infinite;
}

@keyframes shadowMove {
    0%, 100% {
        transform: translateX(-50%) scaleX(1);
        opacity: 0.3;
    }
    50% {
        transform: translateX(-50%) scaleX(1.2);
        opacity: 0.2;
    }
}

.v-btn {
    transition: transform 0.3s ease;
}

.v-btn:hover {
    transform: translateY(-2px);
}

/* Responsywność */
@media (max-width: 600px) {
    .sand-background {
        height: 30vh;
    }

    .sand-wave {
        bottom: 30vh;
    }
}

/* Dodatkowy efekt ziarenek piasku */
.sand-background::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23000000' fill-opacity='0.03'%3E%3Ccircle cx='25' cy='25' r='1'/%3E%3Ccircle cx='75' cy='75' r='1'/%3E%3Ccircle cx='50' cy='50' r='1'/%3E%3C/g%3E%3C/svg%3E");
    animation: sandParticles 15s linear infinite;
}

@keyframes sandParticles {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 100px 100px;
    }
}

.waves-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 40vh; /* Wyrównanie z piaskiem */
    overflow: hidden;
}

/* Podstawowy styl fali */
.wave {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 88.7'%3E%3Cpath d='M800 56.9c-155.5 0-204.9-50-405.5-49.3-200 .7-250 49.3-394.5 49.3v31.8h800v-31.8z' fill='%23263e4a'/%3E%3C/svg%3E");
    background-size: 800px 100px;
    width: 6400px; /* 800 * 8 dla płynnej animacji */
    animation: wave 20s cubic-bezier(0.36, 0.45, 0.63, 0.53) infinite;
    transform: translate3d(0, 0, 0);
}

/* Indywidualne style fal */
.wave1 {
    opacity: 0.3;
    animation: wave 20s cubic-bezier(0.36, 0.45, 0.63, 0.53) infinite;
    animation-delay: 0s;
    z-index: 3;
}

.wave2 {
    opacity: 0.2;
    animation: wave 25s cubic-bezier(0.36, 0.45, 0.63, 0.53) -.125s infinite;
    z-index: 2;
}

.wave3 {
    opacity: 0.1;
    animation: wave 30s cubic-bezier(0.36, 0.45, 0.63, 0.53) -.25s infinite;
    z-index: 1;
}

@keyframes wave {
    0% {
        transform: translateX(0);
    }
    50% {
        /* transform: translateX(-25%); */
        transform: translateX(-800px);
    }
    100% {
        /* transform: translateX(-50%); */
        transform: translateX(-1600px);
    }
}

/* Dostosowanie tła - dodajemy gradient przypominający wodę */
.fill-height {
    background: linear-gradient(
        180deg,
        rgba(38, 62, 74, 0.1) 0%,
        rgba(38, 62, 74, 0.2) 50%,
        rgba(38, 62, 74, 0.3) 100%
    );
}

/* Modyfikacja kontenera piasku dla lepszego przejścia */
.sand-background {
    position: absolute;
    bottom: 0;
    left: -5%; /* Rozszerzamy nieco poza kontener */
    right: -5%;
    height: 40vh;
    background: linear-gradient(
        180deg,
        #f0e6d2 0%,
        #e6d5b8 60%,
        #d4c4a7 100%
    );
    opacity: 0.8;
    z-index: 0;
    border-top-left-radius: 50% 20px;
    border-top-right-radius: 50% 20px;
}

/* Dodanie efektu połysku na wodzie */
.waves-container::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(255, 255, 255, 0.05) 50%,
        transparent 100%
    );
    animation: shimmer 10s linear infinite;
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Responsywność */
@media (max-width: 768px) {
    .wave {
        height: 80px;
        background-size: 600px 80px;
    }

    .waves-container {
        bottom: 30vh;
    }
}
</style>
