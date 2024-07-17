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

export default router;
