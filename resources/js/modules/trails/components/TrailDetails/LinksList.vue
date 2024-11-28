<template>
    <v-container style="min-height: 200px">
        <template v-if="links?.length">
            <v-row>
                <v-col cols="12">
                    <v-list>
                        <v-list-item
                            v-for="link in links"
                            :key="link.id"
                            :href="link.url"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <template v-slot:prepend>
                                <v-icon icon="mdi-link" />
                            </template>

                            <v-list-item-title>
                                {{ getLinkTitle(link) }}
                            </v-list-item-title>

                            <v-list-item-subtitle v-if="getLinkDescription(link)">
                                {{ getLinkDescription(link) }}
                            </v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                </v-col>
            </v-row>
        </template>
        <template v-else>
            <empty-tab-content icon="mdi-link-variant-off">Brak punktów do wyświetlenia</empty-tab-content>
        </template>
    </v-container>
</template>

<script>
import EmptyTabContent from "@/modules/trails/components/TrailDetails/EmptyTabContent.vue";
import {mapGetters} from "vuex";

export default {
    name: 'LinksList',
    components: {EmptyTabContent},
    computed: {
        ...mapGetters('trails', ['currentTrail']),
        links() {
            return this.currentTrail.links
        }
    },

    methods: {
        getLinkTitle(link) {
            return link.meta_data?.title || new URL(link.url).hostname
        },

        getLinkDescription(link) {
            return link.meta_data?.description || null
        }
    }
}
</script>
