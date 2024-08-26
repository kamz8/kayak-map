<template>
    <div class="weather-icon-container">
        <v-icon v-for="(icon, index) in icons" :key="index" :color="icon.color" :size="size" class="weather-icon">
            {{ icon.name }}
        </v-icon>
    </div>
</template>

<script>
export default {
    name: 'WeatherIcon',
    props: {
        symbolCode: {
            type: String,
            required: true
        },
        size: {
            type: [Number, String],
            default: 64
        }
    },
    computed: {
        icons() {
            const iconMap = {
                'clearsky_day': [{ name: 'mdi-weather-sunny', color: 'yellow' }],
                'fair_day': [
                    { name: 'mdi-weather-partly-cloudy', color: 'grey' },
                    { name: 'mdi-weather-sunny', color: 'yellow' }
                ],
                'partlycloudy_day': [
                    { name: 'mdi-weather-partly-cloudy', color: 'grey' },
                    { name: 'mdi-weather-sunny', color: 'yellow darken-2' }
                ],
                'cloudy': [{ name: 'mdi-weather-cloudy', color: 'grey' }],
                'rainshowers_day': [
                    { name: 'mdi-weather-pouring', color: 'blue' },
                    { name: 'mdi-weather-partly-cloudy', color: 'grey' }
                ],
                'rain': [{ name: 'mdi-weather-pouring', color: 'blue' }],
                'heavyrain': [{ name: 'mdi-weather-pouring', color: 'blue darken-2' }],
                'thunderstorm': [
                    { name: 'mdi-weather-lightning', color: 'yellow accent-3' },
                    { name: 'mdi-weather-pouring', color: 'blue' }
                ],
                'fog': [{ name: 'mdi-weather-fog', color: 'grey lighten-1' }],
                'snow': [{ name: 'mdi-weather-snowy', color: 'light-blue lighten-4' }],
                'sleet': [
                    { name: 'mdi-weather-snowy', color: 'light-blue lighten-4' },
                    { name: 'mdi-weather-rainy', color: 'blue lighten-3' }
                ]
            };
            return iconMap[this.symbolCode] || [{ name: 'mdi-help-circle-outline', color: 'grey' }];
        }
    }
}
</script>

<style scoped>
.weather-icon-container {
    position: relative;
    display: inline-block;
}
.weather-icon {
    position: absolute;
    top: 0;
    left: 0;
}
</style>
