# Plan/Edit Route Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the pencil tool in the dashboard trail map editor work as a single Plan/Edit Route mode for drawing and editing kayak route paths, moving start/end points, saving locally first, and then persisting to the backend.

**Architecture:** Use existing Vuex editor state as the source of truth and existing Leaflet + leaflet-draw dependencies for path drawing/editing. Refactor route editing behavior out of `MapCanvas.vue` into a focused helper so POI behavior remains isolated and the large map component does not keep growing. Backend persistence extends the existing dashboard trail update endpoint with optional `track_points` support and updates/creates the related `riverTrack` record.

**Tech Stack:** Vue 3 Options API, Vuex 4, Leaflet 1.9, leaflet-draw 1.0, Laravel 11, PHP 8.3, Pest 2, MySQL spatial data through `matanyadaev/laravel-eloquent-spatial`.

## Global Constraints

- Do not touch POI behavior except disabling route draw/edit handlers when `activeTool === 'poi'`.
- Do not add new frontend or backend dependencies.
- Dashboard UI should use existing UI Kit components where practical and should not introduce raw Vuetify replacements beyond existing local patterns.
- Keep route coordinates in frontend state as Leaflet `[lat, lng]` arrays.
- Persist API `track_points` as GeoJSON-style `[lng, lat]` coordinate arrays.
- Local save must happen before backend save, and the local snapshot must remain available if backend save fails.
- `trail_length` is stored in meters in the dashboard API payload, while `calculateTrackLength()` returns kilometers.
- Every code change must be verified by an affected automated test where practical, plus `npm run build` for frontend integration.
- Run `vendor/bin/pint --dirty` before final completion.

---

## File Structure

- Modify `resources/js/dashboard/modules/trails/editor/components/EditorToolbar.vue` to simplify the route toolbar to a single pencil Plan/Edit Route tool.
- Modify `resources/js/dashboard/modules/trails/editor/components/MapCanvas.vue` to integrate the route editor helper, sync active tools, rebuild layers, and keep POIs isolated.
- Modify `resources/js/dashboard/modules/trails/editor/store/trailEditor.js` to save a local snapshot first and then persist track data to the backend.
- Modify `resources/js/dashboard/modules/trails/editor/utils/coordinateUtils.js` only if shared coordinate normalization is needed.
- Create `resources/js/dashboard/modules/trails/editor/utils/leafletRouteEditor.js` as a small Leaflet route drawing/editing adapter.
- Modify `app/Http/Requests/Dashboard/Trail/UpdateTrailRequest.php` to accept optional route track payload fields without requiring full trail form fields when saving from the map editor.
- Modify `app/Http/Controllers/Api/V1/Dashboard/TrailController.php` to separate trail attributes from `track_points` and update/create the `riverTrack` relation.
- Modify `tests/Feature/Api/V1/Dashboard/TrailControllerTest.php` to cover route track persistence and validation.

---

## Task 1: Backend Track Persistence Test

**Files:**
- Modify: `tests/Feature/Api/V1/Dashboard/TrailControllerTest.php`

**Interfaces:**
- Consumes: existing authenticated dashboard trail API helpers.
- Produces: failing tests for `PUT /api/v1/dashboard/trails/{id}` with optional `track_points`.

- [ ] **Step 1: Add authenticated PUT helper**

Add this helper next to `authenticatedPost()` and `authenticatedPatch()`:

```php
private function authenticatedPut(string $uri, array $data = [])
{
    return $this->putJson($uri, $data, [
        'Authorization' => 'Bearer '.$this->token,
        'X-Client-Type' => 'web',
    ]);
}
```

- [ ] **Step 2: Add persistence test**

Add a test that sends only map editor fields and asserts trail coordinates and a `river_tracks` row are persisted:

