<template>
    <v-breadcrumbs>
        <template v-for="(region, index) in sortedRegions" :key="region.id">
            <v-breadcrumbs-item>{{ region.name }}</v-breadcrumbs-item>
            <v-breadcrumbs-divider v-if="index < sortedRegions.length - 1">
                <v-icon icon="mdi-circle-small"/>
            </v-breadcrumbs-divider>
        </template>
    </v-breadcrumbs>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: "TrailOverviewToolbar",
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
