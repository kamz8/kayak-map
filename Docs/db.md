# Rozszerzona Dokumentacja Bazy Danych

## Spis treści
1. [Tabela `trails`](#tabela-trails)
2. [Tabela `point_types`](#tabela-point_types)
3. [Tabela `river_tracks`](#tabela-river_tracks)
4. [Tabela `sections`](#tabela-sections)
5. [Tabela `points`](#tabela-points)
6. [Tabela `images`](#tabela-images)
7. [Tabela `imageables`](#tabela-imageables)
8. [Tabela `links`](#tabela-links)
9. [Tabela `personal_access_tokens`](#tabela-personal_access_tokens)
10. [Tabela `rivers`](#tabela-rivers)
11. [Tabela `regions`](#tabela-regions)
12. [Tabela `trail_region`](#tabela-trail_region)

## Tabela `trails`

Przechowuje informacje o szlakach kajakowych.

| Kolumna      | Typ                    | Opis                                      |
|--------------|------------------------|-------------------------------------------|
| id           | BIGINT                 | Klucz główny, auto-inkrementowany         |
| river_name   | STRING                 | Nazwa rzeki, indeksowana                  |
| trail_name   | STRING                 | Nazwa szlaku, indeksowana                 |
| slug         | STRING                 | Unikalny identyfikator URL, nullable, indeksowany |
| description  | TEXT                   | Opis szlaku, domyślnie: ''                |
| start_lat    | DECIMAL(17,14)         | Szerokość geograficzna początku, indeksowana |
| start_lng    | DECIMAL(17,14)         | Długość geograficzna początku, indeksowana |
| end_lat      | DECIMAL(17,14)         | Szerokość geograficzna końca, indeksowana |
| end_lng      | DECIMAL(17,14)         | Długość geograficzna końca, indeksowana   |
| trail_length | INTEGER                | Długość szlaku w metrach                  |
| rating       | DECIMAL(3,1)           | Ocena własna trasy, indeksowana, domyślnie: 0 |
| author       | STRING                 | Autor szlaku                              |
| difficulty   | ENUM                   | Poziom trudności: 'łatwy', 'umiarkowany', 'trudny', domyślnie: 'łatwy' |
| scenery      | INTEGER                | Ocena krajobrazu, domyślnie: 0            |
| created_at   | TIMESTAMP              | Data utworzenia rekordu                   |
| updated_at   | TIMESTAMP              | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO trails (river_name, trail_name, slug, description, start_lat, start_lng, end_lat, end_lng, trail_length, rating, author, difficulty, scenery)
VALUES ('Wisła', 'Kraków - Sandomierz', 'krakow-sandomierz', 'Malowniczy szlak wzdłuż Wisły', 50.0546, 19.9352, 50.6821, 21.7506, 115000, 4.5, 'Jan Kowalski', 'umiarkowany', 8);
```

## Tabela `point_types`

Przechowuje typy punktów na szlakach.

| Kolumna     | Typ       | Opis                                      |
|-------------|-----------|-------------------------------------------|
| id          | BIGINT    | Klucz główny, auto-inkrementowany         |
| type        | STRING    | Typ punktu, indeksowany                   |
| created_at  | TIMESTAMP | Data utworzenia rekordu                   |
| updated_at  | TIMESTAMP | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO point_types (type) VALUES ('Przystań');
INSERT INTO point_types (type) VALUES ('Miejsce biwakowe');
INSERT INTO point_types (type) VALUES ('Przenoszenie kajaka');
```

## Tabela `river_tracks`

Przechowuje szczegółowy przebieg rzeki dla każdego szlaku.

| Kolumna      | Typ       | Opis                                      |
|--------------|-----------|-------------------------------------------|
| id           | BIGINT    | Klucz główny, auto-inkrementowany         |
| trail_id     | BIGINT    | Klucz obcy do tabeli trails               |
| track_points | JSON      | Punkty trasy w formacie JSON              |
| created_at   | TIMESTAMP | Data utworzenia rekordu                   |
| updated_at   | TIMESTAMP | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO river_tracks (trail_id, track_points)
VALUES (1, '[
  {"lat": 50.0546, "lng": 19.9352},
  {"lat": 50.3546, "lng": 20.5352},
  {"lat": 50.6821, "lng": 21.7506}
]');
```

## Tabela `sections`

Przechowuje informacje o sekcjach szlaków.

| Kolumna             | Typ       | Opis                                      |
|---------------------|-----------|-------------------------------------------|
| id                  | BIGINT    | Klucz główny, auto-inkrementowany         |
| trail_id            | BIGINT    | Klucz obcy do tabeli trails               |
| name                | STRING    | Nazwa sekcji, indeksowana                 |
| description         | TEXT      | Opis sekcji                               |
| polygon_coordinates | JSON      | Współrzędne wielokąta w formacie JSON     |
| scenery             | INTEGER   | Ocena krajobrazu sekcji, domyślnie: 0     |
| created_at          | TIMESTAMP | Data utworzenia rekordu                   |
| updated_at          | TIMESTAMP | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO sections (trail_id, name, description, polygon_coordinates, scenery)
VALUES (1, 'Kraków - Niepołomice', 'Początkowy odcinek szlaku', '[
  {"lat": 50.0546, "lng": 19.9352},
  {"lat": 50.0746, "lng": 20.2152},
  {"lat": 50.0846, "lng": 20.3552},
  {"lat": 50.0546, "lng": 19.9352}
]', 7);
```

## Tabela `points`

Przechowuje informacje o punktach na szlakach.

| Kolumna       | Typ           | Opis                                      |
|---------------|---------------|-------------------------------------------|
| id            | BIGINT        | Klucz główny, auto-inkrementowany         |
| trail_id      | BIGINT        | Klucz obcy do tabeli trails               |
| point_type_id | BIGINT        | Klucz obcy do tabeli point_types          |
| name          | STRING        | Nazwa punktu, indeksowana                 |
| description   | TEXT          | Opis punktu                               |
| lat           | DECIMAL(10,7) | Szerokość geograficzna punktu             |
| lng           | DECIMAL(10,7) | Długość geograficzna punktu               |
| created_at    | TIMESTAMP     | Data utworzenia rekordu                   |
| updated_at    | TIMESTAMP     | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO points (trail_id, point_type_id, name, description, lat, lng)
VALUES (1, 1, 'Przystań Kraków', 'Początkowa przystań w Krakowie', 50.0546, 19.9352);
```

## Tabela `images`

Przechowuje informacje o obrazach.

| Kolumna    | Typ       | Opis                                      |
|------------|-----------|-------------------------------------------|
| id         | BIGINT    | Klucz główny, auto-inkrementowany         |
| path       | STRING    | Ścieżka do pliku obrazu                   |
| created_at | TIMESTAMP | Data utworzenia rekordu                   |
| updated_at | TIMESTAMP | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO images (path) VALUES ('/storage/images/trail_1_image.jpg');
```

## Tabela `imageables`

Tabela pośrednia dla relacji polimorficznej obrazów.

| Kolumna        | Typ       | Opis                                      |
|----------------|-----------|-------------------------------------------|
| id             | BIGINT    | Klucz główny, auto-inkrementowany         |
| image_id       | BIGINT    | Klucz obcy do tabeli images               |
| imageable_id   | BIGINT    | ID powiązanego obiektu                    |
| imageable_type | STRING    | Typ powiązanego obiektu                   |
| is_main        | BOOLEAN   | Czy jest głównym obrazem, domyślnie: false|
| order          | INTEGER   | Kolejność wyświetlania, domyślnie: 0      |
| created_at     | TIMESTAMP | Data utworzenia rekordu                   |
| updated_at     | TIMESTAMP | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO imageables (image_id, imageable_id, imageable_type, is_main, order)
VALUES (1, 1, 'App\Models\Trail', true, 1);
```

## Tabela `links`

Przechowuje linki związane z sekcjami.

| Kolumna    | Typ       | Opis                                      |
|------------|-----------|-------------------------------------------|
| id         | BIGINT    | Klucz główny, auto-inkrementowany         |
| section_id | BIGINT    | Klucz obcy do tabeli sections             |
| url        | STRING    | Adres URL linku                           |
| meta_data  | STRING    | Metadane linku, nullable                  |
| created_at | TIMESTAMP | Data utworzenia rekordu                   |
| updated_at | TIMESTAMP | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO links (section_id, url, meta_data)
VALUES (1, 'https://example.com/krakow-info', '{"title": "Informacje o Krakowie", "description": "Przewodnik po Krakowie"}');
```

## Tabela `personal_access_tokens`

Przechowuje tokeny dostępu osobistego dla API.

| Kolumna        | Typ         | Opis                                      |
|----------------|-------------|-------------------------------------------|
| id             | BIGINT      | Klucz główny, auto-inkrementowany         |
| tokenable_id   | BIGINT      | ID powiązanego modelu                     |
| tokenable_type | STRING      | Typ powiązanego modelu                    |
| name           | STRING      | Nazwa tokenu                              |
| token          | STRING(64)  | Unikalny hash tokenu                      |
| abilities      | TEXT        | Uprawnienia tokenu, nullable              |
| last_used_at   | TIMESTAMP   | Data ostatniego użycia tokenu, nullable   |
| expires_at     | TIMESTAMP   | Data wygaśnięcia tokenu, nullable         |
| created_at     | TIMESTAMP   | Data utworzenia rekordu                   |
| updated_at     | TIMESTAMP   | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO personal_access_tokens (tokenable_id, tokenable_type, name, token, abilities)
VALUES (1, 'App\Models\User', 'API Token', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', '["read", "write"]');
```

## Tabela `rivers`

Przechowuje informacje o rzekach z wykorzystaniem typu przestrzennego.

| Kolumna    | Typ                  | Opis                                      |
|------------|----------------------|-------------------------------------------|
| id         | BIGINT               | Klucz główny, auto-inkrementowany         |
| name       | STRING               | Nazwa rzeki, indeksowana                  |
| path       | GEOGRAPHY(LINESTRING, 4326) | Przestrzenny przebieg rzeki, indeksowany |
| created_at | TIMESTAMP            | Data utworzenia rekordu                   |
| updated_at | TIMESTAMP            | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO rivers (name, path)
VALUES ('Wisła', ST_GeomFromText('LINESTRING(19.9352 50.0546, 20.5352 50.3546, 21.7506 50.6821)', 4326));
```

## Tabela `regions`

Przechowuje informacje o regionach geograficznych.

| Kolumna      | Typ                  | Opis                                      |
|--------------|----------------------|-------------------------------------------|
| id           | BIGINT               | Klucz główny, auto-inkrementowany         |
| name         | STRING               | Nazwa regionu                             |
| slug         | STRING               | Unikalny identyfikator URL                |
| type         | ENUM                 | Typ regionu: 'country', 'state', 'city', 'geographic_area' |
| parent_id    | BIGINT               | ID regionu nadrzędnego, nullable          |
| is_root      | BOOLEAN              | Czy jest regionem głównym, domyślnie: false |
| center_point | GEOGRAPHY(POINT, 4326) | Punkt centralny regionu, nullable         |
| area         | GEOGRAPHY(POLYGON, 4326) | Obszar regionu, nullable                 |
| created_at   | TIMESTAMP            | Data utworzenia rekordu                   |
| updated_at   | TIMESTAMP            | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO regions (name, slug, type, center_point, area)
VALUES ('Małopolska', 'malopolska', 'state', 
        ST_GeomFromText('POINT(20.0000 50.0000)', 4326),
        ST_GeomFromText('POLYGON((19.0000 49.5000, 21.0000 49.5000, 21.0000 50.5000, 19.0000 50.5000, 19.0000 49.5000))', 4326));
```

## Tabela `trail_region`

## Tabela `trail_region`

Tabela pośrednia łącząca szlaki z regionami.

| Kolumna    | Typ       | Opis                                      |
|------------|-----------|-------------------------------------------|
| id         | BIGINT    | Klucz główny, auto-inkrementowany         |
| trail_id   | BIGINT    | Klucz obcy do tabeli trails               |
| region_id  | BIGINT    | Klucz obcy do tabeli regions              |
| created_at | TIMESTAMP | Data utworzenia rekordu                   |
| updated_at | TIMESTAMP | Data ostatniej aktualizacji rekordu       |

Przykład użycia:
```sql
INSERT INTO trail_region (trail_id, region_id)
VALUES (1, 1);
```

## Relacje między tabelami

1. `trails` ma wiele `river_tracks` (one-to-many)
2. `trails` ma wiele `sections` (one-to-many)
3. `trails` ma wiele `points` (one-to-many)
4. `trails` należy do wielu `regions` poprzez `trail_region` (many-to-many)
5. `sections` ma wiele `links` (one-to-many)
6. `points` należy do jednego `point_type` (many-to-one)
7. `images` może być powiązane z wieloma różnymi tabelami poprzez `imageables` (polymorphic many-to-many)
8. `rivers` może mieć wiele `trails` (one-to-many)
9. `regions` może mieć wiele podregionów (self-referencing one-to-many)

## Przykład JSON dla `polygon_coordinates` i `track_points`

### `polygon_coordinates`

```json
[
    {"lat": 51.5074, "lng": -0.1278},
    {"lat": 51.5075, "lng": -0.1277},
    {"lat": 51.5076, "lng": -0.1276},
    {"lat": 51.5077, "lng": -0.1275}
]
```

## Indeksy

Poza kluczami głównymi, następujące kolumny są indeksowane dla poprawy wydajności zapytań:

1. `trails`: river_name, trail_name, slug, start_lat, start_lng, end_lat, end_lng, rating
2. `point_types`: type
3. `sections`: name
4. `points`: name
5. `rivers`: name, path
6. `regions`: slug

## Typy przestrzenne

W bazie danych wykorzystywane są typy przestrzenne dla przechowywania danych geograficznych:

1. W tabeli `rivers`, kolumna `path` używa typu GEOGRAPHY(LINESTRING, 4326) do reprezentacji przebiegu rzeki.
2. W tabeli `regions`, kolumny `center_point` i `area` używają odpowiednio typów GEOGRAPHY(POINT, 4326) i GEOGRAPHY(POLYGON, 4326) do reprezentacji punktu centralnego i obszaru regionu.

Przykład zapytania wykorzystującego typ przestrzenny:

```sql
-- Znalezienie wszystkich szlaków w promieniu 10 km od punktu
SELECT t.*
FROM trails t
JOIN river_tracks rt ON t.id = rt.trail_id
WHERE ST_DWithin(
    ST_GeomFromText('POINT(20.0000 50.0000)', 4326)::geography,
    ST_GeomFromText(rt.track_points->>0, 4326)::geography,
    10000
);
```

## Walidacja i ograniczenia

1. W tabeli `trails`, kolumna `difficulty` jest ograniczona do wartości: 'łatwy', 'umiarkowany', 'trudny'.
2. W tabeli `regions`, kolumna `type` jest ograniczona do wartości: 'country', 'state', 'city', 'geographic_area'.
3. Kolumny `lat` i `lng` w tabeli `points` powinny być walidowane na poziomie aplikacji, aby zapewnić poprawne wartości geograficzne.

## Migracje

Migracje dla tej bazy danych są zarządzane przez Laravel. Każda tabela ma swoją własną migrację, co ułatwia śledzenie zmian w strukturze bazy danych i ułatwia współpracę w zespole.

Uruchomienie migracji:

```bash
php artisan migrate
```

## Przykłady złożonych zapytań

1. Pobranie wszystkich szlaków wraz z ich punktami i typami punktów:

```sql
SELECT t.*, p.*, pt.type
FROM trails t
LEFT JOIN points p ON t.id = p.trail_id
LEFT JOIN point_types pt ON p.point_type_id = pt.id;
```

2. Znalezienie wszystkich szlaków w danym regionie:

```sql
SELECT t.*
FROM trails t
JOIN trail_region tr ON t.id = tr.trail_id
JOIN regions r ON tr.region_id = r.id
WHERE r.name = 'Małopolska';
```



