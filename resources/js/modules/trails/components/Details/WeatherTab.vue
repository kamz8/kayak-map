<template>
        <v-tabs v-model="activeTab">
            <v-tab value="weather">Pogoda</v-tab>
        </v-tabs>
        <v-divider></v-divider>
        <v-window v-model="activeTab">
            <v-window-item value="weather">
                <v-card flat>
                    <v-card-text>
                        <div v-if="weatherData && weatherData.properties && weatherData.properties.timeseries">
                            <v-btn-toggle v-model="activeDay" mandatory rounded>
                                <v-btn
                                    v-for="(day, index) in weatherData.properties.timeseries"
                                    :key="index"
                                    :value="index"
                                    color="primary"
                                >
                                    {{ getDayName(day.time) }}<br>
                                    {{ getDayNumber(day.time) }}
                                </v-btn>
                            </v-btn-toggle>

                            <v-row class="mt-4" v-if="activeDay !== null">
                                <v-col cols="8">
                                    <div class="d-flex align-center">
                                        <div style="font-size: 60px" class="font-weight-bold mr-4">{{ Math.round(getCurrentDayTemp()) }}°C</div>
                                        <v-icon size="60" :color="getWeatherIconColor(getCurrentDaySymbol())">
                                            {{ getWeatherIcon(getCurrentDaySymbol()) }}
                                        </v-icon>
                                    </div>
                                    <div class="text-subtitle-1 mt-2">{{ getWeatherDescription(getCurrentDaySymbol()) }}</div>
                                </v-col>
                                <v-col cols="4">
                                    <div class="text-body-2">
                                        <v-icon small>mdi-weather-windy</v-icon>
                                        {{ getCurrentDayWindSpeed() }} m/s
                                    </div>
                                    <div class="text-body-2 mt-1">
                                        <v-icon small>mdi-water</v-icon>
                                        {{ getCurrentDayHumidity() }}%
                                    </div>

                                </v-col>
                            </v-row>
                        </div>
                        <v-alert v-else-if="error" type="warning" text>
                            {{ error }}
                        </v-alert>
                        <v-progress-circular v-else indeterminate color="primary"></v-progress-circular>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <div class="text-caption">
                            Dane pogodowe: <a href="https://www.yr.no/" target="_blank" rel="noopener noreferrer">Yr.no</a>
                        </div>
                    </v-card-actions>
                </v-card>
            </v-window-item>
        </v-window>
</template>

<script>
import axios from 'axios';
import apiClient from "@/plugins/apiClient.js";

