<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hệ thống Quan trắc Chất lượng Không khí')</title>
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
        .main-content {
            margin: 24px 0;
        }
        .map-container {
            overflow: hidden; 
        }
    </style>
    @stack('scripts')
</head>
<body class="min-h-screen flex flex-col">
    @include('partials._header')

    <main class="main-content flex-1">
        @yield('content')
    </main>

    @include('partials._footer')

    @stack('bottom-scripts')
</body>
</html>