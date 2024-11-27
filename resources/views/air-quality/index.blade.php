<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quan trắc Môi trường không khí khu vực nhà máy xi măng</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex items-center space-x-3">
                <i class="fas fa-chart-line text-xl md:text-2xl"></i>
                <h1 class="text-xl md:text-2xl font-semibold">Hệ thống Quan trắc Môi trường không khí</h1>
            </div>
            <div class="flex items-center mt-2 text-sm">
                <i class="fas fa-clock mr-2"></i>
                <span>Cập nhật lần cuối: {{ now()->format('H:i:s d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6 mb-6">
            <form action="{{ route('air-quality.search') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-industry mr-2"></i>Vị trí quan trắc
                        </label>
                        <select name="location_code" class="w-full rounded-lg border-gray-300 shadow-sm">
                            <option value="">Tất cả vị trí</option>
                            @foreach($factories as $factory)
                                <option value="{{ $factory->code }}" 
                                    {{ request('location_code') == $factory->code ? 'selected' : '' }}>
                                    {{ $factory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-2"></i>Từ ngày
                        </label>
                        <input type="date" name="start_date" 
                            class="w-full rounded-lg border-gray-300 shadow-sm"
                            value="{{ request('start_date') }}">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Đến ngày
                        </label>
                        <input type="date" name="end_date" 
                            class="w-full rounded-lg border-gray-300 shadow-sm"
                            value="{{ request('end_date') }}">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i>
                            <span>Tìm kiếm</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 divide-y divide-gray-200">
                @forelse($measurements as $measurement)
                    <div class="p-4">
                        <!-- Hiển thị thông tin cơ bản trên mobile -->
                        <div class="md:hidden">
                            <div class="font-medium text-gray-900 mb-2">{{ $measurement->factory->name }}</div>
                            <div class="text-sm text-gray-500 mb-3">
                                {{ Carbon\Carbon::parse($measurement->measurement_time)->format('H:i d/m/Y') }}
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-500">Nhiệt độ:</span>
                                    <span class="font-medium">{{ number_format($measurement->temperature, 1) }}°C</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Độ ẩm:</span>
                                    <span class="font-medium">{{ number_format($measurement->humidity, 1) }}%</span>
                                </div>
                            </div>
                            <button onclick="toggleDetails('details-{{ $measurement->id }}')" 
                                class="mt-3 text-blue-600 text-sm flex items-center">
                                <i class="fas fa-chevron-down mr-1"></i>
                                <span>Xem chi tiết</span>
                            </button>
                            <div id="details-{{ $measurement->id }}" class="hidden mt-3 space-y-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Tốc độ gió:</span>
                                    <span class="font-medium">{{ number_format($measurement->wind_speed, 1) }} m/s</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Tiếng ồn:</span>
                                    <span class="font-medium">{{ number_format($measurement->noise_level, 1) }} dBA</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Độ bụi:</span>
                                    <span class="font-medium">{{ number_format($measurement->dust_level, 2) }} mg/m³</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">CO:</span>
                                    <span class="font-medium">{{ number_format($measurement->co_level, 3) }} mg/m³</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">SO₂:</span>
                                    <span class="font-medium">{{ number_format($measurement->so2_level, 3) }} mg/m³</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">TSP:</span>
                                    <span class="font-medium">{{ number_format($measurement->tsp_level, 3) }} mg/m³</span>
                                </div>
                            </div>
                        </div>

                        <!-- Hiển thị dạng bảng trên desktop -->
                        <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full">
    <thead>
        <tr class="bg-gray-50 text-gray-600 text-sm leading-normal">
            <th class="py-3 px-6 text-left">Vị trí</th>
            <th class="py-3 px-6 text-left">Thời gian</th>
            <th class="py-3 px-6 text-center">Nhiệt độ (°C)</th>
            <th class="py-3 px-6 text-center">Độ ẩm (%)</th>
            <th class="py-3 px-6 text-center">Tốc độ gió (m/s)</th>
            <th class="py-3 px-6 text-center">Tiếng ồn (dBA)</th>
            <th class="py-3 px-6 text-center">Độ bụi (mg/m³)</th>
            <th class="py-3 px-6 text-center">CO (mg/m³)</th>
            <th class="py-3 px-6 text-center">SO₂ (mg/m³)</th>
            <th class="py-3 px-6 text-center">TSP (mg/m³)</th>
        </tr>
    </thead>
    <tbody class="text-gray-600 text-sm">
        @foreach($measurements as $measurement)
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="py-3 px-6">{{ $measurement->factory->name }}</td>
                <td class="py-3 px-6">{{ Carbon\Carbon::parse($measurement->measurement_time)->format('H:i d/m/Y') }}</td>
                <td class="py-3 px-6 text-center">{{ number_format($measurement->temperature, 1) }}</td>
                <td class="py-3 px-6 text-center">{{ number_format($measurement->humidity, 1) }}</td>
                <td class="py-3 px-6 text-center">{{ number_format($measurement->wind_speed, 1) }}</td>
                <td class="py-3 px-6 text-center">{{ number_format($measurement->noise_level, 1) }}</td>
                <td class="py-3 px-6 text-center">{{ number_format($measurement->dust_level, 2) }}</td>
                <td class="py-3 px-6 text-center">{{ number_format($measurement->co_level, 3) }}</td>
                <td class="py-3 px-6 text-center">{{ number_format($measurement->so2_level, 3) }}</td>
                <td class="py-3 px-6 text-center">{{ number_format($measurement->tsp_level, 3) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-database mr-2"></i>Không có dữ liệu quan trắc
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <footer class="container mx-auto px-4 py-6 text-center text-gray-500">
        <p class="text-sm">
            <i class="fas fa-copyright mr-2"></i>{{ date('Y') }} Hệ thống Quan trắc Môi trường không khí khu vực nhà máy xi măng
        </p>
    </footer>

    <script>
        function toggleDetails(elementId) {
            const detailsElement = document.getElementById(elementId);
            const isHidden = detailsElement.classList.contains('hidden');
            
            if (isHidden) {
                detailsElement.classList.remove('hidden');
                const button = event.currentTarget;
                button.querySelector('i').classList.remove('fa-chevron-down');
                button.querySelector('i').classList.add('fa-chevron-up');
                button.querySelector('span').textContent = 'Ẩn chi tiết';
            } else {
                detailsElement.classList.add('hidden');
                const button = event.currentTarget;
                button.querySelector('i').classList.remove('fa-chevron-up');
                button.querySelector('i').classList.add('fa-chevron-down');
                button.querySelector('span').textContent = 'Xem chi tiết';
            }
        }
    </script>
</body>
</html>