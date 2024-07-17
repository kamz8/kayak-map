<template>
    <v-container>
        <v-carousel>
            <v-carousel-item v-for="(src, i) in images" :key="i" :src="src"></v-carousel-item>
        </v-carousel>
        <header class="header-content">
            <h1>Spłyń w nowe miejsce</h1>
            <v-text-field label="Wpisz miejscowość, region, rzekę" outlined hide-details></v-text-field>
        </header>
    </v-container>
</template>

<script>
export default {
    name: 'Header',
    data() {
        return {
            images: [
                'https://images.pexels.com/photos/1005415/pexels-photo-1005415.jpeg',
                'https://images.pexels.com/photos/104827/canoe-fisherman-lake-nature-104827.jpeg',
                'https://images.pexels.com/photos/705244/pexels-photo-705244.jpeg'
            ]
        };
    }
};
</script>

<style>
.header-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
}
</style>
