<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quan trắc Chất lượng Không khí</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .info-box {
            @apply bg-white p-4 rounded-lg shadow-lg max-w-sm;
        }
        .factory-item:hover {
            @apply bg-gray-100;
        }
        .aqi-legend {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .aqi-color {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin-right: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="h-full flex flex-col">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 via-teal-500 to-green-500 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-xl font-bold">
                <i class="fas fa-chart-line mr-2"></i>
                Hệ thống Quan trắc Chất lượng Không khí
            </h1>
            <div class="text-sm mt-1">
                <i class="fas fa-clock mr-2"></i>
                Cập nhật lần cuối: <span id="lastUpdate"></span>
            </div>
        </div>
    </header>

    <main class="flex flex-1 min-h-0">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg p-4 overflow-y-auto">
            <!-- Base Map Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-map mr-2"></i>Bản đồ nền
                </label>
                <select class="w-full p-2 border rounded border-gray-300" id="baseMapSelect">
                    <option value="roadmap">Đường phố</option>
                    <option value="satellite">Vệ tinh</option>
                    <option value="terrain" selected>Địa hình</option>
                    <option value="hybrid">Tổng hợp</option>
                </select>
            </div>

            <!-- Layers Control -->
            <div class="space-y-2 mb-6">
                <h3 class="font-medium text-gray-700">
                    <i class="fas fa-layer-group mr-2"></i>Lớp hiển thị
                </h3>
                <div class="space-y-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="factoryLayer">
                        <span>Vị trí nhà máy</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="aqiLayer">
                        <span>Chỉ số AQI</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="thaiNguyenLayer">
                        <span>Ranh giới Thái Nguyên</span>
                    </label>
                </div>
            </div>

            <!-- Factories List -->
            <div class="border-t pt-4">
                <h3 class="font-medium text-gray-700 mb-2">
                    <i class="fas fa-industry mr-2"></i>Danh sách nhà máy
                </h3>
                <div class="space-y-2 max-h-96 overflow-y-auto text-sm">
                    @foreach($factories as $factory)
                    <div class="factory-item p-2 rounded cursor-pointer hover:bg-gray-100" 
                         data-id="{{ $factory['id'] }}">
                        <div class="font-medium flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" 
                                  style="background-color: {{ $factory['aqi_color'] }}">
                            </span>
                            {{ $factory['name'] }}
                        </div>
                        <div class="text-gray-600 text-xs mt-1">{{ $factory['code'] }}</div>
                        @if($factory['measurement_time'])
                        <div class="text-xs mt-1" style="color: {{ $factory['aqi_color'] }}">
                            AQI: {{ $factory['aqi'] }} - {{ $factory['aqi_status'] }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Thai Nguyen Areas -->
            <div class="border-t pt-4 mt-4">
                <h3 class="font-medium text-gray-700 mb-2">
                    <i class="fas fa-map-marked-alt mr-2"></i>Khu vực
                </h3>
                <div class="space-y-2 max-h-48 overflow-y-auto text-sm">
                    @foreach($thaiNguyenBoundaries as $area)
                    <div class="area-item p-2 rounded cursor-pointer hover:bg-gray-100" 
                         data-id="{{ $area['id'] }}">
                        {{ $area['name'] }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Map Container -->
        <div class="flex-1 relative">
            <div id="map" class="w-full h-full"></div>

            <!-- AQI Legend -->
            <div class="aqi-legend text-sm">
                <div class="font-medium mb-2">Chú giải AQI</div>
                <div class="space-y-1">
                    <div class="flex items-center">
                        <span class="aqi-color" style="background-color: #00E400"></span>
                        <span>0-50: Tốt</span>
                    </div>
                    <div class="flex items-center">
                        <span class="aqi-color" style="background-color: #FFFF00"></span>
                        <span>51-100: Trung bình</span>
                    </div>
                    <div class="flex items-center">
                        <span class="aqi-color" style="background-color: #FF7E00"></span>
                        <span>101-150: Kém</span>
                    </div>
                    <div class="flex items-center">
                        <span class="aqi-color" style="background-color: #FF0000"></span>
                        <span>151-200: Xấu</span>
                    </div>
                    <div class="flex items-center">
                        <span class="aqi-color" style="background-color: #8F3F97"></span>
                        <span>201-300: Rất xấu</span>
                    </div>
                    <div class="flex items-center">
                        <span class="aqi-color" style="background-color: #7E0023"></span>
                        <span>>300: Nguy hại</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white p-4 text-sm text-center">
        <p>© {{ date('Y') }} Trung tâm Nghiên cứu Địa tin học - Đại học Nông Lâm Thái Nguyên</p>
    </footer>

    <script>
        let map;
        let factoryMarkers = [];
        let thaiNguyenPolygons = [];
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
            
            factories.forEach(factory => {
                const marker = new google.maps.Marker({
                    position: { 
                        lat: parseFloat(factory.lat), 
                        lng: parseFloat(factory.lng)
                    },
                    map: map,
                    title: factory.name,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: factory.aqi_color,
                        fillOpacity: 0.7,
                        strokeColor: '#000000',
                        strokeWeight: 1,
                        scale: 8
                    }
                });

                const infoContent = `
                    <div class="info-box">
                        <h3 class="font-bold">${factory.name}</h3>
                        <p class="text-sm text-gray-600">${factory.code}</p>
                        <p class="text-sm">${factory.address}</p>
                        
                        ${factory.measurement_time ? `
                            <div class="mt-3 p-2 rounded" style="background-color: ${factory.aqi_color}20">
                                <div class="text-lg font-bold" style="color: ${factory.aqi_color}">
                                    AQI: ${factory.aqi} - ${factory.aqi_status}
                                </div>
                                <div class="text-xs text-gray-600">
                                    Cập nhật: ${new Date(factory.measurement_time).toLocaleString('vi-VN')}
                                </div>
                            </div>
                            <div class="mt-3 text-sm">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>Nhiệt độ: ${factory.latest_measurements.temperature}°C</div>
                                    <div>Độ ẩm: ${factory.latest_measurements.humidity}%</div>
                                    <div>Gió: ${factory.latest_measurements.wind_speed} m/s</div>
                                    <div>Tiếng ồn: ${factory.latest_measurements.noise_level} dBA</div>
                                    <div>Bụi: ${factory.latest_measurements.dust_level} mg/m³</div>
                                    <div>CO: ${factory.latest_measurements.co_level} mg/m³</div>
                                    <div>SO₂: ${factory.latest_measurements.so2_level} mg/m³</div>
                                    <div>TSP: ${factory.latest_measurements.tsp_level} mg/m³</div>
                                </div>
                            </div>
                        ` : '<div class="mt-3 text-sm text-gray-500">Chưa có dữ liệu quan trắc</div>'}
                    </div>
                `;

                const infoWindow = new google.maps.InfoWindow({
                    content: infoContent
                });

                marker.addListener('click', () => {
                    if (currentInfoWindow) {
                        currentInfoWindow.close();
                    }
                    infoWindow.open(map, marker);
                    currentInfoWindow = infoWindow;
                });

                factoryMarkers.push({
                    marker: marker,
                    infoWindow: infoWindow,
                    id: factory.id
                });
            });

            // Add Thai Nguyen boundaries
            const thaiNguyenBoundaries = @json($thaiNguyenBoundaries);
            
            thaiNguyenBoundaries.forEach(area => {
                const coordinates = area.geometry.coordinates[0][0].map(coord => ({
                    lat: coord[1],
                    lng: coord[0]
                }));

                const polygon = new google.maps.Polygon({
                    paths: coordinates,
                    strokeColor: "#FF0000",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#FF0000",
                    fillOpacity: 0.1,
                    map: map
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `<div class="info-box">${area.name}</div>`
                });

                polygon.addListener("mouseover", (e) => {
                    polygon.setOptions({ fillOpacity: 0.3 });
                    infoWindow.setPosition(e.latLng);
                    infoWindow.open(map);
                });

                polygon.addListener("mouseout", () => {
                    polygon.setOptions({ fillOpacity: 0.1 });
                    infoWindow.close();
                });

                thaiNguyenPolygons.push({
                    polygon: polygon,
                    id: area.id
                });
            });

            // Sidebar controls
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

            document.querySelectorAll('.area-item').forEach(item => {
                item.addEventListener('click', () => {
                    const areaId = parseInt(item.dataset.id);
                    const polygonData = thaiNguyenPolygons.find(p => p.id === areaId);
                    if (polygonData) {
                        const bounds = new google.maps.LatLngBounds();
                        polygonData.polygon.getPath().forEach(function(latLng) {
                            bounds.extend(latLng);
                        });
                        map.fitBounds(bounds);
                    }
                });
            });

            // Layer visibility controls
            document.getElementById('factoryLayer').addEventListener('change', function() {
                factoryMarkers.forEach(item => {
                    item.marker.setVisible(this.checked);
                });
            });

            document.getElementById('aqiLayer').addEventListener('change', function() {
                factoryMarkers.forEach(item => {
                    if (this.checked) {
                        item.marker.setIcon({
                            ...item.marker.getIcon(),
                            scale: 8
                        });
                    } else {
                        item.marker.setIcon({
                            ...item.marker.getIcon(),
                            scale: 4
                        });
                    }
                });
            });

            document.getElementById('thaiNguyenLayer').addEventListener('change', function() {
                thaiNguyenPolygons.forEach(item => {
                    item.polygon.setVisible(this.checked);
                });
            });

            // Base map type control
            document.getElementById('baseMapSelect').addEventListener('change', function() {
                map.setMapTypeId(this.value);
            });

            // Update last update time
            updateLastUpdateTime(factories);
        }

        function updateLastUpdateTime(factories) {
            let latestTime = null;
            factories.forEach(factory => {
                if (factory.measurement_time) {
                    const time = new Date(factory.measurement_time);
                    if (!latestTime || time > latestTime) {
                        latestTime = time;
                    }
                }
            });

            const lastUpdateElement = document.getElementById('lastUpdate');
            if (latestTime) {
                lastUpdateElement.textContent = latestTime.toLocaleString('vi-VN', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            } else {
                lastUpdateElement.textContent = 'Chưa có dữ liệu';
            }
        }

        // Add hover effects for sidebar items
        document.querySelectorAll('.factory-item, .area-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                item.classList.add('bg-gray-100');
            });
            item.addEventListener('mouseleave', () => {
                item.classList.remove('bg-gray-100');
            });
        });

        // Auto refresh data every 5 minutes
        setInterval(() => {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');
                    const newFactories = JSON.parse(newDoc.getElementById('factoryData').textContent);
                    
                    // Update markers and info windows
                    newFactories.forEach(newFactory => {
                        const markerData = factoryMarkers.find(m => m.id === newFactory.id);
                        if (markerData) {
                            // Update marker color
                            markerData.marker.setIcon({
                                ...markerData.marker.getIcon(),
                                fillColor: newFactory.aqi_color
                            });

                            // Update info window content
                            const newContent = `
                                <div class="info-box">
                                    <h3 class="font-bold">${newFactory.name}</h3>
                                    <p class="text-sm text-gray-600">${newFactory.code}</p>
                                    <p class="text-sm">${newFactory.address}</p>
                                    
                                    ${newFactory.measurement_time ? `
                                        <div class="mt-3 p-2 rounded" style="background-color: ${newFactory.aqi_color}20">
                                            <div class="text-lg font-bold" style="color: ${newFactory.aqi_color}">
                                                AQI: ${newFactory.aqi} - ${newFactory.aqi_status}
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                Cập nhật: ${new Date(newFactory.measurement_time).toLocaleString('vi-VN')}
                                            </div>
                                        </div>
                                        <div class="mt-3 text-sm">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>Nhiệt độ: ${newFactory.latest_measurements.temperature}°C</div>
                                                <div>Độ ẩm: ${newFactory.latest_measurements.humidity}%</div>
                                                <div>Gió: ${newFactory.latest_measurements.wind_speed} m/s</div>
                                                <div>Tiếng ồn: ${newFactory.latest_measurements.noise_level} dBA</div>
                                                <div>Bụi: ${newFactory.latest_measurements.dust_level} mg/m³</div>
                                                <div>CO: ${newFactory.latest_measurements.co_level} mg/m³</div>
                                                <div>SO₂: ${newFactory.latest_measurements.so2_level} mg/m³</div>
                                                <div>TSP: ${newFactory.latest_measurements.tsp_level} mg/m³</div>
                                            </div>
                                        </div>
                                    ` : '<div class="mt-3 text-sm text-gray-500">Chưa có dữ liệu quan trắc</div>'}
                                </div>
                            `;
                            markerData.infoWindow.setContent(newContent);
                        }
                    });

                    // Update last update time
                    updateLastUpdateTime(newFactories);

                    // Update sidebar factory list
                    const newFactoryList = newDoc.querySelector('.factory-list').innerHTML;
                    document.querySelector('.factory-list').innerHTML = newFactoryList;
                })
                .catch(console.error);
        }, 300000); // 5 minutes
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" defer></script>
</body>
</html>