# Agent 05: Router and Graph Cache

## Scope

Use the published persistent graph, implement versioned graph/route caching, and fix routing from snapped positions. Do not add free-form reservoir traversal.

## Files

- Refactor `packages/Kamz8/laravel-brouter/src/Services/RoutingEngine.php`.
- Refactor `packages/Kamz8/laravel-brouter/src/Services/GraphRouter.php`.
- Refactor `packages/Kamz8/laravel-brouter/src/Services/RouteCache.php`.
- Create `GraphCache` and/or a graph repository as needed.
- Add focused tests for cache hits, version invalidation, virtual nodes, partial edge distances, branches, and reservoir crossing rules.

## Required Behavior

- Never rebuild the graph on every request.
- Never query Overpass during routing.
- Include import/graph version, profile, start, end, and snap tolerance in cache keys.
- Use `SplPriorityQueue` or an equivalent priority queue.
- Insert virtual start/end nodes at exact snap locations and include partial edge costs.
- Permit reservoir traversal only when the route follows an imported mapped waterway line.
