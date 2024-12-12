<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $medicalRecords = [
            [
                'id' => 1,
                'patient_name' => 'Nông Văn Hoàn',
                'date_of_birth' => '1/1/1988',
                'checkup_date' => '8/3/2023',
                'address' => 'Hoàng Văn Thụ Ward, Thái Nguyên City',
                'illness' => 'Chronic bronchitis'
            ],
            [
                'id' => 2,
                'patient_name' => 'Hoàng Văn Tố',
                'date_of_birth' => '1/1/1980',
                'checkup_date' => '7/22/2023',
                'address' => 'Đồng Quang Ward, Thái Nguyên City',
                'illness' => 'Asthma'
            ],
            [
                'id' => 3,
                'patient_name' => 'Triệu Sỹ Tùng',
                'date_of_birth' => '1/1/1990',
                'checkup_date' => '7/17/2023',
                'address' => 'Đồng Quang Ward, Thái Nguyên',
                'illness' => 'Mental illness'
            ],
            [
                'id' => 4,
                'patient_name' => 'Triệu Thị Diễm',
                'date_of_birth' => '25/11/1971',
                'checkup_date' => '8/4/2023',
                'address' => 'Phan Đình Phùng Ward, Thái Nguyên City',
                'illness' => 'Lung cancer'
            ],
            [
                'id' => 5,
                'patient_name' => 'Đồng Văn Doãn',
                'date_of_birth' => '17/09/1977',
                'checkup_date' => '2/25/2023',
                'address' => 'Phan Đình Phùng Ward, Thái Nguyên City',
                'illness' => 'Chronic bronchitis'
            ],
            [
                'id' => 6,
                'patient_name' => 'Hoàng Thị Thảo',
                'date_of_birth' => '23/02/1995',
                'checkup_date' => '1/20/2023',
                'address' => 'Nam Tiến Commune, Phổ Yên Town',
                'illness' => 'Mental illness'
            ],
            [
                'id' => 7,
                'patient_name' => 'Hoàng Thị Gấm',
                'date_of_birth' => '15/08/1978',
                'checkup_date' => '3/28/2023',
                'address' => 'Phan Đình Phùng Ward, TP Thái Nguyên',
                'illness' => 'Chronic bronchitis'
            ],
            [
                'id' => 8,
                'patient_name' => 'Hoàng Đức Phương',
                'date_of_birth' => '27/12/1990',
                'checkup_date' => '2/19/2023',
                'address' => 'Phường Quán Triều, TP Thái Nguyên',
                'illness' => 'Chronic bronchitis'
            ],
            [
                'id' => 9,
                'patient_name' => 'Triệu Văn Đăng',
                'date_of_birth' => '27/12/1968',
                'checkup_date' => '2/14/2023',
                'address' => 'Phường Quang Vinh, TP Thái Nguyên',
                'illness' => 'Asthma'
            ],
            [
                'id' => 10,
                'patient_name' => 'Dương Văn Quyền',
                'date_of_birth' => '12/3/1980',
                'checkup_date' => '5/20/2023',
                'address' => 'Phường Túc Duyên, TP Thái Nguyên',
                'illness' => 'Chronic bronchitis'
            ],
            [
                'id' => 11,
                'patient_name' => 'Hoàng Thị Anh',
                'date_of_birth' => '16/06/1986',
                'checkup_date' => '2/17/2023',
                'address' => 'Phường Hoàng Văn Thụ, TP Thái Nguyên',
                'illness' => 'Asthma'
            ],
            [
                'id' => 12,
                'patient_name' => 'Dương Ngọc Tuấn',
                'date_of_birth' => '29/05/1978',
                'checkup_date' => '8/10/2023',
                'address' => 'Phường Trưng Vương, TP Thái Nguyên',
                'illness' => 'Lung cancer'
            ],
            [
                'id' => 13,
                'patient_name' => 'Hoàng Văn Tài',
                'date_of_birth' => '1/12/1980',
                'checkup_date' => '8/11/2023',
                'address' => 'Phường Quang Trung, TP Thái Nguyên',
                'illness' => 'Asthma'
            ],
            [
                'id' => 14,
                'patient_name' => 'Nguyễn Tấn Sang',
                'date_of_birth' => '14/06/1994',
                'checkup_date' => '6/5/2023',
                'address' => 'Phường Phan Đình Phùng, TP Thái Nguyên',
                'illness' => 'Chronic bronchitis'
            ],
            [
                'id' => 15,
                'patient_name' => 'Lý Phúc Ba',
                'date_of_birth' => '17/05/1976',
                'checkup_date' => '6/12/2023',
                'address' => 'Phường Tân Thịnh, TP Thái Nguyên',
                'illness' => 'Mental illness'
            ],
            [
                'id' => 16,
                'patient_name' => 'Bàn Thị Tiên',
                'date_of_birth' => '29/12/1982',
                'checkup_date' => '6/1/2023',
                'address' => 'Phường Thịnh Đán, TP Thái Nguyên',
                'illness' => 'Chronic bronchitis'
            ],
            [
                'id' => 17,
                'patient_name' => 'Triệu Thị Tâm',
                'date_of_birth' => '11/10/1975',
                'checkup_date' => '3/31/2023',
                'address' => 'Phường Đồng Quang, TP Thái Nguyên',
                'illness' => 'Asthma'
            ],
            [
                'id' => 18,
                'patient_name' => 'Triệu Thị Mui',
                'date_of_birth' => '1/1/1976',
                'checkup_date' => '5/29/2023',
                'address' => 'Phường Gia Sàng, TP Thái Nguyên',
                'illness' => 'Asthma'
            ],
            [
                'id' => 19,
                'patient_name' => 'Lý Thị Cầu',
                'date_of_birth' => '26/05/1964',
                'checkup_date' => '4/21/2023',
                'address' => 'Phường Tân Lập, TP Thái Nguyên',
                'illness' => 'Lung cancer'
            ]
        ];

        // Filter by illness if provided
        if ($request->has('illness') && $request->illness != '') {
            $medicalRecords = array_filter($medicalRecords, function($record) use ($request) {
                return $record['illness'] == $request->illness;
            });
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