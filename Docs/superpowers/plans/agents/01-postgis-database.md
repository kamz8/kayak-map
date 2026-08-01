# Agent 01: PostgreSQL/PostGIS Database

## Scope

Create the dedicated BRouter database connection and schema. Do not change routing logic or the primary MariaDB schema.

## Files

- Modify `config/database.php` with a `brouter` connection using `BROUTER_DB_*` environment variables.
- Modify the development/production compose files only enough to add a PostGIS service and environment variables.
- Create BRouter migrations under `packages/Kamz8/laravel-brouter/database/migrations/`.
- Modify `packages/Kamz8/laravel-brouter/src/BRouterServiceProvider.php` only if package migrations need registering.
- Add focused tests under `packages/Kamz8/laravel-brouter/tests/`.

## Schema

Create tables `imports`, `waterways`, `waterway_nodes`, `waterway_edges`, `waterway_features`, `water_bodies`, `graphs`, and `routes`. Use explicit SRID 4326, GiST indexes for spatial columns, JSONB for source tags, and foreign keys scoped by `import_id` where appropriate. Add an active-import invariant so only one published import is active.

## Verification

- Test that the connection configuration is present without replacing the default connection.
- Test migration structure where the test environment supports PostGIS; otherwise document the exact container command needed for integration verification.
- Run the package's focused tests and Pint on changed PHP files.
