// Hàm cập nhật thời gian cập nhật cuối cùng
export function updateLastUpdateTime(monitoringMarkers, factoryMarkers, monitoringStations, factories) {
    const lastUpdateElement = document.getElementById('lastUpdate');
    if (!lastUpdateElement) return;

    const times = [];
    
    // Lấy thời gian từ các trạm quan trắc
    monitoringMarkers.forEach(marker => {
        const station = monitoringStations.find(s => s.id === marker.id);
        if (station?.measurement_time) {
            times.push(new Date(station.measurement_time));
        }
    });

    // Lấy thời gian từ các nhà máy
    factoryMarkers.forEach(marker => {
        const factory = factories.find(f => f.id === marker.id);
        if (factory) {
            if (factory.aqi_time) times.push(new Date(factory.aqi_time));
            if (factory.weather_time) times.push(new Date(factory.weather_time));
        }
    });

    // Cập nhật thời gian mới nhất
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

// Hàm làm mới dữ liệu
export async function refreshData(map, layerManager) {
    try {
        const response = await fetch(window.location.href);
        const html = await response.text();
        
        refreshWMSLayers(map, layerManager);
        // Các hàm cập nhật khác có thể thêm vào đây
        
    } catch (error) {
        console.error('Lỗi khi cập nhật dữ liệu:', error);
    }
}

// Hàm làm mới các WMS layer
export function refreshWMSLayers(map, layerManager) {
    const overlays = map.overlayMapTypes.getArray();
    overlays.forEach((overlay, i) => {
        if (overlay?.name === 'aqiWMS') {
            map.overlayMapTypes.removeAt(i);
            map.overlayMapTypes.push(layerManager.wmsLayer);
        }
        if (overlay?.name === 'laHienWMS') {
            map.overlayMapTypes.removeAt(i);
            map.overlayMapTypes.push(layerManager.laHienLayer);
        }
    });
}