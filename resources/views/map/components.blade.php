<!-- Sidebar -->
<div class="w-64 bg-white shadow-lg p-4 overflow-y-auto sidebar">
    <!-- Base Map Selection -->
    <div class="md:hidden flex items-center justify-between mb-4 border-b pb-4">
        <h2 class="text-lg font-semibold text-gray-800">Menu</h2>
        <button class="close-sidebar text-gray-500 hover:text-gray-700">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            <i class="fas fa-map mr-2"></i>Base map
        </label>
        <select class="w-full p-2 border rounded border-gray-300" id="baseMapSelect">
            <option value="roadmap">Streets</option>
            <option value="satellite">Satellite</option>
            <option value="terrain" selected>Terrain</option>
            <option value="hybrid">Hybrid</option>
        </select>
    </div>

    <!-- Collapsible Sections -->
    <div class="space-y-4">
        <!-- Layers Control Section -->
        <div class="border rounded-lg">
            <button class="w-full px-4 py-2 text-left font-medium flex items-center justify-between" onclick="toggleSection('layerSection')">
                <span><i class="fas fa-layer-group mr-2"></i>Display layers</span>
                <i class="fas fa-chevron-down transform transition-transform" id="layerIcon"></i>
            </button>
            <div id="layerSection" class="px-4 pb-4 hidden">
                <div class="space-y-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="aqiStationLayer">
                        <span>AQI monitoring stations</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="factoryLayer">
                        <span>Factories</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="windLayer">
                        <span>Wind direction</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="wmsLayer">
                        <span>AQI overlay</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="laHienLayer">
                        <span>Factory impact</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="populationLayer">
                        <span>Population map</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="thaiNguyenLayer">
                        <span>Thai Nguyen boundary</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="districtLayer">
                        <span>District boundary</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" checked class="form-checkbox text-blue-600" id="communeLayer">
                        <span>Commune boundary</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Opacity Controls Section -->
        <div class="border rounded-lg">
            <button class="w-full px-4 py-2 text-left font-medium flex items-center justify-between" onclick="toggleSection('opacitySection')">
                <span><i class="fas fa-adjust mr-2"></i>Opacity adjustment</span>
                <i class="fas fa-chevron-down transform transition-transform" id="opacityIcon"></i>
            </button>
            <div id="opacitySection" class="px-4 pb-4 hidden">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-2">AQI overlay opacity</label>
                        <input type="range" min="0" max="100" value="70" class="w-full" id="wmsOpacity">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700 mb-2">Factory impact layer opacity</label>
                        <input type="range" min="0" max="100" value="70" class="w-full" id="laHienOpacity">
                    </div>
                </div>
            </div>
        </div>

        <!-- AQI Monitoring Stations Section -->
        <div class="border rounded-lg">
            <button class="w-full px-4 py-2 text-left font-medium flex items-center justify-between" onclick="toggleSection('stationsSection')">
                <span><i class="fas fa-map-marker mr-2"></i>AQI monitoring stations</span>
                <i class="fas fa-chevron-down transform transition-transform" id="stationsIcon"></i>
            </button>
            <div id="stationsSection" class="px-4 pb-4 hidden">
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
                            Updated: {{ $station['measurement_time'] }}
                            </div>
                        </div>
                        @if(isset($station['latest_measurements']))
                        <div class="text-xs mt-1 grid grid-cols-2 gap-1">
                            <div>Dust: {{ $station['latest_measurements']['dust_level'] }} mg/m³</div>
                            <div>CO: {{ $station['latest_measurements']['co_level'] }} mg/m³</div>
                        </div>
                        @endif
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Factories Section -->
        <div class="border rounded-lg">
            <button class="w-full px-4 py-2 text-left font-medium flex items-center justify-between" onclick="toggleSection('factoriesSection')">
                <span><i class="fas fa-industry mr-2"></i>Factories</span>
                <i class="fas fa-chevron-down transform transition-transform" id="factoriesIcon"></i>
            </button>
            <div id="factoriesSection" class="px-4 pb-4 hidden">
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
                            Updated: {{ $factory['aqi_time'] }}
                            </div>
                        </div>
                        @if(isset($factory['latest_measurements']))
                        <div class="text-xs mt-1 grid grid-cols-2 gap-1">
                            <div>Dust: {{ $factory['latest_measurements']['dust_level'] }} mg/m³</div>
                            <div>CO: {{ $factory['latest_measurements']['co_level'] }} mg/m³</div>
                        </div>
                        @endif
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Legends Section -->
        <div class="space-y-4">
            <!-- AQI Legend -->
            <div class="border rounded-lg">
                <button class="w-full px-4 py-2 text-left font-medium flex items-center justify-between" onclick="toggleSection('aqiLegendSection')">
                    <span><i class="fas fa-info-circle mr-2"></i>AQI legend</span>
                    <i class="fas fa-chevron-down transform transition-transform" id="aqiLegendIcon"></i>
                </button>
                <div id="aqiLegendSection" class="px-4 pb-4 hidden">
                    <div class="space-y-1">
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #00E400"></span>
                            <span>0-50: Good</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #FFFF00"></span>
                            <span>51-100: Moderate</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #FF7E00"></span>
                            <span>101-150: Poor</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #FF0000"></span>
                            <span>151-200: Bad</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #8F3F97"></span>
                            <span>201-300: Very bad</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #7E0023"></span>
                            <span>>300: Hazardous</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wind Speed Legend -->
            <div class="border rounded-lg">
                <button class="w-full px-4 py-2 text-left font-medium flex items-center justify-between" onclick="toggleSection('windLegendSection')">
                    <span><i class="fas fa-wind mr-2"></i>Wind speed legend</span>
                    <i class="fas fa-chevron-down transform transition-transform" id="windLegendIcon"></i>
                </button>
                <div id="windLegendSection" class="px-4 pb-4 hidden">
                    <div class="space-y-1">
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #00ff00"></span>
                            <span>< 0.5 m/s: Light</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #ffff00"></span>
                            <span>0.5-1.0 m/s: Moderate</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #ffa500"></span>
                            <span>1.0-1.5 m/s: Strong</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-5 h-5 rounded mr-2" style="background-color: #ff0000"></span>
                            <span>>1.5 m/s: Very strong</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thai Nguyen Areas Section -->
        <div class="border rounded-lg">
            <button class="w-full px-4 py-2 text-left font-medium flex items-center justify-between" onclick="toggleSection('areasSection')">
                <span><i class="fas fa-map-marked-alt mr-2"></i>Areas</span>
                <i class="fas fa-chevron-down transform transition-transform" id="areasIcon"></i>
            </button>
            <div id="areasSection" class="px-4 pb-4">
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
    </div>
