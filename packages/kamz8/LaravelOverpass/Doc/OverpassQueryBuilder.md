## Klasa: `OverpassQueryBuilder`

Klasa `OverpassQueryBuilder` zapewnia interfejs podobny do Eloquent dla budowania i wykonywania zapytań do Overpass API.

### Metody

---

### `__construct(Client $client)`

Inicjalizuje nową instancję klasy `OverpassQueryBuilder`.

- **Parametry:**
    - `Client $client`: Instancja `GuzzleHttp\Client` skonfigurowana dla Overpass API.

---

### `raw(string $query): static`

Pozwala wprowadzić surowe zapytanie Overpass QL.

- **Parametry:**
    - `string $query`: Surowe zapytanie w języku Overpass QL.

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  $data = Overpass::raw('[out:json]; node["amenity"="cafe"]; out;')->get();
  ```

---

### `node(): static`

Określa, że zapytanie ma dotyczyć elementów typu `node` (węzeł).

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  $query = Overpass::query()->node();
  ```

---

### `way(): static`

Określa, że zapytanie ma dotyczyć elementów typu `way` (droga).

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  $query = Overpass::query()->way();
  ```

---

### `relation(): static`

Określa, że zapytanie ma dotyczyć elementów typu `relation` (relacja).

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  $query = Overpass::query()->relation();
  ```

---

### `nwr(): static`

Określa, że zapytanie ma dotyczyć elementów typu `node`, `way` i `relation`.

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  $query = Overpass::query()->nwr();
  ```

---

### `where(string $key, ?string $value = null): static`

Dodaje filtr do zapytania, aby wyszukać elementy z określonym kluczem i opcjonalnie wartością tagu.

- **Parametry:**
    - `string $key`: Klucz tagu do filtrowania.
    - `?string $value`: *(Opcjonalnie)* Wartość tagu do filtrowania.

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  // Filtruj elementy z tagiem 'amenity' o wartości 'cafe'
  $query->where('amenity', 'cafe');

  // Filtruj elementy, które posiadają tag 'building'
  $query->where('building');
  ```

---

### `orWhere(string $key, ?string $value = null): static`

Dodaje alternatywny filtr do poprzedniego warunku `where`, pozwalając na zastosowanie logicznego OR w zapytaniu.

- **Parametry:**
    - `string $key`: Klucz tagu dla alternatywnego warunku.
    - `?string $value`: *(Opcjonalnie)* Wartość tagu dla alternatywnego warunku.

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  // Filtruj elementy z tagiem 'amenity' o wartości 'cafe' lub 'restaurant'
  $query->where('amenity', 'cafe')->orWhere('amenity', 'restaurant');
  ```

---

### `bbox(float $south, float $west, float $north, float $east): static`

Definiuje bounding box (obszar ograniczający) dla zapytania.

- **Parametry:**
    - `float $south`: Szerokość geograficzna południowej granicy.
    - `float $west`: Długość geograficzna zachodniej granicy.
    - `float $north`: Szerokość geograficzna północnej granicy.
    - `float $east`: Długość geograficzna wschodniej granicy.

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  // Definiuj bounding box dla obszaru Londynu
  $query->bbox(51.5, -0.1, 51.6, 0.1);
  ```

---

### `bboxFromPoints(float $lat1, float $lon1, float $lat2, float $lon2, float $marginPercent = 10): static`

Generuje bounding box na podstawie dwóch punktów z opcjonalnym marginesem procentowym.

- **Parametry:**
    - `float $lat1`: Szerokość geograficzna pierwszego punktu.
    - `float $lon1`: Długość geograficzna pierwszego punktu.
    - `float $lat2`: Szerokość geograficzna drugiego punktu.
    - `float $lon2`: Długość geograficzna drugiego punktu.
    - `float $marginPercent`: *(Opcjonalnie)* Procentowy margines do dodania do bounding box (domyślnie 10%).

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  // Generuj bounding box z marginesem 10%
  $query->bboxFromPoints(51.5, -0.1, 51.6, 0.1, 10);
  ```

---

### `around(float $radius, float $lat, float $lon): static`

Definiuje obszar wyszukiwania w określonym promieniu od podanego punktu.

- **Parametry:**
    - `float $radius`: Promień w metrach.
    - `float $lat`: Szerokość geograficzna punktu centralnego.
    - `float $lon`: Długość geograficzna punktu centralnego.

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  // Wyszukaj elementy w promieniu 1000 metrów od punktu
  $query->around(1000, 51.5, -0.1);
  ```

---

### `recurse(): static`

Dodaje rekurencję do zapytania, aby uwzględnić powiązane elementy (np. węzły należące do drogi).

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  // Uwzględnij powiązane elementy
  $query->recurse();
  ```

---

### `output(string $output = 'json'): static`

Ustawia format wyjściowy zapytania.

- **Parametry:**
    - `string $output`: Format wyjściowy (np. 'json', 'xml'). Domyślnie 'json'.

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

---

### `limit(int $number): static`

Ogranicza liczbę zwracanych wyników zapytania.

- **Parametry:**
    - `int $number`: Maksymalna liczba wyników do zwrócenia.

- **Zwraca:**
    - `OverpassQueryBuilder`: Obecną instancję dla łańcuchowego wywoływania metod.

- **Przykład użycia:**

  ```php
  // Ogranicz wyniki do 5 elementów
  $query->limit(5);
  ```

---

### `build(): string`

Buduje zapytanie w języku Overpass QL na podstawie dostarczonych parametrów.

- **Zwraca:**
    - `string`: Zbudowane zapytanie Overpass QL.

- **Uwagi:**
    - Metoda jest wywoływana wewnętrznie i zazwyczaj nie trzeba jej wywoływać bezpośrednio.

---

### `get(): array|string`

Wykonuje zapytanie do Overpass API i zwraca wyniki.

- **Zwraca:**
    - `array|string`: Odpowiedź z Overpass API. Jeśli format wyjściowy to 'json', zwracana jest tablica asocjacyjna; w przeciwnym razie zwracany jest surowy string.

- **Rzuca:**
    - `\Exception`: Jeśli wystąpi błąd podczas komunikacji z Overpass API lub parsowania odpowiedzi.

- **Przykład użycia:**

  ```php
  $data = $query->get();
  ```

---

### Dodatkowe metody

Możesz rozszerzyć `OverpassQueryBuilder`, dodając własne metody specyficzne dla Twoich potrzeb.

---

## Przykładowe użycie

```php
use Overpass;

$data = Overpass::query()
    ->node()
    ->where('amenity', 'fuel')
    ->bbox(51.5, -0.1, 51.6, 0.1)
    ->limit(5)
    ->get();
```

---

**Uwagi:**

- Wszystkie metody zwracają instancję `OverpassQueryBuilder`, co pozwala na łańcuchowe wywoływanie metod.
- Pamiętaj o obsłudze wyjątków podczas wywoływania metody `get()`, ponieważ błędy sieciowe lub nieprawidłowe zapytania mogą powodować rzucenie wyjątku.

