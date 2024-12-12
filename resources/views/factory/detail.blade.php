@extends('layouts.app')

@section('title', $factory['name'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Alert Banner -->
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <p>Having air quality information to protect your health!</p>
        </div>
    </div>

    <!-- Factory Details -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header Section -->
        <div class="relative h-64">
            <img src="{{ asset($factory['image']) }}" 
                 alt="{{ $factory['name'] }}" 
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                <div class="text-center text-white">
                    <h1 class="text-3xl font-bold mb-2">{{ $factory['name'] }}</h1>
                    <p class="text-xl">Code number: {{ $factory['code'] }}</p>
                    @if(isset($factory['last_updated']))
                        <p class="text-sm mt-2">Last updated: {{ $factory['last_updated'] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-6">
            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Basic information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="mb-2"><strong class="text-gray-700">Address:</strong> {{ $factory['address'] }}</p>
                        <p class="text-gray-600">{{ $factory['description'] }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($factory['stats'] as $key => $value)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 uppercase">{{ $key }}</p>
                                <p class="text-lg font-semibold">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if(isset($factory['aqi']))
            <!-- Current AQI -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Current Air Quality Index (AQI)</h2>
                <div class="bg-gray-50 p-6 rounded-lg" style="border-left: 4px solid {{ $factory['aqi']['color'] }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-4xl font-bold">{{ $factory['aqi']['value'] }}</p>
                            <p class="text-xl text-gray-600">{{ $factory['aqi']['status'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($factory['weather_metrics']))
            <!-- Weather Metrics -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Weather conditions</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @foreach($factory['weather_metrics'] as $key => $value)
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-blue-600 uppercase">{{ $key }}</p>
                            <p class="text-lg font-semibold">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($factory['environmental_metrics']))
            <!-- Environmental Metrics -->
            <div>
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Environmental indicators</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @foreach($factory['environmental_metrics'] as $key => $value)
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-green-600 uppercase">{{ $key }}</p>
                            <p class="text-lg font-semibold">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Back Button -->
            <div class="mt-8">
                <a href="{{ route('health.index') }}" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Return to health information page
                </a>
            </div>
        </div>
    </div>
</div>
@endsection