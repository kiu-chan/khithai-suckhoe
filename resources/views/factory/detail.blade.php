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
                    <p class="text-xl">Mã số: {{ $factory['code'] }}</p>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-6">
            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Thông tin cơ bản</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="mb-2"><strong class="text-gray-700">Địa chỉ:</strong> {{ $factory['address'] }}</p>
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

            <!-- Environmental Metrics -->
            <div>
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Chỉ số môi trường</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-600 uppercase">Nồng độ bụi</p>
                        <p class="text-lg font-semibold">{{ $factory['environmental_metrics']['dust'] }}</p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <p class="text-sm text-yellow-600 uppercase">SO2</p>
                        <p class="text-lg font-semibold">{{ $factory['environmental_metrics']['so2'] }}</p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-sm text-red-600 uppercase">NOx</p>
                        <p class="text-lg font-semibold">{{ $factory['environmental_metrics']['nox'] }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-green-600 uppercase">CO</p>
                        <p class="text-lg font-semibold">{{ $factory['environmental_metrics']['co'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-8">
                <a href="{{ route('health.index') }}" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Quay lại trang thông tin sức khỏe
                </a>
            </div>
        </div>
    </div>
</div>
@endsection