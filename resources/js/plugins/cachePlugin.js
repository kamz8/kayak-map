// src/plugins/cachePlugin.js
import { useStorage } from '@vueuse/core';

// Plugin cache z obsługą TTL, tagów i podstawowych funkcji
export default {
    install(app, options = {}) {
        // Funkcja ustawiająca cache z TTL i opcjonalnymi tagami
        function setCacheWithTTL(key, data, ttlInSeconds, tags = []) {
            const expiryTime = Date.now() + ttlInSeconds * 1000;
            const cacheData = {
                value: data,
                expiry: expiryTime,
                tags: tags
            };

            useStorage(key, cacheData, localStorage);

            // Zapisanie tagów w oddzielnej strukturze
            tags.forEach(tag => {
                const taggedKeys = useStorage(`tag-${tag}`, [], localStorage).value;
                if (!taggedKeys.includes(key)) {
                    taggedKeys.push(key);
                }
                useStorage(`tag-${tag}`, taggedKeys, localStorage);
            });
        }

        // Funkcja pobierająca dane z cache, jeśli są ważne
        function getCacheWithTTL(key) {
            const cache = useStorage(key, null, localStorage).value;

            if (cache && cache.expiry > Date.now()) {
                return cache.value;
            } else {
                return null;
            }
        }

        // Funkcja sprawdzająca, czy dany klucz istnieje w cache
        function hasCache(key) {
            const cache = useStorage(key, null, localStorage).value;
            return cache !== null && cache.expiry > Date.now();
        }

        // Funkcja czyszcząca dane z cache dla danego klucza
        function removeCache(key) {
            const cache = useStorage(key, null, localStorage).value;
            if (cache && cache.tags) {
                // Usuń powiązania z tagami
                cache.tags.forEach(tag => {
                    const taggedKeys = useStorage(`tag-${tag}`, [], localStorage).value;
                    const index = taggedKeys.indexOf(key);
                    if (index !== -1) taggedKeys.splice(index, 1);
                    useStorage(`tag-${tag}`, taggedKeys, localStorage);
                });
            }
            useStorage(key, null, localStorage).value = null;
        }

        // Funkcja do czyszczenia cache dla określonego tagu
        function removeCacheByTag(tag) {
            const taggedKeys = useStorage(`tag-${tag}`, [], localStorage).value;
            taggedKeys.forEach(key => removeCache(key));
            useStorage(`tag-${tag}`, [], localStorage).value = [];
        }

        // Funkcja do czyszczenia całego cache
        function clearAllCache() {
            localStorage.clear();
        }

        // Funkcja obsługująca "remember", podobnie jak Laravel Cache::remember, z tagami
        async function remember(key, ttlInSeconds, fetchFunction, tags = []) {
            let cachedData = getCacheWithTTL(key);

            if (cachedData) {
                console.log(`Returning cached data for ${key}`);
                return cachedData;
            }

            try {
                const freshData = await fetchFunction();
                setCacheWithTTL(key, freshData, ttlInSeconds, tags);
                console.log(`Setting new data in cache for ${key} with tags: ${tags}`);
                return freshData;
            } catch (error) {
                console.error("Error fetching data:", error);
                throw error;
            }
        }

        // Udostępnienie metod w całej aplikacji
        app.config.globalProperties.$cache = {
            remember,
            getCacheWithTTL,
            setCacheWithTTL,
            hasCache,
            removeCache,
            removeCacheByTag,
            clearAllCache,
        };
    },
};
