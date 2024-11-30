<?php
namespace App\Services;

class GDALInterpolation {
    private $vrtPath;
    private $shapePath;
    private $tempPath;
    private $outputPath;
    private $bounds;

    public function __construct() {
        $this->vrtPath = base_path('commands/shp/air.vrt');
        $this->shapePath = base_path('commands/border/thainguyen_border.shp');
        $this->tempPath = base_path('commands/shp/temp_air.tif');
        $this->outputPath = base_path('commands/shp/air.tif');
        
        $this->bounds = [
            'minX' => 105.47,
            'maxX' => 106.23,
            'minY' => 21.32,
            'maxY' => 22.04
        ];

        if (!file_exists(dirname($this->vrtPath))) {
            mkdir(dirname($this->vrtPath), 0755, true);
        }
    }

    private function createCSV($aqiData) {
        $csvPath = base_path('commands/shp/temp_points.csv');
        
        $csv = fopen($csvPath, 'w');
        if ($csv === false) {
            throw new \Exception("Không thể tạo file CSV tại: " . $csvPath);
        }

        // Ghi header
        fputcsv($csv, ['longitude', 'latitude', 'value']);

        // Ghi dữ liệu
        foreach ($aqiData as $point) {
            if (isset($point['lng'], $point['lat'], $point['aqi'])) {
                fputcsv($csv, [
                    number_format($point['lng'], 6, '.', ''),
                    number_format($point['lat'], 6, '.', ''),
                    number_format($point['aqi'], 2, '.', '')
                ]);
            }
        }

        fclose($csv);
        
        if (!file_exists($csvPath)) {
            throw new \Exception("File CSV không được tạo thành công");
        }

        return $csvPath;
    }

    private function generateVRT($aqiData) {
        try {
            $csvPath = $this->createCSV($aqiData);
            $absoluteCsvPath = realpath($csvPath);

            if (!$absoluteCsvPath) {
                throw new \Exception("Không thể lấy đường dẫn tuyệt đối của file CSV");
            }

            // Log content of CSV file for debugging
            $csvContent = file_get_contents($absoluteCsvPath);
            \Log::debug("CSV Content:", ['content' => $csvContent]);

            $vrtContent = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<OGRVRTDataSource>
    <OGRVRTLayer name="air_layer">
        <SrcDataSource>{$absoluteCsvPath}</SrcDataSource>
        <SrcLayer>temp_points</SrcLayer>
        <GeometryType>wkbPoint</GeometryType>
        <LayerSRS>EPSG:4326</LayerSRS>
        <Field name="longitude" src="longitude" type="Real"/>
        <Field name="latitude" src="latitude" type="Real"/>
        <Field name="value" src="value" type="Real"/>
        <GeometryField encoding="PointFromColumns" x="longitude" y="latitude"/>
    </OGRVRTLayer>
</OGRVRTDataSource>
EOT;

            file_put_contents($this->vrtPath, $vrtContent);

            // Log content of VRT file for debugging
            \Log::debug("VRT Content:", ['content' => $vrtContent]);

            if (!file_exists($this->vrtPath)) {
                throw new \Exception("File VRT không được tạo thành công");
            }

            return $this->vrtPath;
        } catch (\Exception $e) {
            throw new \Exception("Lỗi khi tạo file VRT: " . $e->getMessage());
        }
    }

    public function interpolate($aqiData) {
        try {
            if (empty($aqiData)) {
                throw new \Exception("Không có dữ liệu AQI để nội suy");
            }

            // Log input data for debugging
            \Log::debug("Input AQI Data:", ['data' => $aqiData]);

            // Tạo file VRT
            $vrtFile = $this->generateVRT($aqiData);

            // Command nội suy
            $gridCommand = sprintf(
                "gdal_grid -zfield value -a invdist:power=2.0:smoothing=0.0 " .
                "-a_srs EPSG:4326 -txe %f %f -tye %f %f " .
                "-outsize 3245 3083 -of GTiff -ot Byte -l air_layer %s %s",
                $this->bounds['minX'],
                $this->bounds['maxX'],
                $this->bounds['minY'],
                $this->bounds['maxY'],
                $vrtFile,
                $this->tempPath
            );

            // Log command for debugging
            \Log::debug("GDAL Grid Command:", ['command' => $gridCommand]);

            exec($gridCommand . " 2>&1", $output, $returnValue);
            if ($returnValue !== 0) {
                throw new \Exception("Lỗi khi nội suy: " . implode("\n", $output));
            }

            // Command cắt ranh giới
            $warpCommand = sprintf(
                "gdalwarp -overwrite -co COMPRESS=LZW " .
                "-cutline %s -crop_to_cutline %s %s",
                $this->shapePath,
                $this->tempPath,
                $this->outputPath
            );

            // Log command for debugging
            \Log::debug("GDAL Warp Command:", ['command' => $warpCommand]);

            exec($warpCommand . " 2>&1", $output, $returnValue);
            if ($returnValue !== 0) {
                throw new \Exception("Lỗi khi cắt ranh giới: " . implode("\n", $output));
            }

            if (!file_exists($this->outputPath)) {
                throw new \Exception("File kết quả không được tạo");
            }

            // Dọn dẹp file tạm
            @unlink($this->tempPath);
            @unlink(base_path('commands/shp/temp_points.csv'));

            return $this->outputPath;

        } catch (\Exception $e) {
            // Dọn dẹp file tạm nếu có lỗi
            @unlink($this->tempPath);
            @unlink(base_path('commands/shp/temp_points.csv'));
            throw $e;
        }
    }
}