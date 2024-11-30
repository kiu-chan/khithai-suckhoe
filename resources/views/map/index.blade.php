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
    <header class="bg-gradient-to-r from-blue-600 via-teal-500 to-green-500 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-xl font-bold">
                <i class="fas fa-chart-line mr-2"></i>
                Hệ thống Quan trắc Chất lượng Không khí
            </h1>
            <div class="text-sm mt-1">
                <i class="fas fa-clock mr-2"></i>
                Cập nhật lần cuối: <span id="lastUpdate"></span>
            </div>
        </div>
    </header>

    <main class="flex flex-1 min-h-0">
        @include('map.components')
    </main>

    <footer class="bg-gray-900 text-white p-4 text-sm text-center">
        <p>© {{ date('Y') }} Trung tâm Nghiên cứu Địa tin học - Đại học Nông Lâm Thái Nguyên</p>
    </footer>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" defer></script>
    @include('map.scripts')
</body>
</html>