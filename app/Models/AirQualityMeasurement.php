<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirQualityMeasurement extends Model
{
    protected $table = 'air_quality_measurements';
    
    protected $fillable = [
        'location_code',
        'measurement_time',
        'temperature',
        'humidity',
        'wind_speed',
        'noise_level',
        'dust_level',
        'co_level',
        'so2_level',
        'tsp_level'
    ];

    public $timestamps = false; // Thêm dòng này nếu bảng không có created_at và updated_at
}