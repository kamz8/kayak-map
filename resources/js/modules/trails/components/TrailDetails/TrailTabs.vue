<template>
        <v-tabs
            v-model="activeTab"
            :grow="$vuetify.display.mdAndDown"
            align-tabs="left"
            :density="$vuetify.display.mdAndDown ? 'compact' : 'default'"
        >
            <v-tab
                v-for="tab in tabs"
                :key="tab.id"
                :value="tab.id"
            >
                {{ tab.title }} <span v-if="tab.count">({{tab.count}})</span>
            </v-tab>
        </v-tabs>

        <v-divider />

        <v-window v-model="activeTab">
            <v-window-item
                v-for="tab in tabs"
                :key="tab.id"
                :value="tab.id"
            >
                <v-card flat style="min-height: 100px">
                    <keep-alive>
                        <component
                            :is="markRaw(tab.component)"
                            v-bind="markRaw(tab.props)"
                            @update="handleUpdate"
                            :ref="markRaw(tab.id)"
                        />
                    </keep-alive>
                </v-card>
            </v-window-item>
        </v-window>

</template>

<script>
import {markRaw, shallowRef} from "vue";

export default {
    name: 'TrailTabs',

    props: {
        tabs: {
            type: Array,
            required: true,
            validator(tabs) {
                return tabs.every(tab =>
                    tab.id &&
                    tab.title &&
                    tab.component,

                )
            }
        },
        count: {
            type: Number,
            required: false,
            default: 0
        },
        initialTab: {
            type: String,
            default: null
        }
    },

    data() {
        return {
            activeTab: this.initialTab || this.tabs[0]?.id
        }
    },

    watch: {
        activeTab(newTab) {
            this.$emit('tab-change', newTab)
        }
    },

    methods: {
        markRaw,
        handleUpdate(data) {
            this.$emit('update', {
                tabId: this.activeTab,
                data
            })
        }
    }
}
</script>
