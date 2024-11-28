import NotFound from "@/modules/main-page/pages/NotFound.vue";

const ExamplePage = () => import("@/modules/main-page/pages/ExamplePage.vue");

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
    // Information pages
    {
        path: '/about',
        name: 'about',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'O nas'
        }
    },
    {
        path: '/team',
        name: 'team',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Zespół'
        }
    },
    {
        path: '/contact',
        name: 'contact',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Kontakt'
        }
    },

    // Legal pages
    {
        path: '/privacy-policy',
        name: 'privacy',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Polityka Prywatności'
        }
    },
    {
        path: '/terms',
        name: 'terms',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Regulamin'
        }
    },
    {
        path: '/cookies',
        name: 'cookies',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Polityka Cookies'
        }
    },

    // Support pages
    {
        path: '/support',
        name: 'support',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Wsparcie'
        }
    },
    {
        path: '/report',
        name: 'report',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'Zgłoś Problem'
        }
    },
    {
        path: '/faq',
        name: 'faq',
        component: ExamplePage,
        meta: {
            layout: 'MainLayout',
            title: 'FAQ'
        }
    },

    // 404 route - always keep this last
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
        meta: {
            layout: 'MainLayout',
            title: '404 - Nie znaleziono strony'
        }
    }

];
