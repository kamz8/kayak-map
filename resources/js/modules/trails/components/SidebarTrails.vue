<template>
    <v-toolbar color="white" density="compact" elevation="4">
        <v-toolbar-title class="font-weight-bold" style="font-size: 1em">Trasy dostępne w okolicy ({{trails.length}})</v-toolbar-title>
    </v-toolbar>
    <v-divider></v-divider>
    <TrailCard v-for="trail in trails" :key="trail.id" :trail="trail" class="trail-card"/>
    <trails-not-found v-if="trails.length === 0 && !loading"></trails-not-found>
    <trails-loading v-if="trails.length === 0 && loading"></trails-loading>
</template>

<script>
import {mapGetters} from "vuex";
import TrailCard from "@/modules/trails/components/TrailCard.vue";
import TrailsNotFound from "@/modules/trails/components/TrailsNotFound.vue";
import TrailsLoading from "@/modules/trails/components/TrailsLoading.vue";

export default {
    name: "sidebarTrails",
    components: {TrailsLoading, TrailsNotFound, TrailCard},
    computed: {
        ...mapGetters({
            trails: 'trails/trails',
            loading: 'trails/loading'
        }),
    },
}
</script>


<style scoped>
.trail-card:nth-of-type(1) {
    margin-top: 0;
}
</style>
