<?php

namespace App\Http\Controllers;

use App\Services\GaussianPlumeService;
use Illuminate\Http\Request;

class PlumeController extends Controller
{
    protected $plumeService;

    public function __construct(GaussianPlumeService $plumeService)
    {
        $this->plumeService = $plumeService;
    }

    public function generate(Request $request)
    {
        // Validate đầu vào
        $validated = $request->validate([
            'source_x' => 'required|numeric',
            'source_y' => 'required|numeric',
            'stack_height' => 'required|numeric|min:0',
            'emission_rate' => 'required|numeric|min:0',
            'wind_speed' => 'required|numeric|min:0',
            'wind_direction' => 'required|numeric|min:0|max:360',
        ]);

        // Chuyển đổi tên tham số
        $params = [
            'source-x' => $validated['source_x'],
            'source-y' => $validated['source_y'],
            'stack-height' => $validated['stack_height'],
            'emission-rate' => $validated['emission_rate'],
            'wind-speed' => $validated['wind_speed'],
            'wind-direction' => $validated['wind_direction'],
        ];

        // Gọi service để tạo plume
        $result = $this->plumeService->generatePlume($params);

        if ($result['success']) {
            return view('plume.result', [
                'plume_url' => $result['url'],
                'params' => $validated
            ]);
        }

        return back()->withErrors(['msg' => 'Không thể tạo plume: ' . $result['error']]);
    }

    public function index()
    {
        return view('plume.form');
    }
}