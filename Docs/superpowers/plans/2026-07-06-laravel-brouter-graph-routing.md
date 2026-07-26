# Laravel BRouter Graph Routing Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build graph-based river routing in `packages/Kamz8/laravel-brouter` and expose it to the Kayak Map dashboard map editor.

**Architecture:** `laravel-brouter` owns OSM fetching, Redis-backed cache, graph construction, snapping, and Dijkstra routing. Kayak Map consumes the package through a dashboard endpoint and the editor previews generated routes before applying them.

**Tech Stack:** Laravel 11, PHP 8.3, Testbench/PHPUnit for package tests, Pest for app tests, Vue 3/Vuex for dashboard editor, `clue/graph:^0.9`, `graphp/algorithms:^0.8`, Laravel Cache with Redis store.

## Global Constraints

- Keep the routing implementation inside `packages/Kamz8/laravel-brouter` as a reusable package.
- Route by `river_name` first; no automatic multi-river discovery in v1.
- Treat graph edges as undirected in v1.
- Cache normalized OSM/graph arrays and route results, not raw GraphP objects.
- Dashboard endpoint must not persist generated route data automatically.
- Frontend must preview generated routes before overwriting `trackCoordinates`.
- Run `vendor/bin/pint --dirty` before final completion.

---

## File Structure

- Modify `packages/Kamz8/laravel-brouter/composer.json`: add graph dependencies and package scripts.
- Modify `packages/Kamz8/laravel-brouter/src/BRouterServiceProvider.php`: bind provider, cache, graph, and router services correctly.
- Modify `packages/Kamz8/laravel-brouter/config/brouter.php`: add cache TTL, bbox, route, and Overpass options.
- Create `packages/Kamz8/laravel-brouter/src/DTO/*.php`: route request, snap result, graph node, graph edge.
- Modify `packages/Kamz8/laravel-brouter/src/Models/RouteResult.php`: typed result model with array/GeoJSON output.
- Create `packages/Kamz8/laravel-brouter/src/Exceptions/*.php`: routing exceptions.
- Create `packages/Kamz8/laravel-brouter/src/Services/RouteCache.php`: Laravel cache wrapper.
- Modify `packages/Kamz8/laravel-brouter/src/Services/OverpassDataProvider.php`: fetch waterways by name and bbox.
- Create `packages/Kamz8/laravel-brouter/src/Services/WaterwayNormalizer.php`: Overpass JSON to normalized graph arrays.
- Create `packages/Kamz8/laravel-brouter/src/Services/WaterwayGraphBuilder.php`: normalized graph arrays to GraphP graph.
- Create `packages/Kamz8/laravel-brouter/src/Services/EdgeSnapper.php`: point-to-segment snapping.
- Create `packages/Kamz8/laravel-brouter/src/Services/GraphRouter.php`: Dijkstra routing with virtual vertices.
- Create `packages/Kamz8/laravel-brouter/src/Services/PathGeometryBuilder.php`: Graph path to `[lng, lat]` route geometry.
- Modify `packages/Kamz8/laravel-brouter/src/Services/RoutingEngine.php`: orchestrate full routing flow.
- Modify `packages/Kamz8/laravel-brouter/src/Http/Controllers/RouteController.php`: POST route endpoint.
- Modify `packages/Kamz8/laravel-brouter/routes/api.php`: expose POST route.
- Create/modify package tests under `packages/Kamz8/laravel-brouter/tests`.
- Create app request/controller/service integration files for `POST /api/dashboard/trails/{trail}/river-route`.
- Modify dashboard trail editor Vuex store and map components for route preview.

## Task 1: Package Foundation and Contracts

