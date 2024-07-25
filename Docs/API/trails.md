# Dokumentacja API dla Endpointu Trails

## Bazowy URL
Ustawiasz url zgodnie ze śwrodowiskiem na jakim pracujesz. Url bazowy będzie doklejany do zapytania

`http://your-app-url/api/v1`

## Endpoint

`GET /trails`

## Opis

Ten endpoint pobiera kolekcję szlaków na podstawie podanych kryteriów filtrowania. Obsługuje filtrowanie według współrzędnych geograficznych, poziomu trudności i oceny krajobrazu. Odpowiedź zawiera szczegółowe informacje o każdym szlaku, takie jak sekcje, punkty i dane o trasie rzeki.

## Parametry zapytania

| Parametr   | Typ    | Opis                                                             | Wymagany | Przykład |
|------------|--------|------------------------------------------------------------------|----------|----------|
| start_lat  | float  | Początkowa szerokość geograficzna obszaru wyszukiwania.          | Tak      | 51.0     |
| end_lat    | float  | Końcowa szerokość geograficzna obszaru wyszukiwania.             | Tak      | 53.0     |
| start_lng  | float  | Początkowa długość geograficzna obszaru wyszukiwania.            | Tak      | 15.0     |
| end_lng    | float  | Końcowa długość geograficzna obszaru wyszukiwania.               | Tak      | 17.0     |
| difficulty | string | Poziom trudności szlaku. Możliwe wartości: łatwy, umiarkowany, trudny. | Nie      | umiarkowany |
| scenery    | int    | Minimalna ocena krajobrazu (0-10).                                | Nie      | 5        |

## Odpowiedź

Odpowiedź zawiera dane dotyczące szlaków oraz informacje meta. Jeśli nie znaleziono żadnych szlaków, w sekcji meta zawarta jest wiadomość informująca o braku wyników zgodnych z podanymi kryteriami.

### Odpowiedź w przypadku sukcesu

```json
{
    "data": [
        {
            "id": 1,
            "river_name": "Test River",
            "trail_name": "Test Trail",
            "description": "A test trail for testing.",
            "start_lat": 51.5,
            "start_lng": 15.5,
            "end_lat": 52.0,
            "end_lng": 16.0,
            "trail_length": 10000,
            "author": "Test Author",
            "difficulty": "łatwy",
            "scenery": 5,
            "created_at": "2024-07-10T00:00:00.000000Z",
            "updated_at": "2024-07-10T00:00:00.000000Z",
            "river_track": {
                "id": 1,
                "trail_id": 1,
                "track_points": [
                    {"lat": 51.5, "lng": 15.5},
                    {"lat": 52.0, "lng": 16.0}
                ],
                "created_at": "2024-07-10T00:00:00.000000Z",
                "updated_at": "2024-07-10T00:00:00.000000Z"
            },
            "sections": [
                {
                    "id": 1,
                    "trail_id": 1,
                    "name": "Test Section",
                    "description": "A test section.",
                    "polygon_coordinates": [
                        {"lat": 51.5, "lng": 15.5},
                        {"lat": 52.0, "lng": 16.0}
                    ],
                    "scenery": 5,
                    "created_at": "2024-07-10T00:00:00.000000Z",
                    "updated_at": "2024-07-10T00:00:00.000000Z"
                }
            ],
            "points": [
                {
                    "id": 1,
                    "trail_id": 1,
                    "point_type_id": 1,
                    "name": "Test Point",
                    "description": "A test point.",
                    "lat": 51.75,
                    "lng": 15.75,
                    "created_at": "2024-07-10T00:00:00.000000Z",
                    "updated_at": "2024-07-10T00:00:00.000000Z"
                }
            ]
        }
    ],
    "meta": {
        "total_trails": 1
    }
}
```

Odpowiedź w przypadku braku szlaków

```json

{
    "data": [],
    "meta": {
        "total_trails": 0,
        "message": "No trails found for the given criteria."
    }
}
```

### Przykładowe zapytanie

```bash

curl -G http://your-app-url/api/v1/trails --data-urlencode "start_lat=51.0" --data-urlencode "end_lat=53.0" --data-urlencode "start_lng=15.0" --data-urlencode "end_lng=17.0" --data-urlencode "difficulty=łatwy" --data-urlencode "scenery=5"
```
### Podsumowanie

    Bazowy URL: http://your-app-url/api/v1
    Endpoint: GET /trails
    Parametry: start_lat, end_lat, start_lng, end_lng, difficulty, scenery
    Odpowiedź: Obiekt JSON zawierający dane o szlakach i informacje meta.

