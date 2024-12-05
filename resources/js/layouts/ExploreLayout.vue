<template>
    <!-- Navbar -->
    <Navbar/>

    <!-- Toolbar for search and filters -->
    <v-app-bar app elevation="0">
        <router-view name="toolbar"/>
    </v-app-bar>

    <!-- Sidebar Panel -->
    <v-navigation-drawer v-model="drawer" app clipped width="400" elevation="2" class="d-flex flex-column">
        <router-view name="sidebar"></router-view>
        <v-btn
            icon
            @click="toggleDrawer"
            class="drawer-toggle-btn d-none d-sm-inline-block d-md-inline-block d-lg-inline-block"
            size="s"

        >
            <v-icon>{{ drawer ? 'mdi-chevron-left' : 'mdi-chevron-right' }}</v-icon>
        </v-btn>

    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main app class="flex-grow-1">
        <v-container app fluid class="pa-0 d-flex flex-column" style="height: calc(100vh - 64px - 66px);">
            <router-view name="main"></router-view>
<!--    toggle map / list button       -->
                <v-btn variant="flat" size="x-large" density="default" @click="toggleDrawer" color="river-blue" class="mapToggleButton d-inline-block d-md-none d-lg-none" rounded="xl"><v-icon :icon="!drawer ? 'mdi-view-list' : 'mdi-map'"  />
                    &nbsp;{{  mapToggleBtnText }}
                </v-btn>
        </v-container>
    </v-main>

</template>

<script>
import Navbar from '@/modules/main-page/components/Navbar.vue'
import {useDisplay} from "vuetify";

export default {
    components: {
        Navbar,
    },
    setup() {
        const {name} = useDisplay()
        return {
            name
        }
    },
    data() {
        return {
            drawer: false,
        }
    },
    computed: {
        mapToggleBtnText() {
            return (this.drawer) ? "Mapa" : 'Lista'
        },
        drawerOnMobile() {

        }
    },
    methods: {
        toggleDrawer() {
            this.drawer = !this.drawer
            this.$nextTick(() => {
                if (this.$refs.map) {
                    this.$refs.map.mapObject.invalidateSize()
                }
            })
        },
    },
    created() {
        if (this.name === 'md' || this.name === 'lg' || this.name === 'xl' || this.name === 'xxl') {
            this.drawer = true
        }
    }
}
</script>

<style scoped>
.full-height {
    height: 100vh;
}

.drawer-toggle-btn {
    position: absolute;
    top: 10px;
    right: -25px;
    z-index: 1000;
    background-color: white;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    border-bottom-left-radius: 0;
    border-top-left-radius: 0;

    height: 2em !important;
}
.mapToggleButton {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    translate: -50%;
    z-index: 1005;
}

html {
    overflow: initial;
}
</style>
