<template>
  <v-progress-circular
      :color="color"
      indeterminate
      :size="size"
      :width="thickness"
  >
    <v-icon
        class="mx-0"
        :color="color"
        icon="mdi-kayaking"
        :size="size / 2"
    ></v-icon>
  </v-progress-circular>
</template>

<script>
export default {
  name: "BaseProgress",
  props: {
    color: {
      required: true,
      default: 'primary'
    },
    size: {
      required: false,
      default: 50,
      min: 40
    }
  },
  computed: {
    thickness () {
      const baseThick = [5,10,15]

      if (this.size <100) {
        return baseThick[0]
      }
      else if (this.size < 200) {
        return baseThick[1]
      }
      else {
        return baseThick[2]
      }
    }
  }
}
</script>



<style scoped>

</style>
