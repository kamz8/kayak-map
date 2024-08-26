<template>
<v-container>
    <SingleTrailMap />
</v-container>
</template>

<script>
import Map from "@/modules/trails/components/MapView.vue";
import SingleTrailMap from "@/modules/trails/components/SingleTrailMap.vue";
import {mapActions} from "vuex";

export default {
    name: "Explore",
    components: {SingleTrailMap, Map},
}
</script>

<style scoped>

</style>
