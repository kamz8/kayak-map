<template>
    <v-breadcrumbs>
        <template v-for="(region, index) in sortedRegions" :key="region.id">
            <v-breadcrumbs-item
                v-if="index < sortedRegions.length - 1"
                tag="a"
                :to="{
                   name: 'region',
                   params: {
                       slug: buildSlugPath(index)
                   }
               }"
            >
                {{ region.name }}
            </v-breadcrumbs-item>
            <v-breadcrumbs-item
                v-else
                active
                class="font-weight-medium"
            >
                {{ region.name }}
            </v-breadcrumbs-item>
            <v-breadcrumbs-divider
                class="px-lg-1 pa-sm-0"
                v-if="index < sortedRegions.length - 1"
            >
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
            type: Array,
            required: true,
        }
    },

    methods: {
        buildSlugPath(currentIndex) {
            return this.sortedRegions
                .slice(0, currentIndex + 1)
                .map(region => region.slug)
                .join('/');
        }
    }
}
</script>
