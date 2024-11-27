<!DOCTYPE html>
<html>
<head>
    <title>Dữ liệu Quan trắc Môi trường</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Dữ liệu Quan trắc Môi trường</h1>
        
        <!-- Form tìm kiếm -->
        <form action="{{ route('air-quality.search') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <select name="location_code" class="form-control">
                    <option value="">Chọn vị trí</option>
                    <option value="KLV.01">KLV.01</option>
                    <option value="KLV.02">KLV.02</option>
                    <option value="KLV.03">KLV.03</option>
                    <option value="KLV.04">KLV.04</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" placeholder="Từ ngày">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" placeholder="Đến ngày">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </div>
        </form>

        <!-- Bảng dữ liệu -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Vị trí</th>
                        <th>Thời gian</th>
                        <th>Nhiệt độ (°C)</th>
                        <th>Độ ẩm (%)</th>
                        <th>Tốc độ gió (m/s)</th>
                        <th>Tiếng ồn (dBA)</th>
                        <th>Độ bụi (mg/m³)</th>
                        <th>CO (mg/m³)</th>
                        <th>SO₂ (mg/m³)</th>
                        <th>TSP (mg/m³)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($measurements as $measurement)
                    <tr>
                        <td>{{ $measurement->location_code }}</td>
                        <td>{{ $measurement->measurement_time }}</td>
                        <td>{{ $measurement->temperature }}</td>
                        <td>{{ $measurement->humidity }}</td>
                        <td>{{ $measurement->wind_speed }}</td>
                        <td>{{ $measurement->noise_level }}</td>
                        <td>{{ $measurement->dust_level }}</td>
                        <td>{{ $measurement->co_level }}</td>
                        <td>{{ $measurement->so2_level }}</td>
                        <td>{{ $measurement->tsp_level }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>