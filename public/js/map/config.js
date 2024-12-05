export const mapConfig = {
    styles: [
        {
            featureType: "poi",
            elementType: "labels",
            stylers: [{ visibility: "off" }]
        }
    ],
    
    // URL WMS server
    wmsUrl: 'http://geoserver.tuaf.edu.vn/mt_thainguyen/wms',
    
    // Thời gian refresh data (ms)
    refreshInterval: 300000, // 5 phút

    // Cấu hình icon
    icons: {
        factory: {
            url: '/images/icon_nha_may.png',
            size: 32,
            anchor: 16
        }
    }
};

// Cấu hình màu sắc cho tốc độ gió
export const windSpeedColors = {
    low: '#00ff00',    // < 0.5 m/s
    medium: '#ffff00', // 0.5-1.0 m/s
    high: '#ffa500',   // 1.0-1.5 m/s
    veryHigh: '#ff0000' // > 1.5 m/s
};

export function getWindColor(speed) {
    if (!speed) return '#808080';
    if (speed < 0.5) return windSpeedColors.low;
    if (speed < 1.0) return windSpeedColors.medium;
    if (speed < 1.5) return windSpeedColors.high;
    return windSpeedColors.veryHigh;
}