<template>
        <v-card flat width="200px" height="60px">
            <v-row class="ma-1">
                <v-col cols="12" class="pa-2">
                    <span class="font-weight-bold">{{ text }}</span>
                    <v-spacer></v-spacer>
                    <span style="line-height: 1.8em" class="text-grey-darken-2">{{latLang[0]}}, {{latLang[1]}}</span>
                </v-col>
            </v-row>
        </v-card>
</template>

<script>
export default {
    name: "MiniPopup",
    props: {
        latLang: {
            default: [],
            required: true
        },
        text: {
            default: 'punkt'
        }
    },
    data() {
        return {
            miniPopupOptions: {
                closeButton: false,
                className: 'mini-popup'
            }
        }
    },
}
</script>



<style scoped>

</style>
