<template>
    <v-row class="d-flex align-center">
        <v-col cols="4">
            <search-box
                :value="filters.searchQuery.value"
                @update:value="handleFilterUpdate('searchQuery', $event)"
            />
        </v-col>
        <v-col cols="auto">
            <length-filter
                :value="filters.length.value"
                @update:value="handleFilterUpdate('length', $event)"
            />
        </v-col>
        <v-col cols="auto">
            <difficulty-filter
                :value="filters.difficulty.value"
                @update:value="handleFilterUpdate('difficulty', $event)"
            />
        </v-col>
        <v-col cols="auto">
            <scenery-filter
                :value="filters.scenery.value"
                @update:value="handleFilterUpdate('scenery', $event)"
            />
        </v-col>
        <v-col cols="auto">
            <rating-filter
                :value="filters.rating.value"
                @update:value="handleFilterUpdate('rating', $event)"
            />
        </v-col>
    </v-row>
</template>

<script>
import { mapActions, mapState } from 'vuex'
import SearchBox from './Toolbar/SearchBox.vue'
import LengthFilter from './Toolbar/LengthFilter.vue'
import DifficultyFilter from './Toolbar/DifficultyFilter.vue'
import SceneryFilter from './Toolbar/SceneryFilter.vue'
import RatingFilter from "@/modules/trails/components/Toolbar/RatingFilter.vue"

export default {
    name: 'TrailsFiltersToolbar',
    components: {
        SearchBox,
        LengthFilter,
        DifficultyFilter,
        SceneryFilter,
        RatingFilter
    },
    computed: {
        ...mapState('trails', ['filters'])
    },
    methods: {
        ...mapActions('trails', ['updateFilter', 'applyFilters']),

        handleFilterUpdate(filterName, value) {

            this.updateFilter({ filterName, value, touched: true });
            this.applyFilters()
        }
    }
}
</script>
