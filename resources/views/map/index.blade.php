<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quan trắc Chất lượng Không khí</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @include('map.styles')
    <style>
        .banner-text {
            font-family: Arial, sans-serif;
            text-shadow: 1px 1px 3px rgba(255, 255, 255, 0.8);
        }
        .footer-content {
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.8);
        }
    </style>
    <script type="module">
        window.mapData = @json($mapData);
        window.monitoringStations = @json($monitoringStations);
        window.factories = @json($factories);
        window.weatherStations = @json($weatherStations);
        window.thaiNguyenBoundaries = @json($thaiNguyenBoundaries);
    </script>
    <script type="module" src="{{ asset('js/map/init.js') }}"></script>
</head>
<body class="h-full flex flex-col">
    <!-- Header -->
    <header class="flex flex-col">
        <!-- Banner section -->
        <div class="relative w-full h-36">
            <img src="{{ asset('images/Banner.jpg') }}" 
                 alt="Banner background" 
                 class="w-full h-full object-cover">
            
            <div class="absolute inset-0 flex items-center">
                <div class="container mx-auto px-12">
                    <h1 class="banner-text text-[#1a4c8c] font-bold">
                        <div class="text-4xl leading-tight">Public health and Air environment</div>
                        <div class="text-5xl leading-tight">Monitoring system</div>
                    </h1>
                </div>
            </div>
        </div>

        <!-- Navigation bar -->
        <div class="bg-gradient-to-r from-blue-600 via-teal-500 to-green-500 text-white shadow-md">
            <div class="container mx-auto flex justify-between items-center p-4">
                <h2 class="text-xl font-bold">
                    <i class="fas fa-chart-line mr-2"></i>
                    Hệ thống Quan trắc Chất lượng Không khí
                </h2>
                <nav class="flex space-x-6">
                    <a href="#" class="hover:text-gray-200">GIS Map</a>
                    <a href="#" class="hover:text-gray-200">Emissions Management</a>
                    <a href="#" class="hover:text-gray-200">Real Time Monitoring</a>
                    <a href="#" class="hover:text-gray-200">Health Information</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main content -->
    <main class="flex flex-1 min-h-0">
        @include('map.components')
    </main>

    <!-- Footer -->
    <footer class="relative h-48">
        <img src="{{ asset('images/Footer.jpg') }}" 
             alt="Footer background" 
             class="absolute inset-0 w-full h-full object-cover">
             
        <div class="relative h-full">
            <div class="container mx-auto h-full flex flex-col md:flex-row justify-between items-start md:items-center space-y-6 md:space-y-0 px-4 py-6">
                <!-- Contact information -->
                <div class="space-y-2 footer-content">
                    <p class="flex items-center">
                        <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                        <span class="font-medium">Copyright: GeoInformatics Research Center - TUAF, VietNam</span>
                    </p>
                    <p class="flex items-center">
                        <i class="fas fa-phone-alt text-blue-600 mr-2"></i>
                        <span class="font-medium">Phone: +84 094.03.11.03</span>
                    </p>
                    <p class="flex items-center">
                        <i class="fas fa-envelope text-blue-600 mr-2"></i>
                        <a href="mailto:trungtamdiatinhoc@tuaf.edu.vn" class="font-medium hover:text-blue-600 hover:underline">
                            trungtamdiatinhoc@tuaf.edu.vn
                        </a>
                    </p>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-2 gap-x-12 gap-y-4 footer-content">
                    <div class="flex items-center">
                        <i class="fas fa-signal text-blue-600 mr-2"></i>
                        <span class="font-medium">Online now: 2</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                        <span class="font-medium">Today: 11</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                        <span class="font-medium">This month: 1813</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users text-blue-600 mr-2"></i>
                        <span class="font-medium">Total: 52363</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" defer></script>
</body>
</html>