<template>
  <v-sheet class="mx-auto my-4 mt-4 text-center" max-width="300">
    <base-progress color="primary" size="200" class="ma-8"/>

    <h2 class="text-h5 font-weight-black">
        Sprawdzmy trasy w naszej bazie....
    </h2>
  </v-sheet>
</template>

<script>
import BaseProgress from "@/components/BaseProgress.vue";

export default {
  name: 'TrailsLoading',
  components: {BaseProgress},


};
</script>

<style scoped>
.v-carousel {
  position: relative;
  overflow: hidden;
}

.custom-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 1;
  background-color: transparent !important;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.custom-arrow .v-icon {
  font-size: 24px;
  color: rgba(255, 255, 255, 0.7);
}

.v-carousel:hover .custom-arrow {
  opacity: 1;
}

.custom-arrow:hover .v-icon {
  color: rgba(255, 255, 255, 1);
}

.custom-arrow:first-of-type {
  left: 5px;
}

.custom-arrow:last-of-type {
  right: 5px;
}

.custom-controls {
  position: absolute;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  justify-content: center;
  align-items: center;
}

.custom-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.5);
  margin: 0 3px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.dot-large {
  transform: scale(1.5);
  background-color: white;
}

.dot-medium {
  transform: scale(1.25);
}

.dot-small {
  transform: scale(1);
}

.dot-hidden {
  opacity: 0;
  width: 0;
  margin: 0;
}
</style>
