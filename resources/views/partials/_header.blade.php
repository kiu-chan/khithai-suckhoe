<header class="flex flex-col h-[184px]">
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

    <div class="bg-gradient-to-r from-blue-600 via-teal-500 to-green-500 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center p-4">
            <h2 class="text-xl font-bold">
                <i class="fas fa-chart-line mr-2"></i>
                Air Quality Monitoring System
            </h2>
            <nav class="flex space-x-6">
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