# Laravel BRouter Graph Routing Design

## Summary

Build `packages/Kamz8/laravel-brouter` as a first-class Laravel package for kayak route generation along rivers. The package will fetch waterway geometry from OpenStreetMap through Overpass, build a graph from the returned waterway network, snap imprecise start and end points to graph edges, and run shortest-path routing between the snapped points.

The Kayak Map dashboard will consume the package through a small admin endpoint used by the existing map editor. The editor will preview the generated route before replacing the current trail track.

## Goals

- Generate kayak route geometry from `river_name`, 
- Snap inaccurate start and end points to the nearest river segment within a configurable tolerance.
- Use a graph-based routing model instead of linear slicing of OSM ways.
- Cache Overpass responses and route results with Laravel cache, backed by Redis in this app.
- Keep `laravel-brouter` reusable as a separate package.
- Integrate with the existing dashboard map editor without automatically overwriting saved trail data.

## Non-Goals

- Multi-river automatic route discovery in the first version.
- Hydrological direction/current modeling in the first version.
- Public user-facing route planning UI in the first version.
- Persisting package-specific graph tables in the database in the first version.

## Package Dependencies

The package will use the GraphP ecosystem for graph structures and shortest-path algorithms.

Use the stable GraphP package pair:

```json
{
  "clue/graph": "^0.9",
  "graphp/algorithms": "^0.8 || ^0.9@dev"
}
```

The reason for using `clue/graph` is compatibility: the current stable `graphp/algorithms` package depends on `clue/graph ~0.9`. The package can move to `graphp/graph` when the stable algorithms package supports it.

Install command for the implementation step:

```bash
composer require clue/graph:^0.9 graphp/algorithms:^0.8
```

## Package Architecture

### Public API

The package exposes a facade and a contract.

```php
BRouter::findRoute([
    'river_name' => 'Wda',
    'start' => ['lat' => 53.9, 'lng' => 18.2],
    'end' => ['lat' => 53.8, 'lng' => 18.1],
    'snap_tolerance_m' => 500,
    'simplify' => true,
]);
```

Main contract:

```php
interface RouterInterface
{
    public function findRoute(RouteRequestData $request): RouteResult;
}
```

### Core Components

- `Contracts\RouterInterface`: public package routing API.
- `Contracts\DataProviderInterface`: source of waterway geometry.
- `Services\RoutingEngine`: orchestrates fetch, cache, graph build, snap, routing, and result formatting.
- `Services\OverpassDataProvider`: fetches OSM waterways by `river_name` and bounding box.
- `Services\WaterwayNormalizer`: converts Overpass ways and relations into normalized node and edge arrays.
- `Services\WaterwayGraphBuilder`: builds the graph from normalized waterway data.
- `Services\EdgeSnapper`: snaps coordinates to the closest graph edge.
- `Services\GraphRouter`: inserts snapped virtual vertices and runs shortest-path routing.
- `Services\PathGeometryBuilder`: converts graph edges back into route geometry.
- `Services\RouteCache`: wraps Laravel cache for OSM data, normalized graphs, and route results.
- `DTO\RouteRequestData`: validated route request value object.
- `DTO\SnapResultData`: snapped point, edge reference, projected coordinate, and distance.
- `DTO\GraphNodeData`: OSM or virtual graph vertex data.
- `DTO\GraphEdgeData`: graph edge data with geometry and length.
- `Models\RouteResult`: path, snap metadata, distance, warnings, and cache metadata.

### Exceptions

- `NoWaterwayFoundException`: no matching OSM waterway for `river_name` in bbox.
- `SnapDistanceExceededException`: start or end cannot be snapped within tolerance.
- `DisconnectedWaterwayException`: snapped points are on disconnected graph components.
- `OverpassException`: Overpass failed and no usable cached data exists.
- `InvalidRouteRequestException`: invalid request data reaches package internals.

## Graph Model

The graph represents the waterway network returned by Overpass.

- Vertex: OSM node, keyed by OSM node id when available.
- Virtual vertex: snapped start/end point inserted into an existing edge.
- Edge: waterway segment between two vertices.
- Weight: segment length in meters, calculated with Haversine distance.
- Edge attributes: `way_id`, `river_name`, `waterway`, `from_node`, `to_node`, `geometry`, `distance_m`.

The graph should be treated as undirected for the first version. Kayak routing direction and current direction can be added later as edge costs or directed edges.

## Routing Flow

1. Accept `river_name`, start point, end point, snap tolerance, and simplify flag.
2. Build a bounding box around start/end with configurable buffer.
3. Normalize `river_name` for cache keys and Overpass filtering.
4. Load normalized OSM waterway data from cache or fetch it from Overpass.
5. Build the graph from normalized waterway nodes and edges.
6. Snap the start point to the nearest edge by projecting onto each candidate segment.
7. Snap the end point in the same way.
8. Reject the route if either snap distance exceeds tolerance.
9. Insert virtual start/end vertices by splitting the snapped edges.
10. Run Dijkstra shortest path between the virtual start and end vertices.
11. Convert the path edges into `[lng, lat]` route geometry.
12. Simplify the geometry if requested.
13. Return route geometry, snap metadata, distance, warnings, and cache metadata.

## Cache Design

The package uses Laravel cache so the host application can choose Redis, file, array, or another store. In Kayak Map the store will be Redis.

Configuration:

