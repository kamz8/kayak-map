<template>
    <v-toolbar color="white" density="compact" elevation="4">
        <v-toolbar-title class="font-weight-bold" style="font-size: 1em">Trasy dostępne w okolicy ({{trails.length}})</v-toolbar-title>
    </v-toolbar>
    <v-divider></v-divider>
    <TrailCard v-for="trail in trails" :key="trail.id" :trail="trail" class="trail-card"/>
    <trails-not-found v-if="trails.length === 0 && !loading"></trails-not-found>
</template>

<script>
import {mapGetters} from "vuex";
import TrailCard from "@/modules/trails/components/TrailCard.vue";
import TrailsNotFound from "@/modules/trails/components/TrailsNotFound.vue";

export default {
    name: "sidebarTrails",
    components: {TrailsNotFound, TrailCard},
    computed: {
        ...mapGetters({
            trails: 'trails/trails',
            loading: 'trails/loading'
        }),
    },
    mounted() {
        console.log('sidebar')
    }
}
</script>


<style scoped>
.trail-card:nth-of-type(1) {
    margin-top: 0;
}
</style>