**Files:**
- Modify: `packages/Kamz8/laravel-brouter/composer.json`
- Modify: `packages/Kamz8/laravel-brouter/config/brouter.php`
- Modify: `packages/Kamz8/laravel-brouter/src/BRouterServiceProvider.php`
- Modify: `packages/Kamz8/laravel-brouter/src/Contracts/RouterInterface.php`
- Create: `packages/Kamz8/laravel-brouter/src/DTO/RouteRequestData.php`
- Create: `packages/Kamz8/laravel-brouter/src/DTO/SnapResultData.php`
- Create: `packages/Kamz8/laravel-brouter/src/DTO/GraphNodeData.php`
- Create: `packages/Kamz8/laravel-brouter/src/DTO/GraphEdgeData.php`
- Modify: `packages/Kamz8/laravel-brouter/src/Models/RouteResult.php`
- Create: `packages/Kamz8/laravel-brouter/src/Exceptions/NoWaterwayFoundException.php`
- Create: `packages/Kamz8/laravel-brouter/src/Exceptions/SnapDistanceExceededException.php`
- Create: `packages/Kamz8/laravel-brouter/src/Exceptions/DisconnectedWaterwayException.php`
- Create: `packages/Kamz8/laravel-brouter/src/Exceptions/OverpassException.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/BRouterServiceProviderTest.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/Models/RouteResultTest.php`

**Interfaces:**
- Produces: `RouteRequestData::fromArray(array $data): self`, `RouteResult::toArray(): array`, `RouterInterface::findRoute(RouteRequestData $request): RouteResult`.

- [ ] **Step 1: Add failing package foundation tests**

Update `BRouterServiceProviderTest.php` to assert bindings for `RouterInterface` and add/update `RouteResultTest.php` to assert typed array output with `path`, `start_snap`, `end_snap`, `distance_m`, `warnings`, and `cache`.

- [ ] **Step 2: Run foundation tests and verify failure**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/BRouterServiceProviderTest.php packages/Kamz8/laravel-brouter/tests/Unit/Models/RouteResultTest.php`

Expected: FAIL because DTOs/result shape/bindings are incomplete.

- [ ] **Step 3: Implement DTOs, exceptions, config, and bindings**

Use constructor property promotion and explicit return types. Bind `DataProviderInterface` to `OverpassDataProvider` and `RouterInterface` to `RoutingEngine` through the container.

- [ ] **Step 4: Run foundation tests and verify pass**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/BRouterServiceProviderTest.php packages/Kamz8/laravel-brouter/tests/Unit/Models/RouteResultTest.php`

Expected: PASS.

## Task 2: Cache and Overpass Provider

**Files:**
- Create: `packages/Kamz8/laravel-brouter/src/Services/RouteCache.php`
- Modify: `packages/Kamz8/laravel-brouter/src/Contracts/DataProviderInterface.php`
- Modify: `packages/Kamz8/laravel-brouter/src/Services/OverpassDataProvider.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/Services/RouteCacheTest.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/Services/OverpassDataProviderTest.php`

**Interfaces:**
- Consumes: `RouteRequestData`.
- Produces: `RouteCache::rememberOsm(string $riverName, array $bbox, Closure $callback): array`, `DataProviderInterface::getWaterwayByName(string $name, array $bbox): array`.

- [ ] **Step 1: Write failing cache and provider tests**

Test key normalization, cache hit/miss, bbox hash stability, and Overpass query payload containing `waterway` and `name` filters.

- [ ] **Step 2: Run tests and verify failure**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/Services/RouteCacheTest.php packages/Kamz8/laravel-brouter/tests/Unit/Services/OverpassDataProviderTest.php`

Expected: FAIL because classes and methods do not exist or return old shapes.

- [ ] **Step 3: Implement cache and provider**

Use `Cache::store(config('brouter.cache.store'))`; use `Http::timeout(...)->retry(...)->get(...)`; throw `OverpassException` for failed responses.

- [ ] **Step 4: Run tests and verify pass**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/Services/RouteCacheTest.php packages/Kamz8/laravel-brouter/tests/Unit/Services/OverpassDataProviderTest.php`

Expected: PASS.

## Task 3: Waterway Normalization and Graph Building

**Files:**
- Create: `packages/Kamz8/laravel-brouter/src/Services/WaterwayNormalizer.php`
- Create: `packages/Kamz8/laravel-brouter/src/Services/WaterwayGraphBuilder.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/Services/WaterwayNormalizerTest.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/Services/WaterwayGraphBuilderTest.php`

