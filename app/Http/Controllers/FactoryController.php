<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FactoryController extends Controller
{
    public function detail($slug)
    {
        // Xử lý hiển thị chi tiết nhà máy dựa vào slug
        // Ví dụ: luu-xa, quan-trieu, cao-ngan,...
        return view('factory.detail', [
            'factory' => $this->getFactoryData($slug)
        ]);
    }

    private function getFactoryData($slug)
    {
        // Dữ liệu mẫu, trong thực tế nên lấy từ database
        $factories = [
            'luu-xa' => [
                'name' => 'Luu Xa Cement Company',
                'image' => 'images/factories/luu-xa.jpg',
                'description' => 'Thông tin về nhà máy Luu Xa...',
            ],
            'quan-trieu' => [
                'name' => 'Quan Trieu Cement Joint Stock Company',
                'image' => 'images/factories/quan-trieu.jpg',
                'description' => 'Thông tin về nhà máy Quan Trieu...',
            ],
            // Thêm thông tin cho các nhà máy khác
        ];

        return $factories[$slug] ?? abort(404);
    }
}