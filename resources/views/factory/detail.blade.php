@extends('layouts.app')

@section('title', $factory['name'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-4">{{ $factory['name'] }}</h1>
        <p class="text-gray-700">{{ $factory['description'] }}</p>
        
        <div class="mt-6">
            <a href="{{ route('health-information') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại trang thông tin sức khỏe
            </a>
        </div>
    </div>
</div>
@endsection