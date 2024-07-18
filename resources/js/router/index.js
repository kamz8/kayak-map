import { createRouter, createWebHistory } from 'vue-router';

const routes = [];

const modules = import.meta.glob('../modules/**/router/*.js', { eager: true });

for (const path in modules) {
    routes.push(...modules[path].default);
}

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    // Ustawienie tytułu strony
    if (to.meta.title) {
        document.title = to.meta.title;
    }

    // Usunięcie istniejących meta tagów
    Array.from(document.querySelectorAll('[data-vue-router-controlled]')).map(el => el.parentNode.removeChild(el));

    // Dodanie nowych meta tagów
    if (to.meta.metaTags) {
        to.meta.metaTags.forEach(tagDef => {
            const tag = document.createElement('meta');

            Object.keys(tagDef).forEach(key => {
                tag.setAttribute(key, tagDef[key]);
            });

            tag.setAttribute('data-vue-router-controlled', '');

            document.head.appendChild(tag);
        });
    }

    next();
});

export default router;
