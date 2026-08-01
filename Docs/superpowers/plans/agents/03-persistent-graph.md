# Agent 03: Persistent Waterway Graph

## Scope

Move normalization and graph construction to import time. Do not implement the spatial snapper or alter API response shapes beyond required graph metadata.

## Files

- Refactor `packages/Kamz8/laravel-brouter/src/Services/WaterwayNormalizer.php`.
- Refactor `packages/Kamz8/laravel-brouter/src/Services/WaterwayGraphBuilder.php`.
- Create repository/persistence services for `waterway_nodes`, `waterway_edges`, and `graphs`.
- Add tests for shared nodes, duplicate ways, relations, branching, disconnected components, and full segment geometry.

## Required Behavior

- Preserve OSM IDs and full source tags.
- Store full edge LineString geometry and measured length.
- Preserve source direction metadata; do not silently claim one-way data is bidirectional.
- Record connected-component identifiers.
- Associate imported edges with a graph/import version.
- Store reservoir association when an edge is inside a mapped water body, but allow routing only on the line edge itself.

## Performance

Build adjacency once per published graph. Do not use `asort()` as a priority queue operation. Keep the persisted database representation authoritative and make any in-memory graph cache versioned.
