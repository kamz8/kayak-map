# Agent 04: PostGIS Spatial Snapper

## Scope

Replace linear PHP edge scanning with PostGIS candidate lookup and exact projection. Do not implement the complete router.

## Files

- Refactor `packages/Kamz8/laravel-brouter/src/Services/EdgeSnapper.php` behind a clear interface.
- Create a PostGIS edge repository/provider in the package.
- Extend `SnapResultData` only with required edge position/offset fields.
- Add unit tests and PostGIS integration tests.

## Required SQL Semantics

Use `ST_SetSRID(ST_MakePoint(:lng, :lat), 4326)` and `ST_DWithin` for the tolerance filter. Use a GiST-indexed geometry/geography column and `ST_ClosestPoint` for the exact projected coordinate. Distances must be in meters and SRIDs must match.

## Required Result

Return the selected edge ID, projected point, distance from request point, fractional position or offset along the edge, and the two endpoint IDs. Reject candidates outside snap tolerance. Do not use the current meters-per-degree approximation for the database path.