**Interfaces:**
- Consumes: Overpass JSON array.
- Produces: `WaterwayNormalizer::normalize(array $overpassData, string $riverName): array`, `WaterwayGraphBuilder::build(array $normalized): array` returning `['graph' => Graph, 'vertices' => array, 'edges' => array]`.

- [ ] **Step 1: Write failing normalization and graph tests**

Use a small fixture with three OSM nodes and two connected way segments. Assert node ids, edge ids, distances, and graph vertex/edge counts.

- [ ] **Step 2: Run tests and verify failure**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/Services/WaterwayNormalizerTest.php packages/Kamz8/laravel-brouter/tests/Unit/Services/WaterwayGraphBuilderTest.php`

Expected: FAIL because services do not exist.

- [ ] **Step 3: Implement normalizer and graph builder**

Create undirected edges between consecutive OSM geometry points. Store edge attributes needed by snapping and path geometry.

- [ ] **Step 4: Run tests and verify pass**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/Services/WaterwayNormalizerTest.php packages/Kamz8/laravel-brouter/tests/Unit/Services/WaterwayGraphBuilderTest.php`

Expected: PASS.

## Task 4: Snapping and Graph Routing

**Files:**
- Create: `packages/Kamz8/laravel-brouter/src/Services/EdgeSnapper.php`
- Create: `packages/Kamz8/laravel-brouter/src/Services/GraphRouter.php`
- Create: `packages/Kamz8/laravel-brouter/src/Services/PathGeometryBuilder.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/Services/EdgeSnapperTest.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/Services/GraphRouterTest.php`

**Interfaces:**
- Consumes: graph payload from `WaterwayGraphBuilder::build()`.
- Produces: `EdgeSnapper::snap(array $point, array $edges, float $maxDistanceMeters): SnapResultData`, `GraphRouter::route(array $graphPayload, SnapResultData $start, SnapResultData $end): array`.

- [ ] **Step 1: Write failing snap and route tests**

Test projection to segment midpoint, tolerance exception, connected route, reversed route, and disconnected graph exception.

- [ ] **Step 2: Run tests and verify failure**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/Services/EdgeSnapperTest.php packages/Kamz8/laravel-brouter/tests/Unit/Services/GraphRouterTest.php`

Expected: FAIL because routing services do not exist.

- [ ] **Step 3: Implement snapper, router, and geometry builder**

Use Haversine distance for weights. For v1, route between nearest edge endpoints plus snapped projections if virtual-edge splitting is too risky; preserve snapped start and end coordinates in the returned geometry.

- [ ] **Step 4: Run tests and verify pass**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/Services/EdgeSnapperTest.php packages/Kamz8/laravel-brouter/tests/Unit/Services/GraphRouterTest.php`

Expected: PASS.

## Task 5: Routing Engine and Package HTTP Endpoint

**Files:**
- Modify: `packages/Kamz8/laravel-brouter/src/Services/RoutingEngine.php`
- Modify: `packages/Kamz8/laravel-brouter/src/Http/Controllers/RouteController.php`
- Modify: `packages/Kamz8/laravel-brouter/routes/api.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Unit/Services/RoutingEngineTest.php`
- Test: `packages/Kamz8/laravel-brouter/tests/Feature/RouteControllerTest.php`

**Interfaces:**
- Consumes: all services from Tasks 2-4.
- Produces: `RoutingEngine::findRoute(RouteRequestData $request): RouteResult`, `POST /api/brouter/route`.

- [ ] **Step 1: Write failing routing engine and endpoint tests**

Mock `DataProviderInterface` with a small Overpass response and assert route result shape. Assert endpoint validation errors for missing `river_name`, invalid lat/lng, and successful JSON response.

- [ ] **Step 2: Run tests and verify failure**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/Services/RoutingEngineTest.php packages/Kamz8/laravel-brouter/tests/Feature/RouteControllerTest.php`

Expected: FAIL because engine and endpoint still use placeholder logic.

- [ ] **Step 3: Implement orchestration and controller**

Compute bbox from start/end plus `brouter.routing.bbox_buffer_km`, call cache/provider/normalizer/builder/snapper/router, and map exceptions to JSON errors.

- [ ] **Step 4: Run tests and verify pass**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests/Unit/Services/RoutingEngineTest.php packages/Kamz8/laravel-brouter/tests/Feature/RouteControllerTest.php`

