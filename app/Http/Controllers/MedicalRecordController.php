<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MedicalRecordController extends Controller
{
    private function getMedicalRecords()
    {
        $jsonPath = storage_path('app/data/medical_records.json');
        if (!File::exists($jsonPath)) {
            return [];
        }
        return json_decode(File::get($jsonPath), true);
    }

    public function index(Request $request)
    {
        $medicalRecords = $this->getMedicalRecords();

        if ($request->ajax()) {
            $filteredRecords = $medicalRecords;
            
            if ($request->has('illness') && $request->illness != '') {
                $filteredRecords = array_filter($medicalRecords, function($record) use ($request) {
                    return $record['illness'] == $request->illness;
                });
            }

            return response()->json([
                'data' => array_values($filteredRecords)
            ]);
        }

        // Get unique illnesses for filter dropdown
        $illnesses = array_unique(array_column($medicalRecords, 'illness'));
        sort($illnesses);

        return view('medical-records.index', [
            'medicalRecords' => $medicalRecords,
            'illnesses' => $illnesses,
            'selectedIllness' => $request->illness ?? ''
        ]);
    }
}