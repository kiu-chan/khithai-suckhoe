@extends('layouts.app')

@section('title', 'Thông tin sức khỏe - Hệ thống Quan trắc Chất lượng Không khí')

@section('content')
<div class="container mx-auto px-4">
    <!-- Alert Banner -->
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p>Having air quality information to protect your health!</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Map Section with Factories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="relative">
                <img src="{{ asset('images/xi_mang1.jpg') }}" alt="Bản đồ Thái Nguyên" class="w-full rounded-lg">
                
                <!-- Factory Images -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <a href="{{ route('factory.detail', 'luu-xa') }}" class="block">
                        <div class="relative group">
                            <img src="{{ asset('images/Luuxa.png') }}" alt="Luu Xa Cement Company" class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute bottom-0 w-full bg-red-600 text-white text-center py-1 text-sm">
                                Luu Xa Cement Company
                            </div>
                        </div>
                    </a>
                    
                    <a href="{{ route('factory.detail', 'quan-trieu') }}" class="block">
                        <div class="relative group">
                            <img src="{{ asset('images/quantrieu.png') }}" alt="Quan Trieu Cement Joint Stock Company" class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute bottom-0 w-full bg-red-600 text-white text-center py-1 text-sm">
                                Quan Trieu Cement Joint Stock Company
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('factory.detail', 'cao-ngan') }}" class="block">
                        <div class="relative group">
                            <img src="{{ asset('images/Caongan.png') }}" alt="Cao Ngan Cement Joint Stock Company" class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute bottom-0 w-full bg-red-600 text-white text-center py-1 text-sm">
                                Cao Ngan Cement Joint Stock Company
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('factory.detail', 'quang-son') }}" class="block">
                        <div class="relative group">
                            <img src="{{ asset('images/QuangSon.png') }}" alt="Quang Son Cement One Member Co., Ltd" class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute bottom-0 w-full bg-red-600 text-white text-center py-1 text-sm">
                                Quang Son Cement One Member Co., Ltd
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('factory.detail', 'la-hien') }}" class="block col-span-2">
                        <div class="relative group">
                            <img src="{{ asset('images/Lahien.png') }}" alt="La Hien Joint Stock Company" class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute bottom-0 w-full bg-red-600 text-white text-center py-1 text-sm">
                                La Hien Joint Stock Company
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Health Impact Information -->
            <div>
                <img src="{{ asset('images/health/health-impacts.jpg') }}" alt="Tác động sức khỏe" class="w-full rounded-lg">
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .factory-image:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
    }
</style>
@endpush