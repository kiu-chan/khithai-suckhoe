@extends('layouts.app')

@section('title', 'Air Environment Monitoring Data')

@section('content')
<main class="flex-1 container mx-auto px-4 py-6">
    <!-- Alert Banner -->
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="ml-3">
                <p>Having air quality information to protect your health!</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Title -->
        <div class="mb-6">
            <h2 class="text-xl font-bold bg-blue-600 text-white py-2 px-4 rounded-lg inline-flex items-center">
                <i class="fas fa-chart-bar mr-2"></i>
                Air environment monitoring data
            </h2>
        </div>

        <!-- Date Title -->
        <div class="text-center mb-6">
            <h3 class="text-lg font-semibold text-gray-700">Air environment monitoring data - 1<sup>st</sup> 2024</h3>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border">
                <thead>
                    <tr class="bg-[#2F4F4F] text-white text-center">
                        <th rowspan="2" class="border px-4 py-2">No</th>
                        <th rowspan="2" class="border px-4 py-2">Code</th>
                        <th rowspan="2" class="border px-4 py-2">AQI</th>
                        <th colspan="3" class="border px-4 py-2">Group of physical parameters</th>
                        <th colspan="7" class="border px-4 py-2">Group of chemical parameters</th>
                    </tr>
                    <tr class="bg-[#2F4F4F] text-white text-center">
                        <th class="border px-2 py-1">Temperature (°C)</th>
                        <th class="border px-2 py-1">Humidity (%)</th>
                        <th class="border px-2 py-1">Noise (dBA)</th>
                        <th class="border px-2 py-1">Total Suspended Particles (TSP) (μg/m³)</th>
                        <th class="border px-2 py-1">Pb dust (μg/m³)</th>
                        <th class="border px-2 py-1">NO₂ (μg/m³)</th>
                        <th class="border px-2 py-1">SO₂ (μg/m³)</th>
                        <th class="border px-2 py-1">CO (μg/m³)</th>
                        <th class="border px-2 py-1">PM₁₀ (μg/m³)</th>
                        <th class="border px-2 py-1">PM₂.₅ (μg/m³)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center hover:bg-gray-50">
                        <td class="border px-2 py-1">1</td>
                        <td class="border px-2 py-1">K1</td>
                        <td class="border px-2 py-1 bg-red-500 text-white">174</td>
                        <td class="border px-2 py-1">90</td>
                        <td class="border px-2 py-1">82</td>
                        <td class="border px-2 py-1 bg-red-500 text-white">200</td>
                        <td class="border px-2 py-1">0.60</td>
                        <td class="border px-2 py-1">0.100</td>
                        <td class="border px-2 py-1 bg-red-500 text-white">0.8</td>
                        <td class="border px-2 py-1">20</td>
                        <td class="border px-2 py-1">30</td>
                        <td class="border px-2 py-1">120</td>
                        <td class="border px-2 py-1">60</td>
                    </tr>
                    <tr class="text-center hover:bg-gray-50">
                        <td class="border px-2 py-1">2</td>
                        <td class="border px-2 py-1">K2</td>
                        <td class="border px-2 py-1 bg-yellow-300">95</td>
                        <td class="border px-2 py-1">88</td>
                        <td class="border px-2 py-1">78</td>
                        <td class="border px-2 py-1">180</td>
                        <td class="border px-2 py-1">0.55</td>
                        <td class="border px-2 py-1">0.090</td>
                        <td class="border px-2 py-1">0.6</td>
                        <td class="border px-2 py-1">18</td>
                        <td class="border px-2 py-1">25</td>
                        <td class="border px-2 py-1">100</td>
                        <td class="border px-2 py-1">50</td>
                    </tr>
                    <tr class="text-center hover:bg-gray-50">
                        <td class="border px-2 py-1">3</td>
                        <td class="border px-2 py-1">K3</td>
                        <td class="border px-2 py-1 bg-green-500 text-white">45</td>
                        <td class="border px-2 py-1">85</td>
                        <td class="border px-2 py-1">75</td>
                        <td class="border px-2 py-1">160</td>
                        <td class="border px-2 py-1">0.48</td>
                        <td class="border px-2 py-1">0.080</td>
                        <td class="border px-2 py-1">0.5</td>
                        <td class="border px-2 py-1">15</td>
                        <td class="border px-2 py-1">20</td>
                        <td class="border px-2 py-1">80</td>
                        <td class="border px-2 py-1">40</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- AQI Legend -->
        <div class="mt-8">
            <h4 class="font-bold mb-4">Note:</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#4682B4] text-white text-center">
                            <th class="border px-4 py-2">Daily AQI Color</th>
                            <th class="border px-4 py-2">Levels of Concern</th>
                            <th class="border px-4 py-2">Values of Index</th>
                            <th class="border px-4 py-2">Description of Air Quality</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-4 py-2 bg-green-500 text-center text-white">Green</td>
                            <td class="border px-4 py-2 text-center">Good</td>
                            <td class="border px-4 py-2 text-center">0 to 50</td>
                            <td class="border px-4 py-2">Air quality is satisfactory, and air pollution poses little or no risk.</td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2 bg-yellow-300 text-center">Yellow</td>
                            <td class="border px-4 py-2 text-center">Moderate</td>
                            <td class="border px-4 py-2 text-center">51 to 100</td>
                            <td class="border px-4 py-2">Air quality is acceptable. However, there may be a risk for some people, particularly those who are unusually sensitive to air pollution.</td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2 bg-orange-500 text-center text-white">Orange</td>
                            <td class="border px-4 py-2 text-center">Unhealthy for Sensitive Groups</td>
                            <td class="border px-4 py-2 text-center">101 to 150</td>
                            <td class="border px-4 py-2">Members of sensitive groups may experience health effects. The general public is less likely to be affected.</td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2 bg-red-500 text-center text-white">Red</td>
                            <td class="border px-4 py-2 text-center">Unhealthy</td>
                            <td class="border px-4 py-2 text-center">151 to 200</td>
                            <td class="border px-4 py-2">Some members of the general public may experience health effects; members of sensitive groups may experience more serious health effects.</td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2 bg-purple-600 text-center text-white">Purple</td>
                            <td class="border px-4 py-2 text-center">Very Unhealthy</td>
                            <td class="border px-4 py-2 text-center">201 to 300</td>
                            <td class="border px-4 py-2">Health alert: The risk of health effects is increased for everyone.</td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2 bg-red-900 text-center text-white">Maroon</td>
                            <td class="border px-4 py-2 text-center">Hazardous</td>
                            <td class="border px-4 py-2 text-center">301 and higher</td>
                            <td class="border px-4 py-2">Health warning of emergency conditions: everyone is more likely to be affected.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reference Values -->
        <div class="mt-8">
            <h4 class="font-bold mb-4">Reference Values According to National Technical Regulation:</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#4682B4] text-white text-center">
                            <th class="border px-4 py-2">Parameter</th>
                            <th class="border px-4 py-2">Unit</th>
                            <th class="border px-4 py-2">Average Time</th>
                            <th class="border px-4 py-2">Limit Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2">Total Suspended Particles (TSP)</td>
                            <td class="border px-4 py-2 text-center">μg/m³</td>
                            <td class="border px-4 py-2 text-center">24 hours</td>
                            <td class="border px-4 py-2 text-center">300</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2">PM₁₀</td>
                            <td class="border px-4 py-2 text-center">μg/m³</td>
                            <td class="border px-4 py-2 text-center">24 hours</td>
                            <td class="border px-4 py-2 text-center">150</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2">PM₂.₅</td>
                            <td class="border px-4 py-2 text-center">μg/m³</td>
                            <td class="border px-4 py-2 text-center">24 hours</td>
                            <td class="border px-4 py-2 text-center">50</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-end space-x-4">
            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-print mr-2"></i>Print
            </button>
            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors duration-200">
                <i class="fas fa-download mr-2"></i>Download
            </button>
        </div>
    </div>
</main>
@endsection