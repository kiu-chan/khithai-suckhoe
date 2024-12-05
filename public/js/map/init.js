import { mapConfig } from './config.js';
import { MarkerManager } from './markers.js';
import { LayerManager } from './layers.js';
import { ControlManager } from './controls.js';
import { updateLastUpdateTime, refreshData } from './utils.js';

let map;
let markerManager;
let layerManager;
let controlManager;

window.initMap = function() {
    const mapCenter = { 
        lat: mapData.lat, 
        lng: mapData.lng 
    };

    // Khởi tạo bản đồ
    map = new google.maps.Map(document.getElementById('map'), {
        center: mapCenter,
        zoom: mapData.zoom,
        mapTypeId: 'terrain',
        mapTypeControl: false,
        styles: mapConfig.styles
    });

    // Khởi tạo các manager
    markerManager = new MarkerManager(map);
    layerManager = new LayerManager(map);
    controlManager = new ControlManager(map, markerManager, layerManager);

    // Khởi tạo các layer
    layerManager.initWMSLayer();
    layerManager.addThaiNguyenBoundaries(thaiNguyenBoundaries);

    // Thêm các marker
    markerManager.addMonitoringStations(monitoringStations);
    markerManager.addFactories(factories);
    markerManager.addWeatherStations(weatherStations);

    // Cập nhật thời gian
    updateLastUpdateTime(
        markerManager.monitoringMarkers,
        markerManager.factoryMarkers,
        monitoringStations,
        factories
    );

    // Thiết lập interval để refresh data
    setInterval(() => {
        refreshData(map, layerManager);
    }, mapConfig.refreshInterval);
};