<!-- Tạo file mới: resources/views/monitoring/partials/chart-modal.blade.php -->
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartInstance = null;

function showChartModal(value, parameter, limit, title) {
    // Hiển thị modal
    document.getElementById('chartModal').classList.remove('hidden');
    document.getElementById('chartTitle').textContent = title;

    // Tạo biểu đồ
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    
    // Hủy chart cũ nếu có
    if (chartInstance) {
        chartInstance.destroy();
    }

    // Tạo chart mới
    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [parameter],
            datasets: [
                {
                    label: 'Measured Value',
                    data: [value],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'VN Standard',
                    data: [limit],
                    type: 'line',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointStyle: false
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'μg/m³'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Analysis Results - Monitoring Point',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });
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
</script>