Expected: PASS.

## Task 6: Kayak Map Dashboard Backend Integration

**Files:**
- Create: `app/Http/Requests/Dashboard/Trail/GenerateRiverRouteRequest.php`
- Create: `app/Http/Controllers/Api/V1/Dashboard/TrailRiverRouteController.php`
- Modify: `routes/dashboard.php`
- Test: `tests/Feature/Dashboard/TrailRiverRouteTest.php`

**Interfaces:**
- Consumes: `BRouter::findRoute(RouteRequestData $request): RouteResult`.
- Produces: `POST /api/dashboard/trails/{trail}/river-route`.

- [ ] **Step 1: Write failing feature tests**

Test that the endpoint uses `trail->river_name`, accepts start/end override, returns route data, and does not persist `RiverTrack` or mutate `trails` coordinates.

- [ ] **Step 2: Run tests and verify failure**

Run: `php artisan test --compact tests/Feature/Dashboard/TrailRiverRouteTest.php`

Expected: FAIL because endpoint does not exist.

- [ ] **Step 3: Implement request, controller, and route**

Use Form Request validation. Use route model binding for `Trail`. Return package `RouteResult::toArray()` as JSON.

- [ ] **Step 4: Run tests and verify pass**

Run: `php artisan test --compact tests/Feature/Dashboard/TrailRiverRouteTest.php`

Expected: PASS.

## Task 7: Dashboard Frontend Preview Integration

**Files:**
- Modify: `resources/js/dashboard/modules/trails/editor/store/trailEditor.js`
- Modify: `resources/js/dashboard/modules/trails/editor/components/EditorToolbar.vue`
- Modify: `resources/js/dashboard/modules/trails/editor/components/MapCanvas.vue`
- Test: `tests/vitest/trailEditorRiverRoute.spec.js`

**Interfaces:**
- Consumes: `POST /dashboard/trails/{trail}/river-route` via dashboard axios client.
- Produces: Vuex actions `generateRiverRoute`, `applyRoutePreview`, `clearRoutePreview` and state `routePreviewCoordinates`.

- [ ] **Step 1: Write failing Vitest tests**

Test that `generateRiverRoute` stores preview coordinates and that `applyRoutePreview` copies preview into `trackCoordinates` while clearing preview.

- [ ] **Step 2: Run tests and verify failure**

Run: `npm run test -- tests/vitest/trailEditorRiverRoute.spec.js`

Expected: FAIL because actions/state do not exist.

- [ ] **Step 3: Implement Vuex state/actions and toolbar call**

Replace simulated progress in `applySnap()` with `generateRiverRoute`. Add apply/discard UI for preview. Keep existing save flow unchanged.

- [ ] **Step 4: Render preview layer on map**

Add a distinct dashed polyline for `routePreviewCoordinates`; do not replace the editable track until apply.

- [ ] **Step 5: Run frontend test and build check**

Run: `npm run test -- tests/vitest/trailEditorRiverRoute.spec.js`

Expected: PASS.

## Task 8: Final Verification

**Files:**
- All changed files.

**Interfaces:**
- Consumes: completed package, backend, and frontend work.
- Produces: verified implementation.

- [ ] **Step 1: Run package routing tests**

Run: `vendor/bin/phpunit packages/Kamz8/laravel-brouter/tests`

Expected: PASS.

- [ ] **Step 2: Run app feature test**

Run: `php artisan test --compact tests/Feature/Dashboard/TrailRiverRouteTest.php`

Expected: PASS.

- [ ] **Step 3: Run frontend route preview test**

Run: `npm run test -- tests/vitest/trailEditorRiverRoute.spec.js`

Expected: PASS.

- [ ] **Step 4: Format changed PHP files**

Run: `vendor/bin/pint --dirty`

Expected: PASS/fixes applied.

- [ ] **Step 5: Inspect git diff**

Run: `git status --short` and `git diff --stat`

Expected: only intended package, backend integration, frontend integration, tests, and spec/plan files changed.
