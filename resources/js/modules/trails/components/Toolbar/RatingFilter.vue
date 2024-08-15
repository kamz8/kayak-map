<template>
    <BaseFilter
        ref="baseFilter"
        :button-text="buttonText"
        icon="mdi-star"
        :show-clear-button="localRating > 0"
        @clear="clearRating"
        @apply="applyRating"
    >
        <template #filter-content>
            <v-card-text>
                <div class="text-center mb-2">Minimalna ocena:</div>
                <v-rating
                    v-model="localRating"
                    color="yellow-darken-3"
                    active-color="yellow-darken-3"
                    half-increments
                    hover
                    class="justify-center"
                ></v-rating>
            </v-card-text>
        </template>
    </BaseFilter>
</template>

<script>
import BaseFilter from '@/components/BaseFilter.vue';

export default {
    name: 'RatingFilter',
    components: { BaseFilter },
    props: {
        value: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            localRating: this.value,
        };
    },
    computed: {
        buttonText() {
            if (this.localRating === 0) return 'Ocena';
            return `Ocena: ${this.localRating.toFixed(1)}+`;
        }
    },
    methods: {
        applyRating() {
            this.$emit('update:value', this.localRating);
        },
        clearRating() {
            this.localRating = 0;
            this.$emit('update:value', 0);
        }
    },
    watch: {
        value(newValue) {
            this.localRating = newValue;
        }
    }
};
</script>
