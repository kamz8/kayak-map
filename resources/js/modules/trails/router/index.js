import SidebarTrailsOverview from "@/modules/trails/components/SidebarTrailsOverview.vue";
import MapOveriew from "@/modules/trails/components/SingleTrailMap.vue";
import TrailOverviewToolbar from "@/modules/trails/components/TrailOverviewToolbar.vue";
import store from "@/store/index.js";
import trailDetails from "@/modules/trails/pages/TrailDetails.vue";

const explore = () => import('../pages/Explore.vue')

const MapComponent = () => import('../components/MapView.vue');
const SidebarTrails = () => import('../components/SidebarTrails.vue');
const TrailsFiltersToolbar = () => import('../components/TrailsFiltersToolbar.vue');
const APP_NAME = 'Kayak'
export default [
    {
        path: '/explore',
        component: explore,
        name: 'explore',
        meta: {
            layout: 'ExploreLayout',
            title: 'Wartki Nurt - Odkrywaj',
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
                    const trail = store.state.trails.currentTrail;
                    const mainImageUrl = trail.main_image ? trail.main_image.path : '';
                    to.meta.title = `Odkryj szlak ${trail.trail_name} | ${APP_NAME}`;
                    to.meta.metaTags = [
                        {
                            name: 'description',
                            content: trail.description || `Odkryj szlak kajakowy ${trail.trail_name} o długości ${trail.trail_length}km`
                        },
                        {
                            property: 'og:title',
                            content: `Odkryj szlak ${trail.trail_name} | ${APP_NAME}`
                        },
                        {
                            property: 'og:description',
                            content: trail.description || `Odkryj szlak kajakowy ${trail.trail_name} o długości ${trail.trail_length}km`
                        },
                        {
                            property: 'og:image',
                            content: mainImageUrl
                        },
                        {
                            property: 'og:type',
                            content: 'website'
                        },
                        {
                            property: 'og:url',
                            content: `https://twojastrona.pl/explore/trail/${trail.slug}`
                        },
                        {
                            name: 'twitter:card',
                            content: 'summary_large_image'
                        },
                        {
                            name: 'twitter:image',
                            content: mainImageUrl
                        }
                    ];
                    next();
                })
                .catch(error => {
                    console.error('Error fetching trail details:', error);
                    if (error.response?.status === 404) {
                        next({
                            name: 'not-found',
                            params: {
                                pathMatch: to.path.substring(1).split('/')
                            }
                        })
                    }
                    next(false);
                });
        },
        meta: {
            layout: 'ExploreLayout',
        },
        components: {
            main: MapOveriew,
            sidebar: SidebarTrailsOverview,
            toolbar: TrailOverviewToolbar
        },
    },
    {
        path: '/trail/:slug',
        name: 'trail-details',
        component: trailDetails,
        beforeEnter: (to, from, next) => {
                store.dispatch('trails/fetchTrailDetails', to.params.slug)
                .then(() => {
                    const trail = store.state.trails.currentTrail;
                    const mainImageUrl = trail.main_image ? trail.main_image.path : '';
                    to.meta.title = `Odkryj szlak ${trail.trail_name} | ${APP_NAME}`;
                    to.meta.metaTags = [
                        {
                            name: 'description',
                            content: trail.description || `Odkryj szlak kajakowy ${trail.trail_name} o długości ${trail.trail_length}km`
                        },
                        {
                            property: 'og:title',
                            content: `Poznaj szlak ${trail.trail_name} | ${APP_NAME}`
                        },
                        {
                            property: 'og:description',
                            content: trail.description || `Odkryj szlak kajakowy ${trail.trail_name} o długości ${trail.trail_length}km`
                        },
                        {
                            property: 'og:image',
                            content: mainImageUrl
                        },
                        {
                            property: 'og:type',
                            content: 'website'
                        },
                        {
                            property: 'og:url',
                            content: `https://twojastrona.pl/explore/trail/${trail.slug}`
                        },
                        {
                            name: 'twitter:card',
                            content: 'summary_large_image'
                        },
                        {
                            name: 'twitter:image',
                            content: mainImageUrl
                        }
                    ];
                    next();
                })
                .catch(error => {
                    if (error.response?.status === 404) {
                        next({
                            name: 'not-found',
                            params: {
                                pathMatch: to.path.substring(1).split('/')
                            }
                        })
                    }
                    console.error('Error fetching trail details:', error);
                    next(false);
                });
        },
        meta: {
            layout: 'MainLayout',
        },

    },
];

