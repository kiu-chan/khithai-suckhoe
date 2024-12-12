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

    if (nowButton) {
        nowButton.addEventListener('click', (e) => {
            dayButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            hourButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            
            nowButton.classList.remove('bg-gray-200');
            nowButton.classList.add('bg-green-500', 'text-white');
            
            currentDateDisplay.textContent = new Date().toLocaleDateString('vi-VN');
            
            layerManager.showCurrentLayers();
        });
    }
    
    dayButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (nowButton) {
                nowButton.classList.remove('bg-green-500', 'text-white');
                nowButton.classList.add('bg-gray-200');
            }
            
            dayButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            
            e.target.classList.remove('bg-gray-200');
            e.target.classList.add('bg-blue-600', 'text-white');
            
            const day = e.target.dataset.day;
            updateDateDisplay(day);
            
            const activeHourButton = document.querySelector('.hour-button.bg-blue-600');
            const hour = activeHourButton ? activeHourButton.dataset.hour : "00";
            
            layerManager.updateForecastLayers(day, hour);
        });
    });
    
    hourButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (nowButton) {
                nowButton.classList.remove('bg-green-500', 'text-white');
                nowButton.classList.add('bg-gray-200');
            }
            
            hourButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            
            e.target.classList.remove('bg-gray-200');
            e.target.classList.add('bg-blue-600', 'text-white');
            
            const activeDayButton = document.querySelector('.day-button.bg-blue-600');
            const day = activeDayButton ? activeDayButton.dataset.day : "0";
            
            const hour = e.target.dataset.hour;
            
            layerManager.updateForecastLayers(day, hour);
        });
    });
    
    if (nowButton) {
        nowButton.click();
    }
}

window.initMap = function() {
    const mapCenter = { 
        lat: mapData.lat, 
        lng: mapData.lng 
    };

    map = new google.maps.Map(document.getElementById('map'), {
        center: mapCenter,
        zoom: 9.8,
        mapTypeId: 'terrain',
        mapTypeControl: false,
        styles: mapConfig.styles
    });

    markerManager = new MarkerManager(map);
    layerManager = new LayerManager(map);
    controlManager = new ControlManager(map, markerManager, layerManager);
    
    mapInteractions = new MapInteractions(map, markerManager, layerManager);

    layerManager.initWMSLayer();
    layerManager.addThaiNguyenBoundaries(thaiNguyenBoundaries);

    markerManager.addMonitoringStations(monitoringStations);
    markerManager.addFactories(factories);
    markerManager.addWeatherStations(weatherStations);

    updateLastUpdateTime(
        markerManager.monitoringMarkers,
        markerManager.factoryMarkers,
        monitoringStations,
        factories
    );

    initTimeControl();

    setInterval(() => {
        refreshData(map, layerManager);
    }, mapConfig.refreshInterval);

    const toggleSidebarButton = document.getElementById('toggleSidebar');
    if (toggleSidebarButton) {
        toggleSidebarButton.addEventListener('click', () => {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        });
    }

    const thaiNguyenButton = document.getElementById('getThaiNguyenLocation');
    if (thaiNguyenButton) {
        thaiNguyenButton.addEventListener('click', () => {
            const thaiNguyenCenter = {
                lat: 21.592487,
                lng: 105.824769
            };
            map.setCenter(thaiNguyenCenter);
            map.setZoom(9);
        });
    }
};