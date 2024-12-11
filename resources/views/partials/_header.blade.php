<header class="flex flex-col">
    <!-- Banner section -->
    <div class="relative w-full h-28 sm:h-36">
        <img src="{{ asset('images/Banner.jpg') }}" 
             alt="Banner background" 
             class="w-full h-full object-cover">
        
        <div class="absolute inset-0 flex items-center">
            <div class="container mx-auto px-4 sm:px-12">
                <h1 class="banner-text text-[#1a4c8c] font-bold">
                    <div class="text-2xl sm:text-4xl leading-tight">Air environment monitoring system of </div>
                    <div class="text-3xl sm:text-5xl leading-tight">Cement factories</div>
                </h1>
            </div>
        </div>
    </div>

    <!-- Navigation section -->
    <div class="bg-gradient-to-r from-blue-600 via-teal-500 to-green-500 text-white shadow-md">
        <div class="container mx-auto flex flex-col sm:flex-row justify-between items-center p-4 space-y-4 sm:space-y-0">
            <h2 class="text-lg sm:text-xl font-bold text-center sm:text-left">
                <i class="fas fa-chart-line mr-2"></i>
                Air Quality Monitoring System
            </h2>
            <nav class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-6 items-center">
                <a href="{{ route('map.index') }}" 
                   class="hover:text-gray-200 pb-1 border-b-2 {{ request()->routeIs('map.index') ? 'border-white' : 'border-transparent' }}">
                    GIS Map
                </a>
                <a href="{{ route('monitoring.index') }}" 
                   class="hover:text-gray-200 pb-1 border-b-2 {{ request()->routeIs('monitoring.index') ? 'border-white' : 'border-transparent' }}">
                    Real Time Monitoring
                </a>
                <a href="{{ route('health.index') }}" 
                   class="hover:text-gray-200 pb-1 border-b-2 {{ request()->routeIs('health.index') ? 'border-white' : 'border-transparent' }}">
                    Health Information
                </a>
            </nav>
        </div>
    </div>
</header>