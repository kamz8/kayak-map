<template>
<!-- show details without map   -->
<v-container>
    <Map></Map>
</v-container>
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
