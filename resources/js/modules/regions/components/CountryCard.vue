<template>
    <v-card
        class="h-100 country-card d-inline-flex"
        elevation="1"
        @click="$emit('click')"
        rounded="xl"
        :max-width="this.$vuetify.display.smAndDown ? 270 : 300"
        height="380"
        :color="country.color ?? 'river-blue'"
    >
        <v-img
            :src="country.image"
            height="200"
            cover
            gradient="to top, rgba(0,0,0,.8), rgba(0,0,0,0)"
        >
            <template v-slot:placeholder>
                <v-row
                    class="fill-height ma-0"
                    align="center"
                    justify="center"
                >
                    <v-progress-circular
                        indeterminate
                        color="grey-lighten-5"
                    />
                </v-row>
            </template>

            <v-card-title class="text-white mt-auto country-card-title">
                <div>
                    <h3 class="text-h5 font-weight-bold">{{ country.name }}</h3>
                    <p class="text-body-2">{{ country.trailsCount }} szlaków</p>
                </div>
            </v-card-title>
        </v-img>

        <v-card-text class="countrycard-text">
            <p class="text-body-2 mb-4 country-description">{{ country.description }}</p>
            <v-row no-gutters align="center" justify="space-between">
                <v-col>
                    <v-icon size="small" class="mr-1">mdi-image-filter-hdr-outline</v-icon>
                    <span class="text-body-2">{{ country.nationalParks }} parków</span>
                </v-col>
                <v-col class="text-right">
                    <v-icon size="small" class="mr-1">mdi-map</v-icon>
                    <span class="text-body-2">{{ country.regionsCount }} regionów</span>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>

<script>
export default {
    name: 'CountryCard',

    props: {
        country: {
            type: Object,
            required: true
        }
    }
}
</script>

<style>
.country-card {
    display: flex;
    flex: 0 0 300px;
    flex-direction: column;
    height: 100%;
    transition: scale .3s ease-in-out 0s,transform .4s ease-in-out 0s;
    scale: 1;
}

.country-card:hover {
    transform: translateY(-10px);
    scale: 1.05;
}

.country-card-title{
    background: linear-gradient(180deg, rgba(12,22,27,0.8015406846332283) 0%, rgba(255,255,255,0) 100%);
}
.countrycard-text{
    min-height: 110px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.country-description{
    min-height: 3em;
}

@media (max-width: 959.98px) {
    .country-card {
        flex: 0 0 290px;
    }
}
</style>
