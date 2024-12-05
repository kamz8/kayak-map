<template>
    <v-slide-group
        class="filters-slide-group"
        :show-arrows="false"
        mobile-breakpoint="md"
    >
        <!-- Search Box Filter -->
        <v-slide-group-item>
            <search-box
                :value="filters.searchQuery.value"
                @update:value="handleFilterUpdate('searchQuery', $event)"
                class="mr-1"
            />
        </v-slide-group-item>

        <!-- Length Filter -->
        <v-slide-group-item>
            <length-filter
                :value="filters.length.value"
                @update:value="handleFilterUpdate('length', $event)"
                class="w-25 filter-item"
            />
        </v-slide-group-item>

        <!-- Difficulty Filter -->
        <v-slide-group-item>
            <difficulty-filter
                :value="filters.difficulty.value"
                @update:value="handleFilterUpdate('difficulty', $event)"
                class="filter-item"
            />
        </v-slide-group-item>

        <!-- Scenery Filter -->
        <v-slide-group-item>
            <scenery-filter
                :value="filters.scenery.value"
                @update:value="handleFilterUpdate('scenery', $event)"
                class="filter-item"
            />
        </v-slide-group-item>

        <!-- Rating Filter -->
        <v-slide-group-item>
            <rating-filter
                :value="filters.rating.value"
                @update:value="handleFilterUpdate('rating', $event)"
                class="filter-item"
            />
        </v-slide-group-item>
    </v-slide-group>
</template>

<script>
import { mapActions, mapState } from 'vuex';
import SearchBox from './Toolbar/SearchBox.vue';
import LengthFilter from './Toolbar/LengthFilter.vue';
import DifficultyFilter from './Toolbar/DifficultyFilter.vue';
import SceneryFilter from './Toolbar/SceneryFilter.vue';
import RatingFilter from '@/modules/trails/components/Toolbar/RatingFilter.vue';

export default {
    name: 'TrailsFiltersToolbar',
    components: {
        SearchBox,
        LengthFilter,
        DifficultyFilter,
        SceneryFilter,
        RatingFilter,
    },
    computed: {
        ...mapState('trails', ['filters']),
    },
    methods: {
        ...mapActions('trails', ['updateFilter', 'applyFilters']),

        handleFilterUpdate(filterName, value) {
            this.updateFilter({ filterName, value, touched: true });
            this.applyFilters();
        },
    },
};
</script>

<style scoped>
.filters-slide-group {
    height: 64px;
}

:deep(.v-slide-group__content) {
    gap: 0.5em;
    align-items: center;
    padding: 0 16px;
}

:deep(.v-slide-group-item) {
    display: flex;
    align-items: center;
    height: 100%;
}

.filter-item {
    height: 100%;
    display: flex;
    align-items: center;
}

/*@media (max-width: 960px) {
    .w-md-33 {
        width: 33% !important;
    }
}*/
</style>
