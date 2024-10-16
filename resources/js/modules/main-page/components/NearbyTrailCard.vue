<template>
  <v-card class="mx-auto my-4" outlined>
    <v-img :src="trail.image || appConfig.placeholderImage" cover eager height="200px">
      <template v-slot:placeholder>
        <div class="no-images text-grey-lighten-3 overlay">
          <v-icon size="large">mdi-image-off</v-icon>
          <span>Brak dostępnych zdjęć</span>
        </div>
      </template>
    </v-img>
    <v-card-title>{{ trail.name }}</v-card-title>
    <v-card-subtitle>{{ trail.location_name ?? '' }}</v-card-subtitle>
    <v-card-subtitle>
      <v-rating v-model="trail.rating" readonly size="xs" color="yellow" >{{trail.rating}}</v-rating>
    </v-card-subtitle>
    <v-card-text>
      <span>{{ trail.length }} km - {{ trail.distance ?? '' }} - {{ trail.difficulty }}</span>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: 'NearbyTrailCard',
  props: {
    trail: {
      type: Object,
      required: true
    },
    appConfig: {
      type: Object,
      required: true
    }
  },
};
</script>

<style scoped>
.no-images {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  background: rgba(0, 0, 0, 0.3);
}

.no-images v-icon {
  margin-bottom: 8px;
}
</style>
