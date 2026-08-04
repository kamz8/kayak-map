# BRouter PostGIS Verification Report

Date: 2026-08-05
Branch: `feature/brouter-postgis`
Worktree: `D:\Laravel\kayak-map\.worktrees\brouter-postgis`

## Summary

Implemented and verified the BRouter PostGIS import/routing path with a real Overpass import, a dedicated PostgreSQL/PostGIS database, persistent graph data, and query-speed benchmarking.

The key runtime failure was fixed: real `brouter:precache` imports no longer fail with `Undefined array key "lon"`. Query performance for nearest-edge snapping was improved by adding a GiST index on `geometry::geography`.

## What Was Changed

### Docker PHP Runtime

Files:
- `Dockerfile`
- `Dockerfile.prod`

Changes:
- Added `libpq-dev`.
- Added PHP extension `pdo_pgsql`.
- Changed Docker `npm ci` calls to `npm ci --legacy-peer-deps`, matching the existing project precedent in `Dockerfile.vite`.

Reason:
- Laravel needs `pdo_pgsql` to use the dedicated BRouter PostgreSQL/PostGIS connection.
- Docker image build failed because current `vite@7.2.7` conflicts with `laravel-vite-plugin@1.3.0` peer ranges unless legacy peer resolution is used.

### PostGIS Schema

File:
- `packages/Kamz8/laravel-brouter/database/migrations/2026_08_01_000000_create_brouter_schema.php`

Changes:
- Added unique constraints for composite FK targets:
  - `waterway_edges(id, import_id)`
  - `graphs(id, import_id)`
- Added spatial index:
  - `waterway_edges_geography_gist_idx` on `(geometry::geography)`

Reason:
- PostgreSQL rejected composite foreign keys unless the referenced column pair was unique.
- The nearest-edge query uses `ST_DWithin(e.geometry::geography, ...)`, so a plain GiST index on `geometry` was not used for that filter.

### Import Normalization

File:
- `packages/Kamz8/laravel-brouter/src/Services/WaterwayNormalizer.php`

Changes:
- Node features are only persisted when node coordinates contain numeric `lat` and `lon`.
- Way/relation geometries are filtered to points with numeric `lat` and `lon`.

Reason:
- Real Overpass responses can contain elements or geometry members without full coordinates.
- Previous code used `$element['lat']`, `$element['lon']`, and `$point['lon']` without validating their presence, causing real imports to fail.

### WKT Geometry Writer

File:
- `packages/Kamz8/laravel-brouter/src/Services/BrouterImportRepository.php`

Changes:
- WKT generation now accepts both `lon` and `lng` as longitude keys.
- If neither key exists, it throws a clear `RuntimeException`.

Reason:
- Raw Overpass geometry uses `lon`.
- Internal graph edge geometry uses `lng`.
- The repository previously assumed only `lon`, causing real persisted edge imports to fail.

### Tests

Files:
- `packages/Kamz8/laravel-brouter/tests/Feature/RealOverpassImportTest.php`
- `packages/Kamz8/laravel-brouter/tests/Unit/Services/WaterwayNormalizerTest.php`
- `packages/Kamz8/laravel-brouter/tests/Unit/ConfigurationTest.php`
- `packages/Kamz8/laravel-brouter/tests/Unit/Console/BRouterServiceProviderTest.php`
- `packages/Kamz8/laravel-brouter/tests/Unit/Facades/BRouterFacadeTest.php`

Changes:
- Added a real, opt-in integration test that runs:
  - PostGIS schema reset
  - BRouter migrations
  - real `brouter:precache`
  - real Overpass request
  - real persistence to PostGIS
- Added unit regressions for missing `lat/lon` in node and geometry input.
- Updated older tests to match current package contracts and config keys.

The real integration test is opt-in because it depends on external Overpass availability:
- Environment flag: `BROUTER_REAL_IMPORT_TEST=1`
- Recommended endpoint during verification: `https://overpass.kumi.systems/api/interpreter`

## Verified Results

### Docker

Verified Docker became responsive after reset:

```text
Docker Desktop 4.34.2
Engine 27.2.0
```

Verified running services included:

```text
brouter-postgis Up
kayak-app Up (healthy)
mariadb Up
redis Up
```

### Migration

Command pattern used:

```bash
php artisan migrate --database=brouter --path=packages/Kamz8/laravel-brouter/database/migrations --force
```

Observed result:

```text
2026_08_01_000000_create_brouter_schema ... DONE
```

### Real Import Without Mocks

Command pattern used:

```bash
php artisan brouter:precache '51.105,17.000,51.130,17.060' --name=Odra
```

Environment used:

```text
OVERPASS_ENDPOINT=https://overpass.kumi.systems/api/interpreter
BROUTER_DB_HOST=brouter-db
BROUTER_DB_DATABASE=brouter
BROUTER_DB_USERNAME=brouter
BROUTER_DB_PASSWORD=brouter
```

Observed result:

```text
Import 1 published with 18097 nodes and 18541 edges.
```

### Real Integration Test

Command pattern used:

```bash
php artisan test --compact packages/Kamz8/laravel-brouter/tests/Feature/RealOverpassImportTest.php
```

Required environment:

```text
BROUTER_REAL_IMPORT_TEST=1
OVERPASS_ENDPOINT=https://overpass.kumi.systems/api/interpreter
```

Observed result:

```text
Tests: 1 passed (5 assertions)
Duration: 40.73s
```

### BRouter Unit Tests

Command pattern used:

```bash
php artisan test --compact packages/Kamz8/laravel-brouter/tests/Unit
```

