const Region = () => import('@/modules/main-page/pages/Region.vue')
const Home = () => import('../../main-page/pages/Home.vue')

export default [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: {
            layout: 'MainLayout',
            title: 'KAYAK - znajdź swoją wymarzoną trasę',
            metaTags: [
                {
                    name: 'description',
                    content: 'Super aplikacja, gdzie znajdziesz swoje trasy na kajaki'
                },
                {
                    property: 'og:description',
                    content: 'Super aplikacja, gdzie znajdziesz swoje trasy na kajaki'
                }
            ]
        }

    },
    {
        path: '/:slug',
        name: 'regions',
        component: Region,
        meta: {
            layout: 'MainLayout',
            title: 'KAYAK - znajdź swoją wymarzoną trasę',
            metaTags: [
                {
                    name: 'description',
                    content: 'Super aplikacja, gdzie znajdziesz swoje trasy na kajaki'
                },
                {
                    property: 'og:description',
                    content: 'Super aplikacja, gdzie znajdziesz swoje trasy na kajaki'
                }
            ]
        }

    }
];
