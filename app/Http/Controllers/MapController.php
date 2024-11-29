<?php
namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\MapThaiNguyen;
use App\Models\AirQualityMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MapController extends Controller
{
    /**
     * Hiển thị trang bản đồ với dữ liệu nhà máy và AQI
     */
    public function index()
    {
        // Cấu hình mặc định cho bản đồ
        $mapData = [
            'lat' => 21.592449,
            'lng' => 105.854059,
            'zoom' => 13
        ];

        // Lấy dữ liệu nhà máy kèm thông số đo mới nhất
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
                'air_quality_measurements.tsp_level'
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
                // Tính toán AQI cho nhà máy
                $aqiData = $this->calculateDetailedAQI([
                    'dust' => $factory->dust_level,
                    'co' => $factory->co_level,
                    'so2' => $factory->so2_level,
                    'tsp' => $factory->tsp_level
                ]);

                return [
                    'id' => $factory->id,
                    'code' => $factory->code,
                    'name' => $factory->name,
                    'address' => $factory->address,
                    'capacity' => $factory->capacity,
                    'lat' => (float)$factory->latitude,
                    'lng' => (float)$factory->longitude,
                    'aqi' => $aqiData['aqi'],
                    'aqi_status' => $aqiData['status'],
                    'aqi_color' => $aqiData['color'],
                    'measurement_time' => $factory->measurement_time ? 
                        Carbon::parse($factory->measurement_time)->format('Y-m-d H:i:s') : null,
                    'latest_measurements' => $factory->measurement_time ? [
                        'temperature' => number_format($factory->temperature, 1),
                        'humidity' => number_format($factory->humidity, 1),
                        'wind_speed' => number_format($factory->wind_speed, 1),
                        'noise_level' => number_format($factory->noise_level, 1),
                        'dust_level' => number_format($factory->dust_level, 3),
                        'co_level' => number_format($factory->co_level, 3),
                        'so2_level' => number_format($factory->so2_level, 3),
                        'tsp_level' => number_format($factory->tsp_level, 3),
                    ] : null
                ];
            });

        // Lấy ranh giới Thái Nguyên
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

    /**
     * Tính toán chỉ số AQI chi tiết
     * @param array $params Các thông số đo
     * @return array Thông tin AQI
     */
    private function calculateDetailedAQI($params)
    {
        // Quy đổi các đơn vị đo sang μg/m3 theo chuẩn AQI
        $dust = isset($params['dust']) ? $params['dust'] * 1000 : 0; // mg/m3 -> μg/m3
        $co = isset($params['co']) ? $params['co'] * 1000 : 0;      // mg/m3 -> μg/m3
        $so2 = isset($params['so2']) ? $params['so2'] * 1000 : 0;   // mg/m3 -> μg/m3
        $tsp = isset($params['tsp']) ? $params['tsp'] * 1000 : 0;   // mg/m3 -> μg/m3

        // Tính AQI cho từng thông số
        $dustAQI = $this->calculateSubIndex($dust, [
            [0, 50, 0, 50],
            [50, 150, 51, 100],
            [150, 250, 101, 150],
            [250, 350, 151, 200],
            [350, 420, 201, 300],
            [420, 500, 301, 400],
            [500, 600, 401, 500]
        ]);

        $coAQI = $this->calculateSubIndex($co, [
            [0, 10000, 0, 50],
            [10000, 30000, 51, 100],
            [30000, 45000, 101, 150],
            [45000, 60000, 151, 200],
            [60000, 90000, 201, 300],
            [90000, 120000, 301, 400],
            [120000, 150000, 401, 500]
        ]);

        $so2AQI = $this->calculateSubIndex($so2, [
            [0, 125, 0, 50],
            [125, 350, 51, 100],
            [350, 550, 101, 150],
            [550, 800, 151, 200],
            [800, 1600, 201, 300],
            [1600, 2100, 301, 400],
            [2100, 2620, 401, 500]
        ]);

        // Lấy giá trị AQI lớn nhất
        $aqi = max($dustAQI, $coAQI, $so2AQI);

        // Xác định trạng thái và màu sắc
        $status = $this->getAQIStatus($aqi);
        $color = $this->getAQIColor($aqi);

        return [
            'aqi' => round($aqi),
            'status' => $status,
            'color' => $color
        ];
    }

    /**
     * Tính chỉ số AQI cho một thông số
     */
    private function calculateSubIndex($value, $breakpoints)
    {
        if ($value <= 0) return 0;

        foreach ($breakpoints as [$bpLow, $bpHigh, $iLow, $iHigh]) {
            if ($value >= $bpLow && $value <= $bpHigh) {
                return (($iHigh - $iLow) / ($bpHigh - $bpLow)) * ($value - $bpLow) + $iLow;
            }
        }

        return 500; // Giá trị tối đa
    }

    /**
     * Lấy trạng thái AQI
     */
    private function getAQIStatus($aqi)
    {
        if ($aqi <= 50) return 'Tốt';
        if ($aqi <= 100) return 'Trung bình';
        if ($aqi <= 150) return 'Kém';
        if ($aqi <= 200) return 'Xấu';
        if ($aqi <= 300) return 'Rất xấu';
        return 'Nguy hại';
    }

    /**
     * Lấy màu tương ứng với AQI
     */
    private function getAQIColor($aqi)
    {
        if ($aqi <= 50) return '#00E400';  // Xanh lá
        if ($aqi <= 100) return '#FFFF00'; // Vàng
        if ($aqi <= 150) return '#FF7E00'; // Cam
        if ($aqi <= 200) return '#FF0000'; // Đỏ
        if ($aqi <= 300) return '#8F3F97'; // Tím
        return '#7E0023';                  // Nâu đỏ
    }
}