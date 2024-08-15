const explore = () => import('../pages/Explore.vue')

const MapComponent = () => import('../components/MapView.vue');
const SidebarTrails = () => import('../components/SidebarTrails.vue');
const TrailsFiltersToolbar = () => import('../components/TrailsFiltersToolbar.vue');
export default [
    {
        path: '/explore',
        component: explore,
        meta: {
            layout: 'ExploreLayout',
            title: 'KAYAK - Odkrywaj',
            metaTags: [
                {
                    name: 'description',
                    content: 'Odkrywaj trasy z KAYAK. Przeglądaj mapę i znajdź najlepsze trasy dla siebie'
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


    },
    {
        path: '/explore/trail/:slug',
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


    },
];

