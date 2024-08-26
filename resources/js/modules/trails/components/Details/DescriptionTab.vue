<template>
    <v-tabs v-model="activeTab">
        <v-tab key="description">Opis</v-tab>
    </v-tabs>
    <v-divider></v-divider>
    <v-tabs-items v-model="activeTab">
        <v-tab-item key="description">
            <v-card flat>
                <v-card-text v-if="description">
                    {{ description }}
                </v-card-text>
                <v-card-text v-else>
                    <span class="text-grey-lighten-1 font-weight-light">Trasa nie ma jeszcze opisu</span>
                </v-card-text>
            </v-card>
        </v-tab-item>

    </v-tabs-items>
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
