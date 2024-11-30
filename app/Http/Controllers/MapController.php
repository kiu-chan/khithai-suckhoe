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

        $factories = DB::table('factories')
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
                $join->on('factories.id', '=', 'air_quality_measurements.factory_id')
                    ->whereRaw('air_quality_measurements.id IN (
                        SELECT MAX(id) 
                        FROM air_quality_measurements 
                        GROUP BY factory_id
                    )');
            })
            ->get()
            ->map(function($factory) {
                $aqiData = [
                    'id' => $factory->id,
                    'code' => $factory->code,
                    'name' => $factory->name,
                    'address' => $factory->address,
                    'capacity' => $factory->capacity,
                    'lat' => (float)$factory->latitude,
                    'lng' => (float)$factory->longitude,
                    'aqi' => $factory->aqi,
                    'aqi_status' => $this->getAQIStatus($factory->aqi),
                    'aqi_color' => $this->getAQIColor($factory->aqi),
                    'measurement_time' => $factory->measurement_time ? 
                        Carbon::parse($factory->measurement_time)->format('Y-m-d H:i:s') : null,
                ];

                if ($factory->measurement_time) {
                    $aqiData['latest_measurements'] = [
                        'temperature' => number_format($factory->temperature, 1),
                        'humidity' => number_format($factory->humidity, 1),
                        'wind_speed' => number_format($factory->wind_speed, 1),
                        'noise_level' => number_format($factory->noise_level, 1),
                        'dust_level' => number_format($factory->dust_level, 3),
                        'co_level' => number_format($factory->co_level, 3),
                        'so2_level' => number_format($factory->so2_level, 3),
                        'tsp_level' => number_format($factory->tsp_level, 3),
                    ];
                }

                return $aqiData;
            });

        try {
            $interpolation = new GDALInterpolation();
            $interpolatedFile = $interpolation->interpolate($factories);
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

        return view('map.index', compact('mapData', 'factories', 'thaiNguyenBoundaries'));
    }

    private function getAQIStatus($aqi)
    {
        if ($aqi <= 50) return 'Tốt';
        if ($aqi <= 100) return 'Trung bình';
        if ($aqi <= 150) return 'Kém';
        if ($aqi <= 200) return 'Xấu';
        if ($aqi <= 300) return 'Rất xấu';
        return 'Nguy hại';
    }

    private function getAQIColor($aqi)
    {
        if ($aqi <= 50) return '#00E400';  // Xanh lá
        if ($aqi <= 100) return '#FFFF00'; // Vàng
        if ($aqi <= 150) return '#FF7E00'; // Cam
        if ($aqi <= 200) return '#FF0000'; // Đỏ
        if ($aqi <= 300) return '#8F3F97'; // Tím
        return '#7E0023';                  // Nâu đỏ
    }

    public function updateAQILayer() 
    {
        try {
            // Lấy dữ liệu AQI mới nhất từ mỗi trạm
            $factories = DB::table('factories')
                ->select([
                    'factories.id',
                    DB::raw("ST_X(ST_AsText(geom)) as lng"),
                    DB::raw("ST_Y(ST_AsText(geom)) as lat"),
                    'air_quality_measurements.aqi'
                ])
                ->leftJoin('air_quality_measurements', function($join) {
                    $join->on('factories.id', '=', 'air_quality_measurements.factory_id')
                        ->whereRaw('air_quality_measurements.id IN (
                            SELECT MAX(id) 
                            FROM air_quality_measurements 
                            GROUP BY factory_id
                        )');
                })
                ->whereNotNull('air_quality_measurements.aqi')
                ->get();

            if ($factories->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không có dữ liệu AQI để nội suy'
                ], 400);
            }

            $factoryArray = $factories->map(function($factory) {
                return [
                    'lng' => (float)$factory->lng,
                    'lat' => (float)$factory->lat,
                    'aqi' => (float)$factory->aqi
                ];
            })->toArray();

            $interpolation = new GDALInterpolation();
            $interpolatedFile = $interpolation->interpolate($factoryArray);

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
}