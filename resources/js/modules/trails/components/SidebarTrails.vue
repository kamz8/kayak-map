<template>
    <v-toolbar color="white" density="compact" elevation="4">
        <v-toolbar-title class="font-weight-bold" style="font-size: 1em">Trasy dostępne w okolicy ({{ trails.length }})</v-toolbar-title>
    </v-toolbar>
    <v-divider/>
    <trails-not-found v-if="trails.length === 0 && !loading"/>
    <trails-loading v-if="trails.length === 0 && loading"/>
    <v-virtual-scroll
        v-if="trails.length > 0"
        :items="trails"
        :item-height="350"
        height="100%"
    >
        <template #default="{ item }">
            <TrailCard :trail="item" :key="item.id" class="trail-card"/>
        </template>
    </v-virtual-scroll>
</template>

<script>
import { mapGetters } from "vuex";
import TrailCard from "@/modules/trails/components/TrailCard.vue";
import TrailsNotFound from "@/modules/trails/components/TrailsNotFound.vue";
import TrailsLoading from "@/modules/trails/components/TrailsLoading.vue";

export default {
    name: "SidebarTrails",
    components: { TrailsLoading, TrailsNotFound, TrailCard },
    computed: {
        ...mapGetters({
            trails: 'trails/trails',
            loading: 'trails/loading'
        }),
    },
}
</script>
