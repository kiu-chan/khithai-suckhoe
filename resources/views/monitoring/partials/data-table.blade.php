<!-- resources/views/monitoring/partials/data-table.blade.php -->

<!-- Modal Chart -->
<div id="chartModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="bg-white rounded-lg p-8 max-w-4xl mx-auto mt-20">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold" id="chartTitle"></h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="mt-4">
            <canvas id="comparisonChart" width="600" height="400"></canvas>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <div id="chartInfo"></div>
        </div>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-300">
        <thead>
            <tr>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">No</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Code</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Name</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">AQI</th>
                <th colspan="3" class="border border-gray-300 bg-gray-700 text-white px-4 py-2 text-center">Group of physical parameters</th>
                <th colspan="4" class="border border-gray-300 bg-gray-700 text-white px-4 py-2 text-center">Group of chemical parameters</th>
            </tr>
            <tr>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2"></th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2"></th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2"></th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2"></th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Temperature (°C)</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Humidity (%)</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Noise (dBA)</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">TSP (μg/m³)</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Pb dust (μg/m³)</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">SO₂ (μg/m³)</th>
                <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">CO (μg/m³)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td class="border border-gray-300 px-4 py-2">{{ $index + 1 }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $item['code'] }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $item['name'] }}</td>
                <td class="border border-gray-300 px-4 py-2 text-white font-bold cursor-pointer" 
                    style="background-color: {{ getAQIBackgroundColor($item['aqi']) }}"
                    onclick="showChartModal({{ $item['aqi'] ?? 'null' }}, 'AQI', 100, 'AQI Analysis')">
                    {{ $item['aqi'] ?? 'N/A' }}
                </td>
                <td class="border border-gray-300 px-4 py-2">{{ $item['temperature'] ?? 'N/A' }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $item['humidity'] ?? 'N/A' }}</td>
                <td class="border border-gray-300 px-4 py-2 cursor-pointer"
                    style="background-color: {{ getNoiseBackgroundColor($item['noise']) }}"
                    onclick="showChartModal({{ $item['noise'] ?? 'null' }}, 'Noise', 70, 'Noise Level Analysis', 'dBA')">
                    {{ $item['noise'] ?? 'N/A' }}
                </td>
                <td class="border border-gray-300 px-4 py-2 cursor-pointer hover:bg-gray-100"
                    onclick="showChartModal({{ $item['tsp'] ?? 'null' }}, 'TSP', 0.3, 'TSP Analysis', 'μg/m³')">
                    {{ $item['tsp'] ?? 'N/A' }}
                </td>
                <td class="border border-gray-300 px-4 py-2 cursor-pointer hover:bg-gray-100"
                    onclick="showChartModal({{ $item['pb_dust'] ?? 'null' }}, 'Pb Dust', 0.3, 'Pb Dust Analysis', 'μg/m³')">
                    {{ $item['pb_dust'] ?? 'N/A' }}
                </td>
                <td class="border border-gray-300 px-4 py-2 cursor-pointer hover:bg-gray-100"
                    onclick="showChartModal({{ $item['so2'] ?? 'null' }}, 'SO₂', 0.35, 'SO₂ Analysis', 'μg/m³')">
                    {{ $item['so2'] ?? 'N/A' }}
                </td>
                <td class="border border-gray-300 px-4 py-2 cursor-pointer hover:bg-gray-100"
                    onclick="showChartModal({{ $item['co'] ?? 'null' }}, 'CO', 30, 'CO Analysis', 'μg/m³')">
                    {{ $item['co'] ?? 'N/A' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>

<script>
let chartInstance = null;

function showChartModal(value, parameter, limit, title, unit = '') {
    if (!value || value === 'N/A') return;

    // Hiển thị modal
    document.getElementById('chartModal').classList.remove('hidden');
    document.getElementById('chartTitle').textContent = title;

    // Cập nhật thông tin chi tiết
    document.getElementById('chartInfo').innerHTML = `
        <p>Measured Value: ${value} ${unit}</p>
        <p>VN Standard Limit: ${limit} ${unit}</p>
        ${value > limit ? '<p class="text-red-600">⚠️ Exceeds permitted limit</p>' : '<p class="text-green-600">✓ Within permitted limit</p>'}
    `;

    // Tạo biểu đồ
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    
    // Hủy chart cũ nếu có
    if (chartInstance) {
        chartInstance.destroy();
    }

    const data = {
        labels: [parameter],
        datasets: [
            {
                label: 'Measured Value',
                data: [value],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                barPercentage: 0.5
            }
        ]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: unit || 'Value'
                    }
                }
            },
            plugins: {
                annotation: {
                    annotations: {
                        line1: {
                            type: 'line',
                            yMin: limit,
                            yMax: limit,
                            borderColor: 'red',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            label: {
                                display: true,
                                content: `VN Standard: ${limit} ${unit}`,
                                position: 'end'
                            }
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        generateLabels: function(chart) {
                            const defaultLabels = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                            defaultLabels.push({
                                text: 'VN Standard',
                                fillStyle: 'transparent',
                                strokeStyle: 'red',
                                lineWidth: 2,
                                borderDash: [5, 5],
                                hidden: false
                            });
                            return defaultLabels;
                        }
                    }
                }
            }
        }
    };

    // Tạo chart mới
    chartInstance = new Chart(ctx, config);
}

function closeModal() {
    document.getElementById('chartModal').classList.add('hidden');
}

// Đóng modal khi click bên ngoài
document.getElementById('chartModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Đóng modal khi nhấn ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('chartModal').classList.contains('hidden')) {
        closeModal();
    }
});
</script>