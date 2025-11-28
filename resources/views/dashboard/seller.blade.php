<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Dashboard - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  
  <!-- Google Fonts - Inter (Premium Typography) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  
  <style>
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    body { background: #F8F9FB; margin: 0; }
    
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
        padding: 1rem;
        margin-top: 60px;
      }
    }
    
    .main-content {
      margin-left: 220px;
      padding: 1.5rem;
    }
    
    .page-header {
      margin-bottom: 1.5rem;
    }
    
    .page-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2F2F2F;
      margin: 0;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1.25rem;
      transition: box-shadow 0.2s;
    }
    
    .stat-card:hover {
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .stat-card-label {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .stat-card-value {
      font-size: 1.75rem;
      font-weight: 700;
      color: #2F2F2F;
      margin: 0;
    }
    
    .content-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    
    .content-card-title {
      font-size: 1rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 1rem;
    }
    
    .chart-container {
      position: relative;
      height: 300px;
      margin-top: 1rem;
    }
    
    .popular-product-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.75rem;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .popular-product-item:last-child {
      border-bottom: none;
    }
    
    .popular-product-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #e9ecef;
    }
    
    .popular-product-info {
      flex: 1;
    }
    
    .popular-product-name {
      font-size: 0.875rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .popular-product-stats {
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: #6c757d;
    }
    
    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.3;
    }
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <h1>Home</h1>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <span class="stat-card-label">Penjualan Hari Ini</span>
        <h2 class="stat-card-value">{{ number_format($salesToday ?? 0) }}</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Penjualan Perbulan</span>
        <h2 class="stat-card-value">{{ number_format($salesThisMonth ?? 0) }}</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Produk Aktif</span>
        <h2 class="stat-card-value">{{ number_format($activeProducts ?? 0) }}</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Alat Aktif</span>
        <h2 class="stat-card-value">{{ number_format($activeTools ?? 0) }}</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Ulasan Baru</span>
        <h2 class="stat-card-value">{{ number_format($newReviews ?? 0) }}</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Chat Baru</span>
        <h2 class="stat-card-value">{{ number_format($newChats ?? 0) }}</h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Total Pendapatan Perbulan</span>
        <h2 class="stat-card-value">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h2>
      </div>
    </div>
    
    <!-- Chart Penjualan Produk -->
    <div class="content-card">
      <h3 class="content-card-title">
        <i class="fa-solid fa-chart-line me-2"></i>Grafik Penjualan (7 Hari Terakhir)
      </h3>
      <div class="chart-container">
        <canvas id="salesChart"></canvas>
      </div>
    </div>
    
    <!-- Chart Penjualan Per Produk -->
    <div class="content-card">
      <h3 class="content-card-title">
        <i class="fa-solid fa-chart-pie me-2"></i>Penjualan Per Produk (Bulan Ini)
      </h3>
      <div class="chart-container">
        <canvas id="productSalesChart"></canvas>
      </div>
    </div>
    
    <!-- Produk Terpopuler -->
    <div class="content-card">
      <h3 class="content-card-title">
        <i class="fa-solid fa-fire me-2"></i>Produk Terpopuler
      </h3>
      @if(isset($popularProducts) && $popularProducts->count() > 0)
        <div>
          @foreach($popularProducts as $product)
            @php
              $totalSold = $product->total_sold ?? 0;
              $imageUrl = $product->images->first()->image_url ?? null;
              if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL) && !str_starts_with($imageUrl, 'data:')) {
                $imageUrl = asset('storage/' . $imageUrl);
              }
            @endphp
            <div class="popular-product-item">
              <img src="{{ $imageUrl ?? 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZHVjdDwvdGV4dD48L3N2Zz4=' }}" 
                   alt="{{ $product->name }}" 
                   class="popular-product-image"
                   onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZHVjdDwvdGV4dD48L3N2Zz4='">
              <div class="popular-product-info">
                <div class="popular-product-name">{{ $product->name }}</div>
                <div class="popular-product-stats">
                  Terjual: {{ number_format($totalSold) }} unit | 
                  Stok: {{ number_format($product->stock ?? 0) }} | 
                  Harga: Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-state">
          <i class="fa-solid fa-box-open"></i>
          <p class="mb-0">Belum ada data produk terpopuler</p>
        </div>
      @endif
    </div>
  </main>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/dashboard-alerts.js') }}"></script>
  
  <script>
    // SweetAlert Helper Functions
    window.showSuccess = function(message) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            confirmButtonColor: '#22C55E',
            confirmButtonText: 'OK'
        });
    };
    
    window.showError = function(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'OK'
        });
    };
    
    // Sales Chart (7 Days)
    const salesChartData = @json($salesChartData ?? []);
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx && salesChartData.length > 0) {
      new Chart(salesCtx, {
        type: 'line',
        data: {
          labels: salesChartData.map(item => item.date),
          datasets: [{
            label: 'Jumlah Penjualan',
            data: salesChartData.map(item => item.sales),
            borderColor: '#22C55E',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    }
    
    // Product Sales Chart (This Month)
    const productSalesData = @json($productSalesData ?? []);
    const productSalesCtx = document.getElementById('productSalesChart');
    if (productSalesCtx && productSalesData.length > 0) {
      new Chart(productSalesCtx, {
        type: 'bar',
        data: {
          labels: productSalesData.map(item => item.name),
          datasets: [{
            label: 'Jumlah Terjual',
            data: productSalesData.map(item => item.total_sold),
            backgroundColor: [
              'rgba(34, 197, 94, 0.8)',
              'rgba(59, 130, 246, 0.8)',
              'rgba(168, 85, 247, 0.8)',
              'rgba(236, 72, 153, 0.8)',
              'rgba(251, 146, 60, 0.8)'
            ],
            borderColor: [
              '#22C55E',
              '#3B82F6',
              '#A855F7',
              '#EC4899',
              '#FB923C'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    } else if (productSalesCtx) {
      productSalesCtx.parentElement.innerHTML = '<div class="empty-state"><i class="fa-solid fa-chart-bar"></i><p class="mb-0">Belum ada data penjualan produk bulan ini</p></div>';
    }
    
    // Show success message if redirected with success
    @if(session('success'))
        showSuccess('{{ session('success') }}');
    @endif
    
    // Show error message if redirected with error
    @if(session('error'))
        showError('{{ session('error') }}');
    @endif
  </script>
</body>
</html>
