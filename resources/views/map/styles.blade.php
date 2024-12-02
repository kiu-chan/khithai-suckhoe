<style>
    /* Layout và container styles */
    .info-box {
        @apply bg-white p-4 rounded-lg shadow-lg max-w-sm;
    }

    .factory-item {
        @apply transition-colors duration-200;
    }
    
    .factory-item:hover {
        @apply bg-gray-100;
    }

    /* AQI Legend styles */
    .aqi-legend {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1000;
        max-width: 200px;
    }

    .aqi-color {
        width: 20px;
        height: 20px;
        display: inline-block;
        margin-right: 8px;
        border-radius: 4px;
        vertical-align: middle;
    }

    /* Wind Legend styles */
    .wind-legend {
        position: fixed;
        bottom: 180px;
        right: 20px;
        background: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1000;
        max-width: 200px;
    }

    .wind-color {
        width: 20px;
        height: 20px;
        display: inline-block;
        margin-right: 8px;
        border-radius: 4px;
        vertical-align: middle;
    }

    /* WMS Opacity Control styles */
    .wms-opacity-control {
        position: fixed;
        bottom: 20px;
        left: 20px;
        background: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1000;
        width: 200px;
    }

    .wms-opacity-control input[type="range"] {
        width: 100%;
        margin: 8px 0;
    }

    /* Wind Direction Marker styles */
    .wind-direction-marker {
        position: absolute;
        transform: translate(-50%, -50%);
        pointer-events: none;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: -20px;
    }

    .wind-direction-marker .arrow-container {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        filter: drop-shadow(1px 1px 1px rgba(0,0,0,0.5));
        transition: transform 0.3s ease;
    }

    .wind-direction-marker .speed-label {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255, 255, 255, 0.9);
        padding: 2px 4px;
        border-radius: 2px;
        font-size: 10px;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        margin-top: 5px;
    }

    /* Sidebar styles */
    .sidebar {
        @apply bg-white shadow-lg p-4 overflow-y-auto;
        width: 16rem;
    }

    .sidebar-section {
        @apply mb-6 border-b pb-4;
    }

    .sidebar-section:last-child {
        @apply border-b-0 pb-0;
    }

    .section-title {
        @apply font-medium text-gray-700 mb-2 flex items-center;
    }

    .section-title i {
        @apply mr-2;
    }

    /* Form Control styles */
    .form-checkbox {
        @apply rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50;
    }

    .form-select {
        @apply w-full p-2 border rounded border-gray-300 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50;
    }

    /* Info Window styles */
    .info-window {
        @apply p-4 max-w-sm;
    }

    .info-window-header {
        @apply mb-2;
    }

    .info-window-title {
        @apply font-bold text-lg;
    }

    .info-window-subtitle {
        @apply text-sm text-gray-600;
    }

    .info-window-content {
        @apply mt-3;
    }

    .measurement-grid {
        @apply grid grid-cols-2 gap-2 text-sm;
    }

    .measurement-item {
        @apply flex justify-between;
    }

    /* Status Indicators */
    .status-dot {
        @apply w-3 h-3 rounded-full inline-block mr-2;
    }

    /* Loading Indicator */
    .loading-overlay {
        @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50;
    }

    .loading-spinner {
        @apply animate-spin rounded-full h-12 w-12 border-4 border-white border-t-transparent;
    }

    /* Area List styles */
    .area-item {
        @apply p-2 rounded cursor-pointer hover:bg-gray-100 transition-colors duration-200;
    }

    /* Custom Range Input styling */
    input[type="range"] {
        @apply w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer;
    }

    input[type="range"]::-webkit-slider-thumb {
        @apply w-4 h-4 bg-blue-600 rounded-full appearance-none cursor-pointer;
        -webkit-appearance: none;
    }

    input[type="range"]::-moz-range-thumb {
        @apply w-4 h-4 bg-blue-600 rounded-full cursor-pointer;
    }

    /* Map Controls */
    .map-control {
        @apply bg-white rounded-lg shadow-md p-2 m-2;
    }

    .map-control-button {
        @apply px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors duration-200;
    }

    /* Tooltip styles */
    .tooltip {
        @apply invisible absolute bg-gray-900 text-white px-2 py-1 rounded text-xs;
        transform: translateX(-50%);
    }

    .has-tooltip:hover .tooltip {
        @apply visible z-50;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .aqi-legend,
        .wind-legend,
        .wms-opacity-control {
            position: static;
            margin: 10px;
            width: auto;
        }

        .sidebar {
            width: 100%;
            max-height: 50vh;
        }

        .wind-direction-marker {
            margin-top: -15px;
        }

        .wind-direction-marker .arrow-container {
            width: 20px;
            height: 20px;
        }

        .wind-direction-marker .speed-label {
            font-size: 9px;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
</style>