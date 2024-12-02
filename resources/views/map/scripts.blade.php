<script>
let map;
let factoryMarkers = [];
let monitoringMarkers = [];
let windMarkers = [];
let thaiNguyenPolygons = [];
let currentInfoWindow = null;
let wmsLayer;
let laHienLayer;

function createWindArrow(position, windDirection, windSpeed) {
    if (!windDirection || !windSpeed) return null;
    
    return new google.maps.Marker({
        position: position,
        map: map,
        icon: {
            path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
            scale: 6,
            rotation: parseFloat(windDirection),
            fillColor: getWindColor(windSpeed),
            fillOpacity: 0.9,
            strokeColor: '#000000',
            strokeWeight: 1
        }
    });
}

function initMap() {
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

    // Initialize WMS Layer
    wmsLayer = new google.maps.ImageMapType({
        getTileUrl: function(coord, zoom) {
            const proj = map.getProjection();
            const zfactor = Math.pow(2, zoom);
            const top = proj.fromPointToLatLng(new google.maps.Point(coord.x * 256 / zfactor, coord.y * 256 / zfactor));
            const bot = proj.fromPointToLatLng(new google.maps.Point((coord.x + 1) * 256 / zfactor, (coord.y + 1) * 256 / zfactor));
            const bbox = [top.lng(), bot.lat(), bot.lng(), top.lat()].join(',');

            return 'http://geoserver.tuaf.edu.vn/mt_thainguyen/wms' +
                '?service=WMS&version=1.1.0&request=GetMap' +
                '&layers=mt_thainguyen:air_cement&styles=' +
                '&bbox=' + bbox +
                '&width=256&height=256&srs=EPSG:4326' +
                '&format=image/png&transparent=true';
        },
        tileSize: new google.maps.Size(256, 256),
        isPng: true,
        opacity: 0.7,
        name: 'aqiWMS'
    });

    laHienLayer = new google.maps.ImageMapType({
        getTileUrl: function(coord, zoom) {
            const proj = map.getProjection();
            const zfactor = Math.pow(2, zoom);
            const top = proj.fromPointToLatLng(new google.maps.Point(coord.x * 256 / zfactor, coord.y * 256 / zfactor));
            const bot = proj.fromPointToLatLng(new google.maps.Point((coord.x + 1) * 256 / zfactor, (coord.y + 1) * 256 / zfactor));
            const bbox = [top.lng(), bot.lat(), bot.lng(), top.lat()].join(',');

            return 'http://geoserver.tuaf.edu.vn/mt_thainguyen/wms' +
                '?service=WMS&version=1.1.0&request=GetMap' +
                '&layers=mt_thainguyen:la_hien_plume&styles=' +
                '&bbox=' + bbox +
                '&width=256&height=256&srs=EPSG:4326' +
                '&format=image/png&transparent=true';
        },
        tileSize: new google.maps.Size(256, 256),
        isPng: true,
        opacity: 0.7,
        name: 'laHienWMS'
    });

    map.overlayMapTypes.push(wmsLayer);
    map.overlayMapTypes.push(laHienLayer);

    // Add weather stations wind arrows
    const weatherStations = @json($weatherStations);
    weatherStations.forEach(station => {
        if (station.weather_measurements?.wind_direction) {
            const windArrow = createWindArrow(
                { lat: parseFloat(station.lat), lng: parseFloat(station.lng) },
                station.weather_measurements.wind_direction,
                station.weather_measurements.wind_speed
            );
            
            const infoContent = `
                <div class="info-box">
                    <h3 class="font-bold">${station.name}</h3>
                    <p class="text-sm text-gray-600">${station.code}</p>
                    <div class="mt-3">
                        <h4 class="font-semibold">Thông tin gió</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm mt-2">
                            <div>Tốc độ: ${station.weather_measurements.wind_speed} m/s</div>
                            <div>Hướng: ${station.weather_measurements.wind_direction}°</div>
                        </div>
                        <div class="text-xs text-gray-600 mt-2">
                            Cập nhật: ${new Date(station.measurement_time).toLocaleString('vi-VN')}
                        </div>
                    </div>
                </div>
            `;

            const infoWindow = new google.maps.InfoWindow({ content: infoContent });
            
            windArrow.addListener('click', () => {
                if (currentInfoWindow) currentInfoWindow.close();
                infoWindow.open(map, windArrow);
                currentInfoWindow = infoWindow;
            });

            windMarkers.push({
                code: station.code,
                marker: windArrow,
                infoWindow: infoWindow
            });
        }
    });

    // Add monitoring stations
    const monitoringStations = @json($monitoringStations);
    monitoringStations.forEach(station => {
        const marker = new google.maps.Marker({
            position: { lat: parseFloat(station.lat), lng: parseFloat(station.lng) },
            map: map,
            title: station.name,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: station.aqi_color,
                fillOpacity: 0.7,
                strokeColor: '#000000',
                strokeWeight: 1,
                scale: 8
            }
        });

        const infoContent = `
            <div class="info-box">
                <h3 class="font-bold">${station.name}</h3>
                <p class="text-sm text-gray-600">${station.code}</p>
                ${station.measurement_time ? `
                    <div class="mt-3 p-2 rounded" style="background-color: ${station.aqi_color}20">
                        <div class="text-lg font-bold" style="color: ${station.aqi_color}">
                            AQI: ${station.aqi} - ${station.aqi_status}
                        </div>
                        <div class="text-xs text-gray-600">
                            Cập nhật: ${new Date(station.measurement_time).toLocaleString('vi-VN')}
                        </div>
                    </div>
                    ${station.latest_measurements ? `
                        <div class="mt-3 text-sm">
                            <div class="grid grid-cols-2 gap-2">
                                <div>Bụi: ${station.latest_measurements.dust_level} mg/m³</div>
                                <div>CO: ${station.latest_measurements.co_level} mg/m³</div>
                                <div>SO₂: ${station.latest_measurements.so2_level} mg/m³</div>
                                <div>TSP: ${station.latest_measurements.tsp_level} mg/m³</div>
                            </div>
                        </div>
                    ` : ''}
                ` : '<div class="mt-3 text-sm text-gray-500">Chưa có dữ liệu đo</div>'}
            </div>
        `;

        const infoWindow = new google.maps.InfoWindow({ content: infoContent });
        
        marker.addListener('click', () => {
            if (currentInfoWindow) currentInfoWindow.close();
            infoWindow.open(map, marker);
            currentInfoWindow = infoWindow;
        });

        monitoringMarkers.push({
            id: station.id,
            marker: marker,
            infoWindow: infoWindow
        });
    });

    // Add factories
    const factories = @json($factories);
    factories.forEach(factory => {
        const marker = new google.maps.Marker({
            position: { lat: parseFloat(factory.lat), lng: parseFloat(factory.lng) },
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

        // Add factory wind arrow
        if (factory.weather_measurements?.wind_direction) {
            const windArrow = createWindArrow(
                { lat: parseFloat(factory.lat), lng: parseFloat(factory.lng) },
                factory.weather_measurements.wind_direction,
                factory.weather_measurements.wind_speed
            );

            windMarkers.push({
                code: factory.code,
                marker: windArrow,
                infoWindow: null
            });
        }

        const infoContent = `
            <div class="info-box">
                <h3 class="font-bold">${factory.name}</h3>
                <p class="text-sm text-gray-600">${factory.code}</p>
                ${factory.aqi_time ? `
                    <div class="mt-3 p-2 rounded" style="background-color: ${factory.aqi_color}20">
                        <div class="text-lg font-bold" style="color: ${factory.aqi_color}">
                            AQI: ${factory.aqi} - ${factory.aqi_status}
                        </div>
                        <div class="text-xs text-gray-600">
                            Cập nhật: ${new Date(factory.aqi_time).toLocaleString('vi-VN')}
                        </div>
                    </div>
                    ${factory.latest_measurements ? `
                        <div class="mt-3 text-sm">
                            <div class="grid grid-cols-2 gap-2">
                                <div>Bụi: ${factory.latest_measurements.dust_level} mg/m³</div>
                                <div>CO: ${factory.latest_measurements.co_level} mg/m³</div>
                                <div>SO₂: ${factory.latest_measurements.so2_level} mg/m³</div>
                                <div>TSP: ${factory.latest_measurements.tsp_level} mg/m³</div>
                            </div>
                        </div>
                    ` : ''}
                ` : '<div class="mt-3 text-sm text-gray-500">Chưa có dữ liệu AQI</div>'}
                
                ${factory.weather_measurements ? `
                    <div class="mt-3">
                        <h4 class="font-semibold">Thông tin gió</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm mt-2">
                            <div>Tốc độ: ${factory.weather_measurements.wind_speed} m/s</div>
                            <div>Hướng: ${factory.weather_measurements.wind_direction}°</div>
                        </div>
                        <div class="text-xs text-gray-600 mt-2">
                            Cập nhật: ${new Date(factory.weather_time).toLocaleString('vi-VN')}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;

        const infoWindow = new google.maps.InfoWindow({ content: infoContent });
        
        marker.addListener('click', () => {
            if (currentInfoWindow) currentInfoWindow.close();
            infoWindow.open(map, marker);
            currentInfoWindow = infoWindow;
        });

        factoryMarkers.push({
            id: factory.id,
            marker: marker,
            infoWindow: infoWindow
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

        polygon.addListener("mouseover", () => {
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
    document.getElementById('aqiStationLayer').addEventListener('change', function() {
        const isVisible = this.checked;
        monitoringMarkers.forEach(item => {
            item.marker.setVisible(isVisible);
        });
    });

    document.getElementById('factoryLayer').addEventListener('change', function() {
        const isVisible = this.checked;
        factoryMarkers.forEach(item => {
            item.marker.setVisible(isVisible);
        });
    });

    document.getElementById('windLayer').addEventListener('change', function() {
        const isVisible = this.checked;
        windMarkers.forEach(item => {
            item.marker.setVisible(isVisible);
        });
    });

    document.getElementById('wmsLayer').addEventListener('change', function() {
        const index = map.overlayMapTypes.getArray().indexOf(wmsLayer);
        if (this.checked && index === -1) map.overlayMapTypes.push(wmsLayer);
        else if (!this.checked && index !== -1) map.overlayMapTypes.removeAt(index);
    });

    document.getElementById('laHienLayer').addEventListener('change', function() {
        const index = map.overlayMapTypes.getArray().indexOf(laHienLayer);
        if (this.checked && index === -1) map.overlayMapTypes.push(laHienLayer);
        else if (!this.checked && index !== -1) map.overlayMapTypes.removeAt(index);
    });

    document.getElementById('thaiNguyenLayer').addEventListener('change', function() {
        const isVisible = this.checked;
        thaiNguyenPolygons.forEach(item => {
            item.polygon.setVisible(isVisible);
        });
    });

    // Opacity Controls
    document.getElementById('wmsOpacity').addEventListener('input', function() {
        wmsLayer.setOpacity(this.value / 100);
    });

    document.getElementById('laHienOpacity').addEventListener('input', function() {
        laHienLayer.setOpacity(this.value / 100);
    });

    // Base map type control
    document.getElementById('baseMapSelect').addEventListener('change', function() {
        map.setMapTypeId(this.value);
    });

    // Sidebar controls
    document.querySelectorAll('.station-item').forEach(item => {
        item.addEventListener('click', () => {
            const stationId = parseInt(item.dataset.id);
            const markerData = monitoringMarkers.find(m => m.id === stationId);
            if (markerData) {
                map.panTo(markerData.marker.getPosition());
                map.setZoom(15);
                if (currentInfoWindow) currentInfoWindow.close();
                markerData.infoWindow.open(map, markerData.marker);
                currentInfoWindow = markerData.infoWindow;
            }
        });
    });

    document.querySelectorAll('.factory-item').forEach(item => {
        item.addEventListener('click', () => {
            const factoryId = parseInt(item.dataset.id);
            const markerData = factoryMarkers.find(m => m.id === factoryId);
            if (markerData) {
                map.panTo(markerData.marker.getPosition());
                map.setZoom(15);
                if (currentInfoWindow) currentInfoWindow.close();
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
                polygonData.polygon.getPath().forEach(latLng => bounds.extend(latLng));
                map.fitBounds(bounds);
            }
        });
    });

    updateLastUpdateTime();
}

function getWindColor(speed) {
    if (!speed) return '#808080';
    if (speed < 0.5) return '#00ff00';
    if (speed < 1.0) return '#ffff00';
    if (speed < 1.5) return '#ffa500';
    return '#ff0000';
}

function updateLastUpdateTime() {
    const lastUpdateElement = document.getElementById('lastUpdate');
    if (!lastUpdateElement) return;

    const times = [];
    
    monitoringMarkers.forEach(marker => {
        const station = @json($monitoringStations).find(s => s.id === marker.id);
        if (station?.measurement_time) {
            times.push(new Date(station.measurement_time));
        }
    });

    factoryMarkers.forEach(marker => {
        const factory = @json($factories).find(f => f.id === marker.id);
        if (factory) {
            if (factory.aqi_time) times.push(new Date(factory.aqi_time));
            if (factory.weather_time) times.push(new Date(factory.weather_time));
        }
    });

    const latestTime = times.length ? new Date(Math.max(...times)) : null;
    lastUpdateElement.textContent = latestTime 
        ? latestTime.toLocaleString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        })
        : 'Chưa có dữ liệu';
}

setInterval(refreshData, 300000);

function refreshData() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            refreshWMSLayers();
            updateMarkers();
            updateLastUpdateTime();
        })
        .catch(error => console.error('Lỗi khi cập nhật dữ liệu:', error));
}

function refreshWMSLayers() {
    const overlays = map.overlayMapTypes.getArray();
    overlays.forEach((overlay, i) => {
        if (overlay?.name === 'aqiWMS') {
            map.overlayMapTypes.removeAt(i);
            map.overlayMapTypes.push(wmsLayer);
        }
        if (overlay?.name === 'laHienWMS') {
            map.overlayMapTypes.removeAt(i);
            map.overlayMapTypes.push(laHienLayer);
        }
    });
}

window.initMap = initMap;
</script>