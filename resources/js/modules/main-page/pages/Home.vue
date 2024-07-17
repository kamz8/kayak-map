<template>
    <MainLayout>
        <SectionOne />
        <SectionTwo />
        <SectionThree />
    </MainLayout>
</template>

<script>
import MainLayout from '@/layouts/MainLayout.vue';
import SectionOne from '@/modules/home/components/Sections/SectionOne.vue';
import SectionTwo from '@/modules/home/components/Sections/SectionTwo.vue';
import SectionThree from '@/modules/home/components/Sections/SectionThree.vue';

export default {
    name: 'Home',
    components: {
        MainLayout,
        SectionOne,
        SectionTwo,
        SectionThree
    }
};
</script>

<style>
/* Możesz dodać swoje style tutaj */
</style>
