import numpy as np
from osgeo import gdal, osr
import argparse
from datetime import datetime
import math

class GaussianPlumeModel:
    def __init__(self, args):
        """
        Khởi tạo mô hình với các thông số đầu vào
        """
        # Kích thước vùng tính và độ phân giải
        self.width = args.width
        self.height = args.height
        self.resolution = args.resolution
        
        # Tính toán kích thước lưới
        self.nx = int(self.width / self.resolution)
        self.ny = int(self.height / self.resolution)
        
        # Tọa độ gốc và nguồn
        self.x_min = args.source_x - (self.width / (2 * 111111))
        self.y_min = args.source_y - (self.height / (2 * 111111))
        self.source_x = self.nx // 2
        self.source_y = self.ny // 2

        # Thông số đầu vào cơ bản
        self.emission_rate = args.emission_rate  # Q: Tải lượng bụi phát thải (mg/s)
        self.wind_speed = args.wind_speed       # u: Tốc độ gió (m/s)
        self.stack_height = args.stack_height   # h: Chiều cao thực của ống khói (m)
        
        # Thông số nhiệt độ
        self.stack_temp = args.stack_temp + 273.15    # Ts: Nhiệt độ khí thải (K)
        self.ambient_temp = args.ambient_temp + 273.15  # Ta: Nhiệt độ môi trường (K)
        self.delta_T = self.stack_temp - self.ambient_temp  # ΔT: Chênh lệch nhiệt độ
        
        # Các thông số khác
        self.stack_velocity = args.stack_velocity  # Vận tốc phát thải (m/s)
        self.stack_diameter = args.stack_diameter  # Đường kính ống khói (m)
        self.g = 9.8  # Gia tốc trọng trường (m/s²)

        # Thêm tham số hướng gió
        self.wind_direction = 0 -(args.wind_direction - 180)   # Hướng gió (độ)
        
        # Ma trận nồng độ
        self.C = np.zeros((self.nx, self.ny))
        
        # Tính các thông số bổ sung
        self.calc_plume_rise()
        
        print("\nCác thông số đầu vào:")
        print(f"Tải lượng bụi phát thải (Q): {self.emission_rate} mg/s")
        print(f"Tốc độ gió (u): {self.wind_speed} m/s")
        print(f"Chiều cao ống khói (h): {self.stack_height} m")
        print(f"Chiều cao hiệu dụng (H): {self.effective_height} m")
        print(f"Nhiệt độ khí thải: {args.stack_temp}°C ({self.stack_temp}K)")
        print(f"Nhiệt độ môi trường: {args.ambient_temp}°C ({self.ambient_temp}K)")
        print(f"Hướng gió: {self.wind_direction}°")

    def calc_plume_rise(self):
        """Tính toán độ nâng của khói và chiều cao hiệu dụng"""
        # Tính F - lực nâng nhiệt
        V = np.pi * (self.stack_diameter/2)**2 * self.stack_velocity  # Lưu lượng khí thải
        F = (self.g * V * self.stack_temp * self.delta_T) / self.ambient_temp
        
        # Tính độ nâng Δh
        self.F = F
        self.plume_rise = 2.68 * (F**(1/3) / self.wind_speed)
        
        # Chiều cao hiệu dụng H = h + Δh
        self.effective_height = self.stack_height + self.plume_rise

    def calc_dispersion_coefficients(self, x):
        """Tính hệ số khuếch tán theo khoảng cách x và điều kiện khí quyển"""
        # Thêm một giá trị nhỏ để tránh chia cho 0
        x = max(0.1, abs(x))  # Đảm bảo x luôn dương và lớn hơn 0.1
        
        # Hệ số khuếch tán cho điều kiện trung bình (loại D)
        sigma_y = max(0.1, 0.32 * x * (1 + 0.0004 * x)**(-0.5))
        sigma_z = max(0.1, 0.24 * x * (1 + 0.001 * x)**(-0.5))
        return sigma_y, sigma_z

    def calculate_concentration(self):
        """Tính nồng độ theo mô hình Gaussian"""
        print("\nĐang tính toán nồng độ...")
        
        for i in range(self.nx):
            for j in range(self.ny):
                # Tính khoảng cách từ nguồn với hướng gió
                x_wind = ((i - self.source_x) * self.resolution * math.cos(math.radians(self.wind_direction))) + \
                         ((j - self.source_y) * self.resolution * math.sin(math.radians(self.wind_direction)))
                y_wind = ((j - self.source_y) * self.resolution * math.cos(math.radians(self.wind_direction))) - \
                         ((i - self.source_x) * self.resolution * math.sin(math.radians(self.wind_direction)))
                
                # Chỉ tính cho khu vực xuôi gió và có khoảng cách > 0
                if x_wind >= 0.1:  # Thêm điều kiện khoảng cách tối thiểu
                    sigma_y, sigma_z = self.calc_dispersion_coefficients(x_wind)
                    
                    # Kiểm tra để tránh chia cho 0
                    if sigma_y > 0 and sigma_z > 0:
                        try:
                            C = (self.emission_rate / (self.wind_speed * np.pi * sigma_y * sigma_z)) * \
                                np.exp(-0.5 * (y_wind/sigma_y)**2) * \
                                (np.exp(-0.5 * (self.effective_height/sigma_z)**2))
                            
                            self.C[i,j] = C if not np.isnan(C) else 0
                        except:
                            self.C[i,j] = 0
                    else:
                        self.C[i,j] = 0
                else:
                    self.C[i,j] = 0

        try:
            # Tính nồng độ cực đại
            x_max = max(0.1, (self.wind_speed * self.effective_height**2) / (self.F**(1/3)))
            sigma_y_max, sigma_z_max = self.calc_dispersion_coefficients(x_max)
            C_max = self.emission_rate / (self.wind_speed * np.pi * sigma_y_max * sigma_z_max)
            
            print(f"\nKết quả tính toán:")
            print(f"Khoảng cách đến nồng độ cực đại (x_max): {x_max:.1f} m")
            print(f"Nồng độ cực đại (C_max): {C_max:.4f} mg/m³")
            
            # Tính thêm giá trị tối đa trong ma trận nồng độ
            print(f"Nồng độ lớn nhất trong vùng tính: {np.max(self.C):.4f} mg/m³")
        except:
            print("Không thể tính được nồng độ cực đại")

    def save_to_geotiff(self, output_path, threshold=0.01):
        """Lưu kết quả thành file GeoTIFF với màu đen cho các giá trị không đáng kể"""
        print(f"\nĐang lưu kết quả vào file {output_path}...")
        
        # Tạo mặt nạ cho các giá trị có ý nghĩa
        significant_mask = self.C > threshold * np.max(self.C)
        
        # Chuẩn hóa dữ liệu chỉ cho các giá trị có ý nghĩa
        max_concentration = np.max(self.C)
        min_concentration = np.min(self.C[self.C > 0])
        
        # Tạo ma trận giá trị grayscale
        grayscale_values = np.zeros_like(self.C)
        
        # Chuẩn hóa các giá trị có ý nghĩa để nằm trong khoảng 30-220
        normalized_values = (self.C[significant_mask] - min_concentration) / (max_concentration - min_concentration)
        grayscale_values[significant_mask] = 30 + normalized_values * (220 - 30)
        
        # Chuyển đổi sang uint8 và đảo ngược thang màu
        grayscale_values = 255 - grayscale_values.astype(np.uint8)
        
        # Đặt các giá trị không đáng kể thành đen (0)
        grayscale_values[~significant_mask] = 0
        
        # Tạo file GeoTIFF
        driver = gdal.GetDriverByName('GTiff')
        dataset = driver.Create(
            output_path,
            self.nx,
            self.ny,
            1,  # Số kênh (1 kênh ảnh xám)
            gdal.GDT_Byte  # Kiểu dữ liệu byte (0-255)
        )
        
        # Thiết lập thông tin địa lý
        dataset.SetGeoTransform((
            self.x_min,
            self.resolution/111111,
            0,
            self.y_min + self.height/111111,
            0,
            -self.resolution/111111
        ))
        
        # Thiết lập hệ tọa độ WGS 84
        srs = osr.SpatialReference()
        srs.ImportFromEPSG(4326)
        dataset.SetProjection(srs.ExportToWkt())
        
        # Thêm metadata
        dataset.SetMetadata({
            'TIFFTAG_DATETIME': datetime.now().strftime('%Y:%m:%d %H:%M:%S'),
            'SOURCE_HEIGHT': str(self.stack_height),
            'EFFECTIVE_HEIGHT': str(self.effective_height),
            'WIND_SPEED': str(self.wind_speed),
            'WIND_DIRECTION': str(self.wind_direction),
            'EMISSION_RATE': str(self.emission_rate),
            'STACK_TEMP': str(self.stack_temp),
            'AMBIENT_TEMP': str(self.ambient_temp),
            'MAX_CONCENTRATION': str(np.max(self.C)),
            'UNITS': 'mg/m³',
            'DESCRIPTION': 'Ground level concentration (z=0m)',
            'VISUALIZATION_RANGE': '30-220 grayscale for significant values'
        })
        
        # Ghi dữ liệu vào kênh ảnh xám
        band = dataset.GetRasterBand(1)
        band.WriteArray(grayscale_values)
        
        # Đóng dataset
        dataset = None
        print("Hoàn thành!")

