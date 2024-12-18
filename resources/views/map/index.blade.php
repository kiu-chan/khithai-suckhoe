@extends('layouts.app')

@section('title', 'GIS Map - Hệ thống Quan trắc Chất lượng Không khí')

@section('content')

<div class="flex justify-center w-full">
    <div class="w-[90%] h-[70vh] flex map-container relative">
        <button id="toggleSidebar" class="absolute top-4 left-4 z-10 bg-white p-2 rounded-full shadow-md">
            <i class="fas fa-bars"></i>
        </button>
        @include('map.components')
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    window.mapData = @json($mapData);
    window.monitoringStations = @json($monitoringStations); 
    window.factories = @json($factories);
    window.weatherStations = @json($weatherStations);
    window.thaiNguyenBoundaries = @json($thaiNguyenBoundaries);
</script>
<script type="module" src="{{ asset('js/map/init.js') }}"></script>
@endpush

@push('bottom-scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" defer></script>
@endpush