```php
/** @test */
public function test_can_update_trail_track_from_map_editor()
{
    $trail = Trail::factory()->create([
        'start_lat' => 50.000000,
        'start_lng' => 19.000000,
        'end_lat' => 50.100000,
        'end_lng' => 19.100000,
        'trail_length' => 1000,
    ]);

    $payload = [
        'track_points' => [
            [19.200000, 50.200000],
            [19.250000, 50.250000],
            [19.300000, 50.300000],
        ],
        'start_lat' => 50.200000,
        'start_lng' => 19.200000,
        'end_lat' => 50.300000,
        'end_lng' => 19.300000,
        'trail_length' => 15000,
    ];

    $response = $this->authenticatedPut("/api/v1/dashboard/trails/{$trail->id}", $payload);

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Szlak został zaktualizowany',
            'data' => [
                'id' => $trail->id,
                'start_lat' => 50.2,
                'start_lng' => 19.2,
                'end_lat' => 50.3,
                'end_lng' => 19.3,
                'trail_length' => 15000,
            ],
        ]);

    $this->assertDatabaseHas('trails', [
        'id' => $trail->id,
        'start_lat' => 50.2,
        'start_lng' => 19.2,
        'end_lat' => 50.3,
        'end_lng' => 19.3,
        'trail_length' => 15000,
    ]);

    $this->assertDatabaseHas('river_tracks', [
        'trail_id' => $trail->id,
    ]);
}
```

- [ ] **Step 3: Add validation tests**

Add tests for a too-short track and invalid coordinate order/ranges:

```php
/** @test */
public function test_track_update_requires_at_least_two_track_points()
{
    $trail = Trail::factory()->create();

    $response = $this->authenticatedPut("/api/v1/dashboard/trails/{$trail->id}", [
        'track_points' => [[19.2, 50.2]],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['track_points']);
}

/** @test */
public function test_track_update_validates_geojson_coordinate_ranges()
{
    $trail = Trail::factory()->create();

    $response = $this->authenticatedPut("/api/v1/dashboard/trails/{$trail->id}", [
        'track_points' => [
            [200.0, 50.2],
            [19.3, 95.0],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['track_points.0.0', 'track_points.1.1']);
}
```

- [ ] **Step 4: Run the failing tests**

Run: `php artisan test --compact tests/Feature/Api/V1/Dashboard/TrailControllerTest.php --filter=track`

Expected before implementation: failures or validation errors showing `track_points` is not handled yet.

---

## Task 2: Backend Track Persistence Implementation

**Files:**
- Modify: `app/Http/Requests/Dashboard/Trail/UpdateTrailRequest.php`
- Modify: `app/Http/Controllers/Api/V1/Dashboard/TrailController.php`
- Modify if needed: `app/Models/RiverTrack.php`
- Test: `tests/Feature/Api/V1/Dashboard/TrailControllerTest.php`

**Interfaces:**
- Consumes: Task 1 tests.
- Produces: dashboard trail update endpoint accepting partial map editor payload and optional `track_points`.

- [ ] **Step 1: Make update rules partial-friendly**

Change required trail form fields in `UpdateTrailRequest` from `required` to `sometimes|required` so map editor saves can update only coordinates and track data:

```php
'trail_name' => ['sometimes', 'required', 'string', 'max:255'],
'river_name' => ['sometimes', 'required', 'string', 'max:255'],
'description' => ['sometimes', 'required', 'string'],
'trail_length' => ['sometimes', 'required', 'integer', 'min:1', 'max:99999999'],
'difficulty' => ['sometimes', 'required', 'string', Rule::in(['łatwy', 'umiarkowany', 'trudny'])],
```

- [ ] **Step 2: Add track validation rules**

Add to `UpdateTrailRequest::rules()`:

```php
'track_points' => ['nullable', 'array', 'min:2'],
'track_points.*' => ['required', 'array', 'size:2'],
'track_points.*.0' => ['required', 'numeric', 'min:-180', 'max:180'],
'track_points.*.1' => ['required', 'numeric', 'min:-90', 'max:90'],
```

- [ ] **Step 3: Persist track in controller**

In `TrailController::update()`, remove `track_points` from the trail attributes before `$trail->update()` and write it through the relation after the trail update.

Use existing spatial package conventions. If direct array assignment does not satisfy the `LineString` cast, construct a `LineString` from `MatanYadaev\EloquentSpatial\Objects\Point` values using lat/lng extracted from `[lng, lat]` pairs.

The controller must preserve this behavior:

```php
$trackPoints = $validated['track_points'] ?? null;
unset($validated['track_points']);

if (isset($validated['trail_name']) && $validated['trail_name'] !== $trail->trail_name) {
    $validated['slug'] = Str::slug($validated['trail_name']);
}

if ($validated !== []) {
    $trail->update($validated);
}

if ($trackPoints !== null) {
    // Convert and update/create riverTrack here.
}
```

- [ ] **Step 4: Run backend track tests**

Run: `php artisan test --compact tests/Feature/Api/V1/Dashboard/TrailControllerTest.php --filter=track`

Expected: PASS.

---

## Task 3: Frontend Save Local Snapshot Then Backend

**Files:**
- Modify: `resources/js/dashboard/modules/trails/editor/store/trailEditor.js`

**Interfaces:**
- Consumes: backend endpoint from Task 2.
- Produces: `SAVE_TRACK` action that writes local snapshot before backend save.

- [ ] **Step 1: Build payload once**

In `SAVE_TRACK`, replace the current simulated delay and local-only save with a payload that includes route fields:

```js
const payload = {
  track_points: state.trackCoordinates.map(([lat, lng]) => [lng, lat]),
  start_lat: state.startPoint[0],
  start_lng: state.startPoint[1],
  end_lat: state.endPoint[0],
  end_lng: state.endPoint[1],
  trail_length: Math.round(getters[GETTERS.TRACK_LENGTH] * 1000),
  poi_points: state.poiPoints.map(poi => ({
    id: poi.id,
    point_type_id: poi.point_type_id,
    name: poi.name,
    description: poi.description,
    lat: poi.lat,
    lng: poi.lng,
    icon: poi.icon,
    at_length: poi.at_length,
    order: poi.order
  })),
  saved_at: new Date().toISOString()
}
```

- [ ] **Step 2: Save local snapshot before API call**

Store this snapshot before calling the backend:

```js
localStorage.setItem(`trail_track_${state.trailId}`, JSON.stringify({
  trailId: state.trailId,
  ...payload
}))
```

- [ ] **Step 3: Persist backend after local snapshot**

Call:

```js
await apiClient.put(`/dashboard/trails/${state.trailId}`, {
  track_points: payload.track_points,
  start_lat: payload.start_lat,
  start_lng: payload.start_lng,
  end_lat: payload.end_lat,
  end_lng: payload.end_lng,
  trail_length: payload.trail_length
})
```

- [ ] **Step 4: Reset unsaved changes only after backend success**

Keep `commit(MUTATIONS.RESET_UNSAVED_CHANGES)` after the successful `await apiClient.put(...)` call.

- [ ] **Step 5: Verify frontend compile later in Task 8**

Do not run full frontend build yet unless this task is implemented outside the combined flow.

---

## Task 4: Toolbar Simplification

**Files:**
- Modify: `resources/js/dashboard/modules/trails/editor/components/EditorToolbar.vue`

**Interfaces:**
- Consumes: existing `activeTool` Vuex state.
- Produces: a single pencil route tool setting `activeTool` to `'draw'` for Plan/Edit Route.

- [ ] **Step 1: Update pencil tooltip**

Change tooltip text from `Rysuj trasę (P)` to:

```vue
<v-tooltip text="Planuj/edytuj trasę (P)" location="bottom">
```

- [ ] **Step 2: Remove edit button block**

Remove the entire `Edit Points` toolbar block that uses `mdi-vector-polyline-edit` and `handleEditTool`.

- [ ] **Step 3: Remove unused method**

Remove `handleEditTool()` from `methods`.

- [ ] **Step 4: Keep draw toggle semantics**

Keep `handleDrawTool()` as:

```js
handleDrawTool() {
  const newTool = this.activeTool === 'draw' ? null : 'draw'
  console.log('🖊️ Plan/Edit route tool', newTool ? 'activated' : 'deactivated')
  this.$store.commit(`trailEditor/${trailEditorMutations.SET_ACTIVE_TOOL}`, newTool)
}
```

---

## Task 5: Leaflet Route Editor Helper

**Files:**
- Create: `resources/js/dashboard/modules/trails/editor/utils/leafletRouteEditor.js`

**Interfaces:**
- Consumes: Leaflet `L`, map instance, feature group, current track layer callback, route callbacks.
- Produces: `createLeafletRouteEditor()` with `enablePlanningMode(hasTrack)`, `disable()`, and `destroy()`.

