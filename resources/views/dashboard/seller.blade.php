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
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 1.5rem;
      transition: all 0.3s ease;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .stat-card:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transform: translateY(-2px);
      border-color: #d1d5db;
    }
    
    .stat-card-label {
      font-size: 0.8125rem;
      font-weight: 500;
      color: #6b7280;
      margin-bottom: 0.75rem;
      display: block;
      letter-spacing: 0.025em;
      text-transform: uppercase;
    }
    
    .stat-card-value {
      font-size: 1.875rem;
      font-weight: 700;
      color: #111827;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: wrap;
      line-height: 1.2;
    }
    
    .stat-card-value.revenue {
      font-size: 1.5rem;
      line-height: 1.4;
      word-break: break-word;
    }
    
    .stat-card-value.revenue > span:first-child {
      display: inline-block;
      margin-right: 0.5rem;
    }
    
    .stat-trend {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      font-size: 0.6875rem;
      font-weight: 600;
      padding: 0.375rem 0.625rem;
      border-radius: 6px;
      letter-spacing: 0.025em;
    }
    
    .stat-trend.up {
      background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
      color: #16a34a;
      border: 1px solid #86efac;
    }
    
    .stat-trend.down {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #dc2626;
      border: 1px solid #fca5a5;
    }
    
    .stat-trend.neutral {
      background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
      color: #6b7280;
      border: 1px solid #d1d5db;
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
    
    .stock-alert-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      margin-left: 0.5rem;
      padding: 0.25rem 0.5rem;
      background: #FFF3E0;
      color: #FF9800;
      border-radius: 4px;
      font-size: 0.65rem;
      font-weight: 600;
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
    
    /* Skeleton Loaders */
    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s ease-in-out infinite;
      border-radius: 4px;
    }
    
    @keyframes loading {
      0% {
        background-position: 200% 0;
      }
      100% {
        background-position: -200% 0;
      }
    }
    
    .skeleton-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1.25rem;
    }
    
    .skeleton-text {
      height: 1rem;
      margin-bottom: 0.5rem;
    }
    
    .skeleton-title {
      height: 2rem;
      width: 60%;
      margin-bottom: 0.5rem;
    }
    
    .skeleton-chart {
      height: 300px;
      width: 100%;
      margin-top: 1rem;
    }
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <h1>Dashboard</h1>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='/dashboard/sales?filter=today'">
        <span class="stat-card-label">Penjualan Hari Ini</span>
        <h2 class="stat-card-value">
          {{ number_format($salesToday ?? 0) }}
          @if(isset($salesTodayChange))
            @php
              $trendClass = $salesTodayChange > 0 ? 'up' : ($salesTodayChange < 0 ? 'down' : 'neutral');
              $trendIcon = $salesTodayChange > 0 ? 'fa-arrow-up' : ($salesTodayChange < 0 ? 'fa-arrow-down' : 'fa-minus');
            @endphp
            <span class="stat-trend {{ $trendClass }}">
              <i class="fa-solid {{ $trendIcon }}"></i>
              {{ abs(round($salesTodayChange, 1)) }}%
            </span>
          @endif
        </h2>
      </div>
      
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='/dashboard/sales'">
        <span class="stat-card-label">Penjualan Perbulan</span>
        <h2 class="stat-card-value">
          {{ number_format($salesThisMonth ?? 0) }}
          @if(isset($salesMonthChange))
            @php
              $trendClass = $salesMonthChange > 0 ? 'up' : ($salesMonthChange < 0 ? 'down' : 'neutral');
              $trendIcon = $salesMonthChange > 0 ? 'fa-arrow-up' : ($salesMonthChange < 0 ? 'fa-arrow-down' : 'fa-minus');
            @endphp
            <span class="stat-trend {{ $trendClass }}">
              <i class="fa-solid {{ $trendIcon }}"></i>
              {{ abs(round($salesMonthChange, 1)) }}%
            </span>
          @endif
        </h2>
      </div>
      
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='/dashboard/products'">
        <span class="stat-card-label">Produk</span>
        <h2 class="stat-card-value">
          {{ number_format($activeProducts ?? 0) }}
          @if(isset($activeProductsChange) && abs($activeProductsChange) > 0.1)
            @php
              $trendClass = $activeProductsChange > 0 ? 'up' : ($activeProductsChange < 0 ? 'down' : 'neutral');
              $trendIcon = $activeProductsChange > 0 ? 'fa-arrow-up' : ($activeProductsChange < 0 ? 'fa-arrow-down' : 'fa-minus');
            @endphp
            <span class="stat-trend {{ $trendClass }}">
              <i class="fa-solid {{ $trendIcon }}"></i>
              {{ abs(round($activeProductsChange, 1)) }}%
            </span>
          @endif
        </h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Alat Aktif</span>
        <h2 class="stat-card-value">
          {{ number_format($activeTools ?? 0) }}
          @if(isset($activeToolsChange) && abs($activeToolsChange) > 0.1)
            @php
              $trendClass = $activeToolsChange > 0 ? 'up' : ($activeToolsChange < 0 ? 'down' : 'neutral');
              $trendIcon = $activeToolsChange > 0 ? 'fa-arrow-up' : ($activeToolsChange < 0 ? 'fa-arrow-down' : 'fa-minus');
            @endphp
            <span class="stat-trend {{ $trendClass }}">
              <i class="fa-solid {{ $trendIcon }}"></i>
              {{ abs(round($activeToolsChange, 1)) }}%
            </span>
          @endif
        </h2>
      </div>
      
      <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='/dashboard/reviews'">
        <span class="stat-card-label">Ulasan Baru</span>
        <h2 class="stat-card-value">
          {{ number_format($newReviews ?? 0) }}
          @if(isset($newReviewsChange))
            @php
              $trendClass = $newReviewsChange > 0 ? 'up' : ($newReviewsChange < 0 ? 'down' : 'neutral');
              $trendIcon = $newReviewsChange > 0 ? 'fa-arrow-up' : ($newReviewsChange < 0 ? 'fa-arrow-down' : 'fa-minus');
            @endphp
            <span class="stat-trend {{ $trendClass }}">
              <i class="fa-solid {{ $trendIcon }}"></i>
              {{ abs(round($newReviewsChange, 1)) }}%
            </span>
          @endif
        </h2>
      </div>
      
      <div class="stat-card">
        <span class="stat-card-label">Total Pendapatan Perbulan</span>
        <h2 class="stat-card-value revenue">
          <span>Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</span>
          @if(isset($totalRevenueChange))
            @php
              $trendClass = $totalRevenueChange > 0 ? 'up' : ($totalRevenueChange < 0 ? 'down' : 'neutral');
              $trendIcon = $totalRevenueChange > 0 ? 'fa-arrow-up' : ($totalRevenueChange < 0 ? 'fa-arrow-down' : 'fa-minus');
            @endphp
            <span class="stat-trend {{ $trendClass }}">
              <i class="fa-solid {{ $trendIcon }}"></i>
              {{ abs(round($totalRevenueChange, 1)) }}%
            </span>
          @endif
        </h2>
      </div>
    </div>
    
    <!-- Chart Penjualan Produk -->
    <div class="content-card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 class="content-card-title" style="margin: 0;">
          <i class="fa-solid fa-chart-line me-2"></i>Grafik Penjualan
        </h3>
        <div class="chart-range-selector" style="display: flex; gap: 0.5rem; align-items: center;">
          <select id="chartRangeSelect" class="form-select form-select-sm" style="width: auto; display: inline-block;">
            <option value="3days" {{ ($dateRange ?? '7days') === '3days' ? 'selected' : '' }}>3 Hari</option>
            <option value="7days" {{ ($dateRange ?? '7days') === '7days' ? 'selected' : '' }}>7 Hari</option>
            <option value="30days" {{ ($dateRange ?? '7days') === '30days' ? 'selected' : '' }}>30 Hari</option>
            <option value="3months" {{ ($dateRange ?? '7days') === '3months' ? 'selected' : '' }}>3 Bulan</option>
            <option value="1year" {{ ($dateRange ?? '7days') === '1year' ? 'selected' : '' }}>1 Tahun</option>
          </select>
          <div class="form-check form-switch" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <input class="form-check-input" type="checkbox" id="compareToggle" style="cursor: pointer;">
            <label class="form-check-label" for="compareToggle" style="font-size: 0.75rem; color: #6c757d; cursor: pointer; margin: 0;">
              Bandingkan
            </label>
          </div>
          <button onclick="exportChart()" class="btn btn-sm btn-outline-secondary" title="Download Grafik">
            <i class="fa-solid fa-download"></i>
          </button>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="salesChart"></canvas>
      </div>
    </div>
    
    <!-- Chart Pendapatan -->
    <div class="content-card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 class="content-card-title" style="margin: 0;">
          <i class="fa-solid fa-chart-line me-2"></i>Grafik Pendapatan
        </h3>
        <div class="chart-range-selector" style="display: flex; gap: 0.5rem; align-items: center;">
          <select id="revenueChartRangeSelect" class="form-select form-select-sm" style="width: auto; display: inline-block;">
            <option value="3days" {{ ($revenueDateRange ?? ($dateRange ?? '7days')) === '3days' ? 'selected' : '' }}>3 Hari</option>
            <option value="7days" {{ ($revenueDateRange ?? ($dateRange ?? '7days')) === '7days' ? 'selected' : '' }}>7 Hari</option>
            <option value="30days" {{ ($revenueDateRange ?? ($dateRange ?? '7days')) === '30days' ? 'selected' : '' }}>30 Hari</option>
            <option value="3months" {{ ($revenueDateRange ?? ($dateRange ?? '7days')) === '3months' ? 'selected' : '' }}>3 Bulan</option>
            <option value="1year" {{ ($revenueDateRange ?? ($dateRange ?? '7days')) === '1year' ? 'selected' : '' }}>1 Tahun</option>
          </select>
          <div class="form-check form-switch" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <input class="form-check-input" type="checkbox" id="revenueCompareToggle" style="cursor: pointer;">
            <label class="form-check-label" for="revenueCompareToggle" style="font-size: 0.75rem; color: #6c757d; cursor: pointer; margin: 0;">
              Bandingkan
            </label>
          </div>
        </div>
      </div>
      <div class="chart-container">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>
    
    <!-- Chart Penjualan Per Produk -->
    <div class="content-card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 class="content-card-title" style="margin: 0;">
          <i class="fa-solid fa-chart-bar me-2"></i>Penjualan Per Produk
        </h3>
        <div class="chart-range-selector" style="display: flex; gap: 0.5rem; align-items: center;">
          <select id="productChartRangeSelect" class="form-select form-select-sm" style="width: auto; display: inline-block;">
            <option value="3days" {{ ($productDateRange ?? ($dateRange ?? '7days')) === '3days' ? 'selected' : '' }}>3 Hari</option>
            <option value="7days" {{ ($productDateRange ?? ($dateRange ?? '7days')) === '7days' ? 'selected' : '' }}>7 Hari</option>
            <option value="30days" {{ ($productDateRange ?? ($dateRange ?? '7days')) === '30days' ? 'selected' : '' }}>30 Hari</option>
            <option value="3months" {{ ($productDateRange ?? ($dateRange ?? '7days')) === '3months' ? 'selected' : '' }}>3 Bulan</option>
            <option value="1year" {{ ($productDateRange ?? ($dateRange ?? '7days')) === '1year' ? 'selected' : '' }}>1 Tahun</option>
          </select>
          <div class="form-check form-switch" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <input class="form-check-input" type="checkbox" id="productCompareToggle" style="cursor: pointer;">
            <label class="form-check-label" for="productCompareToggle" style="font-size: 0.75rem; color: #6c757d; cursor: pointer; margin: 0;">
              Bandingkan
            </label>
          </div>
        </div>
      </div>
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
              // Ambil gambar dari database - gunakan field 'url' bukan 'image_url'
              $firstImage = $product->images->first();
              $imageUrl = null;
              
              if ($firstImage && !empty($firstImage->url)) {
                $imageUrl = $firstImage->url;
                
                // Jika URL adalah path relatif (bukan URL lengkap), tambahkan asset path
                if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL) && !str_starts_with($imageUrl, 'data:')) {
                  // Cek apakah sudah ada 'storage/' di path
                  if (str_starts_with($imageUrl, 'storage/')) {
                    $imageUrl = asset($imageUrl);
                  } else {
                    $imageUrl = asset('storage/' . $imageUrl);
                  }
                }
              }
              
              // Fallback ke placeholder jika tidak ada gambar
              $placeholder = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZHVjdDwvdGV4dD48L3N2Zz4=';
            @endphp
            <div class="popular-product-item">
              <img src="{{ $imageUrl ?? $placeholder }}" 
                   alt="{{ $product->name }}" 
                   class="popular-product-image"
                   onerror="this.onerror=null; this.src='{{ $placeholder }}'">
              <div class="popular-product-info">
                <div class="popular-product-name">
                  {{ $product->name }}
                  @if(($product->stock ?? 0) < 10)
                    <span class="stock-alert-badge" title="Stok Rendah">
                      <i class="fa-solid fa-exclamation-triangle"></i> Stok Rendah
                    </span>
                  @endif
                </div>
                <div class="popular-product-stats">
                  @php
                    $avgRating = $product->avg_rating ?? 0;
                    $totalReviews = $product->total_reviews ?? 0;
                  @endphp
                  @if($avgRating > 0)
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                      <div style="display: flex; gap: 2px;">
                        @php
                          $fullStars = (int)floor($avgRating);
                          $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                        @endphp
                        @for($i = 1; $i <= 5; $i++)
                          @if($i <= $fullStars)
                            <i class="fa-solid fa-star" style="font-size: 0.7rem; color: #FFC107;"></i>
                          @elseif($i == ($fullStars + 1) && $hasHalfStar)
                            <i class="fa-solid fa-star-half-stroke" style="font-size: 0.7rem; color: #FFC107;"></i>
                          @else
                            <i class="fa-regular fa-star" style="font-size: 0.7rem; color: #E5E7EB;"></i>
                          @endif
                        @endfor
                      </div>
                      <span style="font-size: 0.7rem; color: #6c757d; font-weight: 600;">{{ number_format($avgRating, 1) }}</span>
                      <span style="font-size: 0.65rem; color: #9CA3AF;">({{ number_format($totalReviews) }} ulasan)</span>
                    </div>
                  @else
                    <div style="font-size: 0.7rem; color: #9CA3AF; margin-bottom: 0.25rem;">Belum ada rating</div>
                  @endif
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
    
    // Chart Range Selector
    document.getElementById('chartRangeSelect')?.addEventListener('change', function() {
      const range = this.value;
      const productRange = document.getElementById('productChartRangeSelect')?.value || range;
      const revenueRange = document.getElementById('revenueChartRangeSelect')?.value || range;
      window.location.href = '/dashboard?chart_range=' + range + '&product_chart_range=' + productRange + '&revenue_chart_range=' + revenueRange;
    });
    
    // Product Chart Range Selector
    document.getElementById('productChartRangeSelect')?.addEventListener('change', function() {
      const range = this.value;
      const salesRange = document.getElementById('chartRangeSelect')?.value || '7days';
      const revenueRange = document.getElementById('revenueChartRangeSelect')?.value || '7days';
      window.location.href = '/dashboard?chart_range=' + salesRange + '&product_chart_range=' + range + '&revenue_chart_range=' + revenueRange;
    });
    
    // Sales Chart
    const salesChartData = @json($salesChartData ?? []);
    const salesChartDataCompare = @json($salesChartDataCompare ?? []);
    const salesCtx = document.getElementById('salesChart');
    let salesChart = null;
    
    function updateChart(showCompare = false) {
      if (!salesCtx || salesChartData.length === 0) return;
      
      const datasets = [{
        label: 'Jumlah Penjualan',
        data: salesChartData.map(item => item.sales),
        borderColor: '#22C55E',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }];
      
      if (showCompare && salesChartDataCompare.length > 0) {
        datasets.push({
          label: 'Periode Sebelumnya',
          data: salesChartDataCompare.map(item => item.sales),
          borderColor: '#9CA3AF',
          backgroundColor: 'rgba(156, 163, 175, 0.1)',
          borderWidth: 2,
          borderDash: [5, 5],
          fill: false,
          tension: 0.4
        });
      }
      
      if (salesChart) {
        salesChart.data.datasets = datasets;
        salesChart.update();
      } else {
        salesChart = new Chart(salesCtx, {
          type: 'line',
          data: {
            labels: salesChartData.map(item => item.date),
            datasets: datasets
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              },
              tooltip: {
                mode: 'index',
                intersect: false
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
    }
    
    // Initialize chart
    if (salesCtx && salesChartData.length > 0) {
      updateChart(false);
    }
    
    // Comparison toggle
    document.getElementById('compareToggle')?.addEventListener('change', function() {
      updateChart(this.checked);
    });
    
    // Revenue Chart
    const revenueChartData = @json($revenueChartData ?? []);
    const revenueChartDataCompare = @json($revenueChartDataCompare ?? []);
    const revenueCtx = document.getElementById('revenueChart');
    let revenueChart = null;
    
    function updateRevenueChart(showCompare = false) {
      if (!revenueCtx || revenueChartData.length === 0) return;
      
      const datasets = [{
        label: 'Pendapatan (Rp)',
        data: revenueChartData.map(item => item.revenue),
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }];
      
      if (showCompare && revenueChartDataCompare.length > 0) {
        datasets.push({
          label: 'Periode Sebelumnya',
          data: revenueChartDataCompare.map(item => item.revenue),
          borderColor: '#9CA3AF',
          backgroundColor: 'rgba(156, 163, 175, 0.1)',
          borderWidth: 2,
          borderDash: [5, 5],
          fill: false,
          tension: 0.4
        });
      }
      
      if (revenueChart) {
        revenueChart.data.datasets = datasets;
        revenueChart.update();
      } else {
        revenueChart = new Chart(revenueCtx, {
          type: 'line',
          data: {
            labels: revenueChartData.map(item => item.date),
            datasets: datasets
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              },
              tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                  label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                      label += ': ';
                    }
                    if (context.parsed.y !== null) {
                      label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                    }
                    return label;
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                  }
                }
              }
            }
          }
        });
      }
    }
    
    // Initialize revenue chart
    if (revenueCtx && revenueChartData.length > 0) {
      updateRevenueChart(false);
    }
    
    // Revenue comparison toggle
    document.getElementById('revenueCompareToggle')?.addEventListener('change', function() {
      updateRevenueChart(this.checked);
    });
    
    // Revenue Chart Range Selector
    document.getElementById('revenueChartRangeSelect')?.addEventListener('change', function() {
      const range = this.value;
      const salesRange = document.getElementById('chartRangeSelect')?.value || '7days';
      const productRange = document.getElementById('productChartRangeSelect')?.value || '7days';
      window.location.href = '/dashboard?chart_range=' + salesRange + '&product_chart_range=' + productRange + '&revenue_chart_range=' + range;
    });
    
    // Export chart function - download all charts
    function exportChart() {
      if (!salesChart) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Grafik penjualan belum tersedia',
          confirmButtonColor: '#22C55E'
        });
        return;
      }
      
      const dateStr = new Date().toISOString().split('T')[0];
      let downloadCount = 0;
      
      // Download grafik penjualan
      const salesUrl = salesChart.toBase64Image('image/png', 1);
      const salesLink = document.createElement('a');
      salesLink.download = 'grafik-penjualan-' + dateStr + '.png';
      salesLink.href = salesUrl;
      salesLink.click();
      downloadCount++;
      
      // Download grafik pendapatan (jika tersedia)
      if (revenueChart) {
        setTimeout(() => {
          const revenueUrl = revenueChart.toBase64Image('image/png', 1);
          const revenueLink = document.createElement('a');
          revenueLink.download = 'grafik-pendapatan-' + dateStr + '.png';
          revenueLink.href = revenueUrl;
          revenueLink.click();
        }, 300 * downloadCount);
        downloadCount++;
      }
      
      // Download diagram batang produk (jika tersedia)
      if (productSalesChart) {
        setTimeout(() => {
          const productUrl = productSalesChart.toBase64Image('image/png', 1);
          const productLink = document.createElement('a');
          productLink.download = 'diagram-batang-produk-' + dateStr + '.png';
          productLink.href = productUrl;
          productLink.click();
        }, 300 * downloadCount); // Delay kecil untuk memastikan download sebelumnya selesai
      }
    }
    
    // Product Sales Chart
    const productSalesData = @json($productSalesData ?? []);
    const productSalesDataCompare = @json($productSalesDataCompare ?? []);
    const productSalesCtx = document.getElementById('productSalesChart');
    let productSalesChart = null;
    
    function updateProductChart(showCompare = false) {
      if (!productSalesCtx || productSalesData.length === 0) return;
      
      const labels = productSalesData.map(item => item.name);
      const datasets = [{
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
      }];
      
      if (showCompare && productSalesDataCompare.length > 0) {
        datasets.push({
          label: 'Periode Sebelumnya',
          data: productSalesDataCompare.map(item => item.total_sold),
          backgroundColor: 'rgba(156, 163, 175, 0.6)',
          borderColor: '#9CA3AF',
          borderWidth: 1
        });
      }
      
      if (productSalesChart) {
        productSalesChart.data.labels = labels;
        productSalesChart.data.datasets = datasets;
        productSalesChart.update();
      } else {
        productSalesChart = new Chart(productSalesCtx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: datasets
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: showCompare,
                position: 'top'
              },
              tooltip: {
                mode: 'index',
                intersect: false
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
    }
    
    // Initialize product chart
    if (productSalesCtx && productSalesData.length > 0) {
      updateProductChart(false);
    } else if (productSalesCtx) {
      productSalesCtx.parentElement.innerHTML = '<div class="empty-state"><i class="fa-solid fa-chart-bar"></i><p class="mb-0">Belum ada data penjualan produk</p></div>';
    }
    
    // Product comparison toggle
    document.getElementById('productCompareToggle')?.addEventListener('change', function() {
      updateProductChart(this.checked);
    });
    
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
