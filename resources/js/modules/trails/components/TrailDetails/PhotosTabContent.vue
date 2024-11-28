<template>
<v-container style="min-height: 200px">
    <template v-if="photos?.length">
        <v-row dense>
            <v-col
                v-for="photo in photos"
                :key="photos.id"
                class="d-flex child-flex"
                cols="6"
                sm="3"
                md="4"
                lg="3"
            >
                <v-img
                    :lazy-src="photo.path"
                    :src="photo.path"
                    aspect-ratio="1"
                    class="bg-grey-lighten-2"
                    cover
                    rounded="lg"
                >
                    <template v-slot:placeholder>
                        <v-row
                            align="center"
                            class="fill-height ma-0"
                            justify="center"
                        >
                            <v-progress-circular
                                color="grey-lighten-5"
                                indeterminate
                            ></v-progress-circular>
                        </v-row>
                    </template>
                </v-img>
            </v-col>
            <v-col cols="12" class="text-center">
                <v-btn
                    v-if="hasMorePhotos"
                    :color="theme.current.value.colors['river-blue']"
                    size="large"
                    density="comfortable"
                    class="text-capitalize px-8 white--text position-relative pt-2"
                    rounded
                    to="/"
                    elevation="0"
                >
                    Zobacz więcej zdjęć
                </v-btn>
            </v-col>
        </v-row>

    </template>
    <template v-else>
        <empty-tab-content icon="mdi-image-off">Brak zdjęć do wyświetlenia</empty-tab-content>
    </template>
</v-container>
</template>

<script>
import EmptyTabContent from "@/modules/trails/components/TrailDetails/EmptyTabContent.vue";
import {mapGetters} from "vuex";
import { useTheme } from 'vuetify'
const PHOTOS_LIMIT = 20;

export default {
    name: "PhotosTabContent",
    components: {EmptyTabContent},
    setup() {
        const theme = useTheme()
        return {
            theme,
            PHOTOS_LIMIT
        }
    },
    computed: {
        ...mapGetters('trails', ['currentTrail']),
        photos() {
            console.log(this.currentTrail.images)
            if (!this.currentTrail.images) return [];

            return this.currentTrail.images
                .slice() // tworzy kopię tablicy, aby nie modyfikować oryginału
                .sort((a, b) => {
                    // Sortowanie po polu order
                    if (a.order === b.order) {
                        return moment(b.created_at).diff(moment(a.created_at));
                    }
                    return a.order - b.order;
                })
                .slice(0, 20); // limit do 20 zdjęć
        },
    },
    hasMorePhotos() {
        return this.currentTrail?.images?.length > PHOTOS_LIMIT;
    },

    totalPhotosCount() {
        return this.currentTrail?.images?.length || 0;
    }
}
</script>
