<template>
    <v-col cols="12">
        <v-text-field
            v-model="searchQuery"
            label="Wpisz nazwę miasta, parku lub nazwy szlaku"
            prepend-icon="mdi-magnify"
            class="ma-2"
            @input="searchTrails"
            clearable
        ></v-text-field>
        <v-btn icon @click="toggleFilters">
            <v-icon>mdi-filter-variant</v-icon>
        </v-btn>
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
/* Dodaj stylizacje, jeśli potrzebne */
</style>
