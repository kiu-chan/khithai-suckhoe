@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Kết quả Mô phỏng Phát thải</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <h2 class="text-xl font-semibold mb-4">Thông số đầu vào</h2>
            <div class="bg-gray-100 p-4 rounded">
                <dl class="grid grid-cols-2 gap-4">
                    <dt class="font-medium">Tọa độ nguồn:</dt>
                    <dd>{{ $params['source_x'] }}, {{ $params['source_y'] }}</dd>
                    
                    <dt class="font-medium">Chiều cao ống khói:</dt>
                    <dd>{{ $params['stack_height'] }} m</dd>
                    
                    <dt class="font-medium">Tải lượng phát thải:</dt>
                    <dd>{{ $params['emission_rate'] }} mg/s</dd>
                    
                    <dt class="font-medium">Tốc độ gió:</dt>
                    <dd>{{ $params['wind_speed'] }} m/s</dd>
                    
                    <dt class="font-medium">Hướng gió:</dt>
                    <dd>{{ $params['wind_direction'] }}°</dd>
                </dl>
            </div>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-4">Kết quả mô phỏng</h2>
            <img src="{{ $plume_url }}" alt="Plume Visualization" class="w-full rounded shadow-lg">
            <div class="mt-4">
                <a href="{{ $plume_url }}" download 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Tải xuống file TIF
                </a>
            </div>
        </div>
    </div>
</div>
@endsection