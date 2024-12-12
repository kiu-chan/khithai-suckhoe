export class MapInteractions {
    constructor(map, markerManager, layerManager) {
        this.map = map;
        this.markerManager = markerManager;
        this.layerManager = layerManager;
        this.currentInfoWindow = null;
        this.currentLocationMarker = null;
        
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

        this.setupTimeControls();
        this.setupMapClickHandler();
        this.setupLocationControl();
    }

    setupTimeControls() {
        // Lấy tất cả các nút thời gian
        const timeSlotButtons = document.querySelectorAll('.time-slot-button');
        
        // Xử lý click cho từng nút
        timeSlotButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                console.log('Button clicked:', e.target.textContent.trim());
                console.log('DateTime value:', e.target.dataset.datetime);
                
                // Bỏ highlighting của tất cả các nút
                timeSlotButtons.forEach(btn => {
                    btn.classList.remove('bg-green-500', 'bg-blue-600', 'text-white');
                    btn.classList.add('bg-gray-200');
                });
                
                // Highlight nút được chọn
                e.target.classList.remove('bg-gray-200');
                
                // Lấy thời gian từ data-datetime
                const datetime = e.target.dataset.datetime;
                const selectedDate = new Date(datetime);
                const hour = selectedDate.getHours().toString().padStart(2, '0');
                
                // Tính toán số ngày chênh lệch so với hiện tại
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                selectedDate.setHours(0, 0, 0, 0);
                const dayDiff = Math.round((selectedDate - today) / (1000 * 60 * 60 * 24));
    
                // Nếu là nút "Now"
                if (e.target.textContent.trim() === 'Now') {
                    console.log('Switching to current data view');
                    e.target.classList.add('bg-green-500', 'text-white');
                    // Hiển thị dữ liệu hiện tại
                    this.layerManager.showCurrentLayers();
                } else {
                    console.log('Switching to forecast view:', {
                        selectedDateTime: datetime,
                        dayDifference: dayDiff,
                        hour: hour
                    });
                    e.target.classList.add('bg-blue-600', 'text-white');
                    this.layerManager.updateForecastLayers(dayDiff.toString(), hour);
                }
    
                console.log(dayDiff.toString());
                // Luôn cập nhật wind markers, kể cả khi dayDiff = 0
                await this.updateWindMarkers(dayDiff.toString(), hour);
                console.log('Update completed for time slot:', e.target.textContent.trim());
            });
        });
        
        console.log('Time controls setup completed');
        console.log('Total time slots:', timeSlotButtons.length);
    }

    setupLocationControl() {
        const locationButton = document.getElementById('getCurrentLocation');
        const coordinatesDiv = document.getElementById('coordinates');
        const latitudeSpan = document.getElementById('latitude');
        const longitudeSpan = document.getElementById('longitude');
        const closeButton = document.getElementById('closeCoordinates');

        // Thêm xử lý sự kiện cho nút đóng
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                coordinatesDiv.classList.add('hidden');
                if (this.currentLocationMarker) {
                    this.currentLocationMarker = null;
                }
            });
        }
        if (locationButton) {
            locationButton.addEventListener('click', async () => {
                if (navigator.geolocation) {
                    locationButton.disabled = true;
                    locationButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Searching...';

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };

                            // Hiển thị tọa độ
                            latitudeSpan.textContent = pos.lat.toFixed(6);
                            longitudeSpan.textContent = pos.lng.toFixed(6);
                            coordinatesDiv.classList.remove('hidden');

                            // Lấy và hiển thị AQI
                            const aqiData = await this.checkAQIAtLocation(pos.lat, pos.lng);
                            this.updateAQIDisplay(aqiData);

                            // Di chuyển map đến vị trí hiện tại
                            this.map.setCenter(pos);
                            this.map.setZoom(15);

                            // Đặt marker tại vị trí hiện tại
                            if (this.currentLocationMarker) {
                                this.currentLocationMarker.setMap(null);
                            }
                            this.currentLocationMarker = new google.maps.Marker({
                                position: pos,
                                map: this.map,
                                title: 'Your location',
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    fillColor: '#4285F4',
                                    fillOpacity: 1,
                                    strokeColor: '#ffffff',
                                    strokeWeight: 2,
                                    scale: 8
                                }
                            });

                            // Reset button
                            locationButton.disabled = false;
                            locationButton.innerHTML = '<i class="fas fa-location-arrow mr-2"></i>My location';
                        },
                        (error) => {
                            console.error('Error getting location:', error);
                            alert('Unable to get your location. Please check location access permissions.');
                            locationButton.disabled = false;
                            locationButton.innerHTML = '<i class="fas fa-location-arrow mr-2"></i>My location';
                        }
                    );
                } else {
                    alert('Your browser does not support geolocation.');
                }
            });
        }
    }

    async checkAQIAtLocation(lat, lng) {
        const isInThaiNguyen = await this.isPointInThaiNguyen(lat, lng);
        
        if (isInThaiNguyen) {
            return await this.getInterpolatedAQI(lat, lng);
        } else {
            return await this.getIQAirAQI(lat, lng);
        }
    }

    async isPointInThaiNguyen(lat, lng) {
        const point = new google.maps.LatLng(lat, lng);
        for (const area of this.layerManager.thaiNguyenPolygons) {
            if (google.maps.geometry.poly.containsLocation(point, area.polygon)) {
                return true;
            }
        }
        return false;
    }

    async getInterpolatedAQI(lat, lng) {
        try {
            const aqiData = await this.getAQIData(lat, lng);
            if (aqiData) {
                return {
                    aqi: aqiData.aqi,
                    source: 'interpolated',
                    message: this.getAQIInfo(aqiData.aqi)
                };
            }
            return null;
        } catch (error) {
            console.error('Error getting interpolated AQI data:', error);
            return null;
        }
    }

    async getIQAirAQI(lat, lng) {
        try {
            const apiKey = '9c1ee915-0f53-4dd1-a1d8-17843b884cf0';
            const response = await fetch(
                `https://api.airvisual.com/v2/nearest_city?lat=${lat}&lon=${lng}&key=${apiKey}`
            );
            
            if (!response.ok) {
                throw new Error('Cannot get data from IQAir');
            }
            
            const data = await response.json();
            return {
                aqi: data.data.current.pollution.aqius,
                source: 'iqair',
                message: this.getAQIInfo(data.data.current.pollution.aqius)
            };
        } catch (error) {
            console.error('Error getting data from IQAir:', error);
            return null;
        }
    }

    updateAQIDisplay(aqiData) {
        const aqiValueDiv = document.getElementById('aqi-value');
        if (!aqiValueDiv) return;

        if (!aqiData) {
            aqiValueDiv.innerHTML = '<span class="text-red-500">No data</span>';
            return;
        }

        const aqiInfo = aqiData.message;
        aqiValueDiv.innerHTML = `
            <div class="p-2 rounded" style="background-color: ${aqiInfo.color}15">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${aqiInfo.color}"></div>
                    <div class="font-medium" style="color: ${aqiInfo.color}">
                        AQI: ${aqiData.aqi} - ${aqiInfo.status}
                    </div>
                </div>
                <div class="text-xs mt-1">
                    ${aqiInfo.message}
                </div>
                <div class="text-xs mt-1 text-gray-500">
                    Source: ${aqiData.source === 'interpolated' ? 'Thai Nguyen Interpolation' : 'IQAir'}
                </div>
            </div>
        `;
    }

    async updateWindMarkers(day, hour) {
        console.log('updateWindMarkers called with:', { day, hour });  // Thêm log này
        this.markerManager.clearWindMarkers();
    
        if (day === "0" && hour === "00") {  // Đây là vấn đề - điều kiện quá hẹp
            console.log('Using current wind data');
            window.factories.forEach(factory => {
                if (factory.weather_measurements?.wind_direction) {
                    const windArrow = this.markerManager.createWindArrow(
                        { 
                            lat: parseFloat(factory.lat), 
                            lng: parseFloat(factory.lng) 
                        },
                        factory.weather_measurements.wind_direction,
                        factory.weather_measurements.wind_speed
                    );
    
                    if (windArrow) {
                        this.markerManager.windMarkers.push({
                            code: factory.code,
                            marker: windArrow
                        });
                    }
                }
            });
            return;
        }
    
        try {
            const forecastDate = new Date();
            forecastDate.setDate(forecastDate.getDate() + parseInt(day));
            forecastDate.setHours(parseInt(hour), 0, 0, 0);
        
            const year = forecastDate.getFullYear();
            const month = String(forecastDate.getMonth() + 1).padStart(2, '0');
            const dayOfMonth = String(forecastDate.getDate()).padStart(2, '0');
            const hours = String(forecastDate.getHours()).padStart(2, '0');
            const minutes = String(forecastDate.getMinutes()).padStart(2, '0');
            const seconds = String(forecastDate.getSeconds()).padStart(2, '0');
        
            const formattedTime = `${year}-${month}-${dayOfMonth} ${hours}:${minutes}:${seconds}`;
            console.log('Fetching forecast for:', formattedTime);
    
            const response = await fetch(`/api/weather-forecast?forecast_time=${encodeURIComponent(formattedTime)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        
            const forecastData = await response.json();
            console.log('Forecast data received:', forecastData);
        
            forecastData.forEach(forecast => {
                const factory = window.factories.find(f => f.code === forecast.factory_id);
                if (factory) {
                    const windArrow = this.markerManager.createWindArrow(
                        { 
                            lat: parseFloat(factory.lat), 
                            lng: parseFloat(factory.lng) 
                        },
                        forecast.wind_deg,
                        forecast.wind_speed
                    );
        
                    if (windArrow) {
                        windArrow.setMap(this.map);
                        this.markerManager.windMarkers.push({
                            code: factory.code,
                            marker: windArrow
                        });
                    }
                }
            });
        } catch (error) {
            console.error('Error updating wind arrows:', error);
        }
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
        if (grayValue == null) return 0;
 
        for (let i = 0; i < this.aqiLevels.length - 1; i++) {
            const currentLevel = this.aqiLevels[i];
            const nextLevel = this.aqiLevels[i + 1];
            
            if (grayValue >= currentLevel.gray && grayValue <= nextLevel.gray) {
                return Math.round(
                    currentLevel.aqi +
                    (grayValue - currentLevel.gray) *
                    (nextLevel.aqi - currentLevel.aqi) /
                    (nextLevel.gray - currentLevel.gray)
                );
            }
        }
 
        if (grayValue > this.aqiLevels[this.aqiLevels.length - 1].gray) {
            return this.aqiLevels[this.aqiLevels.length - 1].aqi;
        }
 
        return 0;
    }
 
    getAQIInfo(aqi) {
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