Observed result:

```text
Tests: 45 passed (139 assertions)
Duration: 4.82s
```

### Formatting

Command pattern used:

```bash
vendor/bin/pint <changed files>
```

Observed result:

```text
FIXED 10 files, 3 style issues fixed
```

## Benchmark Results

Benchmark command:

```bash
php artisan brouter:benchmark "51.11446,17.072092" "51.119678,17.071742" --river=Odra --snap=500
```

Dataset:
- Active imports: `1`
- Waterway edges: `18541`
- Imported bbox: `51.105,17.000,51.130,17.060`

Benchmark output:

```json
{
  "graph_version": "f51666a3-b392-4178-bf9f-d0a7570c7d95",
  "route_time_ms": 338.331,
  "distance_m": 585.594107255776,
  "points": 6,
  "memory_mb": 94,
  "cache": {
    "osm": "versioned",
    "graph": "f51666a3-b392-4178-bf9f-d0a7570c7d95",
    "route": "versioned"
  }
}
```

Nearest-edge `EXPLAIN ANALYZE` after adding the geography GiST index:

```text
Index Scan using waterway_edges_geography_gist_idx on waterway_edges e
Execution Time: 0.976 ms
```

Before the geography expression index, the same nearest-edge explain used a bitmap/heap scan over all edges for the import and took approximately `55 ms`.

Result:
- Nearest-edge lookup improved from roughly `55 ms` to roughly `0.98 ms`.
- The query now uses the intended spatial index.

## Operational Notes

### Overpass Availability

The default endpoint `https://overpass-api.de/api/interpreter` returned intermittent `504` responses during verification:

```text
Dispatcher_Client::request_read_and_idx::timeout. The server is probably too busy to handle your request.
```

The import completed successfully using:

```text
https://overpass.kumi.systems/api/interpreter
```

This is an external service reliability issue, not a PostGIS or PHP runtime issue.

### Test Strategy

The package now has both:
- Fast unit regressions for malformed coordinate data.
- A real opt-in integration test for Overpass/PostGIS import.

The real test should not run by default in every local/CI test suite unless the environment is prepared and external API availability is acceptable.

### Current Worktree State

Changes are not committed.

Primary changed areas:
- Docker PHP/PostgreSQL runtime support.
- BRouter PostGIS migration and indexes.
- BRouter normalization/persistence fixes.
- Real integration test and unit regression tests.

## Commands Used For Final Verification

Examples below were run inside Docker with the worktree mounted and the current local BRouter package mounted over `vendor/kamz8/laravel-brouter` to avoid using stale vendor package code.

Unit tests:

```bash
docker run --rm \
  -v "D:/Laravel/kayak-map/.worktrees/brouter-postgis:/var/www/html" \
  -v "D:/Laravel/kayak-map/vendor:/var/www/html/vendor" \
  -v "D:/Laravel/kayak-map/.worktrees/brouter-postgis/packages/Kamz8/laravel-brouter:/var/www/html/vendor/kamz8/laravel-brouter" \
  -w /var/www/html \
  -e APP_ENV=testing \
  -e APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
  --entrypoint php kayak-app \
  artisan test --compact packages/Kamz8/laravel-brouter/tests/Unit
```

Real integration test:

```bash
docker run --rm --network brouter-postgis_laravel \
  -v "D:/Laravel/kayak-map/.worktrees/brouter-postgis:/var/www/html" \
  -v "D:/Laravel/kayak-map/vendor:/var/www/html/vendor" \
  -v "D:/Laravel/kayak-map/.worktrees/brouter-postgis/packages/Kamz8/laravel-brouter:/var/www/html/vendor/kamz8/laravel-brouter" \
  -w /var/www/html \
  -e APP_ENV=testing \
  -e APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
  -e CACHE_STORE=array \
  -e BROUTER_CACHE_STORE=array \
  -e BROUTER_REAL_IMPORT_TEST=1 \
  -e OVERPASS_ENDPOINT=https://overpass.kumi.systems/api/interpreter \
  -e BROUTER_DB_HOST=brouter-db \
  -e BROUTER_DB_PORT=5432 \
  -e BROUTER_DB_DATABASE=brouter \
  -e BROUTER_DB_USERNAME=brouter \
  -e BROUTER_DB_PASSWORD=brouter \
  --entrypoint php kayak-app \
  artisan test --compact packages/Kamz8/laravel-brouter/tests/Feature/RealOverpassImportTest.php
```

Benchmark:

```bash
docker run --rm --network brouter-postgis_laravel \
  -v "D:/Laravel/kayak-map/.worktrees/brouter-postgis:/var/www/html" \
  -v "D:/Laravel/kayak-map/vendor:/var/www/html/vendor" \
  -v "D:/Laravel/kayak-map/.worktrees/brouter-postgis/packages/Kamz8/laravel-brouter:/var/www/html/vendor/kamz8/laravel-brouter" \
  -w /var/www/html \
  -e APP_ENV=testing \
  -e APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
  -e CACHE_STORE=array \
  -e BROUTER_CACHE_STORE=array \
  -e BROUTER_DB_HOST=brouter-db \
  -e BROUTER_DB_PORT=5432 \
  -e BROUTER_DB_DATABASE=brouter \
  -e BROUTER_DB_USERNAME=brouter \
  -e BROUTER_DB_PASSWORD=brouter \
  --entrypoint php kayak-app \
  artisan brouter:benchmark "51.11446,17.072092" "51.119678,17.071742" --river=Odra --snap=500
```
