<template>
    <v-menu v-model="isMenuOpen" :close-on-content-click="false" location="bottom">
        <template v-slot:activator="{ props }">
            <v-btn
                v-bind="props"
                variant="outlined"
                class="filter-btn"
                :class="{ 'with-clear-btn': showClearButton }"
                :min-width="minLabelWith"
            >
                <v-icon v-if="icon" start>{{ icon }}</v-icon>
                {{ buttonText }}
                <v-btn
                    v-if="showClearButton"
                    icon="mdi-close"
                    size="x-small"
                    class="clear-btn"
                    @click.stop="clearFilter"
                ></v-btn>
                <v-icon v-else end>{{ isMenuOpen ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
            </v-btn>
        </template>
        <v-card :min-width="minWidth" style="overflow: hidden">
            <slot name="filter-content"></slot>
            <v-divider></v-divider>
            <v-card-actions class="pa-3">
                <v-spacer></v-spacer>
                <v-btn variant="text" rounded @click="clearFilter">{{ clearButtonText }}</v-btn>
                <v-btn color="primary" variant="flat" rounded @click="applyFilter" class="px-3">
                    {{ applyButtonText }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-menu>
</template>

<script>
export default {
    name: 'BaseFilter',
    props: {
        buttonText: { type: String, required: true },
        icon: { type: String, default: '' },
        showClearButton: { type: Boolean, default: false },
        minWidth: { type: [String, Number], default: 280 },
        clearButtonText: { type: String, default: 'Wyczyść' },
        applyButtonText: { type: String, default: 'Zastosuj' },
        menuOverflowX: { type: Boolean, default: false },
        minLabelWith: {type: String, default: '2em'}
    },
    data() {
        return {
            isMenuOpen: false
        };
    },
    methods: {
        clearFilter() {
            this.$emit('clear');
            this.isMenuOpen = false;
        },
        applyFilter() {
            this.$emit('apply');
            this.isMenuOpen = false;
        }
    }
};
</script>

<style scoped>
.filter-btn {
    font-weight: normal;
    text-transform: none;
    position: relative;
}
.filter-btn.with-clear-btn {
    padding-right: 32px;
}
.clear-btn {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
}
.v-card-actions .v-btn {
    font-size: 12px !important;
}
</style>
