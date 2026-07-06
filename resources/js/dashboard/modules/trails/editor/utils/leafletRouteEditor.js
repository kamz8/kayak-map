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
