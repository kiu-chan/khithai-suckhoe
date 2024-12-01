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
                <input type="checkbox" checked class="form-checkbox text-blue-600" id="factoryLayer">
                <span>Vị trí nhà máy</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" checked class="form-checkbox text-blue-600" id="aqiLayer">
                <span>Chỉ số AQI</span>
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
            <div class="mt-2">
                <label class="block text-sm text-gray-600 mb-1">Độ mờ</label>
                <input type="range" min="0" max="100" value="70" class="w-full" id="laHienOpacity">
            </div>
        </div>
    </div>

    <!-- Factories List -->
    <div class="border-t pt-4">
        <h3 class="font-medium text-gray-700 mb-2">
            <i class="fas fa-industry mr-2"></i>Danh sách nhà máy
        </h3>
        <div class="space-y-2 max-h-96 overflow-y-auto text-sm">
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
                @if($factory['measurement_time'])
                <div class="text-xs mt-1" style="color: {{ $factory['aqi_color'] }}">
                    AQI: {{ $factory['aqi'] }} - {{ $factory['aqi_status'] }}
                </div>
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

    <!-- WMS Opacity Control -->
    <div class="wms-opacity-control">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Độ mờ lớp phủ AQI
        </label>
        <input type="range" min="0" max="100" value="70" class="w-full" id="wmsOpacity">
    </div>

    <!-- AQI Legend -->
    <div class="aqi-legend text-sm">
        <div class="font-medium mb-2">Chú giải AQI</div>
        <div class="space-y-1">
            <div class="flex items-center">
                <span class="aqi-color" style="background-color: #00E400"></span>
                <span>0-50: Tốt</span>
            </div>
            <div class="flex items-center">
                <span class="aqi-color" style="background-color: #FFFF00"></span>
                <span>51-100: Trung bình</span>
            </div>
            <div class="flex items-center">
                <span class="aqi-color" style="background-color: #FF7E00"></span>
                <span>101-150: Kém</span>
            </div>
            <div class="flex items-center">
                <span class="aqi-color" style="background-color: #FF0000"></span>
                <span>151-200: Xấu</span>
            </div>
            <div class="flex items-center">
                <span class="aqi-color" style="background-color: #8F3F97"></span>
                <span>201-300: Rất xấu</span>
            </div>
            <div class="flex items-center">
                <span class="aqi-color" style="background-color: #7E0023"></span>
                <span>>300: Nguy hại</span>
            </div>
        </div>
    </div>
</div>