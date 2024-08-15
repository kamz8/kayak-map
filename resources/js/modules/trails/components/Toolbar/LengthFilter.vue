<template>
    <BaseFilter
        ref="baseFilter"
        :button-text="buttonText"
        :show-clear-button="range[0] > 0 || range[1] < maxLength"
        @clear="clearLength"
        @apply="applyLength"
    >
        <template #filter-content>
            <v-card-text>
                <div class="text-center mb-2">{{ `${range[0]} km - ${range[1]} km` }}</div>
                <v-range-slider
                    v-model="range"
                    :max="maxLength"
                    :min="0"
                    step="1"
                    hide-details
                    class="align-center"
                    color="primary"
                ></v-range-slider>
            </v-card-text>
        </template>
    </BaseFilter>
</template>

<script>
import BaseFilter from '@/components/BaseFilter.vue';

export default {
    name: 'LengthFilter',
    components: { BaseFilter },
    props: {
        value: {
            type: Object,
            default: () => ({ min: 0, max: 100 })
        }
    },
    data() {
        return {
            range: [this.value.min, this.value.max],
            maxLength: 100 // Możesz dostosować maksymalną długość
        };
    },
    computed: {
        buttonText() {
            if (this.range[0] === 0 && this.range[1] === this.maxLength) return 'Długość';
            return `${this.range[0]} km - ${this.range[1]} km`;
        }
    },
    methods: {
        applyLength() {
            this.$emit('update:value', { min: this.range[0], max: this.range[1] });
        },
        clearLength() {
            this.range = [0, this.maxLength];
            this.$emit('update:value', { min: 0, max: this.maxLength });
        }
    },
    watch: {
        value: {
            handler(newValue) {
                this.range = [newValue.min, newValue.max];
            },
            deep: true
        }
    }
};
</script>
