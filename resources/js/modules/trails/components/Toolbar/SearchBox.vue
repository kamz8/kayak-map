<template>
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
            :min-width="width"
            @keyup.enter="applySearch"
        >
            <template v-slot:append-inner>
                <v-fade-transition leave-absolute>
                    <v-btn v-if="localSearchQuery" key="clear" size="comfortable" variant="tonal" @click="clearSearch" icon="mdi-close" />
                </v-fade-transition>
            </template>
        </v-text-field>
</template>

<script>
import {useDisplay} from "vuetify";


export default {
    props: {
        value: {
            type: String,
            default: ''
        }
    },
    setup() {
        const { name } = useDisplay()
        return{
            name
        }
    },
    data() {
        return {
            localSearchQuery: this.value,
        }
    },
    computed: {
        width () {
            switch (this.name) {
                case 'xs': return '300px'
                case 'sm': return '300px'
                case 'md': return '500px'
                default: return '400px'
            }
        },
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

<style>

</style>