export default {
    name: "WeatherTab",
    props: {
        latitude: {
            type: Number,
            required: true
        },
        longitude: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            activeTab: null,
            activeDay: 0,
            weatherData: null,
            error: null,
            cacheKey: 'weatherData',
            cacheTime: 3600000 // 1 godzina w milisekundach
        }
    },
    computed: {
        isLocationAvailable() {
            return typeof this.latitude === 'number' && typeof this.longitude === 'number';
        },
        cacheIndex() {
            return this.cacheKey+"-"+this.latitude+this.longitude
        }
    },
    watch: {
        latitude: 'fetchWeatherData',
        longitude: 'fetchWeatherData'
    },
    mounted() {
        this.fetchWeatherData();
    },
    methods: {
        async fetchWeatherData() {
            if (!this.isLocationAvailable) {
                this.error = 'Brak danych o lokalizacji.';
                return;
            }

            const cachedData = this.getCachedData();
            if (cachedData) {
                this.weatherData = cachedData;
                return;
            }

            try {
                const response = await apiClient.get(`/weather`, {
                    params: {
                        lat: this.latitude,
                        lon: this.longitude
                    }
                });
                if (response.data && response.data.properties && response.data.properties.timeseries) {
                    this.weatherData = response.data;
                    this.cacheData(this.weatherData);
                    this.error = null;
                } else {
                    throw new Error('Nieprawidłowa struktura danych pogodowych');
                }
            } catch (error) {
                console.error('Error fetching weather data:', error);
                this.error = 'Nie udało się pobrać danych pogodowych.';
            }
        },
        getDayName(dateString) {
            const days = ['Niedz', 'Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob'];
            return days[new Date(dateString).getDay()];
        },
        getDayNumber(dateString) {
            return new Date(dateString).getDate();
        },
        getWeatherIcon(symbolCode) {
            const iconMap = {
                clearsky_day: "mdi-weather-sunny",
                fair_day: "mdi-weather-partly-cloudy",
                partlycloudy_day: "mdi-weather-partly-cloudy",
                cloudy: "mdi-weather-cloudy",
                rainshowers_day: "mdi-weather-rainy",
                lightrainshowers_day: "mdi-weather-rainy",
                rain: "mdi-weather-pouring",
                heavyrain: "mdi-weather-pouring",
                thunderstorm: "mdi-weather-lightning-rainy",
                fog: "mdi-weather-fog",
                snow: "mdi-weather-snowy",
                sleet: "mdi-weather-snowy-rainy", // Deszcz ze śniegiem
                clearsky_night: "mdi-weather-night", // Bezchmurna noc
                fair_night: "mdi-weather-night-partly-cloudy", // Pogodna noc
                partlycloudy_night: "mdi-weather-night-partly-cloudy", // Częściowe zachmurzenie w nocy
                rainshowers_night: "mdi-weather-rainy", // Przelotne opady deszczu w nocy
                lightrainshowers_night: "mdi-weather-rainy", // Lekkie opady deszczu w nocy
                heavyrainshowers_day: "mdi-weather-pouring", // Silne przelotne opady deszczu
                heavyrainshowers_night: "mdi-weather-pouring", // Silne przelotne opady deszczu w nocy
                heavysleet: "mdi-weather-snowy-rainy", // Silny deszcz ze śniegiem
                heavysnow: "mdi-weather-snowy-heavy", // Silne opady śniegu
                lightrain: "mdi-weather-rainy", // Lekki deszcz
                lightsnow: "mdi-weather-snowy", // Lekkie opady śniegu
                snowshowers_day: "mdi-weather-snowy", // Przelotne opady śniegu w dzień
                snowshowers_night: "mdi-weather-snowy", // Przelotne opady śniegu w nocy
                sleetshowers_day: "mdi-weather-snowy-rainy", // Przelotne opady deszczu ze śniegiem w dzień
                sleetshowers_night: "mdi-weather-snowy-rainy", // Przelotne opady deszczu ze śniegiem w nocy
            };
            return iconMap[symbolCode] || "mdi-help-circle-outline";
        },
        getWeatherIconColor(symbolCode) {
            const colorMap = {
                clearsky_day: "amber",
                fair_day: "gray darken-4",
                partlycloudy_day: "grey darken-5",
                cloudy: "grey",
                rainshowers_day: "blue-grey",
                lightrainshowers_day: "blue-grey",
                rain: "blue",
                heavyrain: "blue darken-2",
                thunderstorm: "deep-purple",
                fog: "grey lighten-1",
                snow: "light-blue lighten-4",
                sleet: "light-blue darken-3", // Deszcz ze śniegiem
                clearsky_night: "blue-grey darken-4", // Bezchmurna noc
                fair_night: "blue-grey darken-3", // Pogodna noc
                partlycloudy_night: "blue-grey darken-2", // Częściowe zachmurzenie w nocy
                rainshowers_night: "blue-grey", // Przelotne opady deszczu w nocy
                lightrainshowers_night: "blue-grey lighten-1", // Lekkie opady deszczu w nocy
                heavyrainshowers_day: "blue darken-3", // Silne przelotne opady deszczu
                heavyrainshowers_night: "blue darken-4", // Silne przelotne opady deszczu w nocy
                heavysleet: "light-blue darken-3", // Silny deszcz ze śniegiem
                heavysnow: "light-blue darken-4", // Silne opady śniegu
            };
            return colorMap[symbolCode] || "grey";
        },
        getWeatherDescription(symbolCode) {
            const descriptions = {
                clearsky_day: 'Bezchmurnie',
                fair_day: 'Pogodnie',
                partlycloudy_day: 'Częściowe zachmurzenie',
                cloudy: 'Pochmurno',
                rainshowers_day: 'Przelotne opady',
                rain: 'Deszcz',
                heavyrain: 'Silny deszcz',
                snow: 'Śnieg',
                sleet: 'Deszcz ze śniegiem',
                fog: 'Mgła',
                thunderstorm: 'Burza',
                lightrainshowers_day: 'Lekkie opady deszczu',
                lightrain: 'Lekki deszcz',
                clearsky_night: 'Bezchmurna noc',
                fair_night: 'Pogodna noc',
                partlycloudy_night: 'Częściowe zachmurzenie w nocy',
                rainshowers_night: 'Przelotne opady deszczu w nocy',
                lightrainshowers_night: 'Lekkie opady deszczu w nocy',
                heavyrainshowers_day: 'Silne przelotne opady deszczu',
                heavyrainshowers_night: 'Silne przelotne opady deszczu w nocy',
                heavysleet: 'Silny deszcz ze śniegiem',
                heavysnow: 'Silne opady śniegu',
                lightsnow: 'Lekkie opady śniegu',
                snowshowers_day: 'Przelotne opady śniegu w dzień',
                snowshowers_night: 'Przelotne opady śniegu w nocy',
                sleetshowers_day: 'Przelotne opady deszczu ze śniegiem w dzień',
                sleetshowers_night: 'Przelotne opady deszczu ze śniegiem w nocy'
            };
            return symbolCode ? (descriptions[symbolCode] || symbolCode) : 'Brak danych';
        },

        cacheData(data) {
            localStorage.setItem(this.cacheIndex, JSON.stringify({
                data: data,
                timestamp: new Date().getTime()
            }));
        },
        getCachedData() {
            const cached = localStorage.getItem(this.cacheIndex);
            if (!cached) return null;

            const { data, timestamp } = JSON.parse(cached);
            if (new Date().getTime() - timestamp > this.cacheTime) {
                localStorage.removeItem(this.cacheIndex);
                return null;
            }

            return data;
        },
        getCurrentDayTemp() {
            return this.weatherData.properties.timeseries[this.activeDay].data.instant.details.air_temperature;
        },
        getCurrentDayMinTemp() {
            return this.weatherData.properties.timeseries[this.activeDay].data.next_6_hours.details.air_temperature_min;
        },
        getCurrentDayMaxTemp() {
            return this.weatherData.properties.timeseries[this.activeDay].data.next_6_hours.details.air_temperature_max;
        },
        getCurrentDaySymbol() {
            return this.weatherData.properties.timeseries[this.activeDay].data.next_6_hours.summary.symbol_code;
        },
        getCurrentDayPrecipitation() {
            return Math.round(this.weatherData.properties.timeseries[this.activeDay].data.next_6_hours.details.probability_of_precipitation);
        },
        getCurrentDayWindSpeed() {
            return this.weatherData.properties.timeseries[this.activeDay].data.instant.details.wind_speed.toFixed(1);
        },
        getCurrentDayHumidity() {
            return Math.round(this.weatherData.properties.timeseries[this.activeDay].data.instant.details.relative_humidity);
        },
    }
}
</script>

<style scoped>
.v-img {
    margin: 0 auto;
}
</style>
