<style>
    .info-box {
        @apply bg-white p-4 rounded-lg shadow-lg max-w-sm;
    }
    .factory-item:hover {
        @apply bg-gray-100;
    }
    .aqi-legend {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1000;
    }
    .aqi-color {
        width: 20px;
        height: 20px;
        display: inline-block;
        margin-right: 8px;
        border-radius: 4px;
    }
    .wms-opacity-control {
        position: fixed;
        bottom: 20px;
        left: 20px;
        background: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1000;
    }
</style>