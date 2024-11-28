<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public health and Air environment Monitoring system</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .info-box {
            @apply bg-white p-4 rounded-lg shadow-lg max-w-sm;
        }
        .factory-item:hover {
            @apply bg-gray-100;
        }
    </style>
</head>
<body class="h-full flex flex-col">
    <header class="bg-gradient-to-r from-blue-600 via-teal-500 to-green-500 text-white p-4">
        <h1 class="text-xl font-bold">Public health and Air environment Monitoring system</h1>
    </header>

    <main class="flex flex-1 min-h-0">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg p-4">
            <!-- Base Map Selection -->
            <select class="mb-4 w-full p-2 border rounded border-gray-300" id="baseMapSelect">
                <option>Road Map</option>
                <option>Satellite</option>
                <option selected>Terrain</option>
            </select>

            <!-- Environment Layers -->
            <div class="space-y-2 mb-6">
                <h3 class="font-medium">Environment layer</h3>
                <div class="space-y-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="airMonitoringLayer">
                        <span>Air monitoring points</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="factoryLayer">
                        <span>Factory points</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="aqiLayer">
                        <span>Air quality index</span>
                    </label>
                </div>
            </div>

            <!-- Factories List -->
            <div class="border-t pt-4">
                <h3 class="font-medium mb-2">Factories</h3>
                <div class="space-y-2 max-h-48 overflow-y-auto text-sm">
                    @foreach($factories as $factory)
                    <div class="factory-item p-2 rounded cursor-pointer" data-id="{{ $factory['id'] }}">
                        <div class="font-medium">{{ $factory['name'] }}</div>
                        <div class="text-gray-600">{{ $factory['code'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Map Container -->
        <div class="flex-1 relative">
            <div id="map" class="w-full h-full"></div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white p-4 text-sm">
        <p>Copyright: Geoinformatics Research Center - TUAF, VietNam</p>
    </footer>

    <script>
    let map;
    let factoryMarkers = [];
    let currentInfoWindow = null;

    function initMap() {
        // Initialize map
        const mapCenter = { 
            lat: {{ $mapData['lat'] }}, 
            lng: {{ $mapData['lng'] }}
        };

        map = new google.maps.Map(document.getElementById('map'), {
            center: mapCenter,
            zoom: {{ $mapData['zoom'] }},
            mapTypeId: 'terrain',
            mapTypeControl: false,
            styles: [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ]
        });

        // Add factories to map
        const factories = @json($factories);
        console.log('Factories data:', factories); // Debug log

        factories.forEach(factory => {
            // Create marker
            const marker = new google.maps.Marker({
                position: { 
                    lat: parseFloat(factory.lat), 
                    lng: parseFloat(factory.lng)
                },
                map: map,
                title: factory.name,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#DC2626',
                    fillOpacity: 0.7,
                    strokeColor: '#991B1B',
                    strokeWeight: 2,
                    scale: 8
                }
            });

            // Create info window
            const infoContent = `
                <div class="info-box">
                    <h3 class="font-bold">${factory.name}</h3>
                    <p class="text-sm text-gray-600">${factory.code}</p>
                    <p class="text-sm">${factory.address}</p>
                    <p class="text-sm mt-2">Capacity: ${factory.capacity}</p>
                </div>
            `;

            const infoWindow = new google.maps.InfoWindow({
                content: infoContent
            });

            // Add click listener
            marker.addListener('click', () => {
                if (currentInfoWindow) {
                    currentInfoWindow.close();
                }
                infoWindow.open(map, marker);
                currentInfoWindow = infoWindow;
            });

            // Store marker reference
            factoryMarkers.push({
                marker: marker,
                infoWindow: infoWindow,
                id: factory.id
            });
        });

        // Add factory list click handlers
        document.querySelectorAll('.factory-item').forEach(item => {
            item.addEventListener('click', () => {
                const factoryId = parseInt(item.dataset.id);
                const markerData = factoryMarkers.find(m => m.id === factoryId);
                if (markerData) {
                    map.panTo(markerData.marker.getPosition());
                    map.setZoom(15);
                    if (currentInfoWindow) {
                        currentInfoWindow.close();
                    }
                    markerData.infoWindow.open(map, markerData.marker);
                    currentInfoWindow = markerData.infoWindow;
                }
            });
        });

        // Handle layer visibility
        document.getElementById('factoryLayer').addEventListener('change', function() {
            factoryMarkers.forEach(item => {
                item.marker.setVisible(this.checked);
            });
        });

        // Handle base map changes
        document.getElementById('baseMapSelect').addEventListener('change', function() {
            map.setMapTypeId(this.value.toLowerCase().replace(' ', '_'));
        });
    }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=visualization&callback=initMap" defer></script>
</body>
</html>