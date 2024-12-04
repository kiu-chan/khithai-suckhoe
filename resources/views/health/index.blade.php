@extends('layouts.app')

@section('title', 'Thông tin sức khỏe - Hệ thống quan trắc')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Alert Banner -->
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <p>Having air quality information to protect your health!</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <!-- Map Container -->
            <div class="mb-8">
                <img src="{{ asset('images/xi_mang1.jpg') }}" 
                     alt="Bản đồ Thái Nguyên" 
                     class="w-full h-[500px] object-cover rounded-lg shadow-lg">
            </div>

            <!-- Factory Links Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Luu Xa Factory -->
                <a href="{{ route('factory.detail', 'luu-xa') }}" 
                   class="factory-link bg-red-600 text-white p-4 rounded-lg text-center hover:bg-red-700 transition-colors duration-300">
                    Luu Xa Cement Company
                </a>

                <!-- Quan Trieu Factory -->
                <a href="{{ route('factory.detail', 'quan-trieu') }}" 
                   class="factory-link bg-red-600 text-white p-4 rounded-lg text-center hover:bg-red-700 transition-colors duration-300">
                    Quan Trieu Cement Joint Stock Company
                </a>

                <!-- Cao Ngan Factory -->
                <a href="{{ route('factory.detail', 'cao-ngan') }}" 
                   class="factory-link bg-red-600 text-white p-4 rounded-lg text-center hover:bg-red-700 transition-colors duration-300">
                    Cao Ngan Cement Joint Stock Company
                </a>

                <!-- Quang Son Factory -->
                <a href="{{ route('factory.detail', 'quang-son') }}" 
                   class="factory-link bg-red-600 text-white p-4 rounded-lg text-center hover:bg-red-700 transition-colors duration-300">
                    Quang Son Cement One Member Co., Ltd
                </a>

                <!-- La Hien Factory -->
                <a href="{{ route('factory.detail', 'la-hien') }}" 
                   class="factory-link bg-red-600 text-white p-4 rounded-lg text-center hover:bg-red-700 transition-colors duration-300 md:col-span-2">
                    La Hien Joint Stock Company
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .factory-link {
        transition: all 0.3s ease-in-out;
    }
    
    .factory-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
</style>
@endpush