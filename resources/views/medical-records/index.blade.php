{{-- resources/views/medical-records/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Medical Records')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-4 py-2">
                <h2 class="text-lg font-semibold">Calculation of dust pollution diffusion from cement factory emissions</h2>
            </div>
            
            <div class="p-4">
                <!-- Chart Buttons -->
                <div class="mb-4 flex gap-4">
                    <button onclick="openChartModal('symptomsChart')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition duration-200">
                        Worker Symptoms Chart
                    </button>
                    <button onclick="openChartModal('diseasesChart')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-200">
                        Common Diseases Chart
                    </button>
                    <button onclick="openChartModal('bronchitisChart')" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded transition duration-200">
                        Bronchitis Rate Chart
                    </button>
                </div>

                <!-- Filter Section -->
                <div class="mb-4 flex items-center gap-4">
                    <form action="{{ route('medical-records.index') }}" method="GET" class="flex items-center gap-4">
                        <div class="flex items-center">
                            <label class="mr-2">Illness:</label>
                            <select name="illness" class="border rounded px-4 py-2 w-48">
                                <option value="">All illnesses</option>
                                @foreach($illnesses as $illness)
                                    <option value="{{ $illness }}" {{ $selectedIllness == $illness ? 'selected' : '' }}>
                                        {{ $illness }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-2 rounded transition duration-200">
                            Filter
                        </button>
                        <a href="{{ route('medical-records.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-2 rounded transition duration-200">
                            Reset
                        </a>
                    </form>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th class="border px-4 py-2">No</th>
                                <th class="border px-4 py-2">Name of patient</th>
                                <th class="border px-4 py-2">Date of birth</th>
                                <th class="border px-4 py-2">Date of check up</th>
                                <th class="border px-4 py-2">Address</th>
                                <th class="border px-4 py-2">Illness</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medicalRecords as $index => $record)
                            <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="border px-4 py-2">{{ $index + 1 }}</td>
                                <td class="border px-4 py-2">{{ $record['patient_name'] }}</td>
                                <td class="border px-4 py-2">{{ $record['date_of_birth'] }}</td>
                                <td class="border px-4 py-2">{{ $record['checkup_date'] }}</td>
                                <td class="border px-4 py-2">{{ $record['address'] }}</td>
                                <td class="border px-4 py-2">{{ $record['illness'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Symptoms Chart Modal -->
<div id="symptomsChartModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Distribution of workers according to functional symptoms and systemic symptoms</h3>
            <button onclick="closeChartModal('symptomsChart')" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="w-full h-[500px]">
            <canvas id="symptomsChart"></canvas>
        </div>
    </div>
</div>

<!-- Common Diseases Chart Modal -->
<div id="diseasesChartModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Chart of common diseases of people in the adjacent area, 2023</h3>
            <button onclick="closeChartModal('diseasesChart')" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="w-full h-[500px]">
            <canvas id="diseasesChart"></canvas>
        </div>
    </div>
</div>

<!-- Bronchitis Chart Modal -->
<div id="bronchitisChartModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Chart of bronchitis rate in people in adjacent areas</h3>
            <button onclick="closeChartModal('bronchitisChart')" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="w-full h-[500px]">
            <canvas id="bronchitisChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script>
    const charts = {
        symptomsChart: null,
        diseasesChart: null,
        bronchitisChart: null
    };

    function openChartModal(chartId) {
        document.getElementById(`${chartId}Modal`).classList.remove('hidden');
        document.getElementById(`${chartId}Modal`).classList.add('flex');
        if (!charts[chartId]) {
            createChart(chartId);
        }
    }

    function closeChartModal(chartId) {
        document.getElementById(`${chartId}Modal`).classList.add('hidden');
        document.getElementById(`${chartId}Modal`).classList.remove('flex');
    }

    function createChart(chartId) {
        const ctx = document.getElementById(chartId).getContext('2d');
        let config;

        switch(chartId) {
            case 'symptomsChart':
                config = {
                    type: 'bar',
                    data: {
                        labels: ['Cough', 'Chest pain', 'Sputum production', 'Shortness of breath', 
                                'Runny nose', 'Hoarseness', 'Wheezing', 'Tired', 'Weight loss'],
                        datasets: [
                            {
                                label: 'No',
                                data: [670, 740, 660, 750, 715, 720, 750, 735, 730],
                                backgroundColor: 'rgb(59, 130, 246)',
                                stack: 'Stack 0',
                            },
                            {
                                label: 'Yes',
                                data: [100, 30, 110, 20, 55, 50, 20, 35, 40],
                                backgroundColor: 'rgb(249, 115, 22)',
                                stack: 'Stack 0',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                max: 780
                            }
                        }
                    }
                };
                break;

            case 'diseasesChart':
                config = {
                    type: 'bar',
                    data: {
                        labels: ['Respiratory', 'Ear, Nose, Throat', 'Dental and Maxillofacial', 'Dermatology', 'Other'],
                        datasets: [{
                            label: '2010',
                            data: [150, 750, 550, 150, 900],
                            backgroundColor: 'rgb(59, 130, 246)'
                        }, {
                            label: '2023',
                            data: [225, 980, 675, 200, 420],
                            backgroundColor: 'rgb(249, 115, 22)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Chart of common diseases of people in the adjacent area, 2023'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 1200
                            }
                        }
                    }
                };
                break;

            case 'bronchitisChart':
                config = {
                    type: 'bar',
                    data: {
                        labels: ['1', '2'],
                        datasets: [{
                            label: 'Bronchitis',
                            data: [40, 100],
                            backgroundColor: 'rgb(59, 130, 246)'
                        }, {
                            label: 'Other respiratory diseases',
                            data: [110, 125],
                            backgroundColor: 'rgb(249, 115, 22)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Chart of bronchitis rate in people in adjacent areas',
                                font: { 
                                    size: 16 
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 140
                            }
                        }
                    }
                };
                break;
        }

        charts[chartId] = new Chart(ctx, config);
    }

    // Close modal when clicking outside
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                const chartId = this.id.replace('Modal', '');
                closeChartModal(chartId);
            }
        });
    });

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id$="Modal"]').forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    const chartId = modal.id.replace('Modal', '');
                    closeChartModal(chartId);
                }
            });
        }
    });
</script>
@endpush

@push('scripts')
    <script src="{{ asset('js/medical-records.js') }}"></script>
@endpush