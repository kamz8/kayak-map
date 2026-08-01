# Agent 07: Tests and Benchmark

## Scope

Add end-to-end verification and a repeatable benchmark without changing production behavior beyond testability.

## Files

- Add/update tests under `packages/Kamz8/laravel-brouter/tests/`.
- Add an appropriate benchmark command or test helper under the package; do not create ad-hoc scripts outside the established structure.
- Update package test configuration only if required.

## Coverage

Cover manual import staging/publishing/failure, persisted points, graph versioning, GiST snap behavior, exact virtual-node routing, cache invalidation, branches, disconnected waterways, and mapped-line reservoir traversal.

## Benchmark Metrics

Record import time, normalization time, graph-build time, snap time, route time, memory usage, cache hit/miss, and error counts. Compare the old in-memory path against the persisted PostGIS path on the same fixtures. Use `EXPLAIN ANALYZE` for nearest-edge queries and verify that the spatial index is selected.
