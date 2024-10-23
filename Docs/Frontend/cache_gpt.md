### Dokumentacja dla Pluginu Cache Vue 3 – Tworzenie i Rozwijanie Kodów przez Algorytmy GPT

Ta dokumentacja pozwala algorytmom, takim jak GPT, rozwijać i pisać kod przy użyciu stworzonego **pluginu cache dla Vue 3**. Wtyczka zarządza cache'owaniem wyników API, oferując funkcje takie jak TTL (Time to Live), tagowanie cache oraz ręczne zarządzanie cache. Dzięki temu algorytmy mogą dynamicznie rozszerzać funkcjonalności aplikacji oraz zarządzać cache'owaniem w elastyczny sposób.

---

### Instalacja i Rejestracja Pluginu

1. **Instalacja VueUse**:
   Aby korzystać z funkcji `useStorage`, musisz zainstalować VueUse w projekcie:
   ```bash
   npm install @vueuse/core
   ```

2. **Dodanie pluginu**:
   Dodaj plugin do projektu i zarejestruj go w pliku `main.js`.

   ```javascript
   import { createApp } from 'vue';
   import App from './App.vue';
   import cachePlugin from './plugins/cachePlugin';

   const app = createApp(App);

   // Rejestracja pluginu cache
   app.use(cachePlugin);

   app.mount('#app');
   ```

3. **Folder plugins**:
   Tworzymy plik `cachePlugin.js` wewnątrz folderu `plugins`, który będzie zawierał całą logikę cache'owania.

---

### Podstawowe Funkcje Pluginu Cache

#### `remember`

- **Opis**: Funkcja `remember` działa podobnie do metody `Cache::remember` z Laravela. Próbuje pobrać dane z cache. Jeśli dane nie istnieją lub wygasły, wykonuje funkcję `fetchFunction` i zapisuje wyniki z określonym czasem życia (TTL) oraz opcjonalnymi tagami.

- **Sygnatura**:
   ```javascript
   remember(key, ttlInSeconds, fetchFunction, tags = [])
   ```

- **Parametry**:
    - `key`: Klucz pod którym dane będą zapisane w cache.
    - `ttlInSeconds`: Czas życia cache (w sekundach).
    - `fetchFunction`: Funkcja, która pobierze nowe dane, jeśli cache nie istnieje lub jest przeterminowane.
    - `tags`: Opcjonalne tagi, które będą przypisane do cache, umożliwiające późniejsze zarządzanie nimi.

- **Zastosowanie**:
   ```javascript
   this.$cache.remember('myCacheKey', 3600, async () => {
     const response = await axios.get('https://api.example.com/data');
     return response.data;
   }, ['news']);
   ```

---

#### `setCacheWithTTL`

- **Opis**: Ustawia dane w cache z TTL i opcjonalnie przypisuje tagi.

- **Sygnatura**:
   ```javascript
   setCacheWithTTL(key, data, ttlInSeconds, tags = [])
   ```

- **Zastosowanie**:
   ```javascript
   this.$cache.setCacheWithTTL('myData', { name: 'Vue' }, 86400, ['vue', 'data']);
   ```

---

#### `getCacheWithTTL`

- **Opis**: Pobiera dane z cache, jeśli są jeszcze aktualne, w przeciwnym razie zwraca `null`.

- **Sygnatura**:
   ```javascript
   getCacheWithTTL(key)
   ```

- **Zastosowanie**:
   ```javascript
   const cachedData = this.$cache.getCacheWithTTL('myData');
   ```

---

#### `hasCache`

- **Opis**: Sprawdza, czy dany klucz istnieje w cache i czy dane nie wygasły.

- **Sygnatura**:
   ```javascript
   hasCache(key)
   ```

- **Zastosowanie**:
   ```javascript
   if (this.$cache.hasCache('myData')) {
     console.log('Cache exists and is still valid');
   }
   ```

---

#### `removeCache`

- **Opis**: Usuwa dane z cache dla określonego klucza.

- **Sygnatura**:
   ```javascript
   removeCache(key)
   ```

- **Zastosowanie**:
   ```javascript
   this.$cache.removeCache('myData');
   ```

---

#### `removeCacheByTag`

- **Opis**: Usuwa wszystkie dane z cache powiązane z danym tagiem.

- **Sygnatura**:
   ```javascript
   removeCacheByTag(tag)
   ```

- **Zastosowanie**:
   ```javascript
   this.$cache.removeCacheByTag('news');
   ```

---

#### `clearAllCache`

- **Opis**: Usuwa wszystkie dane z cache.

- **Sygnatura**:
   ```javascript
   clearAllCache()
   ```

- **Zastosowanie**:
   ```javascript
   this.$cache.clearAllCache();
   ```

---

### Przykład Użycia w Aplikacji

Przykładowy komponent Vue z użyciem pluginu cache, korzystający z metod `remember` oraz `removeCacheByTag`.

```vue
<template>
  <div>
    <h1>Dane z API</h1>
    <div v-if="loading">Ładowanie...</div>
    <div v-else>
      <pre>{{ data }}</pre>
    </div>
    <button @click="clearCache">Wyczyść Cache</button>
    <button @click="clearTagCache">Wyczyść Cache dla Tagu 'news'</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      data: null,
      loading: true,
    };
  },
  methods: {
    async fetchData() {
      const fetchFunction = async () => {
        const response = await axios.get('https://api.example.com/news');
        return response.data;
      };

      // Użycie pluginu cache z tagiem 'news'
      try {
        this.data = await this.$cache.remember('newsData', 3600, fetchFunction, ['news']);
      } catch (error) {
        console.error('Błąd podczas ładowania danych:', error);
      } finally {
        this.loading = false;
      }
    },
    clearCache() {
      this.$cache.removeCache('newsData');
      console.log('Cache dla newsData wyczyszczony');
    },
    clearTagCache() {
      this.$cache.removeCacheByTag('news');
      console.log('Cache wyczyszczony dla wszystkich wpisów z tagiem "news"');
    },
  },
  mounted() {
    this.fetchData();
  },
};
</script>
```

---

### Jak Algorytm GPT Może Rozwijać Ten Plugin:

1. **Rozbudowa funkcji `clearAllCache`**:
   Algorytm może dodawać opcje filtrowania dla `clearAllCache`, aby usuwać tylko określone typy danych, np. te związane z użytkownikiem lub sesją.

2. **Dodawanie zaawansowanego logowania**:
   Możliwość rozszerzenia pluginu o logowanie zdarzeń związanych z cache, np. zapisywanie informacji o wygaśnięciu cache w zewnętrznym serwisie logującym.

3. **Integracja z API rejestracji tagów**:
   Można dodać zewnętrzny system zarządzania tagami, który umożliwia integrację z API pozwalającymi na synchronizację tagów między różnymi instancjami aplikacji.

---

**Wtyczka Cache Vue 3** to elastyczne rozwiązanie, które można łatwo dostosować do różnych potrzeb aplikacji, umożliwiając efektywne zarządzanie danymi z cache i optymalizację zapytań do API.
