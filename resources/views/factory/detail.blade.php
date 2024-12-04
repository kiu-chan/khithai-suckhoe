@extends('layouts.app')

@section('title', $factory['name'])

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <img src="{{ asset($factory['image']) }}" 
             alt="{{ $factory['name'] }}" 
             class="w-full h-64 object-cover">
        
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">{{ $factory['name'] }}</h1>
            <p class="text-gray-700">{{ $factory['description'] }}</p>
            
            <!-- Thêm các thông tin khác về nhà máy -->
        </div>
    </div>
</div>
@endsection