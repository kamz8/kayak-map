### Instrukcje GPT dla rozwoju funkcjonalności w projekcie opartym o plugin cache Vue 3

Te instrukcje pozwolą algorytmowi GPT na rozwój i dodawanie nowych funkcjonalności do istniejącego projektu Vue 3 z zintegrowanym pluginem cache. Plugin ten zarządza cache API z opcjami TTL (Time to Live), tagowania i zarządzania danymi cache.

---

### Ogólne zasady tworzenia nowych funkcjonalności

1. **Używaj istniejącego pluginu cache** do zarządzania danymi i ich przechowywaniem w cache. Staraj się wykorzystywać mechanizmy TTL oraz tagowanie, aby optymalizować zapytania do API i nie powielać danych.

2. **Zasada DRY (Don't Repeat Yourself)**: Jeśli nowa funkcjonalność wymaga cache'owania danych z różnych źródeł, używaj wspólnych funkcji, takich jak `remember`, aby unikać powtarzania logiki zapytań.

3. **Rozszerzenie istniejących funkcji**: Rozważ, czy nowa funkcjonalność może wymagać rozszerzenia już istniejących metod pluginu, takich jak `setCacheWithTTL`, `removeCacheByTag`, czy `clearAllCache`.

---

### Przykładowe kroki dla dodawania nowych funkcjonalności

#### 1. Tworzenie funkcji cache dla nowych zapytań API

Kiedy dodajesz nową funkcjonalność, która wymaga zapytań API, zawsze najpierw sprawdź, czy dane są dostępne w cache. Jeśli nie, wykonaj zapytanie, a następnie zapisz odpowiedź w cache z odpowiednim TTL.

**Kroki do wykonania:**
1. Pobierz dane z cache za pomocą `remember` lub `getCacheWithTTL`.
2. Jeśli dane nie istnieją w cache lub są przeterminowane, pobierz je z API i zapisz w cache.
3. Ustaw TTL i opcjonalnie tagi, aby móc zarządzać danymi.

**Przykład:**
```javascript
methods: {
  async fetchNewData() {
    const fetchFunction = async () => {
      const response = await axios.get('https://api.example.com/new-data');
      return response.data;
    };

    try {
      // Użycie `remember` do cache'owania wyników API z TTL i tagiem
      this.newData = await this.$cache.remember('newDataKey', 7200, fetchFunction, ['newData']);
    } catch (error) {
      console.error('Błąd podczas pobierania nowych danych:', error);
    }
  }
}
```

---

#### 2. Dodanie nowego tagu do istniejącego cache

Jeżeli nowa funkcjonalność wymaga przypisania dodatkowych danych do cache z odpowiednimi tagami (np. grupowanie danych wg użytkowników, kategorii), dodaj tagi przy zapisie cache.

**Kroki do wykonania:**
1. Przy zapisie danych w cache za pomocą `setCacheWithTTL` lub `remember`, dodaj nowy tag.
2. Użyj tego tagu, aby później móc usuwać lub aktualizować powiązane dane.

**Przykład:**
```javascript
methods: {
  async cacheUserData() {
    const fetchFunction = async () => {
      const response = await axios.get('https://api.example.com/user-data');
      return response.data;
    };

    // Cache'owanie danych użytkownika z tagiem 'userData'
    this.userData = await this.$cache.remember('userDataKey', 86400, fetchFunction, ['userData', 'userProfile']);
  }
}
```

---

#### 3. Dodanie czyszczenia cache na podstawie tagów

Jeżeli nowa funkcjonalność wymaga usunięcia wszystkich danych powiązanych z określonym tagiem (np. w przypadku aktualizacji danych użytkownika), użyj metody `removeCacheByTag`.

**Kroki do wykonania:**
1. Wyszukaj odpowiednie dane za pomocą tagu.
2. Usuń wszystkie wpisy cache przypisane do tego tagu.

**Przykład:**
```javascript
methods: {
  clearUserCache() {
    // Usunięcie wszystkich wpisów cache z tagiem 'userData'
    this.$cache.removeCacheByTag('userData');
    console.log('Cache dla danych użytkownika wyczyszczony');
  }
}
```

---

#### 4. Rozszerzanie funkcji `clearAllCache`

Jeśli algorytm GPT ma dodać bardziej zaawansowane zarządzanie cache, można rozszerzyć funkcję `clearAllCache`, aby pozwalała na czyszczenie tylko wybranych grup danych (np. według tagów lub kluczy).

**Kroki do wykonania:**
1. Zmodyfikuj istniejącą funkcję `clearAllCache`, aby obsługiwała parametry pozwalające na selektywne czyszczenie danych.
2. Dodaj opcję czyszczenia cache tylko dla wybranych tagów lub kluczy.

**Przykład:**
```javascript
methods: {
  clearSelectedCache() {
    const tagsToClear = ['news', 'userData']; // Lista tagów do wyczyszczenia
    tagsToClear.forEach(tag => this.$cache.removeCacheByTag(tag));
    console.log('Cache dla wybranych tagów wyczyszczony');
  }
}
```

---

### Dodatkowe instrukcje dla GPT

1. **Rozwijanie logiki TTL**:
    - GPT może rozwijać mechanizmy zarządzania TTL w bardziej zaawansowany sposób, np. umożliwiając dynamiczne zmienianie TTL dla poszczególnych grup danych.

2. **Zarządzanie pamięcią w zależności od sesji użytkownika**:
    - Jeśli dane są specyficzne dla użytkownika, GPT może tworzyć mechanizmy zarządzania cache, które przypisują dane do sesji użytkownika, pozwalając na usuwanie danych powiązanych z wylogowaniem.

3. **Rozwój funkcji obserwacji zmian**:
    - GPT może implementować funkcje obserwacji cache, np. na podstawie `watch`, które monitorują zmiany w cache i reagują na aktualizacje danych w czasie rzeczywistym.

---

### Kluczowe zasady rozwoju funkcjonalności

- **Unikaj nadmiarowego zapisu danych**: Algorytm powinien zawsze najpierw sprawdzić, czy dane są w cache, zanim wywoła API.
- **Tagowanie**: Funkcje powinny być grupowane w cache za pomocą tagów, co ułatwi zarządzanie, aktualizację i usuwanie danych.
- **TTL**: Algorytm musi obsługiwać TTL, aby zapewnić aktualność danych w cache, a także odpowiednio zarządzać czasem przechowywania danych.

To zapewni odpowiednie rozszerzanie funkcjonalności w zgodzie z już istniejącym pluginem cache w aplikacji Vue 3.
