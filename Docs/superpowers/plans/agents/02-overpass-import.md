# Agent 02: Manual Overpass Import

## Scope

Replace the placeholder precache command with a manual, staged import. Do not implement routing or change graph algorithms.

## Files

- Modify `packages/Kamz8/laravel-brouter/src/Services/OverpassDataProvider.php`.
- Modify `packages/Kamz8/laravel-brouter/src/Contracts/DataProviderInterface.php` if import-specific methods are needed.
- Modify `packages/Kamz8/laravel-brouter/src/Console/PrecacheWaterwaysCommand.php`.
- Create focused import services and requests/DTOs in the package source tree.
- Add unit tests for Overpass query contents, bbox validation, and import state transitions.

## Required Data

Fetch `way` and `relation` waterway geometry, explicit `node` waterway features, and `natural=water` / `water=reservoir` / `water=lake` metadata. Preserve all tags. Include configurable feature tags: `dam`, `weir`, `lock_gate`, `sluice_gate`, `watermill`, `rapids`, and `waterfall`, plus `barrier=dam`, `barrier=weir`, and `lock=yes` where present.

## Workflow

Create an import record, fetch into that import, normalize and persist data, build the graph, validate counts/geometries, then publish atomically. A failure must mark the import failed and must not replace the active import. The command must never contact Overpass during a route request.
