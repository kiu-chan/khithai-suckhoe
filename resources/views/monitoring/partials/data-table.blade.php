<table class="min-w-full bg-white border border-gray-300">
    <thead>
        <tr>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">No</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Code</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Name</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">AQI</th>
            <th colspan="3" class="border border-gray-300 bg-gray-700 text-white px-4 py-2 text-center">Group of physical parameters</th>
            <th colspan="4" class="border border-gray-300 bg-gray-700 text-white px-4 py-2 text-center">Group of chemical parameters</th>
        </tr>
        <tr>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2"></th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2"></th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2"></th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2"></th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Temperature (°C)</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Humidity (%)</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Noise (dBA)</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">TSP (μg/m³)</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">Pb dust (μg/m³)</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">SO₂ (μg/m³)</th>
            <th class="border border-gray-300 bg-gray-700 text-white px-4 py-2">CO (μg/m³)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td class="border border-gray-300 px-4 py-2">{{ $index + 1 }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $item['code'] }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $item['name'] }}</td>
            <td class="border border-gray-300 px-4 py-2 text-white font-bold" style="background-color: {{ getAQIBackgroundColor($item['aqi']) }}">
                {{ $item['aqi'] ?? 'N/A' }}
            </td>
            <td class="border border-gray-300 px-4 py-2">{{ $item['temperature'] ?? 'N/A' }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $item['humidity'] ?? 'N/A' }}</td>
            <td class="border border-gray-300 px-4 py-2" style="background-color: {{ getNoiseBackgroundColor($item['noise']) }}">
                {{ $item['noise'] ?? 'N/A' }}
            </td>
            <td class="border border-gray-300 px-4 py-2">{{ $item['tsp'] ?? 'N/A' }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $item['pb_dust'] ?? 'N/A' }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $item['so2'] ?? 'N/A' }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ $item['co'] ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>