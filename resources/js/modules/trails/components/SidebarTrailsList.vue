<template>
    <v-list>
        <TrailCard v-for="trail in trails" :key="trail.id" :trail="trail"/>
    </v-list>
</template>

<script>
export default {
    name: "sidebarTrails"
}
</script>


<style scoped>

</style>
