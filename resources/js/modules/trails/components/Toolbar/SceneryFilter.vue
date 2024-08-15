<template>
    <BaseFilter
        ref="baseFilter"
        :button-text="buttonText"
        icon="mdi-forest"
        :show-clear-button="localSceneryValue > 0"
        min-label-with="50px"
        @clear="clearScenery"
        @apply="applyScenery"
    >
        <template #filter-content>
            <v-card-text>
                <div class="text-center mb-2">{{ localSceneryValue }}</div>
                <v-slider
                    v-model="localSceneryValue"
                    :tick-labels="ticksLabels"
                    :max="10"
                    step="1"
                    show-ticks="always"
                    tick-size="4"
                    :thumb-size="28"
                    :thumb-color="thumbColor"
                    track-size="14"
                    class="custom-slider"
                ></v-slider>
            </v-card-text>
        </template>
    </BaseFilter>
</template>

<script>
import BaseFilter from '@/components/BaseFilter.vue';

export default {
    name: 'SceneryFilter',
    components: { BaseFilter },
    props: {
        value: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            localSceneryValue: this.value,
            ticksLabels: [...Array(11).keys()].map(String)
        };
    },
    computed: {
        buttonText() {
            if (this.localSceneryValue === 0) return 'Malowniczość';
            return ` ${this.localSceneryValue}`;
        },
        thumbColor() {
            const hue = this.localSceneryValue * 12;
            const saturation = 10 + this.localSceneryValue * 8;
            return `hsl(${hue}, ${saturation}%, 50%)`;
        }
    },
    methods: {
        applyScenery() {
            this.$emit('update:value', this.localSceneryValue);
        },
        clearScenery() {
            this.localSceneryValue = 0;
            this.$emit('update:value', 0);
        }
    },
    watch: {
        value(newValue) {
            this.localSceneryValue = newValue;
        }
    }
};
</script>
