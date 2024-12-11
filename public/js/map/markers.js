import { getWindColor } from './config.js';

export class MarkerManager {
    constructor(map) {
        this.map = map;
        this.factoryMarkers = [];
        this.monitoringMarkers = [];
        this.windMarkers = [];
        this.currentInfoWindow = null;
    }

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
                    url: '/images/icon_nha_may.png',
                    scaledSize: new google.maps.Size(32, 32),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(16, 16)
                }
            });

            const infoWindow = this.createFactoryInfoWindow(factory);
            this.setupMarkerListeners(marker, infoWindow);

            this.factoryMarkers.push({
                id: factory.id,
                marker: marker,
                infoWindow: infoWindow
            });

            if (factory.weather_measurements?.wind_direction) {
                const windArrow = this.createWindArrow(
                    { 
                        lat: parseFloat(factory.lat), 
                        lng: parseFloat(factory.lng) 
                    },
                    factory.weather_measurements.wind_direction,
                    factory.weather_measurements.wind_speed
                );

                if (windArrow) {
                    this.windMarkers.push({
                        code: factory.code,
                        marker: windArrow
                    });
                }
            }
        });
    }

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

                if (windArrow) {
                    const infoWindow = this.createWeatherInfoWindow(station);
                    this.setupMarkerListeners(windArrow, infoWindow);

                    this.windMarkers.push({
                        code: station.code,
                        marker: windArrow,
                        infoWindow: infoWindow
                    });
                }
            }
        });
    }

    setupMarkerListeners(marker, infoWindow) {
        marker.addListener('click', () => {
            if (this.currentInfoWindow) {
                this.currentInfoWindow.close();
            }
            infoWindow.open(this.map, marker);
            this.currentInfoWindow = infoWindow;
        });
    }

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

    clearWindMarkers() {
        this.windMarkers.forEach(item => {
            if (item.marker) {
                item.marker.setMap(null);
            }
        });
        this.windMarkers = [];
    }

    refreshMarkers() {
        this.monitoringMarkers.forEach(item => item.marker.setMap(this.map));
        this.factoryMarkers.forEach(item => item.marker.setMap(this.map));
        this.windMarkers.forEach(item => item.marker.setMap(this.map));
    }

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