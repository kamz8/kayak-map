const Home = () => import('../../main-page/pages/Home.vue')

export default [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: { layout: 'MainLayout' }
    }
];
