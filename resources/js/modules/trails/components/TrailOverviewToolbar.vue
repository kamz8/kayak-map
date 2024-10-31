<template>
    <base-regions-breadcrumbs :sorted-regions="sortedRegions" />
</template>

<script>
import { mapGetters } from 'vuex';
import BaseRegionsBreadcrumbs from "@/components/BaseRegionsBreadcrumbs.vue";

export default {
    name: "TrailOverviewToolbar",
    components: {BaseRegionsBreadcrumbs},
    computed: {
        ...mapGetters({
            currentTrail: 'trails/currentTrail'
        }),
        sortedRegions() {
            if (!this.currentTrail || !this.currentTrail.regions) {
                return [];
            }
            return this.currentTrail.regions.sort((a, b) => {
                const order = ['country', 'state', 'city', 'geographic_area'];
                return order.indexOf(a.type) - order.indexOf(b.type);
            });
        }
    }
}
</script>

<style scoped>
</style>
