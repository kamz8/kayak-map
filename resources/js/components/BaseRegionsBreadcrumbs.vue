<template>
    <v-breadcrumbs>
        <template v-for="(region, index) in sortedRegions" :key="region.id">
            <v-breadcrumbs-item>{{ region.name }}</v-breadcrumbs-item>
            <v-breadcrumbs-divider class="px-lg-1 pa-sm-0" v-if="index < sortedRegions.length - 1">
                <v-icon icon="mdi-circle-small"/>
            </v-breadcrumbs-divider>
        </template>
    </v-breadcrumbs>
</template>

<script>
export default {
    name: "BaseRegionsBreadcrumbs",
    props: {
        sortedRegions: {
            required: true,
        }
    }
}
</script>

<style scoped>

</style>
