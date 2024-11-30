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
        $mapData = [
            'lat' => 21.592449,
            'lng' => 105.854059,
            'zoom' => 13
        ];

        // Lấy dữ liệu từ factories
        $factoryData = DB::table('factories')
            ->select([
                'factories.id',
                'factories.code',
                'factories.name',
                'factories.address',
                'factories.capacity',
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
                $join->on('factories.code', '=', 'air_quality_measurements.location_code')
                    ->whereIn('air_quality_measurements.id', function($query) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('air_quality_measurements')
                            ->whereRaw("location_code LIKE 'KLV%'")
                            ->groupBy('location_code');
                    });
            });

        // Lấy dữ liệu từ monitoring_stations
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
            });

        // Kết hợp dữ liệu
        $locations = $factoryData->union($monitoringData)
            ->get()
            ->map(function($location) {
                $aqiData = [
                    'id' => $location->id,
                    'code' => $location->code,
                    'name' => $location->name,
                    'address' => $location->address,
                    'capacity' => $location->capacity,
                    'lat' => (float)$location->latitude,
                    'lng' => (float)$location->longitude,
                    'aqi' => $location->aqi,
                    'aqi_status' => $this->getAQIStatus($location->aqi),
                    'aqi_color' => $this->getAQIColor($location->aqi),
                    'measurement_time' => $location->measurement_time ? 
                        Carbon::parse($location->measurement_time)->format('Y-m-d H:i:s') : null,
                ];

                if ($location->measurement_time) {
                    $aqiData['latest_measurements'] = [
                        'temperature' => number_format($location->temperature, 1),
                        'humidity' => number_format($location->humidity, 1),
                        'wind_speed' => number_format($location->wind_speed, 1),
                        'noise_level' => number_format($location->noise_level, 1),
                        'dust_level' => number_format($location->dust_level, 3),
                        'co_level' => number_format($location->co_level, 3),
                        'so2_level' => number_format($location->so2_level, 3),
                        'tsp_level' => number_format($location->tsp_level, 3),
                    ];
                }

                return $aqiData;
            });

        try {
            $interpolation = new GDALInterpolation();
            $interpolatedFile = $interpolation->interpolate($locations);
            Log::info("Nội suy AQI thành công: " . $interpolatedFile);
        } catch (\Exception $e) {
            Log::error("Lỗi nội suy AQI: " . $e->getMessage());
        }

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

        return view('map.index', compact('mapData', 'locations', 'thaiNguyenBoundaries'));
    }

    public function updateAQILayer() 
    {
        try {
            // Lấy dữ liệu từ factories
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

            // Lấy dữ liệu từ monitoring_stations
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

            // Kết hợp kết quả từ cả hai truy vấn
            $locations = $factoryLocations->union($monitoringLocations)->get();

            if ($locations->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không có dữ liệu AQI để nội suy'
                ], 400);
            }

            $locationArray = $locations->map(function($location) {
                return [
                    'lng' => (float)$location->lng,
                    'lat' => (float)$location->lat,
                    'aqi' => (float)$location->aqi
                ];
            })->toArray();

            $interpolation = new GDALInterpolation();
            $interpolatedFile = $interpolation->interpolate($locationArray);

            Log::info("Nội suy AQI thành công: " . $interpolatedFile);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật lớp AQI thành công',
                'file' => $interpolatedFile
            ]);

        } catch (\Exception $e) {
            Log::error("Lỗi cập nhật lớp AQI: " . $e->getMessage());
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
}