```php
'cache' => [
    'enabled' => true,
    'store' => env('BROUTER_CACHE_STORE', 'redis'),
    'osm_ttl' => 60 * 60 * 24 * 30,
    'graph_ttl' => 60 * 60 * 24 * 30,
    'route_ttl' => 60 * 60 * 24 * 7,
],

'routing' => [
    'max_snap_distance' => 500,
    'bbox_buffer_km' => 15,
    'simplify_tolerance_m' => 10,
],
```

Cache keys:

- `brouter:osm:{river_slug}:{bbox_hash}`
- `brouter:graph:{river_slug}:{bbox_hash}`
- `brouter:route:{river_slug}:{start_hash}:{end_hash}:{tolerance}:{simplify}`

The bounding box and coordinates should be rounded before hashing to avoid cache misses for nearly identical requests.

## HTTP API

The package may expose a route for direct testing and reuse:

`POST /api/brouter/route`

Payload:

```json
{
  "river_name": "Wda",
  "start": { "lat": 53.9, "lng": 18.2 },
  "end": { "lat": 53.8, "lng": 18.1 },
  "snap_tolerance_m": 500,
  "simplify": true
}
```

Response:

```json
{
  "data": {
    "path": [[18.2, 53.9], [18.19, 53.89]],
    "start_snap": {
      "input": [18.2, 53.9],
      "snapped": [18.201, 53.901],
      "distance_m": 42
    },
    "end_snap": {
      "input": [18.1, 53.8],
      "snapped": [18.102, 53.802],
      "distance_m": 55
    },
    "distance_m": 12400,
    "warnings": [],
    "cache": {
      "osm": "hit",
      "graph": "hit",
      "route": "miss"
    }
  }
}
```

## Kayak Map Integration

The application adds a dashboard-only endpoint:

`POST /api/dashboard/trails/{trail}/river-route`

Responsibilities:

- Load the selected `Trail`.
- Use `$trail->river_name` by default.
- Accept start/end from the editor or fall back to trail start/end fields.
- Call `BRouter::findRoute()`.
- Return the package result to the frontend.
- Do not save the generated route automatically.

The existing trail update endpoint remains responsible for saving `track_points`, `start_lat`, `start_lng`, `end_lat`, `end_lng`, and `trail_length`.

## Dashboard Map Editor UX

The existing `EditorToolbar.vue` already has a snap/routing button, but currently simulates progress. Replace that placeholder behavior with a Vuex action.

Frontend changes:

- Add a `GENERATE_RIVER_ROUTE` action to the trail editor store.
- Send `startPoint`, `endPoint`, tolerance, simplify flag, and trail id to the dashboard endpoint.
- Remove the hardcoded river dropdown for the first version and use the trail `river_name`.
- Show the generated route as a preview layer in a distinct color.
- Let the admin apply or discard the preview.
- Only on apply, update `trackCoordinates`, `startPoint`, and `endPoint`.
- Save through the existing `SAVE_TRACK` flow.

## Error Handling

- Missing `river_name`: return validation error and keep current editor state unchanged.
- No OSM data: return `NoWaterwayFoundException` as a user-readable error.
- Overpass timeout: use stale cache if available; otherwise return a retryable error.
- Snap too far: return both snap distances and ask the user to move start/end closer.
- Disconnected graph: return a warning/error and keep manual drawing available.
- Multiple nearby waterway branches: Dijkstra chooses the shortest path in the named graph.

## Testing Strategy

Package tests:

- Service provider binds `DataProviderInterface` and `RouterInterface`.
- Config publishes and merges correctly.
- Route request validation accepts valid payloads and rejects invalid coordinates.
- `RouteCache` reports hit/miss correctly.
- `EdgeSnapper` projects to the nearest segment and calculates distance.
- Snap beyond tolerance throws `SnapDistanceExceededException`.
- `WaterwayGraphBuilder` builds vertices and weighted edges from normalized ways.
- `GraphRouter` finds shortest path through connected ways.
- Reversed start/end returns a valid reversed route.
- Disconnected graph throws `DisconnectedWaterwayException`.

Application tests:

- Dashboard route uses `trail->river_name` by default.
- Dashboard route does not persist generated track data.
- Dashboard route returns package errors as proper JSON responses.

Frontend tests:

- Snap button calls the new action instead of simulated progress.
- Successful route response creates a preview, not an immediate overwrite.
- Applying preview updates track coordinates.
- Failed route response keeps current track unchanged.

## Implementation Order

1. Fix `laravel-brouter` package integration: namespace, Composer autoload, provider registration, facade binding.
2. Add `clue/graph:^0.9` and `graphp/algorithms:^0.8` dependencies to the package.
3. Define package config and publish tags.
4. Define DTOs, result model, contracts, and exceptions.
5. Implement `RouteCache`.
6. Implement `OverpassDataProvider` for `river_name` and bbox.
7. Implement `WaterwayNormalizer`.
8. Implement `WaterwayGraphBuilder`.
9. Implement `EdgeSnapper`.
10. Implement `GraphRouter` using Dijkstra.
11. Implement `PathGeometryBuilder` and distance calculation.
12. Implement `RoutingEngine` orchestration.
13. Implement package HTTP endpoint.
14. Add package tests.
15. Add Kayak Map dashboard endpoint as package consumer.
16. Replace frontend snap placeholder with real API call and preview layer.
17. Add application and frontend tests.

## Fixed Decisions

- Direction/current routing is deferred and graph edges are undirected in v1.
- The package route can remain enabled for development and protected/disabled by host config in production if needed.
- Graph serialization should initially cache normalized edge/node arrays, not raw GraphP objects, to avoid brittle serialization.
