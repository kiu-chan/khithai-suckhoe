import { getWindColor } from './config.js';

export class MarkerManager {
    constructor(map) {
        this.map = map;
        this.factoryMarkers = [];
        this.monitoringMarkers = [];
        this.windMarkers = [];
        this.currentInfoWindow = null;
    }

    // Tạo marker mũi tên gió
    createWindArrow(position, windDirection, windSpeed) {
        if (!windDirection || !windSpeed) return null;
        
        return new google.maps.Marker({
            position: position,
            map: this.map,
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

    // Thêm trạm quan trắc vào bản đồ
    addMonitoringStations(stations) {
        stations.forEach(station => {
            const marker = new google.maps.Marker({
                position: { 
                    lat: parseFloat(station.lat), 
                    lng: parseFloat(station.lng) 
                },
                map: this.map,
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

            const infoWindow = this.createStationInfoWindow(station);
            this.setupMarkerListeners(marker, infoWindow);

            this.monitoringMarkers.push({
                id: station.id,
                marker: marker,
                infoWindow: infoWindow
            });
        });
    }

    // Thêm nhà máy vào bản đồ 
    addFactories(factories) {
        factories.forEach(factory => {
            const marker = new google.maps.Marker({
                position: { 
                    lat: parseFloat(factory.lat), 
                    lng: parseFloat(factory.lng) 
                },
                map: this.map,
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

            const infoWindow = this.createFactoryInfoWindow(factory);
            this.setupMarkerListeners(marker, infoWindow);

            this.factoryMarkers.push({
                id: factory.id,
                marker: marker,
                infoWindow: infoWindow
            });

            // Thêm mũi tên gió cho nhà máy nếu có dữ liệu
            if (factory.weather_measurements?.wind_direction) {
                const windArrow = this.createWindArrow(
                    { 
                        lat: parseFloat(factory.lat), 
                        lng: parseFloat(factory.lng) 
                    },
                    factory.weather_measurements.wind_direction,
                    factory.weather_measurements.wind_speed
                );

                this.windMarkers.push({
                    code: factory.code,
                    marker: windArrow
                });
            }
        });
    }

    // Thêm trạm thời tiết và mũi tên gió
    addWeatherStations(weatherStations) {
        weatherStations.forEach(station => {
            if (station.weather_measurements?.wind_direction) {
                const windArrow = this.createWindArrow(
                    { 
                        lat: parseFloat(station.lat), 
                        lng: parseFloat(station.lng) 
                    },
                    station.weather_measurements.wind_direction,
                    station.weather_measurements.wind_speed
                );

                const infoWindow = this.createWeatherInfoWindow(station);
                this.setupMarkerListeners(windArrow, infoWindow);

                this.windMarkers.push({
                    code: station.code,
                    marker: windArrow,
                    infoWindow: infoWindow
                });
            }
        });
    }

    // Thiết lập sự kiện cho marker
    setupMarkerListeners(marker, infoWindow) {
        marker.addListener('click', () => {
            if (this.currentInfoWindow) {
                this.currentInfoWindow.close();
            }
            infoWindow.open(this.map, marker);
            this.currentInfoWindow = infoWindow;
        });
    }

    // Tạo cửa sổ thông tin cho trạm quan trắc
    createStationInfoWindow(station) {
        const content = `
            <div class="info-box">
                <h3 class="font-bold">${station.name}</h3>
                <p class="text-sm text-gray-600">${station.code}</p>
                ${this.createStationMeasurementContent(station)}
            </div>
        `;
        return new google.maps.InfoWindow({ content });
    }

    // Tạo nội dung đo đạc cho trạm quan trắc
    createStationMeasurementContent(station) {
        if (!station.measurement_time) {
            return '<div class="mt-3 text-sm text-gray-500">Chưa có dữ liệu đo</div>';
        }

        return `
            <div class="mt-3 p-2 rounded" style="background-color: ${station.aqi_color}20">
                <div class="text-lg font-bold" style="color: ${station.aqi_color}">
                    AQI: ${station.aqi} - ${station.aqi_status}
                </div>
                <div class="text-xs text-gray-600">
                    Cập nhật: ${new Date(station.measurement_time).toLocaleString('vi-VN')}
                </div>
            </div>
            ${this.createDetailedMeasurements(station.latest_measurements)}
        `;
    }

    // Tạo cửa sổ thông tin cho nhà máy
    createFactoryInfoWindow(factory) {
        const content = `
            <div class="info-box">
                <h3 class="font-bold">${factory.name}</h3>
                <p class="text-sm text-gray-600">${factory.code}</p>
                ${this.createFactoryMeasurementContent(factory)}
                ${this.createWeatherContent(factory)}
            </div>
        `;
        return new google.maps.InfoWindow({ content });
    }

    // Tạo nội dung đo đạc cho nhà máy
    createFactoryMeasurementContent(factory) {
        if (!factory.aqi_time) {
            return '<div class="mt-3 text-sm text-gray-500">Chưa có dữ liệu AQI</div>';
        }

        return `
            <div class="mt-3 p-2 rounded" style="background-color: ${factory.aqi_color}20">
                <div class="text-lg font-bold" style="color: ${factory.aqi_color}">
                    AQI: ${factory.aqi} - ${factory.aqi_status}
                </div>
                <div class="text-xs text-gray-600">
                    Cập nhật: ${new Date(factory.aqi_time).toLocaleString('vi-VN')}
                </div>
            </div>
            ${this.createDetailedMeasurements(factory.latest_measurements)}
        `;
    }

    // Tạo nội dung chi tiết các thông số đo
    createDetailedMeasurements(measurements) {
        if (!measurements) return '';
        
        return `
            <div class="mt-3 text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <div>Bụi: ${measurements.dust_level} mg/m³</div>
                    <div>CO: ${measurements.co_level} mg/m³</div>
                    <div>SO₂: ${measurements.so2_level} mg/m³</div>
                    <div>TSP: ${measurements.tsp_level} mg/m³</div>
                </div>
            </div>
        `;
    }

    // Tạo nội dung thông tin thời tiết
    createWeatherContent(data) {
        if (!data.weather_measurements) return '';

        return `
            <div class="mt-3">
                <h4 class="font-semibold">Thông tin gió</h4>
                <div class="grid grid-cols-2 gap-2 text-sm mt-2">
                    <div>Tốc độ: ${data.weather_measurements.wind_speed} m/s</div>
                    <div>Hướng: ${data.weather_measurements.wind_direction}°</div>
                </div>
                <div class="text-xs text-gray-600 mt-2">
                    Cập nhật: ${new Date(data.weather_time).toLocaleString('vi-VN')}
                </div>
            </div>
        `;
    }

    // Tạo cửa sổ thông tin cho trạm thời tiết
    createWeatherInfoWindow(station) {
        const content = `
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
        return new google.maps.InfoWindow({ content });
    }

    // Hiển thị/ẩn markers
    setMarkersVisibility(type, visible) {
        const markers = {
            'monitoring': this.monitoringMarkers,
            'factory': this.factoryMarkers,
            'wind': this.windMarkers
        }[type] || [];

        markers.forEach(item => {
            if (item.marker) {
                item.marker.setVisible(visible);
            }
        });
    }

    // Làm mới trạng thái markers
    refreshMarkers() {
        // Cập nhật trạng thái các markers khi cần
        this.monitoringMarkers.forEach(item => item.marker.setMap(this.map));
        this.factoryMarkers.forEach(item => item.marker.setMap(this.map));
        this.windMarkers.forEach(item => item.marker.setMap(this.map));
    }

    // Xóa tất cả markers
    clearMarkers() {
        this.monitoringMarkers.forEach(item => {
            if (item.marker) item.marker.setMap(null);
        });
        this.factoryMarkers.forEach(item => {
            if (item.marker) item.marker.setMap(null);
        });
        this.windMarkers.forEach(item => {
            if (item.marker) item.marker.setMap(null);
        });
        
        this.monitoringMarkers = [];
        this.factoryMarkers = [];
        this.windMarkers = [];
        
        if (this.currentInfoWindow) {
            this.currentInfoWindow.close();
            this.currentInfoWindow = null;
        }
    }
}