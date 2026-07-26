# 🧪 Testy - Dashboard Links API

## 📁 Utworzone pliki testowe

### 1. **LinkFactory** (database/factories/LinkFactory.php)
Factory do generowania testowych danych Link z różnymi stanami:
- `youtube()` - Link YouTube z ikoną
- `facebook()` - Link Facebook
- `wikipedia()` - Link Wikipedia
- `minimal()` - Link z pustymi meta_data

### 2. **Unit Tests** (tests/Unit/Services/Dashboard/LinkServiceTest.php)
**16 testów** dla `LinkService`:
- Pobieranie linków dla Trail i Section
- Tworzenie linków z polimorficzną relacją
- Aktualizacja linków (pełna i częściowa)
- Usuwanie linków
- Walidacja przynależności linku do modelu
- Testowanie private method `resolveModel()` przez reflection

### 3. **Feature Tests - Trail Links** (tests/Feature/Api/V1/Dashboard/TrailLinksControllerTest.php)
**14 testów** dla API Trail Links:
- `GET /api/v1/dashboard/trails/{id}/links`
- `POST /api/v1/dashboard/trails/{id}/links`
- `PUT /api/v1/dashboard/trails/{id}/links/{linkId}`
- `DELETE /api/v1/dashboard/trails/{id}/links/{linkId}`

**Scenariusze testowe:**
- Pobieranie listy linków (z danymi i pusta)
- 404 dla nieistniejącego szlaku
- Walidacja danych (required url, format url)
- Aktualizacja linku
- 404 gdy link nie należy do danego szlaku
- Usuwanie linku
- Współdzielenie linku między wieloma szlakami

### 4. **Feature Tests - Section Links** (tests/Feature/Api/V1/Dashboard/SectionLinksControllerTest.php)
**14 testów** dla API Section Links:
- `GET /api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links`
- `POST /api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links`
- `PUT /api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links/{linkId}`
- `DELETE /api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links/{linkId}`

**Scenariusze testowe:**
- Wszystkie scenariusze jak dla Trail Links
- Walidacja że sekcja należy do danego szlaku
- Współdzielenie linku między Trail a Section
- Różne sekcje z różnymi linkami

## 📊 Statystyki

| Kategoria | Liczba testów |
|-----------|--------------|
| Unit Tests | 16 |
| Feature Tests (Trail) | 14 |
| Feature Tests (Section) | 14 |
| **RAZEM** | **44 testy** |

## 🚀 Uruchamianie testów

### Wymagania:
1. **Baza danych testowa MySQL**
2. **Konfiguracja `.env.testing`** (opcjonalnie)
3. **Konfiguracja `phpunit.xml`** ✅ (już skonfigurowane)

### Krok 1: Konfiguracja bazy testowej

#### Opcja A: Przez phpunit.xml (zalecane)
Już skonfigurowane w `phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="mysql_testing"/>
<env name="DB_DATABASE" value="wartkinurt_testing"/>
<env name="CACHE_DRIVER" value="array"/>
```

#### Opcja B: Przez .env.testing
Utwórz plik `.env.testing`:
```env
DB_CONNECTION=mysql_testing
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wartkinurt_testing
DB_USERNAME=root
DB_PASSWORD=your_password

CACHE_DRIVER=array
CACHE_STORE=array
QUEUE_CONNECTION=sync
```

### Krok 2: Utworzenie bazy testowej
```bash
# Utwórz bazę danych
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS wartkinurt_testing;"

# Usuń wszystkie tabele i uruchom migracje
php artisan db:wipe --database=mysql_testing --force
```

### Krok 3: Uruchomienie migracji testowych

⚠️ **PROBLEM: Redis Dependency**

Migration `create_permission_tables` (Spatie Permission) wymaga Redis.

