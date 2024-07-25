import { createStore } from 'vuex';

const modules = {};

// Zmiana glob pattern, aby pasował do struktury folderów modułów
const files = import.meta.glob('../modules/*/store/index.js', { eager: true });

for (const path in files) {
    // Ekstrakcja nazwy modułu z ścieżki
    const moduleName = path.split('/')[2]; // Zakładając ścieżkę '../modules/moduleName/store/index.js'
    const module = files[path].default;

    if (typeof module === 'object' && module !== null) {
        // Ustawienie namespace na nazwę folderu modułu
        module.namespaced = true;

        if (!module.state && typeof module.state !== 'function') {
            console.warn(`Module ${moduleName}: state should be a function returning an object.`);
            module.state = () => ({}); // Ustawienie domyślnego stanu jako funkcji
        }
        if (!module.getters || Object.keys(module.getters).length === 0) {
            console.warn(`Module ${moduleName} has no getters defined.`);
        }

        modules[moduleName] = module;
    } else {
        console.error(`Failed to load module ${moduleName}. Make sure it exports a valid Vuex module.`);
    }
}

const store = createStore({
    modules
});

export default store;
