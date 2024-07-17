const explore = () => import('../pages/Explore.vue')

export default [
    {
        path: '/explore',
        component: explore,
        meta: { layout: 'ExploreLayout' }
    },
];