- [ ] **Step 1: Create helper skeleton**

Create the module:

```js
export function createLeafletRouteEditor({
  L,
  map,
  featureGroup,
  getTrackLayer,
  onRouteCreated,
  onRouteEdited,
  onDrawStateChanged,
}) {
  let drawHandler = null
  let editHandler = null

  const disable = () => {
    if (drawHandler) {
      drawHandler.disable()
      drawHandler = null
    }

    if (editHandler) {
      editHandler.disable()
      editHandler = null
    }

    onDrawStateChanged(false)
  }

  return {
    enablePlanningMode(hasTrack) {
      disable()

      if (hasTrack && getTrackLayer()) {
        editHandler = new L.EditToolbar.Edit(map, {
          featureGroup,
        })
        editHandler.enable()
        onDrawStateChanged(true)
        return
      }

      drawHandler = new L.Draw.Polyline(map, {
        shapeOptions: { color: '#1976D2', weight: 5, opacity: 0.8 },
        allowIntersection: false,
      })
      drawHandler.enable()
      onDrawStateChanged(true)
    },
    disable,
    destroy() {
      disable()
    },
  }
}
```

- [ ] **Step 2: Leave route event handling in MapCanvas**

Keep event listener methods in `MapCanvas.vue` for now so the helper remains small and easy to verify.

---

## Task 6: MapCanvas Plan/Edit Integration

**Files:**
- Modify: `resources/js/dashboard/modules/trails/editor/components/MapCanvas.vue`
- Uses: `resources/js/dashboard/modules/trails/editor/utils/leafletRouteEditor.js`

**Interfaces:**
- Consumes: helper from Task 5 and Vuex state.
- Produces: pencil-driven Plan/Edit Route behavior without touching POIs.

- [ ] **Step 1: Import helper**

Add:

```js
import { createLeafletRouteEditor } from '../utils/leafletRouteEditor'
```

- [ ] **Step 2: Replace route editor data fields**

In `data()`, add:

```js
routeEditor: null,
```

Keep `startMarkerLayer`, `endMarkerLayer`, and `trackLayer`. Remove `drawControl` later in cleanup if no longer used.

- [ ] **Step 3: Create route editor on map ready**

After `setupMapListeners()` in `onMapReady()`:

```js
this.routeEditor = createLeafletRouteEditor({
  L,
  map: this.map,
  featureGroup: this.editableLayers,
  getTrackLayer: () => this.trackLayer,
  onRouteCreated: this.syncRouteCoordinates,
  onRouteEdited: this.syncRouteCoordinates,
  onDrawStateChanged: (isDrawing) => {
    this.isDrawing = isDrawing
  },
})
```

- [ ] **Step 4: Add active tool watcher**

Add a watcher:

```js
activeTool() {
  this.syncActiveTool()
}
```

- [ ] **Step 5: Add syncActiveTool method**

Add:

```js
syncActiveTool() {
  if (!this.routeEditor) {
    return
  }

  if (this.activeTool === 'draw') {
    this.routeEditor.enablePlanningMode(this.trackCoordinates.length > 0)
    return
  }

  this.routeEditor.disable()
}
```

- [ ] **Step 6: Centralize coordinate sync**

Add:

```js
syncRouteCoordinates(coords) {
  if (!Array.isArray(coords) || coords.length < 2) {
    return
  }

  this.$store.commit(`trailEditor/${trailEditorMutations.UPDATE_TRACK_COORDINATES}`, coords)
  this.$store.commit(`trailEditor/${trailEditorMutations.SET_START_POINT}`, coords[0])
  this.$store.commit(`trailEditor/${trailEditorMutations.SET_END_POINT}`, coords[coords.length - 1])
}
```

- [ ] **Step 7: Update draw created and edited handlers**

In `handleDrawCreated(e)`, for `polyline`, call `this.syncRouteCoordinates(coords)`.

In `handleDrawEdited(e)`, for `L.Polyline`, call `this.syncRouteCoordinates(coords)`.

- [ ] **Step 8: Add marker dragend sync**

When creating start marker, add:

```js
.on('dragend', this.handleStartMarkerDragEnd)
```

When creating end marker, add:

