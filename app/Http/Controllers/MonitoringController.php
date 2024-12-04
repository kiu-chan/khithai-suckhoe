<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonitoringStation;
use App\Models\AirQualityMeasurement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function index()
    {
        // Lấy dữ liệu monitoring từ database
        $stationData = $this->getMonitoringStationData();
        $factoryData = $this->getFactoryData();
        
        return view('monitoring.index', [
            'stationData' => $stationData,
            'factoryData' => $factoryData,
            'currentDate' => Carbon::now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s')
        ]);
    }

    private function getMonitoringStationData()
    {
        // Lấy dữ liệu mới nhất từ các trạm quan trắc
        return DB::table('monitoring_stations')
            ->select([
                'monitoring_stations.id',
                'monitoring_stations.code',
                'monitoring_stations.name',
                'air_quality_measurements.aqi',
                'air_quality_measurements.temperature',
                'air_quality_measurements.humidity',
                'air_quality_measurements.wind_speed',
                'air_quality_measurements.noise_level as noise',
                'air_quality_measurements.tsp_level as tsp',
                'air_quality_measurements.dust_level as pb_dust',
                'air_quality_measurements.co_level as co',
                'air_quality_measurements.so2_level as so2',
                'air_quality_measurements.measurement_time'
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
            ->get()
            ->map(function($station) {
                return [
                    'id' => $station->id,
                    'code' => $station->code,
                    'name' => $station->name,
                    'aqi' => $station->aqi ? round($station->aqi) : null,
                    'temperature' => $station->temperature ? round($station->temperature, 1) : null,
                    'humidity' => $station->humidity ? round($station->humidity, 1) : null,
                    'wind_speed' => $station->wind_speed ? round($station->wind_speed, 1) : null,
                    'noise' => $station->noise ? round($station->noise, 1) : null,
                    'tsp' => $station->tsp ? round($station->tsp, 3) : null,
                    'pb_dust' => $station->pb_dust ? round($station->pb_dust, 3) : null,
                    'so2' => $station->so2 ? round($station->so2, 3) : null,
                    'co' => $station->co ? round($station->co, 3) : null,
                    'measurement_time' => $station->measurement_time ? 
                        Carbon::parse($station->measurement_time)->format('d/m/Y H:i:s') : null
                ];
            })
            ->toArray();
    }

    private function getFactoryData()
    {
        // Lấy dữ liệu mới nhất từ các nhà máy
        return DB::table('factories')
            ->select([
                'factories.id',
                'factories.code',
                'factories.name',
                'air_quality_measurements.aqi',
                'air_quality_measurements.temperature',
                'air_quality_measurements.humidity',
                'air_quality_measurements.wind_speed',
                'air_quality_measurements.noise_level as noise',
                'air_quality_measurements.tsp_level as tsp',
                'air_quality_measurements.dust_level as pb_dust',
                'air_quality_measurements.co_level as co',
                'air_quality_measurements.so2_level as so2',
                'air_quality_measurements.measurement_time'
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
            ->get()
            ->map(function($factory) {
                return [
                    'id' => $factory->id,
                    'code' => $factory->code,
                    'name' => $factory->name,
                    'aqi' => $factory->aqi ? round($factory->aqi) : null,
                    'temperature' => $factory->temperature ? round($factory->temperature, 1) : null,
                    'humidity' => $factory->humidity ? round($factory->humidity, 1) : null,
                    'wind_speed' => $factory->wind_speed ? round($factory->wind_speed, 1) : null,
                    'noise' => $factory->noise ? round($factory->noise, 1) : null,
                    'tsp' => $factory->tsp ? round($factory->tsp, 3) : null,
                    'pb_dust' => $factory->pb_dust ? round($factory->pb_dust, 3) : null,
                    'so2' => $factory->so2 ? round($factory->so2, 3) : null,
                    'co' => $factory->co ? round($factory->co, 3) : null,
                    'measurement_time' => $factory->measurement_time ? 
                        Carbon::parse($factory->measurement_time)->format('d/m/Y H:i:s') : null
                ];
            })
            ->toArray();
    }
}