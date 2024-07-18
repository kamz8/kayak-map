const explore = () => import('../pages/Explore.vue')

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
        }
    },

];

