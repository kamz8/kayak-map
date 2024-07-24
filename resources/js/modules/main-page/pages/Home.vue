<template>
    <HeroSection :city="city" :coordinates="coordinates" />
        <v-container fluid class="mt-5 mx-0">
            <v-row>
                <v-container>
                    <v-row>
                        <v-col cols="12">
                            <v-img src="https://images.pexels.com/photos/2525517/pexels-photo-2525517.jpeg" aspect-ratio="16/9">
                                <div class="search-container">
                                    <v-text-field
                                        label="Search by city, park, or trail name"
                                        filled
                                        rounded
                                        prepend-inner-icon="mdi-magnify"
                                    ></v-text-field>
                                </div>
                            </v-img>
                        </v-col>
                    </v-row>
                    <v-row class="mt-5">
                        <v-col cols="12">
                            <h2>Lokalne szlaki w pobliżu {{city}}</h2>
                        </v-col>
                        <v-col cols="3" v-for="(trail, index) in trails" :key="index">
                            <v-card>
                                <v-img :src="trail.image" height="200px"></v-img>
                                <v-card-title>{{ trail.name }}</v-card-title>
                                <v-card-subtitle>{{ trail.location }}</v-card-subtitle>
                                <v-card-text>
                                    <v-rating v-model="trail.rating" color="yellow" readonly></v-rating>
                                    <span>{{ trail.distance }} km - {{ trail.difficulty }}</span>
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-container>
            </v-row>
        </v-container>
    <section-one></section-one>
    <section-two></section-two>
    <section-three/>
</template>

<script>
import MainLayout from '@/layouts/MainLayout.vue';
import SectionOne from "@/modules/main-page/components/sections/SectionOne.vue";
import SectionTwo from "@/modules/main-page/components/sections/SectionTwo.vue";
import SectionThree from "@/modules/main-page/components/sections/SectionThree.vue";
import HeroSection from "@/modules/main-page/components/Hero.vue";
import { useGeolocation } from '@vueuse/core'
import {watchEffect} from "vue";

export default {
    name: 'Home',
    components: {
        HeroSection,
        SectionThree,
        SectionTwo,
        SectionOne,
        MainLayout
    },
    data() {
        return {
            trails: [
                {
                    name: 'Sobótka - Ślęża',
                    location: 'Ślęża Landscape Park',
                    image: 'https://images.pexels.com/photos/2525517/pexels-photo-2525517.jpeg',
                    rating: 5,
                    distance: 9.8,
                    difficulty: 'Moderate'
                },
                {
                    name: 'Tapada - Ślęża Nature Trail',
                    location: 'Ślęża Landscape Park',
                    image: 'https://images.pexels.com/photos/2525517/pexels-photo-2525517.jpeg',
                    rating: 4.5,
                    distance: 6.0,
                    difficulty: 'Moderate'
                },
                {
                    name: 'Wielka Wyspa Loop',
                    location: 'Wroclaw, Lower Silesian, Poland',
                    image: 'https://images.pexels.com/photos/2525517/pexels-photo-2525517.jpeg',
                    rating: 4.4,
                    distance: 12.2,
                    difficulty: 'Moderate'
                },
                {
                    name: 'Tapada Pass - Ślęża',
                    location: 'Ślęża Landscape Park',
                    image: 'https://images.pexels.com/photos/2525517/pexels-photo-2525517.jpeg',
                    rating: 4.4,
                    distance: 7.7,
                    difficulty: 'Moderate'
                }
            ],
            city: '',
            coordinates: {
                lat: null,
                lng: null
            },
        };
    },
    created() {
        const { coords } = useGeolocation();

        watchEffect(() => {
            if (coords.value) {
                this.coordinates.lat = coords.value.latitude;
                this.coordinates.lng = coords.value.longitude;
                this.getCityName(coords.value.latitude, coords.value.longitude);
            }
        });
    },
    methods: {
        async getCityName(lat, lng) {

            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
            try {
                const response = await fetch(url);
                const data = await response.json();
                this.city = data.address.city || data.address.town || data.address.village || 'Unknown location';
            } catch (error) {
                console.error('Error fetching city name:', error);
            }
        }
    }
};
</script>

<style scoped>
.search-container {
    position: absolute;
    bottom: 10%;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
}

.v-text-field .v-input__control {
    background-color: white;
    border-radius: 25px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}
</style>
