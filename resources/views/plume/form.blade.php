@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Tạo Mô phỏng Phát thải</h1>

    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('plume.generate') }}" method="POST" class="max-w-lg">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tọa độ nguồn X (Kinh độ)</label>
                <input type="number" step="0.000001" name="source_x" value="106.7" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tọa độ nguồn Y (Vĩ độ)</label>
                <input type="number" step="0.000001" name="source_y" value="10.8" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Chiều cao ống khói (m)</label>
                <input type="number" step="0.1" name="stack_height" value="150" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tải lượng phát thải (mg/s)</label>
                <input type="number" step="0.1" name="emission_rate" value="5000" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tốc độ gió (m/s)</label>
                <input type="number" step="0.1" name="wind_speed" value="6" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Hướng gió (độ)</label>
                <input type="number" step="1" name="wind_direction" value="225" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Tạo mô phỏng
            </button>
        </div>
    </form>
</div>
@endsection