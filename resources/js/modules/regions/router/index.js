export default [
    {
        path: '/regions',
        name: 'regions',
        component: () => import('@/modules/regions/pages/RegionGuide.vue'),
        meta: {
            layout: 'MainLayout',
            title: 'KAYAK - Regiony kajakowe',
            metaTags: [
                {
                    name: 'description',
                    content: 'Odkryj regiony kajakowe w Polsce. Przeglądaj trasy według państwa, województw i regionów geograficznych.'
                },
                {
                    property: 'og:description',
                    content: 'Odkryj regiony kajakowe w Polsce. Przeglądaj trasy według państwa, województw i regionów geograficznych.'
                }
            ]
        }
    },
    {
        path: '/region/:slug(.*)',
        name: 'region',
        component: () => import('@/modules/regions/pages/Region.vue'),
        props: true,
        meta: {
            layout: 'MainLayout',
            title: 'KAYAK - Region kajakowy',
            metaTags: [
                {
                    name: 'description',
                    content: 'Szczegółowe informacje o regionie kajakowym. Poznaj dostępne trasy, poziomy trudności i atrakcje.'
                },
                {
                    property: 'og:description',
                    content: 'Szczegółowe informacje o regionie kajakowym. Poznaj dostępne trasy, poziomy trudności i atrakcje.'
                }
            ]
        }
    }
];
