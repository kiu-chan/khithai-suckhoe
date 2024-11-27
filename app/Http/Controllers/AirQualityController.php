<?php

namespace App\Http\Controllers;

use App\Models\AirQualityMeasurement;
use Illuminate\Http\Request;

class AirQualityController extends Controller
{
    public function index()
    {
        // Thêm dòng debug để kiểm tra dữ liệu
        $measurements = AirQualityMeasurement::orderBy('measurement_time', 'desc')->get();
        // dd($measurements); // Dòng này sẽ hiển thị dữ liệu để debug
        
        return view('air-quality.index', compact('measurements'));
    }

    public function search(Request $request)
    {
        $query = AirQualityMeasurement::query();
        
        if ($request->has('location_code')) {
            $query->where('location_code', $request->location_code);
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('measurement_time', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('measurement_time', '<=', $request->end_date);
        }
        
        $measurements = $query->orderBy('measurement_time', 'desc')->get();
        
        return view('air-quality.index', compact('measurements'));
    }
}