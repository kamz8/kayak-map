
# Reverse Geocoding API

Endpoint ten służy do konwersji współrzędnych geograficznych na dane adresowe.

## Endpoint

`POST /api/v1/geocoding/reverse`

## Parametry żądania

Żądanie powinno być wysłane jako `POST` z następującymi danymi w formacie JSON:

| Parametr | Typ    | Opis                                       |
|----------|--------|-------------------------------------------|
| lat      | float  | Szerokość geograficzna (zakres: -90 do 90) |
| lang     | float  | Długość geograficzna (zakres: -180 do 180) |

### Przykład żądania

```json
{
    "lat": 51.97366001,
    "lang": 14.70688685
}
```

## Odpowiedź

Odpowiedź jest zwracana w formacie JSON.

| Pole    | Typ    | Opis                                                |
|---------|--------|-----------------------------------------------------|
| country | string | Nazwa kraju                                         |
| state   | string | Nazwa stanu lub województwa (może być null)         |
| city    | string | Nazwa miasta lub miejscowości (może być null)       |
| lat     | float  | Szerokość geograficzna (może się nieznacznie różnić od podanej) |
| lng     | float  | Długość geograficzna (może się nieznacznie różnić od podanej)   |

### Przykład odpowiedzi

```json
{
    "country": "Nazwa kraju",
    "state": "Nazwa stanu lub województwa",
    "city": "Nazwa miasta lub miejscowości",
    "lat": 51.9736299,
    "lng": 14.7068482
}
```

## Kody odpowiedzi

- 200 OK: Zapytanie powiodło się
- 400 Bad Request: Nieprawidłowe parametry żądania
- 404 Not Found: Nie znaleziono danych dla podanych współrzędnych
- 500 Internal Server Error: Błąd serwera

## Uwagi

- Wartości `lat` i `lng` w odpowiedzi mogą nieznacznie różnić się od wartości podanych w żądaniu ze względu na proces geokodowania.
- Pola `state` i `city` mogą być `null`, jeśli dane nie są dostępne dla podanych współrzędnych.
- API korzysta z zewnętrznego serwisu geokodowania, więc dokładność i dostępność danych może się różnić w zależności od lokalizacji.

## Ograniczenia

- Limit zapytań: 1 zapytanie na sekundę
- Wymagane jest podanie nagłówka `User-Agent`