```js
.on('dragend', this.handleEndMarkerDragEnd)
```

Add methods:

```js
handleStartMarkerDragEnd(event) {
  const latLng = event.target.getLatLng()
  const newCoords = [...this.trackCoordinates]

  if (newCoords.length === 0) {
    return
  }

  newCoords[0] = [latLng.lat, latLng.lng]
  this.syncRouteCoordinates(newCoords)
}

handleEndMarkerDragEnd(event) {
  const latLng = event.target.getLatLng()
  const newCoords = [...this.trackCoordinates]

  if (newCoords.length === 0) {
    return
  }

  newCoords[newCoords.length - 1] = [latLng.lat, latLng.lng]
  this.syncRouteCoordinates(newCoords)
}
```

- [ ] **Step 9: Disable route editor before rebuild loops when needed**

If edit handles remain stale after `rebuildEditableLayers()`, call `this.syncActiveTool()` at the end of rebuild on `$nextTick()` only when `activeTool === 'draw'`.

---

## Task 7: Cleanup Dead Editor Code

**Files:**
- Modify: `resources/js/dashboard/modules/trails/editor/components/TrailMapEditorComponent.vue`
- Modify: `resources/js/dashboard/modules/trails/editor/components/MapCanvas.vue`
- Modify: `resources/js/dashboard/modules/trails/editor/components/EditorToolbar.vue`

**Interfaces:**
- Consumes: working route editor flow.
- Produces: less dead code and fewer nonexistent method calls.

- [ ] **Step 1: Remove nonexistent map method calls**

In `TrailMapEditorComponent.vue`, remove or simplify `handleToolChanged()` because toolbar state is managed by Vuex and `mapInstance` is a Leaflet map, not a wrapper exposing `startDrawing()`, `toggleEdit()`, or `activatePoiMode()`.

- [ ] **Step 2: Remove unused emit wiring if safe**

If no component emits `tool-changed`, remove `@tool-changed="handleToolChanged"` from `TrailMapEditorComponent.vue`.

- [ ] **Step 3: Remove unused route draw control state**

In `MapCanvas.vue`, remove `drawControl` and `setupDrawControl()` if no longer used after helper integration.

- [ ] **Step 4: Remove unused POI temp field**

Remove `tempPoiMarker` from `MapCanvas.vue` if it is still unused.

- [ ] **Step 5: Remove stale CSS for hidden Leaflet.draw toolbar**

Remove styles targeting `.leaflet-draw-toolbar` if the control toolbar is not mounted anywhere.

---

## Task 8: Verification

**Files:**
- No code files unless verification reveals defects.

**Interfaces:**
- Consumes: Tasks 1-7.
- Produces: verified implementation.

- [ ] **Step 1: Run backend track tests**

Run: `php artisan test --compact tests/Feature/Api/V1/Dashboard/TrailControllerTest.php --filter=track`

Expected: PASS.

- [ ] **Step 2: Run dashboard trail controller tests if track tests pass**

Run: `php artisan test --compact tests/Feature/Api/V1/Dashboard/TrailControllerTest.php`

Expected: PASS or report pre-existing unrelated failures with exact test names.

- [ ] **Step 3: Run formatter**

Run: `vendor/bin/pint --dirty`

Expected: completed successfully.

- [ ] **Step 4: Run frontend build**

Run: `npm run build`

Expected: Vite build succeeds.

- [ ] **Step 5: Manual smoke checklist**

If a browser is available, verify:

- Pencil with no route starts drawing.
- Completed drawn line appears with start/end markers.
- Pencil with existing route enables vertex editing.
- Dragging start marker updates first track coordinate.
- Dragging end marker updates last track coordinate.
- POI mode still opens the POI editor on map click.
- Save writes local snapshot and backend route data.
- Reloading editor loads saved route from backend.

---

## Self-Review Notes

- Spec coverage: toolbar simplification, route draw/edit, start/end drag, local-first save, backend persistence, and refactor cleanup all have tasks.
- Placeholder scan: no task uses placeholder-only language; each implementation task names concrete files and expected code shape.
- Type consistency: frontend coordinates remain `[lat, lng]`; backend payload remains `[lng, lat]`; helper method names match MapCanvas integration tasks.
