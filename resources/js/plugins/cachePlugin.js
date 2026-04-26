export default {
    install(app, options = {}) {
        function setCacheWithTTL(key, data, ttlInSeconds, tags = []) {
            const expiryTime = Date.now() + ttlInSeconds * 1000;
            const cacheData = { value: data, expiry: expiryTime, tags };
            localStorage.setItem(key, JSON.stringify(cacheData));

            tags.forEach(tag => {
                const stored = localStorage.getItem(`tag-${tag}`);
                const taggedKeys = stored ? JSON.parse(stored) : [];
                if (!taggedKeys.includes(key)) {
                    taggedKeys.push(key);
                    localStorage.setItem(`tag-${tag}`, JSON.stringify(taggedKeys));
                }
            });
        }

        function getCacheWithTTL(key) {
            const stored = localStorage.getItem(key);
            if (!stored) return null;
            const cache = JSON.parse(stored);
            if (cache && cache.expiry > Date.now()) return cache.value;
            return null;
        }

        function hasCache(key) {
            const stored = localStorage.getItem(key);
            if (!stored) return false;
            const cache = JSON.parse(stored);
            return cache !== null && cache.expiry > Date.now();
        }

        function removeCache(key) {
            const stored = localStorage.getItem(key);
            if (stored) {
                const cache = JSON.parse(stored);
                if (cache && cache.tags) {
                    cache.tags.forEach(tag => {
                        const tagStored = localStorage.getItem(`tag-${tag}`);
                        const taggedKeys = tagStored ? JSON.parse(tagStored) : [];
                        const index = taggedKeys.indexOf(key);
                        if (index !== -1) {
                            taggedKeys.splice(index, 1);
                            localStorage.setItem(`tag-${tag}`, JSON.stringify(taggedKeys));
                        }
                    });
                }
            }
            localStorage.removeItem(key);
        }

        function removeCacheByTag(tag) {
            const stored = localStorage.getItem(`tag-${tag}`);
            const taggedKeys = stored ? JSON.parse(stored) : [];
            taggedKeys.forEach(key => removeCache(key));
            localStorage.removeItem(`tag-${tag}`);
        }

        function clearAllCache() {
            localStorage.clear();
        }

        async function remember(key, ttlInSeconds, fetchFunction, tags = []) {
            const cachedData = getCacheWithTTL(key);
            if (cachedData !== null) return cachedData;
            const freshData = await fetchFunction();
            setCacheWithTTL(key, freshData, ttlInSeconds, tags);
            return freshData;
        }

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
