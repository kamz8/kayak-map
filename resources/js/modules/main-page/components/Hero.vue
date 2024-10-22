<template>
    <v-container class="hero-section ma-0 pa-0" fill-height fluid>
        <v-carousel
            :show-arrows="false"
            height="100%"
            cycle
            :interval="carouselInterval"
            hide-delimiter-background
            hide-delimiters
            v-model="currentSlide"
        >
            <v-carousel-item v-for="(image, i) in images" :key="i">
                <v-img
                    gradient="to top right, rgba(102, 117, 206, 0.1), rgba(33, 36, 54, 0.4)"
                    :src="image"
                    height="100%"
                    cover
                ></v-img>
            </v-carousel-item>

            <v-row class="carousel-controls" no-gutters>
                <v-col cols="auto" v-for="(image, i) in images" :key="i" class="my-1">
                    <BaseBtnProgress
                        :interval="carouselInterval"
                        :is-active="currentSlide === i"
                        @progress-click="currentSlide = i"
                    />
                </v-col>
            </v-row>
        </v-carousel>
<!--   end carusel     -->
        <v-container fluid tag="section" class="overlay">
            <v-row>
                <v-container class="overlay-content d-flex">
                    <v-col>
                        <h1 class="headline">Znajdź wymarzoną trasę</h1>

                        <search-component/>
                    </v-col>
                    <v-row class="pt-4">
                        <v-col>
                            <router-link class="text-white text-uppercase" style="font-size: 1.2em" :to="'/explore'">Odkryj swoją okolicę</router-link>
                        </v-col>
                    </v-row>
                </v-container>
            </v-row>
        </v-container>
    </v-container>
</template>

<script>
import BaseBtnProgress from "@/components/BaseBtnProgress.vue";
import SearchBox from "@/modules/trails/components/Toolbar/SearchBox.vue";
import SearchComponent from "@/modules/main-page/components/MainSearch/SearchComponent.vue";

export default {
    name: 'HeroSection',
    components: {SearchComponent, SearchBox, BaseBtnProgress },
    props: {
        city: {
            type: String,
            default: ''
        },
        coordinates: {
            type: Object,
            default: () => ({ lat: 0, lng: 0 })
        }
    },
    data() {
        return {
            search: '',
            currentSlide: 0,
            carouselInterval: 10000,
            images: [
                'https://images.pexels.com/photos/2422463/pexels-photo-2422463.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
                './storage/assets/pexels-bri-schneiter-28802-346529.jpg',
                './storage/assets/pexels-jonathan-lassen-1263409-2404667.jpg',
                'https://images.pexels.com/photos/1522344/pexels-photo-1522344.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            ],
            recentSearches: [
                { name: 'Milicz', details: 'City • Lower Silesian, Poland' },
                { name: 'Racibórz - Polska Cerekiew', details: 'Trail • Silesian, Poland' },
                { name: 'Barycz Valley Landscape Park', details: 'Park • Lower Silesian, Poland' },
                { name: 'Walk along Stara Odra River', details: 'Trail • Lower Silesian, Poland' },
                { name: 'Wroclaw', details: 'City • Lower Silesian, Poland' },
            ],
        };
    },
};
</script>

<style scoped>
.hero-section {
    position: relative;
    height: 50vh;
}

.v-carousel__controls__item {
    background: transparent;
}

.v-carousel__control--active .v-icon {
    color: white;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.overlay-content {
    border-radius: 8px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.headline {
    font-family: 'Poppins', sans-serif;
    text-shadow: rgba(0, 0, 0, 0.16);
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 20px;
    color: white;
}

.search-box {
    background-color: white;
    width: 65%;
    border-radius: 25px;
    line-height: 2.5em;
    margin: 0 auto;
}

.search-box .map-search {
    font-size: 2em;
}

.v-field__prepend-inner i {
    font-size: 1.8em;
}

.bottom-gradient {
    background-image: linear-gradient(to top, rgba(102, 117, 206, 0.33), rgba(33, 36, 54, 0.7), transparent 72px);
}
</style>
