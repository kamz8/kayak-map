import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import cachePlugin from '@/plugins/cachePlugin'

function buildCache() {
    const app = { config: { globalProperties: {} } }
    cachePlugin.install(app)
    return app.config.globalProperties.$cache
}

describe('cachePlugin', () => {
    let cache

    beforeEach(() => {
        localStorage.clear()
        vi.useFakeTimers()
        cache = buildCache()
    })

    afterEach(() => {
        vi.useRealTimers()
        localStorage.clear()
    })

    // ─── setCacheWithTTL ────────────────────────────────────────────────────────

    describe('setCacheWithTTL', () => {
        it('zapisuje dane w localStorage jako JSON', () => {
            cache.setCacheWithTTL('key1', { name: 'test' }, 60)

            const raw = localStorage.getItem('key1')
            expect(raw).not.toBeNull()
            const parsed = JSON.parse(raw)
            expect(parsed.value).toEqual({ name: 'test' })
        })

        it('ustawia prawidłowy czas wygaśnięcia', () => {
            const before = Date.now()
            cache.setCacheWithTTL('key1', 'data', 100)
            const after = Date.now()

            const { expiry } = JSON.parse(localStorage.getItem('key1'))
            expect(expiry).toBeGreaterThanOrEqual(before + 100 * 1000)
            expect(expiry).toBeLessThanOrEqual(after + 100 * 1000)
        })

        it('zapisuje puste tagi jako pusta tablica', () => {
            cache.setCacheWithTTL('key1', 'data', 60)
            const { tags } = JSON.parse(localStorage.getItem('key1'))
            expect(tags).toEqual([])
        })

        it('zapisuje klucz do struktury tagu', () => {
            cache.setCacheWithTTL('key1', 'data', 60, ['trails'])

            const tagKeys = JSON.parse(localStorage.getItem('tag-trails'))
            expect(tagKeys).toContain('key1')
        })

        it('zapisuje klucz do wielu tagów jednocześnie', () => {
            cache.setCacheWithTTL('key1', 'data', 60, ['trails', 'api'])

            expect(JSON.parse(localStorage.getItem('tag-trails'))).toContain('key1')
            expect(JSON.parse(localStorage.getItem('tag-api'))).toContain('key1')
        })

        it('nie duplikuje klucza w tagu przy ponownym zapisie', () => {
            cache.setCacheWithTTL('key1', 'data1', 60, ['trails'])
            cache.setCacheWithTTL('key1', 'data2', 60, ['trails'])

            const tagKeys = JSON.parse(localStorage.getItem('tag-trails'))
            expect(tagKeys.filter(k => k === 'key1').length).toBe(1)
        })

        it('obsługuje różne typy danych: string', () => {
            cache.setCacheWithTTL('str', 'hello', 60)
            expect(JSON.parse(localStorage.getItem('str')).value).toBe('hello')
        })

        it('obsługuje różne typy danych: number', () => {
            cache.setCacheWithTTL('num', 42, 60)
            expect(JSON.parse(localStorage.getItem('num')).value).toBe(42)
        })

        it('obsługuje różne typy danych: boolean false', () => {
            cache.setCacheWithTTL('bool', false, 60)
            expect(JSON.parse(localStorage.getItem('bool')).value).toBe(false)
        })

        it('obsługuje różne typy danych: number 0', () => {
            cache.setCacheWithTTL('zero', 0, 60)
            expect(JSON.parse(localStorage.getItem('zero')).value).toBe(0)
        })

        it('obsługuje różne typy danych: array', () => {
            cache.setCacheWithTTL('arr', [1, 2, 3], 60)
            expect(JSON.parse(localStorage.getItem('arr')).value).toEqual([1, 2, 3])
        })

        it('obsługuje złożone obiekty zagnieżdżone', () => {
            const data = { trails: [{ id: 1, name: 'Odra' }], total: 1 }
            cache.setCacheWithTTL('complex', data, 3600)
            expect(JSON.parse(localStorage.getItem('complex')).value).toEqual(data)
        })
    })

    // ─── getCacheWithTTL ────────────────────────────────────────────────────────

    describe('getCacheWithTTL', () => {
        it('zwraca dane gdy cache jest ważny', () => {
            cache.setCacheWithTTL('key1', { id: 1 }, 60)
            expect(cache.getCacheWithTTL('key1')).toEqual({ id: 1 })
        })

        it('zwraca null dla nieistniejącego klucza', () => {
            expect(cache.getCacheWithTTL('nonexistent')).toBeNull()
        })

        it('zwraca null gdy cache wygasł', () => {
            cache.setCacheWithTTL('key1', 'data', 1)

            vi.advanceTimersByTime(2000)

            expect(cache.getCacheWithTTL('key1')).toBeNull()
        })

        it('zwraca dane tuż przed wygaśnięciem', () => {
            cache.setCacheWithTTL('key1', 'data', 10)

            vi.advanceTimersByTime(9999)

            expect(cache.getCacheWithTTL('key1')).toBe('data')
        })

        it('zwraca false jako poprawną wartość cache (nie traktuje jako brak)', () => {
            cache.setCacheWithTTL('boolKey', false, 60)
            expect(cache.getCacheWithTTL('boolKey')).toBe(false)
        })

        it('zwraca 0 jako poprawną wartość cache', () => {
            cache.setCacheWithTTL('zeroKey', 0, 60)
            expect(cache.getCacheWithTTL('zeroKey')).toBe(0)
        })

        it('zwraca pusty string jako poprawną wartość cache', () => {
            cache.setCacheWithTTL('emptyStr', '', 60)
            expect(cache.getCacheWithTTL('emptyStr')).toBe('')
        })

        it('zwraca pustą tablicę jako poprawną wartość cache', () => {
            cache.setCacheWithTTL('emptyArr', [], 60)
            expect(cache.getCacheWithTTL('emptyArr')).toEqual([])
        })
    })

    // ─── hasCache ───────────────────────────────────────────────────────────────

    describe('hasCache', () => {
        it('zwraca true gdy cache jest ważny', () => {
            cache.setCacheWithTTL('key1', 'data', 60)
            expect(cache.hasCache('key1')).toBe(true)
        })

        it('zwraca false dla nieistniejącego klucza', () => {
            expect(cache.hasCache('missing')).toBe(false)
        })

        it('zwraca false gdy cache wygasł', () => {
            cache.setCacheWithTTL('key1', 'data', 1)
            vi.advanceTimersByTime(2000)
            expect(cache.hasCache('key1')).toBe(false)
        })

        it('zwraca true dla wartości falsy (false) w cache', () => {
            cache.setCacheWithTTL('boolKey', false, 60)
            expect(cache.hasCache('boolKey')).toBe(true)
        })

        it('zwraca true dla wartości 0 w cache', () => {
            cache.setCacheWithTTL('zeroKey', 0, 60)
            expect(cache.hasCache('zeroKey')).toBe(true)
        })
    })

    // ─── removeCache ────────────────────────────────────────────────────────────

    describe('removeCache', () => {
        it('usuwa klucz z localStorage', () => {
            cache.setCacheWithTTL('key1', 'data', 60)
            cache.removeCache('key1')
            expect(localStorage.getItem('key1')).toBeNull()
        })

        it('usuwa klucz ze struktury tagu', () => {
            cache.setCacheWithTTL('key1', 'data', 60, ['trails'])
            cache.removeCache('key1')

            const tagKeys = JSON.parse(localStorage.getItem('tag-trails'))
            expect(tagKeys).not.toContain('key1')
        })

        it('usuwa klucz z wielu tagów', () => {
            cache.setCacheWithTTL('key1', 'data', 60, ['trails', 'api'])
            cache.removeCache('key1')

            expect(JSON.parse(localStorage.getItem('tag-trails'))).not.toContain('key1')
            expect(JSON.parse(localStorage.getItem('tag-api'))).not.toContain('key1')
        })

        it('nie usuwa innych kluczy z tagu', () => {
            cache.setCacheWithTTL('key1', 'data1', 60, ['trails'])
            cache.setCacheWithTTL('key2', 'data2', 60, ['trails'])
            cache.removeCache('key1')

            const tagKeys = JSON.parse(localStorage.getItem('tag-trails'))
            expect(tagKeys).toContain('key2')
            expect(tagKeys).not.toContain('key1')
        })

        it('nie rzuca błędu dla nieistniejącego klucza', () => {
            expect(() => cache.removeCache('nonexistent')).not.toThrow()
        })

        it('po usunięciu getCacheWithTTL zwraca null', () => {
            cache.setCacheWithTTL('key1', 'data', 60)
            cache.removeCache('key1')
            expect(cache.getCacheWithTTL('key1')).toBeNull()
        })
    })

    // ─── removeCacheByTag ───────────────────────────────────────────────────────

    describe('removeCacheByTag', () => {
        it('usuwa wszystkie klucze powiązane z tagiem', () => {
            cache.setCacheWithTTL('key1', 'data1', 60, ['trails'])
            cache.setCacheWithTTL('key2', 'data2', 60, ['trails'])
            cache.removeCacheByTag('trails')

            expect(localStorage.getItem('key1')).toBeNull()
            expect(localStorage.getItem('key2')).toBeNull()
        })

        it('usuwa strukturę tagu z localStorage', () => {
            cache.setCacheWithTTL('key1', 'data', 60, ['trails'])
            cache.removeCacheByTag('trails')
            expect(localStorage.getItem('tag-trails')).toBeNull()
        })

        it('nie usuwa kluczy przypisanych do innego tagu', () => {
            cache.setCacheWithTTL('key1', 'data1', 60, ['trails'])
            cache.setCacheWithTTL('key2', 'data2', 60, ['regions'])
            cache.removeCacheByTag('trails')

            expect(cache.getCacheWithTTL('key2')).toBe('data2')
        })

        it('nie rzuca błędu dla nieistniejącego tagu', () => {
            expect(() => cache.removeCacheByTag('nonexistent-tag')).not.toThrow()
        })

        it('usuwa też powiązania klucza w innych tagach gdy klucz ma wiele tagów', () => {
            cache.setCacheWithTTL('key1', 'data', 60, ['trails', 'api'])
            cache.removeCacheByTag('trails')

            const apiTagKeys = JSON.parse(localStorage.getItem('tag-api')) ?? []
            expect(apiTagKeys).not.toContain('key1')
        })
    })

    // ─── clearAllCache ──────────────────────────────────────────────────────────

    describe('clearAllCache', () => {
        it('usuwa wszystkie wpisy z localStorage', () => {
            cache.setCacheWithTTL('key1', 'data1', 60)
            cache.setCacheWithTTL('key2', 'data2', 60, ['trails'])
            cache.clearAllCache()

            expect(localStorage.length).toBe(0)
        })

        it('po wyczyszczeniu getCacheWithTTL zwraca null', () => {
            cache.setCacheWithTTL('key1', 'data', 60)
            cache.clearAllCache()
            expect(cache.getCacheWithTTL('key1')).toBeNull()
        })

        it('po wyczyszczeniu hasCache zwraca false', () => {
            cache.setCacheWithTTL('key1', 'data', 60)
            cache.clearAllCache()
            expect(cache.hasCache('key1')).toBe(false)
        })
    })

    // ─── remember ───────────────────────────────────────────────────────────────

    describe('remember', () => {
        it('wywołuje fetchFunction gdy cache jest pusty i zapisuje wynik', async () => {
            const fetchFn = vi.fn().mockResolvedValue({ id: 1, name: 'Odra' })

            const result = await cache.remember('trail-1', 3600, fetchFn)

            expect(fetchFn).toHaveBeenCalledOnce()
            expect(result).toEqual({ id: 1, name: 'Odra' })
            expect(cache.getCacheWithTTL('trail-1')).toEqual({ id: 1, name: 'Odra' })
        })

        it('zwraca dane z cache bez wywołania fetchFunction (cache hit)', async () => {
            cache.setCacheWithTTL('trail-1', { id: 1 }, 3600)
            const fetchFn = vi.fn()

            const result = await cache.remember('trail-1', 3600, fetchFn)

            expect(fetchFn).not.toHaveBeenCalled()
            expect(result).toEqual({ id: 1 })
        })

        it('wywołuje fetchFunction po wygaśnięciu cache', async () => {
            cache.setCacheWithTTL('trail-1', { id: 1 }, 1)
            vi.advanceTimersByTime(2000)

            const fetchFn = vi.fn().mockResolvedValue({ id: 2, name: 'Wisła' })
            const result = await cache.remember('trail-1', 3600, fetchFn)

            expect(fetchFn).toHaveBeenCalledOnce()
            expect(result).toEqual({ id: 2, name: 'Wisła' })
        })

        it('propaguje błąd z fetchFunction', async () => {
            const fetchFn = vi.fn().mockRejectedValue(new Error('Network error'))

            await expect(cache.remember('key1', 60, fetchFn)).rejects.toThrow('Network error')
        })

        it('nie zapisuje danych w cache gdy fetchFunction rzuca błąd', async () => {
            const fetchFn = vi.fn().mockRejectedValue(new Error('API down'))

            try {
                await cache.remember('key1', 60, fetchFn)
            } catch {
                // expected
            }

            expect(cache.hasCache('key1')).toBe(false)
        })

        it('przekazuje tagi do setCacheWithTTL', async () => {
            const fetchFn = vi.fn().mockResolvedValue([1, 2, 3])

            await cache.remember('trails-list', 3600, fetchFn, ['trails', 'api'])

            expect(JSON.parse(localStorage.getItem('tag-trails'))).toContain('trails-list')
            expect(JSON.parse(localStorage.getItem('tag-api'))).toContain('trails-list')
        })

        it('poprawnie obsługuje wartość false jako wynik fetch (cache miss nie powtarza się)', async () => {
            const fetchFn = vi.fn().mockResolvedValue(false)

            await cache.remember('bool-key', 60, fetchFn)
            const result = await cache.remember('bool-key', 60, fetchFn)

            expect(fetchFn).toHaveBeenCalledOnce()
            expect(result).toBe(false)
        })

        it('poprawnie obsługuje wartość 0 jako wynik fetch', async () => {
            const fetchFn = vi.fn().mockResolvedValue(0)

            await cache.remember('zero-key', 60, fetchFn)
            const result = await cache.remember('zero-key', 60, fetchFn)

            expect(fetchFn).toHaveBeenCalledOnce()
            expect(result).toBe(0)
        })
    })

    // ─── integracja ─────────────────────────────────────────────────────────────

    describe('integracja - pełne scenariusze', () => {
        it('zapis → odczyt → usunięcie przez tag → brak danych', () => {
            cache.setCacheWithTTL('t1', 'data1', 3600, ['group'])
            cache.setCacheWithTTL('t2', 'data2', 3600, ['group'])

            expect(cache.getCacheWithTTL('t1')).toBe('data1')
            expect(cache.getCacheWithTTL('t2')).toBe('data2')

            cache.removeCacheByTag('group')

            expect(cache.getCacheWithTTL('t1')).toBeNull()
            expect(cache.getCacheWithTTL('t2')).toBeNull()
            expect(localStorage.getItem('tag-group')).toBeNull()
        })

        it('remember → TTL wygasa → ponowne pobranie z API', async () => {
            const fetchFn = vi.fn()
                .mockResolvedValueOnce('first')
                .mockResolvedValueOnce('second')

            await cache.remember('r1', 1, fetchFn)
            vi.advanceTimersByTime(2000)
            const result = await cache.remember('r1', 1, fetchFn)

            expect(fetchFn).toHaveBeenCalledTimes(2)
            expect(result).toBe('second')
        })

        it('clearAllCache usuwa dane po remember', async () => {
            const fetchFn = vi.fn().mockResolvedValue({ data: 'x' })
            await cache.remember('key', 3600, fetchFn)

            cache.clearAllCache()

            expect(cache.hasCache('key')).toBe(false)
        })

        it('plugin install tworzy obiekt $cache z wszystkimi metodami', () => {
            expect(typeof cache.setCacheWithTTL).toBe('function')
            expect(typeof cache.getCacheWithTTL).toBe('function')
            expect(typeof cache.hasCache).toBe('function')
            expect(typeof cache.removeCache).toBe('function')
            expect(typeof cache.removeCacheByTag).toBe('function')
            expect(typeof cache.clearAllCache).toBe('function')
            expect(typeof cache.remember).toBe('function')
        })
    })
})
