<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FactoryController extends Controller
{
    public function detail($slug)
    {
        $factories = [
            'luu-xa' => [
                'name' => 'Luu Xa Cement Company',
                'code' => 'LX-01',
                'address' => 'Xã Lưu Xá, Thị xã Đại Từ, Thái Nguyên',
                'description' => 'Nhà máy xi măng Lưu Xá được thành lập năm 1974. Nhà máy có công suất sản xuất 1,5 triệu tấn/năm.',
                'image' => 'images/Luuxa.png',
                'stats' => [
                    'capacity' => '1.5 triệu tấn/năm',
                    'employees' => '500 người',
                    'founded' => '1974',
                    'area' => '50 hecta'
                ],
                'environmental_metrics' => [
                    'dust' => '35.6 mg/Nm3',
                    'so2' => '245.7 mg/Nm3', 
                    'nox' => '387.2 mg/Nm3',
                    'co' => '680.5 mg/Nm3'
                ]
            ],
            'quan-trieu' => [
                'name' => 'Quan Trieu Cement Joint Stock Company',
                'code' => 'QT-02',
                'address' => 'Phường Quang Trung, TP.Thái Nguyên, Thái Nguyên',
                'description' => 'Công ty cổ phần xi măng Quán Triều - TISCO được thành lập năm 1982. Nhà máy có công suất thiết kế 1.2 triệu tấn/năm.',
                'image' => 'images/quantrieu.png',
                'stats' => [
                    'capacity' => '1.2 triệu tấn/năm',
                    'employees' => '450 người',
                    'founded' => '1982',
                    'area' => '45 hecta'
                ],
                'environmental_metrics' => [
                    'dust' => '32.4 mg/Nm3',
                    'so2' => '267.8 mg/Nm3',
                    'nox' => '356.4 mg/Nm3',
                    'co' => '645.2 mg/Nm3'
                ]
            ],
            'cao-ngan' => [
                'name' => 'Cao Ngan Cement Joint Stock Company',
                'code' => 'CN-03',
                'address' => 'Xã Cao Ngạn, TP.Thái Nguyên, Thái Nguyên',
                'description' => 'Công ty cổ phần xi măng Cao Ngạn thành lập năm 1995. Với dây chuyền sản xuất hiện đại, công suất đạt 2 triệu tấn/năm.',
                'image' => 'images/Caongan.png',
                'stats' => [
                    'capacity' => '2.0 triệu tấn/năm',
                    'employees' => '600 người',
                    'founded' => '1995',
                    'area' => '65 hecta'
                ],
                'environmental_metrics' => [
                    'dust' => '28.9 mg/Nm3',
                    'so2' => '234.5 mg/Nm3',
                    'nox' => '342.8 mg/Nm3',
                    'co' => '589.6 mg/Nm3'
                ]
            ],
            'quang-son' => [
                'name' => 'Quang Son Cement One Member Co., Ltd',
                'code' => 'QS-04',
                'address' => 'Xã Quang Sơn, Huyện Đồng Hỷ, Thái Nguyên',
                'description' => 'Công ty TNHH một thành viên xi măng Quang Sơn được thành lập năm 2001, là một trong những nhà máy xi măng hiện đại tại Thái Nguyên.',
                'image' => 'images/QuangSon.png',
                'stats' => [
                    'capacity' => '1.8 triệu tấn/năm',
                    'employees' => '520 người',
                    'founded' => '2001',
                    'area' => '55 hecta'
                ],
                'environmental_metrics' => [
                    'dust' => '31.2 mg/Nm3',
                    'so2' => '256.4 mg/Nm3',
                    'nox' => '378.5 mg/Nm3',
                    'co' => '612.3 mg/Nm3'
                ]
            ],
            'la-hien' => [
                'name' => 'La Hien Joint Stock Company',
                'code' => 'LH-05',
                'address' => 'Xã La Hiên, Huyện Võ Nhai, Thái Nguyên',
                'description' => 'Công ty cổ phần xi măng La Hiên được thành lập năm 1996, với công nghệ sản xuất tiên tiến và thân thiện với môi trường.',
                'image' => 'images/Lahien.png',
                'stats' => [
                    'capacity' => '1.6 triệu tấn/năm',
                    'employees' => '480 người',
                    'founded' => '1996',
                    'area' => '48 hecta'
                ],
                'environmental_metrics' => [
                    'dust' => '29.8 mg/Nm3',
                    'so2' => '278.6 mg/Nm3',
                    'nox' => '365.4 mg/Nm3',
                    'co' => '634.7 mg/Nm3'
                ]
            ]
        ];

        if (!isset($factories[$slug])) {
            abort(404);
        }

        return view('factory.detail', [
            'factory' => $factories[$slug]
        ]);
    }
}