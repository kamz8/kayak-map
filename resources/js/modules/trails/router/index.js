const explore = () => import('../pages/Explore.vue')

const MapComponent = () => import('../components/Map.vue');
const SidebarTrails = () => import('../components/sidebarTrails.vue');
const TrailsFiltersToolbar = () => import('../components/TrailsFiltersToolbar.vue');
export default [
    {
        path: '/explore',
        component: explore,
        meta: {
            layout: 'ExploreLayout',
            title: 'Odkrywaj',
            metaTags: [
                {
                    name: 'description',
                    content: 'Opis strony odkrywania'
                },
                {
                    property: 'og:description',
                    content: 'Opis strony odkrywania dla Open Graph'
                }
            ]
        },
        components: {
            main: MapComponent,
            sidebar: SidebarTrails,
            toolbar: TrailsFiltersToolbar
        },
        children: []

    },

];

