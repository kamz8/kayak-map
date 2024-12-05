<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Static Trail Map</title>
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: white;
        }
        #app {
            width: 800px;
            height: 600px;
            background: white;
        }
        .map-container {
            width: 100%;
            height: 100%;
            background: white;
        }
        .marker-icon {
            background-color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 96px;
            height: 96px;
            font-size: 64px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        .leaflet-control-attribution {
            display: none !important;
        }
    </style>
</head>
<body>
<div id="app">
    <static-trail-map :trail='@json($trail)'></static-trail-map>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    console.log('Script started');
    const { createApp, ref, computed, onMounted } = Vue;

    const StaticTrailMap = {
        template: `
            <div class="map-container" ref="mapContainer">
                <div ref="leafletMap" style="width: 100%; height: 100%;"></div>
            </div>
        `,
        props: {
            trail: {
                type: Object,
                required: true
            }
        },
        setup(props) {
            console.log('Setup started', props);

            const mapContainer = ref(null);
            const leafletMap = ref(null);
            const map = ref(null);

            const getInitialBounds = () => {
                console.log('Calculating bounds');

                const points = [];

                // Start point
                if (props.trail.start_lat && props.trail.start_lng) {
                    points.push([props.trail.start_lat, props.trail.start_lng]);
                }

                // End point
                if (props.trail.end_lat && props.trail.end_lng) {
                    points.push([props.trail.end_lat, props.trail.end_lng]);
                }

                // Points of interest
                if (props.trail.points) {
                    props.trail.points.forEach(point => {
                        if (point.lat && point.lng) {
                            points.push([point.lat, point.lng]);
                        }
                    });
                }

                console.log('Points found:', points);
                return points;
            };

            const getPointIcon = (type) => {
                const icons = {
                    'Pole namiotowe': 'mdi-tent',
                    'Miejsce biwakowania': 'mdi-tent',
                    'Przeszkoda': 'mdi-alert',
                    'Niebezpieczeństwo': 'mdi-alert',
                    'uwaga': 'mdi-alert',
                    'Jaz': 'mdi-water',
                    'most': 'mdi-bridge',
                    'przenoska': 'mdi-arrow-up-down',
                    'ujście': 'mdi-call-split',
                    'sklep': 'mdi-store'
                };
                return icons[type] || 'mdi-map-marker';
            };

            const getPointColor = (type) => {
                const colors = {
                    'Pole namiotowe': 'green',
                    'Miejsce biwakowania': 'green',
                    'Przeszkoda': 'red',
                    'Niebezpieczeństwo': 'red',
                    'uwaga': 'red',
                    'Jaz': 'blue',
                    'most': 'brown',
                    'przenoska': 'orange',
                    'ujście': 'purple',
                    'sklep': '#FFC107'
                };
                return colors[type] || '#009688';
            };

            onMounted(() => {
                console.log('Component mounted');

                // Inicjalizacja mapy
                map.value = L.map(leafletMap.value, {
                    zoomControl: false,
                    dragging: false,
                    touchZoom: false,
                    doubleClickZoom: false,
                    scrollWheelZoom: false,
                    boxZoom: false,
                    keyboard: false,
                    attributionControl: false
                });

                // Dodaj warstwę mapy
                const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: ''
                }).addTo(map.value);

                // 3. Rysowanie linii trasy
                if (props.trail.river_track?.track_points) {
                    console.log('Adding river track', props.trail.river_track.track_points.coordinates);
                    const coordinates = props.trail.river_track.track_points.coordinates;
                    const polyline = L.polyline(coordinates, {
                        color: '#4682B4',
                        weight: 8,
                        opacity: 0.8
                    }).addTo(map.value);
                }


                    // Jeśli mamy współrzędne startu i końca, utwórz z nich bounds
                if (props.trail.start_lat && props.trail.start_lng && props.trail.end_lat && props.trail.end_lng) {
                    const bounds = L.latLngBounds(
                        [props.trail.start_lat, props.trail.start_lng],
                        [props.trail.end_lat, props.trail.end_lng]
                    );
                    map.value.fitBounds(bounds, {
                        padding: [10,10],
                        maxZoom: 16
                    });
                }

                // Dodaj markery
                if (props.trail.start_lat && props.trail.start_lng) {
                    const startIcon = L.divIcon({
                        className: 'marker-icon',
                        html: '<i class="mdi mdi-map-marker-circle" style="color: #2E7D32; font-size: 32px;"></i>'
                    });
                    L.marker([props.trail.start_lat, props.trail.start_lng], { icon: startIcon }).addTo(map.value);
                }

                if (props.trail.end_lat && props.trail.end_lng) {
                    const endIcon = L.divIcon({
                        className: 'marker-icon',
                        html: '<i class="mdi mdi-flag-checkered" style="color: #FF0000; font-size: 32px;"></i>'
                    });
                    L.marker([props.trail.end_lat, props.trail.end_lng], { icon: endIcon }).addTo(map.value);
                }

                // Dodaj punkty jeśli istnieją
                if (props.trail.river_track?.track_points?.coordinates) {
                    const coordinates = props.trail.river_track.track_points.coordinates.map(([lng, lat]) => [lat, lng]);
                    const polyline = L.polyline(coordinates, {
                        color: '#4682B4',
                        weight: 6,
                        opacity: 0.8
                    }).addTo(map.value);
                    map.value.fitBounds(polyline.getBounds(), { padding: [20, 20] });
                }

                    // Sygnalizuj gotowość mapy
                tileLayer.on('load', () => {
                    console.log('All tiles loaded');
                    setTimeout(() => {
                        map.value.invalidateSize();
                        window.mapReady = true;
                    }, 2000);
                });
            });

            return {
                mapContainer,
                leafletMap
            };
        }
    };

    const app = createApp({
        components: {
            'static-trail-map': StaticTrailMap
        }
    });

    app.mount('#app');
    console.log('App mounted');
</script>
</body>
</html>
