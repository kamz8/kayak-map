<template>
<v-container>
    <Map></Map>
</v-container>
    <template v-slot:toolbar-content>
        jakaś szukajaka
    </template>
</template>

<script>
import Map from "@/modules/trails/components/MapView.vue";

export default {
    name: "Explore",
    components: {Map}
}
</script>

<style scoped>

</style>
