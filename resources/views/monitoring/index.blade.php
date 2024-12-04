@extends('layouts.app')

@section('title', 'Real Time Monitoring')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p>Having air quality information to protect your health!</p>
            </div>
        </div>

        <!-- Monitoring Stations Data -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="bg-blue-600 text-white px-4 py-2">
                <h2 class="text-lg font-semibold">Monitoring Stations Data</h2>
            </div>
            
            <div class="p-4">
                <h3 class="text-center mb-4">Air environment monitoring data - {{ Carbon\Carbon::now()->format('jS Y') }}</h3>
                <div class="overflow-x-auto">
                    @include('monitoring.partials.data-table', ['data' => $stationData])
                </div>
            </div>
        </div>

        <!-- Factory Data -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="bg-blue-600 text-white px-4 py-2">
                <h2 class="text-lg font-semibold">Factory Monitoring Data</h2>
            </div>
            
            <div class="p-4">
                <div class="overflow-x-auto">
                    @include('monitoring.partials.data-table', ['data' => $factoryData])
                </div>
            </div>
        </div>

        <!-- AQI Basics Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-4 py-2">
                <h2 class="text-lg font-semibold">AQI Basics for Ozone and Particle Pollution</h2>
            </div>
            
            <div class="p-4">
                <div class="overflow-x-auto">
                    @include('monitoring.partials.aqi-table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function downloadData() {
    // Implement download functionality
    alert('Download feature will be implemented based on your requirements');
}

// Auto refresh every 5 minutes
setInterval(() => {
    window.location.reload();
}, 300000);
</script>
@endpush

@php
function getAQIBackgroundColor($aqi) {
    if (!$aqi) return '#808080';
    if ($aqi <= 50) return '#00e400';
    if ($aqi <= 100) return '#ffff00';
    if ($aqi <= 150) return '#ff7e00';
    if ($aqi <= 200) return '#ff0000';
    if ($aqi <= 300) return '#8f3f97';
    return '#7e0023';
}

function getNoiseBackgroundColor($noise) {
    if (!$noise) return '#ffffff';
    if ($noise > 70) return '#ff0000';
    return '#ffffff';
}
@endphp