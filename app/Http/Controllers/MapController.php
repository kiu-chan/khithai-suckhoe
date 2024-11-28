<?php
namespace App\Http\Controllers;

use App\Models\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function index()
    {
        $mapData = [
            'lat' => 21.592449,
            'lng' => 105.854059,
            'zoom' => 13
        ];

        // Get factories with location data
        $factories = DB::table('factories')
            ->select(
                'id',
                'code',
                'name',
                'address',
                'capacity',
                DB::raw("ST_X(ST_AsText(geom)) as longitude"),
                DB::raw("ST_Y(ST_AsText(geom)) as latitude")
            )
            ->get()
            ->map(function($factory) {
                return [
                    'id' => $factory->id,
                    'code' => $factory->code,
                    'name' => $factory->name,
                    'address' => $factory->address,
                    'capacity' => $factory->capacity,
                    'lat' => (float)$factory->latitude,
                    'lng' => (float)$factory->longitude
                ];
            });

        // Debug data
        // dd($factories);

        return view('map.index', compact('mapData', 'factories'));
    }
}