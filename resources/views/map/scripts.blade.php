<script>
let map;
let factoryMarkers = [];
let thaiNguyenPolygons = [];
let currentInfoWindow = null;
let wmsLayer;
let laHienLayer;
let windOverlays = [];

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

    // Initialize WMS Layer for air cement
    wmsLayer = new google.maps.ImageMapType({
        getTileUrl: function(coord, zoom) {
            const proj = map.getProjection();
            const zfactor = Math.pow(2, zoom);
            
            const top = proj.fromPointToLatLng(
                new google.maps.Point(coord.x * 256 / zfactor, coord.y * 256 / zfactor)
            );
            const bot = proj.fromPointToLatLng(
                new google.maps.Point((coord.x + 1) * 256 / zfactor, (coord.y + 1) * 256 / zfactor)
            );

            const bbox = [
                top.lng(),
                bot.lat(),
                bot.lng(),
                top.lat()
            ].join(',');

            return 'http://geoserver.tuaf.edu.vn/mt_thainguyen/wms' +
                '?service=WMS' +
                '&version=1.1.0' +
                '&request=GetMap' +
                '&layers=mt_thainguyen:air_cement' +
                '&styles=' +
                '&bbox=' + bbox +
                '&width=256' +
                '&height=256' +
                '&srs=EPSG:4326' +
                '&format=image/png' +
                '&transparent=true';
        },
        tileSize: new google.maps.Size(256, 256),
        isPng: true,
        opacity: 0.7,
        name: 'aqiWMS'
    });

    // Initialize La Hien Plume WMS Layer
    laHienLayer = new google.maps.ImageMapType({
        getTileUrl: function(coord, zoom) {
            const proj = map.getProjection();
            const zfactor = Math.pow(2, zoom);
            
            const top = proj.fromPointToLatLng(
                new google.maps.Point(coord.x * 256 / zfactor, coord.y * 256 / zfactor)
            );
            const bot = proj.fromPointToLatLng(
                new google.maps.Point((coord.x + 1) * 256 / zfactor, (coord.y + 1) * 256 / zfactor)
            );

            const bbox = [
                top.lng(),
                bot.lat(),
                bot.lng(),
                top.lat()
            ].join(',');

            return 'http://geoserver.tuaf.edu.vn/mt_thainguyen/wms' +
                '?service=WMS' +
                '&version=1.1.0' +
                '&request=GetMap' +
                '&layers=mt_thainguyen:la_hien_plume' +
                '&styles=' +
                '&bbox=' + bbox +
                '&width=256' +
                '&height=256' +
                '&srs=EPSG:4326' +
                '&format=image/png' +
                '&transparent=true';
        },
        tileSize: new google.maps.Size(256, 256),
        isPng: true,
        opacity: 0.7,
        name: 'laHienWMS'
    });

    // Add WMS layers to map
    map.overlayMapTypes.push(wmsLayer);
    map.overlayMapTypes.push(laHienLayer);

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

        // Add wind direction marker
        if (factory.measurement_time && factory.latest_measurements) {
            const windMarkerDiv = document.createElement('div');
            windMarkerDiv.className = 'wind-direction-marker';

            const rotation = factory.latest_measurements.wind_direction ? 
                `rotate(${factory.latest_measurements.wind_direction}deg)` : 'rotate(0deg)';
            const speed = parseFloat(factory.latest_measurements.wind_speed);
            
            const getWindColor = (speed) => {
                if (!speed) return '#808080';
                if (speed < 0.5) return '#00ff00';
                if (speed < 1.0) return '#ffff00';
                if (speed < 1.5) return '#ffa500';
                return '#ff0000';
            };

            windMarkerDiv.innerHTML = `
                <div class="arrow-container" style="transform: ${rotation}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="${getWindColor(speed)}" 
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 19V5M5 12l7-7 7 7"/>
                    </svg>
                </div>
                ${speed ? `<div class="speed-label">${speed} m/s</div>` : ''}
            `;

            const windOverlay = new google.maps.OverlayView();
            windOverlay.factoryId = factory.id;
            
            windOverlay.onAdd = function() {
                this.div = windMarkerDiv;
                const panes = this.getPanes();
                panes.overlayImage.appendChild(windMarkerDiv);
            };
            
            windOverlay.draw = function() {
                const pos = marker.getPosition();
                const point = this.getProjection().fromLatLngToDivPixel(pos);
                
                if (point) {
                    windMarkerDiv.style.left = (point.x) + 'px';
                    windMarkerDiv.style.top = (point.y - 30) + 'px';
                }
            };
            
            windOverlay.onRemove = function() {
                if (this.div) {
                    this.div.parentNode.removeChild(this.div);
                    this.div = null;
                }
            };
            
            windOverlay.setMap(map);
            windOverlays.push(windOverlay);
        }

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
                            <div>Hướng: ${factory.latest_measurements.wind_direction}°</div>
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

        polygon.addListener("mouseover", (e) => {
            polygon.setOptions({ fillOpacity: 0.3 });
        });

        polygon.addListener("mouseout", () => {
            polygon.setOptions({ fillOpacity: 0.1 });
        });

        thaiNguyenPolygons.push({
            polygon: polygon,
            id: area.id
        });
    });

    // Layer Controls
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

    document.getElementById('windLayer').addEventListener('change', function() {
        windOverlays.forEach(overlay => {
            if (this.checked) {
                overlay.setMap(map);
            } else {
                overlay.setMap(null);
            }
        });
    });

    document.getElementById('wmsLayer').addEventListener('change', function() {
        const index = map.overlayMapTypes.getArray().indexOf(wmsLayer);
        if (this.checked) {
            if (index === -1) map.overlayMapTypes.push(wmsLayer);
        } else {
            if (index !== -1) map.overlayMapTypes.removeAt(index);
        }
    });

    document.getElementById('laHienLayer').addEventListener('change', function() {
        const index = map.overlayMapTypes.getArray().indexOf(laHienLayer);
        if (this.checked) {
            if (index === -1) map.overlayMapTypes.push(laHienLayer);
        } else {
            if (index !== -1) map.overlayMapTypes.removeAt(index);
        }
    });

    // WMS and La Hien layer opacity controls
    document.getElementById('wmsOpacity').addEventListener('input', function() {
        const opacity = this.value / 100;
        wmsLayer.setOpacity(opacity);
    });

    document.getElementById('laHienOpacity').addEventListener('input', function() {
        const opacity = this.value / 100;
        laHienLayer.setOpacity(opacity);
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

    // Update last update time
    updateLastUpdateTime(factories);
}

function refreshWMSLayer() {
    const overlays = map.overlayMapTypes.getArray();
    for (let i = overlays.length - 1; i >= 0; i--) {
        if (overlays[i] && overlays[i].name === 'aqiWMS') {
            map.overlayMapTypes.removeAt(i);
            map.overlayMapTypes.push(wmsLayer);
        }
        if (overlays[i] && overlays[i].name === 'laHienWMS') {
            map.overlayMapTypes.removeAt(i);
            map.overlayMapTypes.push(laHienLayer);
        }
    }
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

// Auto refresh data every 5 minutes
setInterval(() => {
    refreshWMSLayer();
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            const factoryData = document.createElement('div');
            factoryData.innerHTML = html;
            const newFactories = Array.from(factoryData.querySelectorAll('.factory-item')).map(item => {
                return {
                    id: parseInt(item.dataset.id),
                    aqi_color: item.querySelector('.rounded-full').style.backgroundColor,
                    name: item.querySelector('.font-medium').textContent.trim(),
                    measurement_time: item.querySelector('.text-xs') ? 
                        new Date(item.querySelector('.text-xs').textContent.split('Cập nhật: ')[1]) : null,
                    latest_measurements: item.querySelector('[data-wind-speed]') ? {
                        wind_speed: parseFloat(item.querySelector('[data-wind-speed]').dataset.windSpeed),
                        wind_direction: parseFloat(item.querySelector('[data-wind-direction]').dataset.windDirection)
                    } : null
                };
            });
            
            // Update markers and info windows
            newFactories.forEach(newFactory => {
                const markerData = factoryMarkers.find(m => m.id === newFactory.id);
                if (markerData) {
                    // Update marker color
                    markerData.marker.setIcon({
                        ...markerData.marker.getIcon(),
                        fillColor: newFactory.aqi_color
                    });

                    // Update wind direction marker if wind layer is visible
                    if (document.getElementById('windLayer').checked) {
                        const windOverlay = windOverlays.find(overlay => overlay.factoryId === newFactory.id);
                        if (windOverlay && windOverlay.div && newFactory.latest_measurements) {
                            const speed = newFactory.latest_measurements.wind_speed;
                            const direction = newFactory.latest_measurements.wind_direction;
                            
                            const arrowContainer = windOverlay.div.querySelector('.arrow-container');
                            const speedLabel = windOverlay.div.querySelector('.speed-label');
                            
                            if (arrowContainer) {
                                arrowContainer.style.transform = `rotate(${direction}deg)`;
                            }
                            if (speedLabel) {
                                speedLabel.textContent = `${speed} m/s`;
                            }

                            // Update arrow color based on wind speed
                            const arrow = windOverlay.div.querySelector('svg');
                            if (arrow) {
                                const getWindColor = (speed) => {
                                    if (!speed) return '#808080';
                                    if (speed < 0.5) return '#00ff00';
                                    if (speed < 1.0) return '#ffff00';
                                    if (speed < 1.5) return '#ffa500';
                                    return '#ff0000';
                                };
                                arrow.setAttribute('stroke', getWindColor(speed));
                            }
                        }
                    }
                }
            });

            // Update last update time
            updateLastUpdateTime(newFactories);
        })
        .catch(console.error);
}, 300000); // 5 minutes

window.initMap = initMap;
</script>