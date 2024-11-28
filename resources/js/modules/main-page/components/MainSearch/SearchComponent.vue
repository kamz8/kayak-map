<template>
    <v-autocomplete
        :items="filteredSearches"
        :loading="isLoading"
        :search-input.sync="searchInput"
        prepend-inner-icon="mdi-magnify"
        :placeholder="placeholder"
        persistent-placeholder
        no-filter
        item-title="name"
        item-value="id"
        variant="solo"
        menu-icon=""
        rounded
        clearable
        item-props
        @click:clear="clearSearch"
        @focus="showRecentSearches"
        @update:search="handleSearch"
        @update:model-value="navigateToPage"
        max-width="580px"
        class="mx-auto"
    >
        <!-- Nagłówek "Popularne wyszukiwania" -->
        <template v-if="!search.length && recentSearches.length" v-slot:prepend-item>
            <v-list-subheader class="text-grey-darken-3 font-weight-bold" >Popularne wyszukiwania</v-list-subheader>
        </template>
        <!-- Nagłówek "Wyniki wyszukiwania" -->
        <template v-if="searchInput.length || allSearches.length" v-slot:prepend-item>
            <v-list-subheader class="text-grey-darken-3 font-weight-bold" >Wyniki wyszukiwania</v-list-subheader>
        </template>

        <!-- Wyświetlanie ikony przed elementem listy -->
        <template v-slot:item="{ item, props }">
            <v-list-item v-bind="props" color="primary" class="text-grey-darken-4" variant="plain" link @click="navigateToPage(item.raw)">
                <template v-slot:prepend>
                    <v-sheet rounded width="50" class="d-flex justify-center align-center mr-4 pr-2" height="50" color="grey-lighten-3">
                        <v-icon class="text-blue-darken-4" size="large" :icon="item.raw.icon"/>
                    </v-sheet>
                </template>
                <section class="ml-2">
                    <v-list-item-title>{{ item.name }}</v-list-item-title>
                    <v-list-item-subtitle>{{getTypeLabel(item.raw.type)}} 🞄 {{ item.raw.location }}</v-list-item-subtitle>
                </section>
            </v-list-item>
        </template>

        <template v-slot:no-data>
            <v-list-item>
                <template v-slot:prepend>
                    <v-sheet rounded width="50" class="d-flex justify-center align-center mr-4 pr-2" height="50" color="grey-lighten-3">
                        <v-icon class="text-grey-darken-2" size="large" icon="mdi-magnify">mdi-magnify</v-icon>
                    </v-sheet>
                </template>
                <v-list-item-title>Brak wyników</v-list-item-title>
                <v-list-item-subtitle>Nie znaleziono wyników dla twojego zapytania...</v-list-item-subtitle>
            </v-list-item>
<!--            <v-list-item v-if="recentSearches.length">
                <template v-slot:prepend>
                    <v-sheet rounded width="50" class="d-flex justify-center align-center mr-4 pr-2" height="50" color="grey-lighten-3">
                        <v-icon class="text-grey-darken-2" size="large" icon="mdi-magnify">mdi-magnify</v-icon>
                    </v-sheet>
                </template>
                <v-list-item-title>Na razie nic tu nie ma, ale</v-list-item-title>
                <v-list-item-subtitle>Tutaj pojawią się wyniki twojego wyszukiwania...</v-list-item-subtitle>
            </v-list-item>-->
        </template>
    </v-autocomplete>
</template>

<script>
import debounce from 'lodash/debounce';
import apiClient from "@/plugins/apiClient.js";
export default {
    data() {
        return {
            search: '',
            searchInput: null,
            isLoading: false,
            allSearches: [],
            recentSearches: [],
            placeholder: 'Wyszukaj po nazwie szlaku, rzeki, lub miejsca',
            searchTypes: {
                'trail': {
                    label: 'Szlak',
                    icon: 'mdi-kayaking'
                },
                'city': {
                    label: 'Miasto',
                    icon: 'mdi-city'
                },
                'country': {
                    label: 'Kraj',
                    icon: 'mdi-flag'
                },
                'state': {
                    label: 'Województwo',
                    icon: 'mdi-map'
                },
                'geographic_area': {
                    label: 'Region',
                    icon: 'mdi-map-marker-radius'
                },
            },
            meta: {}
        }
    },
    computed: {
        filteredSearches() {
            return this.searchInput.length || this.allSearches.length ? this.allSearches : this.recentSearches;
        }
    },
    watch: {
        search: {
            immediate: true,
            handler(newVal) {
                this.searchInput = newVal ?? '';
            }
        }
    },
    methods: {
        handleSearch: debounce(async function(val) {
            if (val && val.length > 2) {
                this.isLoading = true;
                try {
                    const response = await apiClient.get(`search?query=${val}`);

                    this.allSearches = response.data.data;
                    this.meta = response.data.meta
                    if (this.allSearches.length > 0) {
                        this.saveSearch(this.allSearches[0]);
                    }
                } catch (error) {
                    console.error("API error: ", error);
                    this.allSearches = [];
                    this.$alertError('Wystąpił nie oczekiwany błąd', error);
                } finally {
                    this.isLoading = false;
                }
            }
        },300),
        navigateToPage(item) {
            if (!item) return;

            // Obiekty typu Trail
            if (item.type === 'trail') {
                return this.$router.push({
                    name: 'trail-overview',
                    params: { slug: item.slug }
                });
            }
            if (['city', 'country', 'state', 'geographic_area', 'area'].includes(item.type)) {
                console.log(item.slug);
                this.$router.push({
                    path: `/region/${item.slug}`,
                    props: {slug: item.slug}
                });
            }

        },
        showRecentSearches() {
            if (!this.searchInput) {
                this.loadRecentSearches();
            }
        },
        saveSearch(searchItem) {
            let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
            // Usuwamy duplikaty na podstawie id i typu
            recentSearches = recentSearches.filter(
                item => !(item.id === searchItem.id && item.type === searchItem.type)
            );

            // Dodajemy nowy element na początek listy
            recentSearches.unshift({
                id: searchItem.id,
                name: searchItem.name,
                type: searchItem.type,
                icon: searchItem.icon,
                slug: searchItem.slug,
                location: searchItem.location
            });
        },

        loadRecentSearches() {
            const recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
            this.recentSearches = recentSearches.map(item => ({
                ...item,
                type: 'recent',
                icon: item.icon || 'mdi-history'
            }));
        },

        clearSearch() {
            this.searchInput = '';
            this.allSearches = [];
            this.loadRecentSearches();
        },
        getTypeLabel(type) {
            return this.searchTypes[type]?.label || type;
        },
    }
};
</script>

