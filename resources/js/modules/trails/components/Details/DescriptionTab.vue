<template>
    <v-tabs v-model="activeTab">
        <v-tab key="description">Opis</v-tab>
    </v-tabs>
    <v-divider></v-divider>
    <v-tabs-window v-model="activeTab">
        <v-tabs-window-item key="description">
            <v-card flat>
                <v-card-text v-if="description" v-html="description">
                </v-card-text>
                <v-card-text v-else>
                    <span class="text-grey-lighten-1 font-weight-light">Trasa nie ma jeszcze opisu</span>
                </v-card-text>
            </v-card>
        </v-tabs-window-item>

    </v-tabs-window>
</template>

<script>
export default {
    name: "DescriptionTab",
    props: {
        description: {
            default: null
        }
    },
    data() {
      return{
          activeTab: null
      }
    }
}
</script>



<style scoped>

</style>
