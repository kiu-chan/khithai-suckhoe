<?php
// app/Models/Factory.php
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
        'geom'
    ];

    // Convert POINT geometry to lat/lng
    public function getLocationAttribute()
    {
        if (!$this->geom) return null;
        
        // Parse POINT format: POINT(longitude latitude)
        preg_match('/POINT\((.*?)\s(.*?)\)/', $this->geom, $matches);
        
        if (count($matches) === 3) {
            return [
                'lng' => (float)$matches[1],
                'lat' => (float)$matches[2]
            ];
        }
        
        return null;
    }
}