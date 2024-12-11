import { mapConfig } from './config.js';
import { MarkerManager } from './markers.js';
import { LayerManager } from './layers.js';
import { ControlManager } from './controls.js';
import { MapInteractions } from './mapInteractions.js';
import { updateLastUpdateTime, refreshData } from './utils.js';

let map;
let markerManager;
let layerManager;
let controlManager;
let mapInteractions;

function initTimeControl() {
    const nowButton = document.querySelector('.now-button');
    const dayButtons = document.querySelectorAll('.day-button');
    const hourButtons = document.querySelectorAll('.hour-button');
    const currentDateDisplay = document.querySelector('.current-date');
    
    function updateDateDisplay(dayOffset) {
        const date = new Date();
        date.setDate(date.getDate() + parseInt(dayOffset));
        currentDateDisplay.textContent = date.toLocaleDateString('vi-VN');
    }

    // Xử lý click nút Now
    if (nowButton) {
        nowButton.addEventListener('click', (e) => {
            // Reset trạng thái active của các nút
            dayButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            hourButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            
            // Đặt style cho nút Now
            nowButton.classList.remove('bg-gray-200');
            nowButton.classList.add('bg-green-500', 'text-white');
            
            // Hiển thị ngày hiện tại
            currentDateDisplay.textContent = new Date().toLocaleDateString('vi-VN');
            
            // Hiển thị các layer mặc định
            layerManager.showCurrentLayers();
        });
    }
    
    // Xử lý click nút ngày
    dayButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            // Reset trạng thái Now
            if (nowButton) {
                nowButton.classList.remove('bg-green-500', 'text-white');
                nowButton.classList.add('bg-gray-200');
            }
            
            // Reset trạng thái các nút ngày
            dayButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            
            // Đặt active cho nút được click
            e.target.classList.remove('bg-gray-200');
            e.target.classList.add('bg-blue-600', 'text-white');
            
            const day = e.target.dataset.day;
            updateDateDisplay(day);
            
            // Lấy giờ hiện tại hoặc giờ đã chọn
            const activeHourButton = document.querySelector('.hour-button.bg-blue-600');
            const hour = activeHourButton ? activeHourButton.dataset.hour : "00";
            
            // Cập nhật layer dự báo
            layerManager.updateForecastLayers(day, hour);
        });
    });
    
    // Xử lý click nút giờ
    hourButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            // Reset trạng thái Now
            if (nowButton) {
                nowButton.classList.remove('bg-green-500', 'text-white');
                nowButton.classList.add('bg-gray-200');
            }
            
            // Reset trạng thái các nút giờ
            hourButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            
            // Đặt active cho nút được click
            e.target.classList.remove('bg-gray-200');
            e.target.classList.add('bg-blue-600', 'text-white');
            
            // Lấy ngày hiện tại hoặc ngày đã chọn
            const activeDayButton = document.querySelector('.day-button.bg-blue-600');
            const day = activeDayButton ? activeDayButton.dataset.day : "0";
            
            const hour = e.target.dataset.hour;
            
            // Cập nhật layer dự báo
            layerManager.updateForecastLayers(day, hour);
        });
    });
    
    // Set trạng thái mặc định là Now
    if (nowButton) {
        nowButton.click();
    }
}

window.initMap = function() {
    const mapCenter = { 
        lat: mapData.lat, 
        lng: mapData.lng 
    };

    // Khởi tạo bản đồ
    map = new google.maps.Map(document.getElementById('map'), {
        center: mapCenter,
        zoom: 9.8,
        mapTypeId: 'terrain',
        mapTypeControl: false,
        styles: mapConfig.styles
    });

    // Khởi tạo các manager
    markerManager = new MarkerManager(map);
    layerManager = new LayerManager(map);
    controlManager = new ControlManager(map, markerManager, layerManager);
    
    // Khởi tạo map interactions
    mapInteractions = new MapInteractions(map, markerManager, layerManager);

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

    // Khởi tạo time control
    initTimeControl();

    // Thiết lập interval để refresh data
    setInterval(() => {
        refreshData(map, layerManager);
    }, mapConfig.refreshInterval);

    // Xử lý sự kiện click cho nút bật tắt sidebar
    const toggleSidebarButton = document.getElementById('toggleSidebar');
    if (toggleSidebarButton) {
        toggleSidebarButton.addEventListener('click', () => {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        });
    }
};