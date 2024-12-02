<!-- Sidebar -->
<div class="w-64 bg-white shadow-lg p-4 overflow-y-auto">
    <!-- Base Map Selection -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-map mr-2"></i>Bản đồ nền
        </label>
        <select class="w-full p-2 border rounded border-gray-300" id="baseMapSelect">
            <option value="roadmap">Đường phố</option>
            <option value="satellite">Vệ tinh</option>
            <option value="terrain" selected>Địa hình</option>
            <option value="hybrid">Tổng hợp</option>
        </select>
    </div>

    <!-- Layers Control -->
    <div class="space-y-2 mb-6">
        <h3 class="font-medium text-gray-700">
            <i class="fas fa-layer-group mr-2"></i>Lớp hiển thị
        </h3>
        <div class="space-y-2">
            <label class="flex items-center space-x-2">
                <input type="checkbox" checked class="form-checkbox text-blue-600" id="aqiStationLayer">
                <span>Trạm quan trắc AQI</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" checked class="form-checkbox text-blue-600" id="factoryLayer">
                <span>Nhà máy</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" checked class="form-checkbox text-blue-600" id="windLayer">
                <span>Hướng gió</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" checked class="form-checkbox text-blue-600" id="wmsLayer">
                <span>Lớp phủ AQI</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" checked class="form-checkbox text-blue-600" id="thaiNguyenLayer">
                <span>Ranh giới Thái Nguyên</span>
            </label>
        </div>
    </div>

    <!-- Opacity Controls -->
    <div class="space-y-2 mb-6 border-t pt-4">
        <h3 class="font-medium text-gray-700">
            <i class="fas fa-adjust mr-2"></i>Điều chỉnh độ mờ
        </h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-gray-700 mb-2">Độ mờ lớp phủ AQI</label>
                <input type="range" min="0" max="100" value="70" class="w-full" id="wmsOpacity">
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-2">Độ mờ lớp phủ La Hiên</label>
                <input type="range" min="0" max="100" value="70" class="w-full" id="laHienOpacity">
            </div>
        </div>
    </div>

    <!-- Legends -->
    <div class="space-y-4 mb-6 border-t pt-4">
        <div>
            <h3 class="font-medium text-gray-700 mb-2">
                <i class="fas fa-info-circle mr-2"></i>Chú giải AQI
            </h3>
            <div class="space-y-1">
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #00E400"></span>
                    <span>0-50: Tốt</span>
                </div>
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #FFFF00"></span>
                    <span>51-100: Trung bình</span>
                </div>
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #FF7E00"></span>
                    <span>101-150: Kém</span>
                </div>
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #FF0000"></span>
                    <span>151-200: Xấu</span>
                </div>
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #8F3F97"></span>
                    <span>201-300: Rất xấu</span>
                </div>
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #7E0023"></span>
                    <span>>300: Nguy hại</span>
                </div>
            </div>
        </div>

        <div>
            <h3 class="font-medium text-gray-700 mb-2">
                <i class="fas fa-wind mr-2"></i>Chú giải tốc độ gió
            </h3>
            <div class="space-y-1">
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #00ff00"></span>
                    <span>< 0.5 m/s: Nhẹ</span>
                </div>
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #ffff00"></span>
                    <span>0.5-1.0 m/s: Trung bình</span>
                </div>
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #ffa500"></span>
                    <span>1.0-1.5 m/s: Mạnh</span>
                </div>
                <div class="flex items-center">
                    <span class="w-5 h-5 rounded mr-2" style="background-color: #ff0000"></span>
                    <span>>1.5 m/s: Rất mạnh</span>
                </div>
            </div>
        </div>
    </div>

    <!-- La Hien Plume Layer Control -->
    <div class="space-y-2 mb-6 border-t pt-4">
        <h3 class="font-medium text-gray-700">
            <i class="fas fa-wind mr-2"></i>Lớp phủ La Hiên
        </h3>
        <div class="space-y-2">
            <label class="flex items-center space-x-2">
                <input type="checkbox" checked class="form-checkbox text-blue-600" id="laHienLayer">
                <span>Hiển thị lớp phủ</span>
            </label>
        </div>
    </div>

    <!-- AQI Monitoring Stations List -->
    <div class="border-t pt-4">
        <h3 class="font-medium text-gray-700 mb-2">
            <i class="fas fa-map-marker mr-2"></i>Trạm quan trắc AQI
        </h3>
        <div class="space-y-2 max-h-48 overflow-y-auto text-sm">
            @foreach($monitoringStations as $station)
            <div class="station-item p-2 rounded cursor-pointer hover:bg-gray-100" 
                 data-id="{{ $station['id'] }}">
                <div class="font-medium flex items-center">
                    <span class="w-3 h-3 rounded-full mr-2" 
                          style="background-color: {{ $station['aqi_color'] }}">
                    </span>
                    {{ $station['name'] }}
                </div>
                <div class="text-gray-600 text-xs mt-1">{{ $station['code'] }}</div>
                @if($station['measurement_time'])
                <div class="text-xs mt-1" style="color: {{ $station['aqi_color'] }}">
                    AQI: {{ $station['aqi'] }} - {{ $station['aqi_status'] }}
                    <div class="text-gray-600">
                        Cập nhật: {{ $station['measurement_time'] }}
                    </div>
                </div>
                @if(isset($station['latest_measurements']))
                <div class="text-xs mt-1 grid grid-cols-2 gap-1">
                    <div>Bụi: {{ $station['latest_measurements']['dust_level'] }} mg/m³</div>
                    <div>CO: {{ $station['latest_measurements']['co_level'] }} mg/m³</div>
                </div>
                @endif
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Factories List -->
    <div class="border-t pt-4 mt-4">
        <h3 class="font-medium text-gray-700 mb-2">
            <i class="fas fa-industry mr-2"></i>Nhà máy
        </h3>
        <div class="space-y-2 max-h-48 overflow-y-auto text-sm">
            @foreach($factories as $factory)
            <div class="factory-item p-2 rounded cursor-pointer hover:bg-gray-100" 
                 data-id="{{ $factory['id'] }}">
                <div class="font-medium flex items-center">
                    <span class="w-3 h-3 rounded-full mr-2" 
                          style="background-color: {{ $factory['aqi_color'] }}">
                    </span>
                    {{ $factory['name'] }}
                </div>
                <div class="text-gray-600 text-xs mt-1">{{ $factory['code'] }}</div>
                @if($factory['aqi_time'])
                <div class="text-xs mt-1" style="color: {{ $factory['aqi_color'] }}">
                    AQI: {{ $factory['aqi'] }} - {{ $factory['aqi_status'] }}
                    <div class="text-gray-600">
                        Cập nhật: {{ $factory['aqi_time'] }}
                    </div>
                </div>
                @if(isset($factory['latest_measurements']))
                <div class="text-xs mt-1 grid grid-cols-2 gap-1">
                    <div>Bụi: {{ $factory['latest_measurements']['dust_level'] }} mg/m³</div>
                    <div>CO: {{ $factory['latest_measurements']['co_level'] }} mg/m³</div>
                </div>
                @endif
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Thai Nguyen Areas -->
    <div class="border-t pt-4 mt-4">
        <h3 class="font-medium text-gray-700 mb-2">
            <i class="fas fa-map-marked-alt mr-2"></i>Khu vực
        </h3>
        <div class="space-y-2 max-h-48 overflow-y-auto text-sm">
            @foreach($thaiNguyenBoundaries as $area)
            <div class="area-item p-2 rounded cursor-pointer hover:bg-gray-100" 
                 data-id="{{ $area['id'] }}">
                {{ $area['name'] }}
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Map Container -->
<div class="flex-1 relative">
    <div id="map" class="w-full h-full"></div>
</div>