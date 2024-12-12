{{-- resources/views/medical-records/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Medical Records')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white px-4 py-2">
                <h2 class="text-lg font-semibold">List of medical check up records</h2>
            </div>
            
            <div class="p-4">
                <!-- Filter Section -->
                <div class="mb-4 flex items-center gap-4">
                    <form action="{{ route('medical-records.index') }}" method="GET" class="flex items-center gap-4">
                        <div class="flex items-center">
                            <label class="mr-2">Illness:</label>
                            <select name="illness" class="border rounded px-4 py-2 w-48">
                                <option value="">All illnesses</option>
                                @foreach($illnesses as $illness)
                                    <option value="{{ $illness }}" {{ $selectedIllness == $illness ? 'selected' : '' }}>
                                        {{ $illness }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-2 rounded transition duration-200">
                            Filter
                        </button>
                        <a href="{{ route('medical-records.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-2 rounded transition duration-200">
                            Cancel
                        </a>
                    </form>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th class="border px-4 py-2">No</th>
                                <th class="border px-4 py-2">Name of patient</th>
                                <th class="border px-4 py-2">Date of birth</th>
                                <th class="border px-4 py-2">Date of check up</th>
                                <th class="border px-4 py-2">Address</th>
                                <th class="border px-4 py-2">Illness</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medicalRecords as $index => $record)
                            <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }}">
                                <td class="border px-4 py-2">{{ $index + 1 }}</td>
                                <td class="border px-4 py-2">{{ $record['patient_name'] }}</td>
                                <td class="border px-4 py-2">{{ $record['date_of_birth'] }}</td>
                                <td class="border px-4 py-2">{{ $record['checkup_date'] }}</td>
                                <td class="border px-4 py-2">{{ $record['address'] }}</td>
                                <td class="border px-4 py-2">{{ $record['illness'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/medical-records.js') }}"></script>
@endpush