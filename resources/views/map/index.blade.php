<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quan trắc Chất lượng Không khí</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @include('map.styles')
</head>
<body class="h-full flex flex-col">
    <!-- Header -->
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 via-teal-500 to-green-500 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Tiêu đề chính -->
            <h1 class="text-xl font-bold">
                <i class="fas fa-chart-line mr-2"></i>
                Hệ thống Quan trắc Chất lượng Không khí
            </h1>

            <!-- Thanh menu -->
            <nav class="flex space-x-4">
                <a href="#" class="hover:text-gray-300">GIS Map</a>
                <a href="#" class="hover:text-gray-300">Emissions Management</a>
                <a href="#" class="hover:text-gray-300">Real Time Monitoring</a>
                <a href="#" class="hover:text-gray-300">Health Information</a>
            </nav>
        </div>
    </header>

    <main class="flex flex-1 min-h-0">
        @include('map.components')
    </main>

    <footer class="bg-green-100 text-gray-700 py-6">
    <div class="container mx-auto flex flex-col md:flex-row justify-between items-start md:items-center space-y-6 md:space-y-0">
        <!-- Phần thông tin liên hệ -->
        <div class="space-y-2">
            <p class="flex items-center">
                <i class="fas fa-map-marker-alt text-teal-600 mr-2"></i>
                Copyright: GeoInformatics Research Center - TUAF, VietNam
            </p>
            <p class="flex items-center">
                <i class="fas fa-phone-alt text-teal-600 mr-2"></i>
                Phone: +84 094.03.11.03
            </p>
            <p class="flex items-center">
                <i class="fas fa-envelope text-teal-600 mr-2"></i>
                <a href="mailto:trungtamdiatinhoc@tuaf.edu.vn" class="hover:text-teal-600 underline">
                    trungtamdiatinhoc@tuaf.edu.vn
                </a>
            </p>
        </div>

        <!-- Phần thống kê -->
        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div class="flex items-center">
                <i class="fas fa-signal text-teal-600 mr-2"></i>
                <span>Online now: 2</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-calendar-day text-teal-600 mr-2"></i>
                <span>Today: 11</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-calendar-alt text-teal-600 mr-2"></i>
                <span>This month: 1813</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-users text-teal-600 mr-2"></i>
                <span>Total: 52363</span>
            </div>
        </div>
    </div>
</footer>


    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" defer></script>
    @include('map.scripts')
</body>
</html>