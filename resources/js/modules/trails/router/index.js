import SidebarTrailsOverview from "@/modules/trails/components/SidebarTrailsOverview.vue";
import MapOveriew from "@/modules/trails/components/SingleTrailMap.vue";
import TrailOverviewToolbar from "@/modules/trails/components/TrailOverviewToolbar.vue";
import store from "@/store/index.js";

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
        name: 'trail-overview',
        component: explore,
        beforeEnter: (to, from, next) => {
            store.dispatch('trails/fetchTrailDetails', to.params.slug)
                .then(() => {
                    next();
                })
                .catch(error => {

                });
        },
        meta: {
            layout: 'ExploreLayout',
            title: 'Podgląd szlaku',
            metaTags: [
                {
                    name: 'description',
                    content: 'Super trasa'
                },
                {
                    property: 'og:description',
                    content: 'Opis strony odkrywania dla Open Graph'
                }
            ]
        },
        components: {
            main: MapOveriew,
            sidebar: SidebarTrailsOverview,
            toolbar: TrailOverviewToolbar
        },


    },
];

