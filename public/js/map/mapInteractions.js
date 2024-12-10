// mapInteractions.js

export class MapInteractions {
    constructor(map, markerManager, layerManager) {
        this.map = map;
        this.markerManager = markerManager;
        this.layerManager = layerManager;
        this.currentInfoWindow = null;
        
        // Khởi tạo các giá trị thang đo AQI
        this.aqiLevels = [
            { gray: 0, aqi: 0, color: '#FF1010', label: 'No Data', opacity: 0 },
            { gray: 1, aqi: 1, color: '#01e400', label: 'Good', opacity: 1 },
            { gray: 50, aqi: 50, color: '#fff000', label: 'Moderate', opacity: 1 },
            { gray: 100, aqi: 100, color: '#ff7e00', label: 'Unhealthy for Sensitive Groups', opacity: 1 },
            { gray: 150, aqi: 150, color: '#fe0103', label: 'Unhealthy', opacity: 1 },
            { gray: 200, aqi: 200, color: '#99004c', label: 'Very Unhealthy', opacity: 1 },
            { gray: 300, aqi: 300, color: '#7e0023', label: 'Hazardous', opacity: 1 }
        ];

        this.setupMapClickHandler();
    }

    setupMapClickHandler() {
        this.map.addListener('click', async (e) => {
            await this.handleMapClick(e);
        });
    }

    async handleMapClick(e) {
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();

        try {
            const aqiData = await this.getAQIData(lat, lng);
            if (aqiData) {
                this.showInfoWindow(e.latLng, aqiData);
            }
        } catch (error) {
            console.error('Error handling map click:', error);
        }
    }

    async getAQIData(lat, lng) {
        const wmsUrl = this.buildWMSUrl(lat, lng);
        
        try {
            const response = await fetch(wmsUrl);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.features && data.features.length > 0) {
                const grayValue = data.features[0].properties.GRAY_INDEX;
                return {
                    raw: grayValue,
                    aqi: this.calculateAQI(grayValue),
                    time: new Date().toLocaleString('vi-VN')
                };
            }
            return null;
        } catch (error) {
            console.error('Error fetching WMS data:', error);
            return null;
        }
    }

    buildWMSUrl(lat, lng) {
        const buffer = 0.001; // Buffer ~100m
        const bbox = `${lng - buffer},${lat - buffer},${lng + buffer},${lat + buffer}`;

        return `http://geoserver.tuaf.edu.vn/mt_thainguyen/wms?` +
               `SERVICE=WMS&` +
               `VERSION=1.1.1&` +
               `REQUEST=GetFeatureInfo&` +
               `LAYERS=mt_thainguyen:air_cement&` +
               `QUERY_LAYERS=mt_thainguyen:air_cement&` +
               `BBOX=${bbox}&` +
               `HEIGHT=256&` +
               `WIDTH=256&` +
               `FORMAT=image/png&` +
               `INFO_FORMAT=application/json&` +
               `SRS=EPSG:4326&` +
               `X=128&` +
               `Y=128&` +
               `FEATURE_COUNT=1`;
    }

    calculateAQI(grayValue) {
        // Kiểm tra giá trị null hoặc undefined
        if (grayValue == null) return 0;

        // Tìm khoảng giá trị phù hợp để nội suy
        for (let i = 0; i < this.aqiLevels.length - 1; i++) {
            const currentLevel = this.aqiLevels[i];
            const nextLevel = this.aqiLevels[i + 1];
            
            if (grayValue >= currentLevel.gray && grayValue <= nextLevel.gray) {
                // Sử dụng nội suy tuyến tính
                return Math.round(
                    currentLevel.aqi +
                    (grayValue - currentLevel.gray) *
                    (nextLevel.aqi - currentLevel.aqi) /
                    (nextLevel.gray - currentLevel.gray)
                );
            }
        }

        // Xử lý giá trị ngoài thang đo
        if (grayValue > this.aqiLevels[this.aqiLevels.length - 1].gray) {
            return this.aqiLevels[this.aqiLevels.length - 1].aqi;
        }

        return 0;
    }

    getAQIInfo(aqi) {
        // Tìm level phù hợp với giá trị AQI
        for (let i = 0; i < this.aqiLevels.length - 1; i++) {
            const currentLevel = this.aqiLevels[i];
            const nextLevel = this.aqiLevels[i + 1];
            
            if (aqi >= currentLevel.aqi && aqi < nextLevel.aqi) {
                const healthMessages = {
                    'Good': 'Air quality is satisfactory and poses little or no health risk.',
                    'Moderate': 'Air quality is acceptable. However, there may be a risk for some people, particularly those who are unusually sensitive to air pollution.',
                    'Unhealthy for Sensitive Groups': 'Members of sensitive groups may experience health effects. The general public is less likely to be affected.',
                    'Unhealthy': 'Some members of the general public may experience health effects; members of sensitive groups may experience more serious health effects.',
                    'Very Unhealthy': 'Health alert: The risk of health effects is increased for everyone.',
                    'Hazardous': 'Health warning of emergency conditions: everyone is more likely to be affected.'
                };

                return {
                    color: currentLevel.color,
                    status: currentLevel.label,
                    message: healthMessages[currentLevel.label] || ''
                };
            }
        }

        // Giá trị mặc định cho AQI quá cao
        const hazardousLevel = this.aqiLevels[this.aqiLevels.length - 1];
        return {
            color: hazardousLevel.color,
            status: hazardousLevel.label,
            message: 'Health warning of emergency conditions: everyone is more likely to be affected.'
        };
    }

    showInfoWindow(position, data) {
        if (this.currentInfoWindow) {
            this.currentInfoWindow.close();
        }

        const aqiInfo = this.getAQIInfo(data.aqi);
        
        const content = `
            <div class="info-box p-4 max-w-sm">
                <h3 class="font-bold text-lg mb-2">Air Quality Information</h3>
                <div class="p-3 rounded" style="background-color: ${aqiInfo.color}15">
                    <div class="flex items-center mb-2">
                        <div class="w-4 h-4 rounded mr-2" style="background-color: ${aqiInfo.color}"></div>
                        <div class="text-lg font-bold" style="color: ${aqiInfo.color}">
                            AQI: ${data.aqi}
                        </div>
                    </div>
                    <div class="font-medium" style="color: ${aqiInfo.color}">
                        Status: ${aqiInfo.status}
                    </div>
                    <div class="text-sm mt-2">
                        ${aqiInfo.message}
                    </div>
                    <div class="text-xs text-gray-600 mt-2">
                        Raw value: ${data.raw}
                        <br>
                        Updated: ${data.time}
                    </div>
                </div>
            </div>
        `;

        this.currentInfoWindow = new google.maps.InfoWindow({
            position: position,
            content: content
        });

        this.currentInfoWindow.open(this.map);
    }
}