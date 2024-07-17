import { createStore } from 'vuex';

const modules = {};

const files = import.meta.glob('../modules/**/store/*.js', { eager: true });

for (const path in files) {
    const moduleName = path.split('/')[3]; // get module name from path
    modules[moduleName] = files[path].default;
}

const store = createStore({
    modules
});

export default store;
