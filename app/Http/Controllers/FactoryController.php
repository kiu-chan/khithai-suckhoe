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
                'description' => 'Thông tin chi tiết về nhà máy xi măng Lưu Xá',
                'image' => 'images/Luuxa.png'
            ],
            'quan-trieu' => [
                'name' => 'Quan Trieu Cement Joint Stock Company',
                'description' => 'Thông tin chi tiết về nhà máy xi măng Quán Triều',
                'image' => 'images/quantrieu.png'
            ],
            'cao-ngan' => [
                'name' => 'Cao Ngan Cement Joint Stock Company',
                'description' => 'Thông tin chi tiết về nhà máy xi măng Cao Ngạn',
                'image' => 'images/Caongan.png'
            ],
            'quang-son' => [
                'name' => 'Quang Son Cement One Member Co., Ltd',
                'description' => 'Thông tin chi tiết về nhà máy xi măng Quang Sơn',
                'image' => 'images/QuangSon.png'
            ],
            'la-hien' => [
                'name' => 'La Hien Joint Stock Company',
                'description' => 'Thông tin chi tiết về nhà máy xi măng La Hiên',
                'image' => 'images/Lahien.png'
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