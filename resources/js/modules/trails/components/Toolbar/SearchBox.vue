<template>
    <v-col cols="12" class="d-flex align-center">
        <v-text-field
            v-model.trim="localSearchQuery"
            label="Wpisz nazwę miasta, parku lub nazwy szlaku"
            prepend-inner-icon="mdi-magnify"
            density="compact"
            variant="solo"
            hide-details
            single-line
            filled
            rounded
            class="ma-2"
            @keyup.enter="applySearch"
        >
            <template v-slot:append-inner>
                <v-fade-transition leave-absolute>
                    <v-btn v-if="localSearchQuery" key="clear" size="comfortable" variant="tonal" @click="clearSearch" icon="mdi-close" />
                </v-fade-transition>
            </template>
        </v-text-field>
    </v-col>
</template>

<script>
export default {
    props: {
        value: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            localSearchQuery: this.value,
        }
    },
    watch: {
        value(newValue) {
            this.localSearchQuery = newValue;
        }
    },
    methods: {
        applySearch() {
            this.$emit('update:value', this.localSearchQuery);
        },
        clearSearch() {
            this.localSearchQuery = '';
            this.$emit('update:value', '');
        }
    },
}
</script>
