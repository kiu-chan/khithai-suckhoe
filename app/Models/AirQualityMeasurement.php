<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirQualityMeasurement extends Model
{
    protected $fillable = [
        'location_code',
        'factory_id',
        'measurement_time',
        'temperature',
        'humidity',
        'wind_speed',
        'noise_level',
        'dust_level',
        'co_level',
        'so2_level',
        'tsp_level',
        'aqi',
        'aqi_status'
    ];

    public function factory()
    {
        return $this->belongsTo(Factory::class);
    }
}