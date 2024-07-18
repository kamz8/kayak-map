<template>
    <section class="section-one">
        <v-container>
            <h2>Sekcja 1</h2>
            <p>Zawartość sekcji 1</p>

        </v-container>
    </section>
</template>

<script>
import BaseBtnProgress from "@/components/BaseBtnProgress.vue";

export default {
    name: 'SectionOne',
    components: {BaseBtnProgress},
    data() {
        return {
            progress: 0
        }
    },
    mounted() {
            this.progress = (this.progress !== 100) ? this.progress++ : 0;

    }
};
</script>

<style scoped>
.section-one {
    background-color: var(--steel-blue);
    color: white;
    padding: 40px 0;
}
</style>
