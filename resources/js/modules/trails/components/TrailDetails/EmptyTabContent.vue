<template>
    <v-row class="fill-height" align="center" justify="center">
        <v-col cols="12" class="text-center">
            <v-icon
                :icon="icon"
                size="64"
                color="grey-lighten-1"
            />
            <div class="text-grey text-h6 mt-4">
                <template v-if="message">{{message}}</template>
                <slot></slot>
            </div>
        </v-col>
    </v-row>
</template>

<script>
export default {
    name: "EmptyTabContent",
    props: {
        icon:{
            default: 'mdi-block-helper',

        },
        message: {
            default: '',

        }
    }
}
</script>



<style scoped>

</style>
