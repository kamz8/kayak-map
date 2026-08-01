# Agent 06: Waterway Features

## Scope

Persist OSM points and water-body metadata for current API availability and future routing profiles. Do not make obstacle penalties active unless the existing API explicitly requires it.

## Files

- Create package models/repositories/resources for `waterway_features` and `water_bodies`.
- Integrate feature persistence into the import service from Agent 02.
- Add tests for dams, weirs, locks, sluices, mills, rapids, waterfalls, and reservoir metadata.

## Required Fields

Store OSM ID, feature type, name, explicit SRID-4326 point, raw JSONB tags, import ID, nearest edge ID when known, `is_navigable`, `routing_penalty`, `portage_required`, and warning metadata. Preserve unknown tags for future behavior.

## Reservoir Rule

Water-body polygons are metadata only in this phase. They must not create arbitrary graph edges. A route can cross a reservoir only through a mapped waterway LineString imported from OSM.
