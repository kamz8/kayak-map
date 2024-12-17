export default [
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/Login.vue'),
        meta: {
            requiresGuest: true
        }
    },
    {
        path: '/auth/:provider/callback',
        name: 'auth-callback',
        component: () => import('../pages/AuthCallback.vue'),
        meta: {
            requiresGuest: true
        }
    }
];
