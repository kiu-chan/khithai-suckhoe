<table class="min-w-full border border-gray-300">
    <thead>
        <tr>
            <th class="border border-gray-300 bg-blue-500 text-white px-4 py-2">Daily AQI Color</th>
            <th class="border border-gray-300 bg-blue-500 text-white px-4 py-2">Levels of Concern</th>
            <th class="border border-gray-300 bg-blue-500 text-white px-4 py-2">Values of Index</th>
            <th class="border border-gray-300 bg-blue-500 text-white px-4 py-2">Description of Air Quality</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="border border-gray-300 px-4 py-2 bg-green-500 text-white text-center">Green</td>
            <td class="border border-gray-300 px-4 py-2">Good</td>
            <td class="border border-gray-300 px-4 py-2 text-center">0 to 50</td>
            <td class="border border-gray-300 px-4 py-2">Air quality is satisfactory, and air pollution poses little or no risk.</td>
        </tr>
        <tr>
            <td class="border border-gray-300 px-4 py-2 bg-yellow-400 text-center">Yellow</td>
            <td class="border border-gray-300 px-4 py-2">Moderate</td>
            <td class="border border-gray-300 px-4 py-2 text-center">51 to 100</td>
            <td class="border border-gray-300 px-4 py-2">Air quality is acceptable. However, there may be a risk for some people, particularly those who are unusually sensitive to air pollution.</td>
        </tr>
        <tr>
            <td class="border border-gray-300 px-4 py-2 bg-orange-500 text-white text-center">Orange</td>
            <td class="border border-gray-300 px-4 py-2">Unhealthy for Sensitive Groups</td>
            <td class="border border-gray-300 px-4 py-2 text-center">101 to 150</td>
            <td class="border border-gray-300 px-4 py-2">Members of sensitive groups may experience health effects. The general public is less likely to be affected.</td>
        </tr>
        <tr>
            <td class="border border-gray-300 px-4 py-2 bg-red-600 text-white text-center">Red</td>
            <td class="border border-gray-300 px-4 py-2">Unhealthy</td>
            <td class="border border-gray-300 px-4 py-2 text-center">151 to 200</td>
            <td class="border border-gray-300 px-4 py-2">Some members of the general public may experience health effects; members of sensitive groups may experience more serious health effects.</td>
        </tr>
        <tr>
            <td class="border border-gray-300 px-4 py-2 bg-purple-600 text-white text-center">Purple</td>
            <td class="border border-gray-300 px-4 py-2">Very Unhealthy</td>
            <td class="border border-gray-300 px-4 py-2 text-center">201 to 300</td>
            <td class="border border-gray-300 px-4 py-2">Health alert: The risk of health effects is increased for everyone.</td>
        </tr>
        <tr>
            <td class="border border-gray-300 px-4 py-2 bg-red-900 text-white text-center">Maroon</td>
            <td class="border border-gray-300 px-4 py-2">Hazardous</td>
            <td class="border border-gray-300 px-4 py-2 text-center">301 and higher</td>
            <td class="border border-gray-300 px-4 py-2">Health warning of emergency conditions: everyone is more likely to be affected.</td>
        </tr>
    </tbody>
</table>

<div class="mt-4 flex justify-end space-x-4">
    <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded flex items-center">
        <i class="fas fa-print mr-2"></i>Print
    </button>
    <button onclick="downloadData()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center">
        <i class="fas fa-download mr-2"></i>Download
    </button>
</div>