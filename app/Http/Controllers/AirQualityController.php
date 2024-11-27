<?php

namespace App\Http\Controllers;

use App\Models\AirQualityMeasurement;
use App\Models\Factory; 
use Illuminate\Http\Request;
use Carbon\Carbon;

class AirQualityController extends Controller
{
   /**
    * Hiển thị trang chủ với dữ liệu mới nhất
    */
   public function index()
   {
       // Lấy 10 bản ghi mới nhất từ tất cả các vị trí
       $measurements = AirQualityMeasurement::with('factory')
           ->orderBy('measurement_time', 'desc')
           ->limit(10)
           ->get();
       
       // Lấy danh sách các nhà máy cho dropdown
       $factories = Factory::all();
       
       return view('air-quality.index', compact('measurements', 'factories'));
   }

   /**
    * Tìm kiếm và lọc dữ liệu theo điều kiện
    */
   public function search(Request $request)
   {
       // Khởi tạo query với relationship factory
       $query = AirQualityMeasurement::with('factory');
       
       // Lọc theo vị trí nếu có
       if ($request->has('location_code') && $request->location_code != '') {
           $query->where('location_code', $request->location_code);
           
           // Nếu chỉ chọn vị trí mà không chọn ngày
           if (!$request->filled('start_date') && !$request->filled('end_date')) {
               // Lấy ngày đầu tiên của vị trí đó
               $firstRecord = $query->orderBy('measurement_time', 'asc')->first();
               
               if ($firstRecord) {
                   $startDate = Carbon::parse($firstRecord->measurement_time)->startOfDay();
                   $endDate = Carbon::now('Asia/Ho_Chi_Minh');
                   
                   $query = AirQualityMeasurement::with('factory')
                       ->where('location_code', $request->location_code)
                       ->whereBetween('measurement_time', [$startDate, $endDate]);
               }
           }
       }
       
       // Lọc theo ngày bắt đầu
       if ($request->filled('start_date')) {
           $startDate = Carbon::parse($request->start_date)->startOfDay();
           $query->where('measurement_time', '>=', $startDate);
       }
       
       // Lọc theo ngày kết thúc
       if ($request->filled('end_date')) {
           $endDate = Carbon::parse($request->end_date)->endOfDay();
           $query->where('measurement_time', '<=', $endDate);
       }
       
       // Lấy kết quả và sắp xếp theo thời gian giảm dần
       $measurements = $query->orderBy('measurement_time', 'desc')->get();
       
       // Lấy danh sách nhà máy cho dropdown
       $factories = Factory::all();
       
       // Trả về view với dữ liệu đã lọc
       return view('air-quality.index', compact('measurements', 'factories'));
   }
}