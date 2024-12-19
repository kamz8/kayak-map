import store from "@/store/index.js";

export default [
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/Login.vue'),
        meta: {
            requiresGuest: true,
            layout: 'AuthLayout',
            title: 'KAYAK - Zaloguj się'
        },
    },
    {
        path: '/auth/:provider/callback',
        name: 'auth-callback',
        layout: 'CallbackLayout',
        component: () => import('../pages/AuthCallback.vue'),
        meta: {
            layout: 'CallbackLayout',
            requiresGuest: true
        }
    },
    {
            path: '/reset-password',
        name: 'reset-password',
        component: () => import('../pages/ResetPassword.vue'),
        beforeEnter: (to, from, next) => {
            const { token, email } = to.query

            if (!token || !email) {
                store.commit('auth/SET_AUTH_MESSAGE', {
                    type: 'error',
                    title: 'Błąd resetowania hasła',
                    text: 'Nieprawidłowy link resetowania hasła',
                    duration: 5000
                })
                next({
                    name: 'login',
                    replace: true
                })
            } else {
                next()
            }
        },
        meta: {
            requiresGuest: true,
            layout: 'AuthLayout',
            title: 'KAYAK - Resetowanie hasła'
        }
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('../pages/ForgotPassword.vue'),
        meta: {
            requiresGuest: true,
            layout: 'AuthLayout',
            title: 'KAYAK - Przypomnij hasło'
        }
    },
];
