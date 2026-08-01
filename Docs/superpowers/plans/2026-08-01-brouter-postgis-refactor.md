# BRouter PostGIS Refactor Implementation Plan

> **For agentic workers:** Implement this plan task-by-task. Read the assigned agent brief first. Do not modify unrelated frontend work.

**Goal:** Move BRouter's OSM cache and persistent waterway graph to a dedicated PostgreSQL/PostGIS database, persist water features, and improve routing performance while allowing routes across reservoirs only where OSM contains a mapped waterway line.

**Architecture:** MariaDB remains the application's primary database. BRouter uses a second Laravel connection named `brouter` backed by PostgreSQL/PostGIS. Overpass is contacted only by an explicit import command; imports are staged, normalized, validated, and published by version. Routing reads the published graph, uses spatial indexes for snapping, and keeps free-form reservoir traversal out of scope.

**Tech Stack:** Laravel 11, PHP 8.3, PostgreSQL with PostGIS, existing local `laravel-brouter` package, Pest/PHPUnit conventions already present in the package.

## Global Constraints

- Do not change the primary MariaDB schema unless required for a BRouter integration seam.
- Do not introduce a composer dependency without explicit approval; evaluate pgRouting but do not require it in this phase.
- Every production change requires focused tests.
- All spatial data uses explicit SRID 4326; preserve longitude/latitude ordering in WKT and GeoJSON.
- Reservoirs are traversable only through mapped OSM `waterway` lines; free-form water crossing is not part of this plan.
- Preserve raw OSM tags in PostgreSQL `jsonb` columns.
- Keep current unrelated worktree changes untouched.
- Run `vendor/bin/pint --dirty` and focused tests before completion.

## Execution Order

1. PostgreSQL connection and BRouter schema.
2. Overpass import and staging/publishing workflow.
3. Persistent graph normalization/building.
4. Spatial snapper and correct virtual snap nodes.
5. Router and graph cache integration.
6. Water feature persistence and future routing metadata.
7. Tests, benchmark command, and integration verification.

## Agent Briefs

- `docs/superpowers/plans/agents/01-postgis-database.md`
- `docs/superpowers/plans/agents/02-overpass-import.md`
- `docs/superpowers/plans/agents/03-persistent-graph.md`
- `docs/superpowers/plans/agents/04-spatial-snapper.md`
- `docs/superpowers/plans/agents/05-router-cache.md`
- `docs/superpowers/plans/agents/06-water-features.md`
- `docs/superpowers/plans/agents/07-tests-benchmark.md`

## Acceptance Criteria

- BRouter can connect to a separate PostgreSQL/PostGIS database without changing the primary MariaDB connection.
- A manual import stores rivers, OSM nodes, graph edges, reservoirs/lakes metadata, and waterway features such as dams, weirs, locks, sluices, mills, rapids, and waterfalls.
- A failed or incomplete import cannot become active.
- Routing does not call Overpass and does not rebuild the graph for every request.
- Spatial indexes are used for nearest-edge lookup and snap results include the exact projected point.
- A route can cross a reservoir only through an imported mapped waterway line.
- Start/end snapping uses virtual graph nodes and includes partial edge distances.
- Cache keys include the active graph/import version.
- Focused package tests pass and benchmark output records import, snap, graph, and route timings.