</div>

<!-- Map Container -->
<!-- Map Container -->
<div class="flex-1 relative">
    <div id="map" class="w-full h-full"></div>
    <div class="absolute top-4 right-4 z-50 flex flex-col items-end space-y-2">
    <button id="getCurrentLocation" class="bg-white px-4 py-2 rounded-lg shadow-lg hover:bg-gray-100 transition-colors duration-200">
        <i class="fas fa-location-arrow mr-2"></i>
        Vị trí của tôi
    </button>
    <div id="coordinates" class="bg-white px-4 py-2 rounded-lg shadow-lg hidden">
        <div class="text-sm">
            <span class="font-medium">Vĩ độ:</span> <span id="latitude"></span>
        </div>
        <div class="text-sm">
            <span class="font-medium">Kinh độ:</span> <span id="longitude"></span>
        </div>
    </div>
</div>
<!-- Time Control -->
<div class="absolute bottom-4 left-4 bg-white rounded-lg shadow-lg p-4 z-50 w-[600px]">
    <div class="flex items-center justify-between space-x-4">
        <div class="flex space-x-1">
            <button 
                class="now-button px-3 py-1 text-sm rounded bg-green-500 text-white hover:bg-green-600"
                data-mode="current"
            >
                Now
            </button>
            @php
                $currentDate = now();
            @endphp
            @for($i = 0; $i < 6; $i++)
                @php
                    $date = $currentDate->copy()->addDays($i);
                @endphp
                <button 
                    class="day-button px-3 py-1 text-sm rounded {{ $i === 5 ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' }}"
                    data-day="{{ $i }}"
                >
                    {{ $date->format('d/m') }}
                </button>
            @endfor
        </div>
        
        <div class="current-date text-sm font-medium">16/12/2024</div>
    </div>
    
    <!-- Nút chọn giờ -->
    <div class="mt-3 flex items-center justify-between space-x-1">
        @foreach([0, 3, 6, 9, 12, 15, 18, 21] as $hour)
            <button 
                class="hour-button px-2 py-1 text-xs rounded bg-gray-200 hover:bg-gray-300"
                data-hour="{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}"
            >
                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
            </button>
        @endforeach
    </div>
</div>
</div>

<script>
// Giữ nguyên hàm toggleSection cũ
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    const icon = document.getElementById(sectionId.replace('Section', 'Icon'));
    
    section.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

// Thêm xử lý đóng sidebar
document.addEventListener('DOMContentLoaded', function() {
    const closeSidebarBtn = document.querySelector('.close-sidebar');
    const sidebar = document.querySelector('.sidebar');
    
    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }
});
</script>