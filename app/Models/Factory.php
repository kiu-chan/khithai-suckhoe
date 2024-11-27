<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factory extends Model
{
    protected $table = 'factories';
    
    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'capacity',
        'chimney_height',
        'coordinates'
    ];

    // Relationship với bảng air_quality_measurements
    public function airQualityMeasurements()
    {
        return $this->hasMany(AirQualityMeasurement::class);
    }
}