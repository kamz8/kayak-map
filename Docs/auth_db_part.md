# Dokumentacja struktury bazy danych - System użytkowników

## Spis treści
1. [Użytkownicy (users)](#użytkownicy)
2. [Konta społecznościowe (social_accounts)](#konta-społecznościowe)
3. [Urządzenia użytkowników (user_devices)](#urządzenia-użytkowników)
4. [Sesje (sessions)](#sesje)
5. [Kody weryfikacyjne (verification_codes)](#kody-weryfikacyjne)
6. [Reset hasła (password_reset_tokens)](#reset-hasła)

## Użytkownicy
Tabela: `users`

Przechowuje podstawowe informacje o użytkownikach systemu.

### Struktura
| Kolumna | Typ | Opis |
|---------|-----|------|
| id | bigint | Klucz główny |
| first_name | string, nullable | Imię użytkownika |
| last_name | string, nullable | Nazwisko użytkownika |
| name | string | Nazwa wyświetlana/nick użytkownika |
| email | string, unique | Adres email (unikalny) |
| password | string, nullable | Hasło (hash) - nullable dla social login |
| email_verified_at | timestamp, nullable | Data weryfikacji emaila |
| phone | string, unique, nullable | Numer telefonu |
| phone_verified | boolean | Status weryfikacji telefonu |
| bio | text, nullable | Opis profilu |
| location | string, nullable | Lokalizacja użytkownika |
| birth_date | date, nullable | Data urodzenia |
| gender | enum | Płeć: male/female/other/prefer_not_to_say |
| preferences | json, nullable | Preferencje użytkownika |
| notification_settings | json, nullable | Ustawienia powiadomień |
| is_active | boolean | Status aktywności konta |
| last_login_at | timestamp, nullable | Ostatnie logowanie |
| last_login_ip | string, nullable | IP ostatniego logowania |
| remember_token | string, nullable | Token "zapamiętaj mnie" |
| deleted_at | timestamp, nullable | Miękkie usuwanie |
| created_at | timestamp | Data utworzenia |
| updated_at | timestamp | Data aktualizacji |

### Przykłady JSON
#### preferences
```json
{
    "theme": "dark",
    "email_notifications": true,
    "push_notifications": false
}
```

#### notification_settings
```json
{
    "new_message": true,
    "new_follower": true,
    "system_updates": false
}
```

## Konta społecznościowe
Tabela: `social_accounts`

Przechowuje informacje o połączonych kontach społecznościowych użytkownika.

### Struktura
| Kolumna | Typ | Opis |
|---------|-----|------|
| id | bigint | Klucz główny |
| user_id | bigint, foreign | Referencja do użytkownika |
| provider | string | Nazwa dostawcy (google, facebook) |
| provider_id | string | ID użytkownika u dostawcy |
| provider_token | string, nullable | Token dostępu |
| provider_refresh_token | string, nullable | Token odświeżający |
| token_expires_at | timestamp, nullable | Data wygaśnięcia tokenu |
| provider_nickname | string, nullable | Nick u dostawcy |
| created_at | timestamp | Data utworzenia |
| updated_at | timestamp | Data aktualizacji |

### Indeksy
- Unikalny indeks na `['provider', 'provider_id']`

## Urządzenia użytkowników
Tabela: `user_devices`

Przechowuje informacje o urządzeniach używanych przez użytkownika.

### Struktura
| Kolumna | Typ | Opis |
|---------|-----|------|
| id | bigint | Klucz główny |
| user_id | bigint, foreign | Referencja do użytkownika |
| device_id | string, unique | Unikalny identyfikator urządzenia |
| device_type | string | Typ urządzenia (ios/android/web) |
| device_name | string | Nazwa urządzenia |
| push_token | string, nullable | Token dla powiadomień push |
| last_used_at | timestamp | Ostatnie użycie |
| created_at | timestamp | Data utworzenia |
| updated_at | timestamp | Data aktualizacji |

### Przykłady device_id
- iOS: IDFV (Identifier for Vendor)
- Android: Android ID
- Web: Generowane UUID

## Sesje
Tabela: `sessions`

Przechowuje informacje o sesjach użytkowników.

### Struktura
| Kolumna | Typ | Opis |
|---------|-----|------|
| id | string | Klucz główny (ID sesji) |
| user_id | bigint, nullable | Referencja do użytkownika |
| ip_address | string(45), nullable | Adres IP |
| user_agent | text, nullable | User Agent przeglądarki |
| device_id | string, nullable | ID urządzenia |
| payload | longtext | Dane sesji |
| last_activity | integer | Timestamp ostatniej aktywności |

# System kodów weryfikacyjnych (verification_codes)

## Przeznaczenie
Tabela przechowuje jednorazowe kody weryfikacyjne używane do:
- Weryfikacji adresu email
- Weryfikacji numeru telefonu
- Dwuetapowej weryfikacji (2FA)
- Potwierdzenia ważnych operacji na koncie

## Struktura tabeli

| Kolumna | Typ | Opis |
|---------|-----|------|
| id | bigint | Klucz główny |
| user_id | bigint, foreign | Referencja do użytkownika |
| code | string | Kod weryfikacyjny (6 znaków dla SMS, 32 znaki dla email) |
| type | string | Typ weryfikacji (email/phone/2fa/action) |
| used | boolean | Flaga określająca czy kod został już wykorzystany |
| expires_at | timestamp | Data i czas wygaśnięcia kodu |
| created_at | timestamp | Data utworzenia |
| updated_at | timestamp | Data aktualizacji |

## Typy kodów (type)
- **email**: Weryfikacja adresu email
    - 32 znakowy token
    - Ważność: 24 godziny
    - Wysyłany w linku weryfikacyjnym

- **phone**: Weryfikacja numeru telefonu
    - 6 cyfrowy kod numeryczny
    - Ważność: 10 minut
    - Wysyłany przez SMS

- **2fa**: Dwuetapowa weryfikacja
    - 6 cyfrowy kod numeryczny
    - Ważność: 5 minut
    - Wysyłany przez SMS lub generowany przez authenticator

- **action**: Potwierdzenie ważnych operacji
    - 6 cyfrowy kod numeryczny
    - Ważność: 10 minut
    - Wysyłany przez SMS lub email

## Przykładowe rekordy
```sql
-- Weryfikacja email
INSERT INTO verification_codes (
    user_id, 
    code, 
    type, 
    used, 
    expires_at
) VALUES (
    1, 
    'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 
    'email', 
    false, 
    NOW() + INTERVAL '24 hours'
);

-- Weryfikacja SMS
INSERT INTO verification_codes (
    user_id, 
    code, 
    type, 
    used, 
    expires_at
) VALUES (
    1, 
    '123456', 
    'phone', 
    false, 
    NOW() + INTERVAL '10 minutes'
);
```

## Zasady bezpieczeństwa
1. **Jednokrotne użycie**
    - Kod może być użyty tylko raz
    - Po użyciu flaga `used` jest ustawiana na `true`

2. **Czas życia kodu**
    - Każdy kod ma określony czas ważności
    - Po upływie `expires_at` kod jest nieważny
    - Stare kody są automatycznie usuwane przez scheduler

3. **Rate limiting**
    - Maksymalnie 3 próby weryfikacji w ciągu 10 minut
    - Maksymalnie 5 wysłanych kodów w ciągu godziny

4. **Długość i format kodów**
    - Email: 32 znaki alfanumeryczne
    - SMS/2FA: 6 cyfr
    - Kody są generowane z użyciem bezpiecznego generatora liczb losowych

## Powiązane funkcjonalności
1. **Wyzwalacze**:
    - Automatyczne usuwanie przeterminowanych kodów
    - Unieważnianie starych kodów przy generowaniu nowych

2. **Monitorowanie**:
    - Logowanie nieudanych prób weryfikacji
    - Alertowanie przy podejrzanej aktywności

## Indeksy
```sql
CREATE INDEX idx_verification_codes_user_type ON verification_codes(user_id, type);
CREATE INDEX idx_verification_codes_expires_at ON verification_codes(expires_at);
```

## Przykład użycia w kodzie
```php
// Generowanie kodu
$code = VerificationCode::create([
    'user_id' => $user->id,
    'code' => Str::random(32), // dla email
    'type' => 'email',
    'expires_at' => now()->addDay()
]);

// Weryfikacja kodu
$isValid = VerificationCode::where([
    'user_id' => $user->id,
    'code' => $requestCode,
    'type' => 'email',
    'used' => false
])
->where('expires_at', '>', now())
->exists();
```

## Czyszczenie danych
Scheduler usuwa:
- Wykorzystane kody starsze niż 24h
- Niewykorzystane, przeterminowane kody
- Kody dla usuniętych użytkowników (cascade)

## Reset hasła
Tabela: `password_reset_tokens`

Przechowuje tokeny do resetowania hasła.

### Struktura
| Kolumna | Typ | Opis |
|---------|-----|------|
| email | string | Klucz główny (email) |
| token | string | Token resetowania |
| created_at | timestamp, nullable | Data utworzenia |

## Relacje między tabelami

```plaintext
users
 ├── social_accounts (1:N)
 ├── user_devices (1:N)
 ├── verification_codes (1:N)
 └── sessions (1:N)
```

## Indeksy wydajnościowe
- users: email, phone
- social_accounts: provider, provider_id
- user_devices: device_id
- sessions: user_id, last_activity

## Miękkie usuwanie
- Tabela `users` wspiera miękkie usuwanie (soft deletes)
- Pozostałe tabele używają CASCADE DELETE

## Ważne uwagi
1. Wszystkie timestampy są w UTC
2. Hasła są zawsze hashowane przed zapisem
3. Tokeny social media są szyfrowane
4. Pole vattr w imageables przechowuje metadane obrazów
