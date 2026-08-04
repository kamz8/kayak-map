<template>
    <!-- Navbar -->
    <Navbar/>

    <!-- Toolbar for search and filters -->
    <v-app-bar v-if="hasToolbar" app elevation="0">
        <router-view name="toolbar"/>
    </v-app-bar>

    <!-- Sidebar Panel -->
    <v-navigation-drawer v-model="drawer" app clipped :width="drawerWidth" elevation="2">
        <router-view name="sidebar"></router-view>
    </v-navigation-drawer>

    <!-- Drawer toggle button — poza drawerem żeby overflow:hidden go nie przycinał -->
    <v-btn
        icon
        @click="toggleDrawer"
        class="drawer-toggle-btn d-none d-sm-inline-block d-md-inline-block d-lg-inline-block"
        size="s"
    >
        <v-icon>{{ drawer ? 'mdi-chevron-left' : 'mdi-chevron-right' }}</v-icon>
    </v-btn>

    <!-- Main Content -->
    <v-main app class="flex-grow-1">
        <v-container app fluid class="pa-0 d-flex flex-column" :style="mainContainerStyle">
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
        const {name, width} = useDisplay()
        return {
            name,
            displayWidth: width,
        }
    },
    data() {
        return {
            drawer: false,
        }
    },
    computed: {
        hasToolbar() {
            return this.$route.matched.some(record => record.components?.toolbar)
        },
        mainContainerStyle() {
            const appBarHeight = this.hasToolbar ? 66 : 0
            return `height: calc(100vh - 64px - ${appBarHeight}px); overflow: hidden; position: relative;`
        },
        mapToggleBtnText() {
            return (this.drawer) ? "Mapa" : 'Lista'
        },
        isMobile() {
            return ['xs', 'sm'].includes(this.name)
        },
        drawerWidth() {
            return this.isMobile ? this.displayWidth : 400
        },
        toggleBtnLeft() {
            if (this.isMobile) return '0px'
            return this.drawer ? '400px' : '0px'
        },
        toggleBtnTop() {
            const appBarHeight = this.hasToolbar ? 66 : 0
            return `${64 + appBarHeight + 10}px`
        },
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
    position: fixed !important;
    top: v-bind(toggleBtnTop);
    left: v-bind(toggleBtnLeft);
    z-index: 1006;
    background-color: white !important;
    border-radius: 50% !important;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
    border-bottom-left-radius: 0 !important;
    border-top-left-radius: 0 !important;
    height: 2em !important;
    transition: left 0.25s cubic-bezier(0.4, 0, 0.6, 1);
}
.mapToggleButton {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    translate: -50%;
    z-index: 1005;
}

:deep(.v-navigation-drawer__content) {
    overflow: hidden;
}


</style>

<style>
html,
body {
    overflow: hidden;
    height: 100%;
}

.v-navigation-drawer .v-virtual-scroll {
    scrollbar-width: auto;
    scrollbar-color: #9e9e9e transparent;
}

.v-navigation-drawer .v-virtual-scroll::-webkit-scrollbar {
    width: 10px;
}

.v-navigation-drawer .v-virtual-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.v-navigation-drawer .v-virtual-scroll::-webkit-scrollbar-thumb {
    background: #9e9e9e;
    border-radius: 100px;
    transition: background 0.2s ease;
}

.v-navigation-drawer .v-virtual-scroll::-webkit-scrollbar-thumb:hover {
    background: #757575;
}
</style>
