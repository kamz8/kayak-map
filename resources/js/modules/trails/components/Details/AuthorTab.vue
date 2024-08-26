<template>
    <v-tabs v-model="activeTab">
        <v-tab value="author">Autor</v-tab>
    </v-tabs>
    <v-divider></v-divider>
    <v-window v-model="activeTab">
        <v-window-item value="author">
            <v-card flat>
                <v-card-text class="py-4">
                    <div v-if="author">
                        <span class="text-grey">{{ author }}</span>
                        <p v-if="authorDescription">{{ authorDescription }}</p>
                    </div>
                    <span v-else class="text-grey-lighten-1 font-weight-light">Brak informacji o autorze</span>
                </v-card-text>
            </v-card>
        </v-window-item>
    </v-window>
</template>

<script>
export default {
    name: "AuthorTab",
    props: {
        author: String,
        authorDescription: String
    },
    data() {
        return {
            activeTab: null
        }
    }
}
</script>
