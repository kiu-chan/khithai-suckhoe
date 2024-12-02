<?php
namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\MapThaiNguyen;
use App\Models\AirQualityMeasurement;
use App\Services\GDALInterpolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MapController extends Controller
{
    public function index()
    {
        try {
            $mapData = [
                'lat' => 21.592449,
                'lng' => 105.854059,
                'zoom' => 13
            ];

            // 1. Lấy dữ liệu từ factories và AQI measurements
            $factoryData = DB::table('factories')
                ->select([
                    'factories.id',
                    'factories.code',
                    'factories.name',
                    'factories.address',
                    'factories.capacity',
                    DB::raw("ST_X(ST_AsText(geom)) as longitude"),
                    DB::raw("ST_Y(ST_AsText(geom)) as latitude"),
                    'air_quality_measurements.measurement_time as aqi_time',
                    'air_quality_measurements.dust_level',
                    'air_quality_measurements.co_level',
                    'air_quality_measurements.so2_level',
                    'air_quality_measurements.tsp_level',
                    'air_quality_measurements.noise_level',
                    'air_quality_measurements.aqi',
                    'factory_weather_data.measurement_time as weather_time',
                    'factory_weather_data.temperature',
                    'factory_weather_data.humidity',
                    'factory_weather_data.wind_speed',
                    'factory_weather_data.wind_direction',
                    'factory_weather_data.air_pressure',
                    'factory_weather_data.rainfall'
                ])
                ->leftJoin('air_quality_measurements', function($join) {
                    $join->on('factories.code', '=', 'air_quality_measurements.location_code')
                        ->whereIn('air_quality_measurements.id', function($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('air_quality_measurements')
                                ->whereRaw("location_code LIKE 'KLV%'")
                                ->groupBy('location_code');
                        });
                })
                ->leftJoin('factory_weather_data', function($join) {
                    $join->on('factories.code', '=', 'factory_weather_data.factory_code')
                        ->whereIn('factory_weather_data.measurement_time', function($query) {
                            $query->select(DB::raw('MAX(measurement_time)'))
                                ->from('factory_weather_data')
                                ->groupBy('factory_code');
                        });
                })
                ->get();

            // 2. Lấy dữ liệu từ monitoring_stations và AQI
            $monitoringData = DB::table('monitoring_stations')
                ->select([
                    'monitoring_stations.id',
                    'monitoring_stations.code',
                    'monitoring_stations.name',
                    'monitoring_stations.address',
                    DB::raw("'Trạm quan trắc' as capacity"),
                    DB::raw("ST_X(ST_AsText(geom)) as longitude"),
                    DB::raw("ST_Y(ST_AsText(geom)) as latitude"),
                    'air_quality_measurements.measurement_time',
                    'air_quality_measurements.temperature',
                    'air_quality_measurements.humidity',
                    'air_quality_measurements.wind_speed',
                    'air_quality_measurements.noise_level',
                    'air_quality_measurements.dust_level',
                    'air_quality_measurements.co_level',
                    'air_quality_measurements.so2_level',
                    'air_quality_measurements.tsp_level',
                    'air_quality_measurements.aqi'
                ])
                ->leftJoin('air_quality_measurements', function($join) {
                    $join->on('monitoring_stations.code', '=', 'air_quality_measurements.location_code')
                        ->whereIn('air_quality_measurements.id', function($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('air_quality_measurements')
                                ->whereRaw("location_code LIKE 'KH%'")
                                ->groupBy('location_code');
                        });
                })
                ->get();

            // 3. Lấy dữ liệu từ weather_stations
            $weatherStationsData = DB::table('weather_stations')
                ->select([
                    'weather_stations.station_code',
                    'weather_stations.station_code as code',
                    'weather_stations.station_name as name',
                    'weather_stations.district as address',
                    DB::raw("'Trạm thời tiết' as type"),
                    DB::raw("'Trạm quan trắc' as capacity"),
                    DB::raw("ST_X(ST_AsText(geom)) as longitude"),
                    DB::raw("ST_Y(ST_AsText(geom)) as latitude"),
                    'weather_measurements.measurement_time',
                    'weather_measurements.temperature',
                    'weather_measurements.humidity',
                    'weather_measurements.wind_speed',
                    'weather_measurements.wind_direction',
                    'weather_measurements.air_pressure',
                    'weather_measurements.rainfall'
                ])
                ->leftJoin('weather_measurements', function($join) {
                    $join->on('weather_stations.station_code', '=', 'weather_measurements.station_code')
                        ->whereIn('weather_measurements.measurement_time', function($query) {
                            $query->select(DB::raw('MAX(measurement_time)'))
                                ->from('weather_measurements')
                                ->groupBy('station_code');
                        });
                })
                ->get();

            // Format dữ liệu factories
            $factories = $factoryData->map(function($factory) {
                return [
                    'id' => $factory->id,
                    'code' => $factory->code,
                    'name' => $factory->name,
                    'address' => $factory->address,
                    'type' => 'Nhà máy',
                    'capacity' => $factory->capacity,
                    'lat' => (float)$factory->latitude,
                    'lng' => (float)$factory->longitude,
                    'aqi' => $factory->aqi,
                    'aqi_status' => $this->getAQIStatus($factory->aqi),
                    'aqi_color' => $this->getAQIColor($factory->aqi),
                    'aqi_time' => $factory->aqi_time ? 
                        Carbon::parse($factory->aqi_time)->format('Y-m-d H:i:s') : null,
                    'latest_measurements' => $factory->aqi_time ? [
                        'noise_level' => number_format($factory->noise_level, 1),
                        'dust_level' => number_format($factory->dust_level, 3),
                        'co_level' => number_format($factory->co_level, 3),
                        'so2_level' => number_format($factory->so2_level, 3),
                        'tsp_level' => number_format($factory->tsp_level, 3),
                    ] : null,
                    'weather_time' => $factory->weather_time ? 
                        Carbon::parse($factory->weather_time)->format('Y-m-d H:i:s') : null,
                    'weather_measurements' => $factory->weather_time ? [
                        'temperature' => number_format($factory->temperature, 1),
                        'humidity' => number_format($factory->humidity, 1),
                        'wind_speed' => number_format($factory->wind_speed, 1),
                        'wind_direction' => $factory->wind_direction,
                        'air_pressure' => number_format($factory->air_pressure, 1),
                        'rainfall' => number_format($factory->rainfall, 1)
                    ] : null
                ];
            });

            // Format dữ liệu monitoring stations
            $monitoringStations = $monitoringData->map(function($station) {
                return [
                    'id' => $station->id,
                    'code' => $station->code,
                    'name' => $station->name,
                    'address' => $station->address,
                    'type' => 'Trạm quan trắc AQI',
                    'capacity' => $station->capacity,
                    'lat' => (float)$station->latitude,
                    'lng' => (float)$station->longitude,
                    'aqi' => $station->aqi,
                    'aqi_status' => $this->getAQIStatus($station->aqi),
                    'aqi_color' => $this->getAQIColor($station->aqi),
                    'measurement_time' => $station->measurement_time ? 
                        Carbon::parse($station->measurement_time)->format('Y-m-d H:i:s') : null,
                    'latest_measurements' => $station->measurement_time ? [
                        'temperature' => number_format($station->temperature, 1),
                        'humidity' => number_format($station->humidity, 1),
                        'wind_speed' => number_format($station->wind_speed, 1),
                        'noise_level' => number_format($station->noise_level, 1),
                        'dust_level' => number_format($station->dust_level, 3),
                        'co_level' => number_format($station->co_level, 3),
                        'so2_level' => number_format($station->so2_level, 3),
                        'tsp_level' => number_format($station->tsp_level, 3),
                    ] : null
                ];
            });

            // Format dữ liệu weather stations
            $weatherStations = $weatherStationsData->map(function($station) {
                return [
                    'code' => $station->code,
                    'name' => $station->name,
                    'address' => $station->address,
                    'type' => $station->type,
                    'capacity' => $station->capacity,
                    'lat' => (float)$station->latitude,
                    'lng' => (float)$station->longitude,
                    'measurement_time' => $station->measurement_time ? 
                        Carbon::parse($station->measurement_time)->format('Y-m-d H:i:s') : null,
                    'weather_measurements' => $station->measurement_time ? [
                        'temperature' => number_format($station->temperature, 1),
                        'humidity' => number_format($station->humidity, 1),
                        'wind_speed' => number_format($station->wind_speed, 1),
                        'wind_direction' => $station->wind_direction,
                        'air_pressure' => number_format($station->air_pressure, 1),
                        'rainfall' => number_format($station->rainfall, 1)
                    ] : null
                ];
            });

            // Lấy ranh giới các khu vực Thái Nguyên
            $thaiNguyenBoundaries = DB::table('map_thai_nguyen')
                ->select('id', 'name', DB::raw("ST_AsGeoJSON(geom) as geometry"))
                ->get()
                ->map(function($area) {
                    return [
                        'id' => $area->id,
                        'name' => $area->name,
                        'geometry' => json_decode($area->geometry)
                    ];
                });

            // Thực hiện nội suy AQI cho các điểm có dữ liệu AQI
            try {
                $interpolation = new GDALInterpolation();
                $aqiLocations = $monitoringStations->concat($factories)->filter(function($location) {
                    return !is_null($location['aqi']);
                });
                $interpolatedFile = $interpolation->interpolate($aqiLocations);
                Log::info("Nội suy AQI thành công: " . $interpolatedFile);
            } catch (\Exception $e) {
                Log::error("Lỗi nội suy AQI: " . $e->getMessage());
            }

            return view('map.index', compact(
                'mapData',
                'factories',
                'monitoringStations',
                'weatherStations',
                'thaiNguyenBoundaries'
            ));
            
        } catch (\Exception $e) {
            Log::error("Lỗi trong MapController@index: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateAQILayer() 
    {
        try {
            // Lấy dữ liệu AQI mới nhất từ monitoring stations
            $monitoringLocations = DB::table('monitoring_stations')
                ->select([
                    'monitoring_stations.code as location_code',
                    DB::raw("ST_X(ST_AsText(geom)) as lng"),
                    DB::raw("ST_Y(ST_AsText(geom)) as lat"),
                    'air_quality_measurements.aqi'
                ])
                ->join('air_quality_measurements', function($join) {
                    $join->on('monitoring_stations.code', '=', 'air_quality_measurements.location_code')
                        ->whereIn('air_quality_measurements.id', function($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('air_quality_measurements')
                                ->whereRaw("location_code LIKE 'KH%'")
                                ->groupBy('location_code');
                        });
                })
                ->whereNotNull('air_quality_measurements.aqi');

            // Lấy dữ liệu AQI mới nhất từ factories
            $factoryLocations = DB::table('factories')
                ->select([
                    'factories.code as location_code',
                    DB::raw("ST_X(ST_AsText(geom)) as lng"),
                    DB::raw("ST_Y(ST_AsText(geom)) as lat"),
                    'air_quality_measurements.aqi'
                ])
                ->join('air_quality_measurements', function($join) {
                    $join->on('factories.code', '=', 'air_quality_measurements.location_code')
                        ->whereIn('air_quality_measurements.id', function($query) {
                            $query->select(DB::raw('MAX(id)'))
                                ->from('air_quality_measurements')
                                ->whereRaw("location_code LIKE 'KLV%'")
                                ->groupBy('location_code');
                        });
                })
                ->whereNotNull('air_quality_measurements.aqi');

            // Kết hợp dữ liệu
            $locations = $monitoringLocations->union($factoryLocations)->get();

            if ($locations->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không có dữ liệu AQI để nội suy'
                ], 400);
            }

            // Thực hiện nội suy
            try {
                $interpolation = new GDALInterpolation();
                $interpolatedFile = $interpolation->interpolate($locations->toArray());
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Cập nhật lớp AQI thành công',
                    'file' => $interpolatedFile
                ]);
            } catch (\Exception $e) {
                Log::error("Lỗi nội suy AQI: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error("Lỗi trong MapController@updateAQILayer: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getAQIStatus($aqi)
    {
        if (!$aqi) return null;
        if ($aqi <= 50) return 'Tốt';
        if ($aqi <= 100) return 'Trung bình';
        if ($aqi <= 150) return 'Kém';
        if ($aqi <= 200) return 'Xấu';
        if ($aqi <= 300) return 'Rất xấu';
        return 'Nguy hại';
    }

    private function getAQIColor($aqi)
    {
        if (!$aqi) return '#808080';  // Màu xám cho không có dữ liệu
        if ($aqi <= 50) return '#00E400';  // Xanh lá
        if ($aqi <= 100) return '#FFFF00'; // Vàng
        if ($aqi <= 150) return '#FF7E00'; // Cam
        if ($aqi <= 200) return '#FF0000'; // Đỏ
        if ($aqi <= 300) return '#8F3F97'; // Tím
        return '#7E0023';                  // Nâu đỏ
    }

    private function getWindColor($speed)
    {
        if (!$speed) return '#808080';     // Màu xám cho không có dữ liệu
        if ($speed < 0.5) return '#00ff00'; // Nhẹ - xanh lá
        if ($speed < 1.0) return '#ffff00'; // Trung bình - vàng
        if ($speed < 1.5) return '#ffa500'; // Mạnh - cam
        return '#ff0000';                   // Rất mạnh - đỏ
    }
}