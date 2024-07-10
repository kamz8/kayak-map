# Dokumentacja Bazy Danych

## Tabela `trails`

Przechowuje informacje o szlakach.

| Kolumna        | Typ         | Opis                                      |
|----------------|-------------|-------------------------------------------|
| id             | BIGINT      | Klucz główny                              |
| river_name     | STRING      | Nazwa rzeki                               |
| trail_name     | STRING      | Nazwa szlaku                              |
| description    | TEXT        | Opis szlaku                               |
| start_lat      | DECIMAL     | Szerokość geograficzna początku           |
| start_lng      | DECIMAL     | Długość geograficzna początku             |
| end_lat        | DECIMAL     | Szerokość geograficzna końca              |
| end_lng        | DECIMAL     | Długość geograficzna końca                |
| trail_length   | INTEGER     | Długość szlaku w metrach                  |
| author         | STRING      | Imię i nazwisko autora                    |
| created_at     | TIMESTAMP   | Data utworzenia                           |
| updated_at     | TIMESTAMP   | Data ostatniej aktualizacji               |

## Tabela `river_tracks`

Przechowuje punkty trasy rzeki w formacie JSON.

| Kolumna        | Typ         | Opis                                      |
|----------------|-------------|-------------------------------------------|
| id             | BIGINT      | Klucz główny                              |
| trail_id       | BIGINT      | Klucz obcy do tabeli `trails`             |
| track_points   | JSON        | Punkty trasy w formacie JSON              |
| created_at     | TIMESTAMP   | Data utworzenia                           |
| updated_at     | TIMESTAMP   | Data ostatniej aktualizacji               |

## Tabela `sections`

Przechowuje informacje o obszarach związanych z rzeką.

| Kolumna             | Typ         | Opis                                      |
|---------------------|-------------|-------------------------------------------|
| id                  | BIGINT      | Klucz główny                              |
| trail_id            | BIGINT      | Klucz obcy do tabeli `trails`             |
| name                | STRING      | Nazwa sekcji                              |
| description         | TEXT        | Opis sekcji                               |
| polygon_coordinates | JSON        | Współrzędne wielokąta w formacie JSON     |
| created_at          | TIMESTAMP   | Data utworzenia                           |
| updated_at          | TIMESTAMP   | Data ostatniej aktualizacji               |

## Tabela `points`

Przechowuje informacje o punktach związanych z rzeką.

| Kolumna        | Typ         | Opis                                      |
|----------------|-------------|-------------------------------------------|
| id             | BIGINT      | Klucz główny                              |
| trail_id       | BIGINT      | Klucz obcy do tabeli `trails`             |
| point_type_id  | BIGINT      | Klucz obcy do tabeli `point_types`        |
| name           | STRING      | Nazwa punktu                              |
| description    | TEXT        | Opis punktu                               |
| lat            | DECIMAL     | Szerokość geograficzna punktu             |
| lng            | DECIMAL     | Długość geograficzna punktu               |
| created_at     | TIMESTAMP   | Data utworzenia                           |
| updated_at     | TIMESTAMP   | Data ostatniej aktualizacji               |

## Tabela `images`

Globalna tabela przechowująca zdjęcia.

| Kolumna        | Typ         | Opis                                      |
|----------------|-------------|-------------------------------------------|
| id             | BIGINT      | Klucz główny                              |
| path           | STRING      | Ścieżka do pliku zdjęcia                  |
| created_at     | TIMESTAMP   | Data utworzenia                           |
| updated_at     | TIMESTAMP   | Data ostatniej aktualizacji               |

## Tabela `imageables`

Pośrednia tabela dla relacji wiele do wielu między `images` a innymi tabelami.

| Kolumna          | Typ         | Opis                                      |
|------------------|-------------|-------------------------------------------|
| id               | BIGINT      | Klucz główny                              |
| image_id         | BIGINT      | Klucz obcy do tabeli `images`             |
| imageable_id     | BIGINT      | Klucz obcy do powiązanego obiektu         |
| imageable_type   | STRING      | Typ powiązanego obiektu                   |
| is_main          | BOOLEAN     | Czy jest głównym zdjęciem                 |
| order            | INTEGER     | Kolejność wyświetlania                    |
| created_at       | TIMESTAMP   | Data utworzenia                           |
| updated_at       | TIMESTAMP   | Data ostatniej aktualizacji               |

## Tabela `links`

Przechowuje linki z metadanymi związane z sekcjami.

| Kolumna        | Typ         | Opis                                      |
|----------------|-------------|-------------------------------------------|
| id             | BIGINT      | Klucz główny                              |
| section_id     | BIGINT      | Klucz obcy do tabeli `sections`           |
| url            | STRING      | URL linku                                 |
| meta_data      | STRING      | Metadane linku                            |
| created_at     | TIMESTAMP   | Data utworzenia                           |
| updated_at     | TIMESTAMP   | Data ostatniej aktualizacji               |

## Tabela `point_types`

Przechowuje typy punktów.

| Kolumna        | Typ         | Opis                                      |
|----------------|-------------|-------------------------------------------|
| id             | BIGINT      | Klucz główny                              |
| type           | STRING      | Typ punktu                                |
| created_at     | TIMESTAMP   | Data utworzenia                           |
| updated_at     | TIMESTAMP   | Data ostatniej aktualizacji               |

## Relacje między tabelami

- `trails` ma wiele `river_tracks`.
- `trails` ma wiele `sections`.
- `trails` ma wiele `points`.
- `sections` ma wiele `links`.
- `sections` i `trails` mają wiele `images` przez tabelę `imageables`.
- `points` mają typy zdefiniowane w `point_types`.
- `images` mogą być powiązane z wieloma różnymi tabelami poprzez tabelę pośrednią `imageables`.

## Przykład JSON dla `polygon_coordinates` i `track_points`

### `polygon_coordinates`

```json
[
    {"lat": 51.5074, "lng": -0.1278},
    {"lat": 51.5075, "lng": -0.1277},
    {"lat": 51.5076, "lng": -0.1276},
    {"lat": 51.5077, "lng": -0.1275}
]