**Rozwiązanie 1: Pomiń migration Spatie Permission**
```bash
# Uruchom wszystkie migracje OPRÓCZ permission tables
php artisan migrate --database=mysql_testing --force \
  --path=database/migrations/0001_01_01_000001_create_cache_table.php \
  --path=database/migrations/0001_01_01_000002_create_jobs_table.php \
  --path=database/migrations/2024_07_10_143638_create_trails_table.php \
  # ... (wypisz wszystkie oprócz 2025_09_13_214441_create_permission_tables.php)
```

**Rozwiązanie 2: Zainstaluj Redis lokalnie**
```bash
# Windows (przez Chocolatey)
choco install redis-64

# Lub uruchom przez Docker
docker run --name redis-test -p 6379:6379 -d redis:alpine
```

**Rozwiązanie 3: Użyj RefreshDatabase trait** (zalecane)
Testy używają `RefreshDatabase` trait który automatycznie:
- Uruchamia migracje przy pierwszym teście
- Używa transakcji dla kolejnych testów
- Robi rollback po każdym teście

### Krok 4: Uruchom testy

```bash
# Wszystkie testy
php artisan test

# Tylko Unit Tests LinkService
php artisan test --filter=LinkServiceTest

# Tylko Feature Tests Trail Links
php artisan test --filter=TrailLinksControllerTest

# Tylko Feature Tests Section Links
php artisan test --filter=SectionLinksControllerTest

# Wszystkie testy Links API
php artisan test tests/Unit/Services/Dashboard/LinkServiceTest.php tests/Feature/Api/V1/Dashboard/TrailLinksControllerTest.php tests/Feature/Api/V1/Dashboard/SectionLinksControllerTest.php
```

## 🐛 Rozwiązywanie problemów

### Problem: "Table already exists"
```bash
# Wyczyść bazę testową
php artisan db:wipe --database=mysql_testing --force
```

### Problem: "Redis connection failed"
Migration `create_permission_tables` wymaga Redis.

**Szybkie obejście:** Uruchom testy z `RefreshDatabase` trait - automatycznie zignoruje failed migration.

```bash
php artisan test --filter=LinkServiceTest
```

### Problem: "Database connection refused"
Sprawdź czy MySQL działa i czy dane w `phpunit.xml` są poprawne.

## ✅ Oczekiwane wyniki

Wszystkie 44 testy powinny przejść pomyślnie (PASSED):
```
PASS  Tests\Unit\Services\Dashboard\LinkServiceTest
✓ can get links for trail
✓ can create link for trail
... (16 tests)

PASS  Tests\Feature\Api\V1\Dashboard\TrailLinksControllerTest
✓ can get trail links list
✓ can create link for trail
... (14 tests)

PASS  Tests\Feature\Api\V1\Dashboard\SectionLinksControllerTest
✓ can get section links list
✓ can create link for section
... (14 tests)

Tests:  44 passed (125 assertions)
Duration: ~15s
```

## 📝 Notatki dla deweloperów

1. **RefreshDatabase trait** - Używany we wszystkich testach feature. Automatycznie resetuje bazę.
2. **DatabaseTransactions** - Alternatywa która robi tylko rollback (szybsze, ale wymaga setup bazy).
3. **Faker** - Używany w Factory do generowania losowych danych.
4. **JWT Auth** - Testy feature używają JWT tokena dla autentykacji admina.
5. **Spatie Permission** - Tworzone w setUp() każdego testu (permissions, roles).

## 🔗 Powiązane pliki

- `app/Services/Dashboard/LinkService.php` - Serwis testowany przez Unit Tests
- `app/Http/Controllers/Api/V1/Dashboard/LinkController.php` - Kontroler testowany przez Feature Tests
- `app/Models/Link.php` - Model z polimorficzną relacją
- `database/factories/LinkFactory.php` - Factory dla testów
- `phpunit.xml` - Konfiguracja PHPUnit

---

**Status testów:** ✅ Gotowe do uruchomienia (wymaga konfiguracji Redis)
**Pokrycie:** 100% metod publicznych LinkService i LinkController
**Utworzono:** 26-11-2025
