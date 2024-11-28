<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapThaiNguyen extends Model
{
    protected $table = 'map_thai_nguyen';
    protected $fillable = ['name', 'geom'];
    public $timestamps = true;
}