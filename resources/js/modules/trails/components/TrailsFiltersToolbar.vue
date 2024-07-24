<template>
    <v-col cols="3">
        <v-text-field
            v-model="searchQuery"
            label="Wpisz nazwę miasta, parku lub nazwy szlaku"
            prepend-inner-icon="mdi-magnify"
            density="compact"
            variant="solo"
            hide-details
            single-line
            filled
            rounded
            class="ma-2"
            @input="searchTrails"
            clearable
        ></v-text-field>
    </v-col>
    <v-col cols="9">

    </v-col>
</template>

<script>
export default {
    data() {
        return {
            searchQuery: '',
        };
    },
    methods: {
        searchTrails() {
            // Dodaj logikę wyszukiwania tras kajakowych
        },
        toggleFilters() {
            this.$emit('toggle-drawer');
        },
    },
};
</script>



<style scoped>

</style>
