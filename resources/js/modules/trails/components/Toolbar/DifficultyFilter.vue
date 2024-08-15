<template>
    <BaseFilter
        ref="baseFilter"
        :button-text="buttonText"
        :show-clear-button="localSelectedDifficulties.length > 0"
        @clear="clearDifficulty"
        @apply="applyDifficulty"
    >
        <template #filter-content>
            <v-list>
                <v-list-item v-for="(item, index) in difficulties" :key="index" class="px-2">
                    <template v-slot:prepend>
                        <v-checkbox-btn
                            v-model="localSelectedDifficulties"
                            :value="item.value"
                            color="primary"
                            hide-details
                            class="pl-1"
                        ></v-checkbox-btn>
                    </template>
                    <v-list-item-title>{{ item.text }}</v-list-item-title>
                </v-list-item>
            </v-list>
        </template>
    </BaseFilter>
</template>

<script>
import BaseFilter from '@/components/BaseFilter.vue';

export default {
    name: 'DifficultyFilter',
    components: { BaseFilter },
    props: {
        value: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            localSelectedDifficulties: this.value,
            difficulties: [
                { text: 'Łatwy', value: 'łatwy' },
                { text: 'Umiarkowany', value: 'umiarkowany' },
                { text: 'Trudny', value: 'trudny' }
            ]
        };
    },
    computed: {
        buttonText() {
            if (this.localSelectedDifficulties.length === 0) return 'Trudność';
            const firstSelected = this.difficulties.find(d => d.value === this.localSelectedDifficulties[0]);
            if (this.localSelectedDifficulties.length === 1) return firstSelected.text;
            return `${firstSelected.text} +${this.localSelectedDifficulties.length - 1}`;
        }
    },
    methods: {
        applyDifficulty() {
            this.$emit('update:value', this.localSelectedDifficulties);
        },
        clearDifficulty() {
            this.localSelectedDifficulties = [];
            this.$emit('update:value', []);
        }
    },
    watch: {
        value(newValue) {
            this.localSelectedDifficulties = newValue;
        }
    }
};
</script>
