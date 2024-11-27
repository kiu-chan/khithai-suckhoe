<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quan trắc Môi trường</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-form {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .data-container {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .btn-primary {
            background-color: #1976d2;
            border-color: #1976d2;
            padding: 0.5rem 1.5rem;
        }
        
        .btn-primary:hover {
            background-color: #1565c0;
            border-color: #1565c0;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        
        .form-control, .form-select {
            border-color: #dee2e6;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1 class="mb-2">
                <i class="fas fa-chart-line me-2"></i>
                Hệ thống Quan trắc Môi trường
            </h1>
            <div class="d-flex align-items-center">
                <i class="fas fa-clock me-2"></i>
                <span>Cập nhật lần cuối: {{ now()->format('H:i:s d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Form tìm kiếm -->
        <div class="search-form">
            <form action="{{ route('air-quality.search') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-industry me-2"></i>Vị trí quan trắc
                        </label>
                        <select name="location_code" class="form-select">
                            <option value="">Tất cả vị trí</option>
                            @foreach($factories as $factory)
                                <option value="{{ $factory->code }}" 
                                    {{ request('location_code') == $factory->code ? 'selected' : '' }}>
                                    {{ $factory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar me-2"></i>Từ ngày
                        </label>
                        <input type="date" name="start_date" class="form-control" 
                            value="{{ request('start_date') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt me-2"></i>Đến ngày
                        </label>
                        <input type="date" name="end_date" class="form-control" 
                            value="{{ request('end_date') }}">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bảng dữ liệu -->
        <div class="data-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Vị trí</th>
                            <th>Thời gian</th>
                            <th class="text-end">Nhiệt độ (°C)</th>
                            <th class="text-end">Độ ẩm (%)</th>
                            <th class="text-end">Tốc độ gió (m/s)</th>
                            <th class="text-end">Tiếng ồn (dBA)</th>
                            <th class="text-end">Độ bụi (mg/m³)</th>
                            <th class="text-end">CO (mg/m³)</th>
                            <th class="text-end">SO₂ (mg/m³)</th>
                            <th class="text-end">TSP (mg/m³)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($measurements as $measurement)
                            <tr>
                                <td>{{ $measurement->factory->name }}</td>
                                <td>{{ Carbon\Carbon::parse($measurement->measurement_time)->format('H:i d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($measurement->temperature, 1) }}</td>
                                <td class="text-end">{{ number_format($measurement->humidity, 1) }}</td>
                                <td class="text-end">{{ number_format($measurement->wind_speed, 1) }}</td>
                                <td class="text-end">{{ number_format($measurement->noise_level, 1) }}</td>
                                <td class="text-end">{{ number_format($measurement->dust_level, 2) }}</td>
                                <td class="text-end">{{ number_format($measurement->co_level, 3) }}</td>
                                <td class="text-end">{{ number_format($measurement->so2_level, 3) }}</td>
                                <td class="text-end">{{ number_format($measurement->tsp_level, 3) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fas fa-database me-2"></i>Không có dữ liệu quan trắc
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="container text-center text-muted py-4">
        <p class="mb-0">
            <i class="fas fa-copyright me-2"></i>
            {{ date('Y') }} Hệ thống Quan trắc Môi trường
        </p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>