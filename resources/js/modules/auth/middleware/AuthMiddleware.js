router.beforeEach(async (to, from, next) => {
    const store = useStore();

    if (to.meta.requiresAuth && !store.getters['auth/isAuthenticated']) {
        next('/login');
    } else if (to.meta.requiresGuest && store.getters['auth/isAuthenticated']) {
        next('/');
    } else {
        next();
    }
});