def main():
    parser = argparse.ArgumentParser(description='Mô phỏng phát thải theo mô hình Gaussian Plume')
    
    # Thông số vùng tính
    parser.add_argument('--width', type=float, required=True, help='Chiều rộng vùng tính (m)')
    parser.add_argument('--height', type=float, required=True, help='Chiều cao vùng tính (m)')
    parser.add_argument('--resolution', type=float, default=10, help='Độ phân giải (m)')
    
    # Vị trí nguồn
    parser.add_argument('--source-x', type=float, required=True, help='Tọa độ X nguồn (lon)')
    parser.add_argument('--source-y', type=float, required=True, help='Tọa độ Y nguồn (lat)')
    
    # Thông số ống khói
    parser.add_argument('--stack-height', type=float, required=True, help='Chiều cao ống khói (m)')
    parser.add_argument('--stack-diameter', type=float, required=True, help='Đường kính ống khói (m)')
    parser.add_argument('--stack-temp', type=float, required=True, help='Nhiệt độ khí thải (°C)')
    parser.add_argument('--stack-velocity', type=float, required=True, help='Vận tốc phát thải (m/s)')
    parser.add_argument('--ambient-temp', type=float, default=30, help='Nhiệt độ môi trường (°C)')
    
    # Thông số phát thải và khí tượng
    parser.add_argument('--emission-rate', type=float, required=True, help='Tải lượng phát thải (mg/s)')
    parser.add_argument('--wind-speed', type=float, required=True, help='Tốc độ gió (m/s)')
    parser.add_argument('--wind-direction', type=float, default=270, help='Hướng gió (độ)')
    
    # File đầu ra
    parser.add_argument('--output', type=str, default='plume_concentration.tif', help='Tên file đầu ra')
    
    args = parser.parse_args()
    
    # Khởi tạo và chạy mô hình
    model = GaussianPlumeModel(args)
    model.calculate_concentration()
    model.save_to_geotiff(args.output)

if __name__ == "__main__":
    main()