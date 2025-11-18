<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Monitoring Alat - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
  
  <style>
    * { font-family: 'Inter', -apple-system, sans-serif; }
    body { background: #f8f9fa; margin: 0; }
    
    .sidebar {
      width: 220px;
      background: white;
      border-right: 1px solid #e9ecef;
      min-height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
    }
    
    .sidebar-header {
      padding: 1.25rem 1rem;
      border-bottom: 1px solid #e9ecef;
      font-weight: 700;
      font-size: 0.95rem;
      color: #2F2F2F;
    }
    
    .sidebar-profile {
      padding: 1.25rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      border-bottom: 1px solid #e9ecef;
    }
    
    .sidebar-profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #e9ecef;
    }
    
    .sidebar-profile-info h6 {
      margin: 0;
      font-size: 0.875rem;
      font-weight: 600;
      color: #2F2F2F;
    }
    
    .sidebar-profile-info p {
      margin: 0;
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .sidebar-menu {
      padding: 1rem 0;
    }
    
    .sidebar-menu-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.65rem 1rem;
      color: #6c757d;
      text-decoration: none;
      font-size: 0.875rem;
      transition: all 0.2s;
      cursor: pointer;
    }
    
    .sidebar-menu-item:hover,
    .sidebar-menu-item.active {
      background: #f8f9fa;
      color: #69B578;
    }
    
    .sidebar-menu-item.active {
      color: #69B578;
    }
    
    .sidebar-menu-item i {
      width: 20px;
      text-align: center;
    }
    
    .sidebar-submenu {
      display: none;
      padding-left: 2.5rem;
    }
    
    .sidebar-submenu.show {
      display: block;
    }
    
    .sidebar-submenu a {
      display: block;
      padding: 0.5rem 1rem;
      color: #6c757d;
      text-decoration: none;
      font-size: 0.875rem;
      transition: all 0.2s;
    }
    
    .sidebar-submenu a:hover,
    .sidebar-submenu a.active {
      color: #69B578;
    }
    
    .chevron-icon {
      margin-left: auto;
      font-size: 0.7rem;
      transition: transform 0.2s;
    }
    
    .chevron-icon.rotate {
      transform: rotate(180deg);
    }
    
    .sidebar-footer {
      position: absolute;
      bottom: 1rem;
      left: 0;
      right: 0;
      padding: 0 1rem;
    }
    
    .main-content {
      margin-left: 220px;
      padding: 1.5rem;
    }
    
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    
    .page-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2F2F2F;
      margin: 0;
    }
    
    .btn-add {
      background: #69B578;
      color: white;
      border: none;
      padding: 0.6rem 1.5rem;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s;
    }
    
    .btn-add:hover {
      background: #5a9d66;
      color: white;
    }
    
    .content-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      overflow: hidden;
    }
    
    .filter-bar {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
    }
    
    .filter-tabs {
      display: flex;
      gap: 0;
      border-bottom: 2px solid #e9ecef;
      position: relative;
    }
    
    .filter-tab {
      padding: 0.75rem 1.5rem;
      border: none;
      background: transparent;
      color: #6c757d;
      font-size: 0.875rem;
      cursor: pointer;
      transition: all 0.2s;
      position: relative;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
    }
    
    .filter-tab.active {
      color: #69B578;
      border-bottom-color: #69B578;
      font-weight: 500;
    }
    
    .filter-tab:hover:not(.active) {
      color: #2F2F2F;
    }
    
    .filter-right {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .search-box {
      position: relative;
      width: 200px;
    }
    
    .search-box input {
      width: 100%;
      padding: 0.5rem 0.75rem 0.5rem 2.25rem;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      font-size: 0.875rem;
      background: #f8f9fa;
    }
    
    .search-box input:focus {
      outline: none;
      border-color: #69B578;
      background: white;
    }
    
    .search-box i {
      position: absolute;
      left: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      font-size: 0.75rem;
    }
    
    .product-table {
      width: 100%;
    }
    
    .product-table thead {
      background: white;
      border-bottom: 1px solid #e9ecef;
    }
    
    .product-table th {
      padding: 1rem 1.5rem;
      font-size: 0.8rem;
      font-weight: 500;
      color: #6c757d;
      text-align: left;
    }
    
    .product-table td {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #f8f9fa;
      font-size: 0.875rem;
      color: #2F2F2F;
      vertical-align: middle;
    }
    
    .product-info {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .product-img {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      object-fit: cover;
      background: #f8f9fa;
    }
    
    .product-name {
      font-weight: 500;
      color: #2F2F2F;
      font-size: 0.875rem;
    }
    
    .product-subtitle {
      font-size: 0.75rem;
      color: #6c757d;
      margin-top: 0.25rem;
    }
    
    .rating-stars {
      color: #ffc107;
      font-size: 0.875rem;
    }
    
    .status-badge {
      padding: 0.35rem 0.85rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 400;
      display: inline-block;
      background: #f0f0f0;
      color: #6c757d;
    }
    
    .action-badge {
      padding: 0.35rem 0.85rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 400;
      display: inline-block;
      background: #f0f0f0;
      color: #6c757d;
    }
    
    .checkbox-cell {
      width: 40px;
    }
    
    .checkbox-cell input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    
    .product-table tbody tr {
      transition: all 0.2s;
    }
    
    .product-table tbody tr:hover {
      background: #f8f9fa;
    }
    
    .performa-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.25rem 0.75rem;
      background: #f8f9fa;
      border-radius: 6px;
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .performa-value {
      font-weight: 700;
      color: #2F2F2F;
    }
    
    .empty-state-hero {
      padding: 3rem 1.5rem 2rem;
      text-align: center;
      background: white;
      border-bottom: 1px solid #e9ecef;
    }
    
    .empty-hero-cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
      max-width: 800px;
      margin: 0 auto 2rem;
    }
    
    .empty-hero-card {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 2rem 1rem;
      min-height: 120px;
    }
    
    .empty-state-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
    }
    
    .empty-state-text {
      font-size: 0.875rem;
      color: #6c757d;
      margin: 0;
    }

    /* Monitoring Dashboard */
    .monitoring-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .sensor-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1rem 1rem 0.85rem;
      position: relative;
      overflow: hidden;
    }
    .sensor-card h6 {
      margin: 0 0 0.25rem;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: .5px;
      font-weight: 600;
      color: #6c757d;
    }
    .sensor-value {
      font-size: 1.7rem;
      font-weight: 600;
      line-height: 1.1;
      margin: 0;
      color: #2F2F2F;
    }
    .sensor-unit { font-size: 0.75rem; color:#6c757d; margin-left: 4px; }
    .trend-chip {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 0.65rem;
      padding: 0.25rem 0.5rem;
      border-radius: 6px;
      background: #f8f9fa;
      color: #6c757d;
      margin-top: .5rem;
    }
    .trend-chip.up { color:#2F2F2F; }
    .trend-chip.down { color:#dc3545; }
    .prediction-banner {
      background: linear-gradient(90deg,#69B578,#5a9d66);
      color: white;
      border-radius: 10px;
      padding: 1.1rem 1.25rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .prediction-banner i { font-size: 1.4rem; }
    .prediction-banner h5 { margin:0 0 .35rem; font-size: .95rem; font-weight:600; }
    .prediction-banner p { margin:0; font-size:.7rem; opacity:.9; line-height:1.2; }
    .chart-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
    }
    .chart-card h6 { margin:0 0 .75rem; font-size:.8rem; font-weight:600; color:#6c757d; }
    /* Forecast layout */
    .forecast-grid{ display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .forecast-col h5{ margin:0 0 .4rem; font-size:.78rem; font-weight:700; color:#2F2F2F; }
    .metric-item{ display:flex; align-items:flex-start; gap:.6rem; padding:.55rem .6rem; border-radius:8px; background:#f8f9fa; font-size:.72rem; }
    .metric-icon{ width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; background:#69B578; font-size:.8rem; flex:0 0 26px; }
    .metric-item.risk-ok{ border-left:3px solid #69B578; }
    .metric-item.risk-warn{ border-left:3px solid #F4C430; }
    .metric-item.risk-crit{ border-left:3px solid #dc3545; }
    .anomaly-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1rem 1.25rem;
    }
    .anomaly-card h6 { margin:0 0 .6rem; font-size:.8rem; font-weight:600; color:#6c757d; }
    .anomaly-item {
      display:flex;
      align-items:flex-start;
      gap:.75rem;
      padding:.6rem .5rem;
      border-radius:8px;
      background:#f8f9fa;
      margin-bottom:.5rem;
      font-size:.75rem;
    }
    .anomaly-item:last-child { margin-bottom:0; }
    .anomaly-item .anomaly-tag {
      font-size:.6rem;
      padding:.2rem .4rem;
      background:#dc3545;
      color:white;
      border-radius:4px;
      text-transform:uppercase;
      letter-spacing:.5px;
    }
    .loading-overlay { text-align:center; padding:2rem 0; font-size:.8rem; color:#6c757d; }

    /* Data preview */
    .data-card { background:white; border:1px solid #e9ecef; border-radius:10px; padding:1rem 1.25rem; }
    .data-card h6 { margin:0 0 .6rem; font-size:.8rem; font-weight:600; color:#6c757d; }
    .data-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap:.75rem; }
    .data-tile { background:#f8f9fa; border-radius:10px; padding:.7rem .8rem; display:flex; align-items:center; gap:.6rem; }
    .data-tile .kpi-icon{ width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; background:#69B578; }
    .data-tile .label { font-size:.68rem; color:#6c757d; }
    .data-tile .val { font-size:1.1rem; font-weight:700; color:#2F2F2F; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      ChickPatrol Seller
    </div>
    
    <div class="sidebar-profile">
      <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%23e9ecef'/%3E%3C/svg%3E" alt="Profile">
      <div class="sidebar-profile-info">
        <h6>Anto Farm</h6>
        <p>Penjual</p>
      </div>
    </div>
    
    <div class="performa-badge mx-3 mt-3">
      Performa Toko
      <span class="performa-value">95/100</span>
    </div>
    
    <nav class="sidebar-menu">
      <a href="{{ route('dashboard') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
      </a>
      <a href="{{ route('dashboard.products') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-box"></i>
        <span>Produk</span>
      </a>
      <div class="sidebar-menu-item active" onclick="toggleSubmenu()">
        <i class="fa-solid fa-wrench"></i>
        <span>Alat</span>
        <i class="fa-solid fa-chevron-down chevron-icon rotate"></i>
      </div>
      <div class="sidebar-submenu show">
        <a href="{{ route('dashboard.tools') }}">Daftar alat</a>
        <a href="{{ route('dashboard.tools.monitoring') }}" class="active">Monitoring Alat</a>
      </div>
      <a href="{{ route('dashboard.sales') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-shopping-cart"></i>
        <span>Penjualan</span>
      </a>
      <a href="{{ route('dashboard.chat') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-comment"></i>
        <span>Chat</span>
      </a>
    </nav>
    
    <div class="sidebar-footer">
      <a href="{{ route('login') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </div>
  </aside>
  
  <!-- Main Content -->
  <main class="main-content">
    <!-- Monitoring Summary Banner (dynamic) -->
    <div id="predictionBanner" class="prediction-banner" style="display:none;">
      <i class="fa-solid fa-chart-line"></i>
      <div>
        <h5 id="envStatusTitle">Memuat...</h5>
        <p id="envStatusDetail">Analisis kondisi lingkungan kandang akan muncul di sini.</p>
        <p id="envForecastDetail" style="margin-top:.4rem; font-weight:500;"></p>
      </div>
    </div>

    <!-- Sensor Cards -->
    <div id="sensorGrid" class="monitoring-grid"></div>

    <!-- Trend Charts -->
    <div class="chart-card">
      <h6>Tren 24 Jam Terakhir & Prediksi 6 Jam</h6>
      <canvas id="trendChart" height="120"></canvas>
    </div>

    <div class="chart-card" id="forecastCard" style="display:none;">
      <h6>Ringkasan Prediksi (6 Jam & 24 Jam)</h6>
      <div class="forecast-grid">
        <div class="forecast-col">
          <h5>6 Jam</h5>
          <div id="forecastList6" style="display:grid; gap:.4rem;"></div>
        </div>
        <div class="forecast-col">
          <h5>24 Jam</h5>
          <div id="forecastList24" style="display:grid; gap:.4rem;"></div>
        </div>
      </div>
    </div>

    <!-- Data & Results Preview (for UI) -->
    <div class="data-card" id="dataPreview" style="display:none;">
      <h6>Data & Hasil</h6>
      <div id="latestGrid" class="data-grid"></div>
      <div style="margin-top:.75rem;">
        <strong style="font-size:.75rem;">Ringkasan 6 Jam</strong>
        <div id="summary6" style="display:grid; gap:.25rem; margin-top:.25rem; font-size:.72rem;"></div>
      </div>
      <div style="margin-top:.5rem;">
        <strong style="font-size:.75rem;">Ringkasan 24 Jam</strong>
        <div id="summary24" style="display:grid; gap:.25rem; margin-top:.25rem; font-size:.72rem;"></div>
      </div>
      <details style="margin-top:.75rem;">
        <summary style="cursor:pointer; font-size:.75rem;">Lihat JSON</summary>
        <pre id="jsonDump" style="white-space:pre-wrap; background:#0f172a; color:#e2e8f0; padding:.75rem; border-radius:8px; overflow:auto; max-height:240px; font-size:.72rem;"></pre>
      </details>
    </div>

    <!-- Anomaly List -->
    <div class="anomaly-card" id="anomalyPanel" style="display:none;">
      <h6>Deteksi Anomali Sensor</h6>
      <div id="anomalyList"></div>
    </div>
    <div id="noAnomaly" style="display:none;" class="anomaly-card">
      <h6>Deteksi Anomali Sensor</h6>
      <p style="font-size:.75rem; color:#6c757d; margin:0;">Tidak ada anomali terdeteksi. Semua sensor dalam batas aman.</p>
    </div>
    
    <!-- Content Section -->
    <div style="padding: 1.5rem 0;">
      <div class="content-card">
        <!-- Filter Bar -->
        <div class="filter-bar">
          <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">Semua Alat (1)</button>
            <button class="filter-tab" data-filter="active">Aktif</button>
            <button class="filter-tab" data-filter="inactive">Tidak Aktif</button>
          </div>
          <div class="filter-right">
            <div class="search-box">
              <i class="fa-solid fa-search"></i>
              <input type="text" placeholder="Cari Produk">
            </div>
          </div>
        </div>
        
        <!-- Product Table -->
        <table class="product-table">
          <thead>
            <tr>
              <th class="checkbox-cell">
                <input type="checkbox">
              </th>
              <th>Info Alat</th>
              <th>Statistik</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <!-- Tool Row 1 -->
            <tr data-status="active">
              <td class="checkbox-cell">
                <input type="checkbox">
              </td>
              <td>
                <div class="product-info">
                  <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23ffeaa7'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23fdcb6e' font-size='20'%3E🐔%3C/text%3E%3C/svg%3E" alt="Tool" class="product-img">
                  <div>
                    <div class="product-name">Kandang Ayam</div>
                    <div class="product-subtitle">ChickPatrol Kamura</div>
                  </div>
                </div>
              </td>
              <td>
                <div class="rating-stars">
                  <i class="fa-solid fa-star"></i>
                  <i class="fa-solid fa-star"></i>
                  <i class="fa-solid fa-star"></i>
                  <i class="fa-solid fa-star"></i>
                  <i class="fa-regular fa-star"></i>
                </div>
              </td>
              <td>
                <span class="status-badge">Aktif</span>
              </td>
              <td>
                <span class="action-badge">Selected</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  
  <script>
    // SweetAlert Helper Functions
    window.showSuccess = function(message) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            confirmButtonColor: '#69B578',
            confirmButtonText: 'OK'
        });
    };
    
    window.showError = function(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
        });
    };
    
    // Toggle Submenu
    function toggleSubmenu() {
        const submenu = document.querySelector('.sidebar-submenu');
        const chevron = document.querySelector('.chevron-icon');
        submenu.classList.toggle('show');
        chevron.classList.toggle('rotate');
    }
    
    // Filter tabs with functional filtering
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Get filter text
            const filterText = this.textContent.trim().toLowerCase();
            const rows = document.querySelectorAll('.product-table tbody tr');
            
            // Filter tools
            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                
                if (filterText.includes('semua')) {
                    row.style.display = '';
                } else if (filterText.includes('aktif') && !filterText.includes('tidak')) {
                    row.style.display = status === 'active' ? '' : 'none';
                } else if (filterText.includes('tidak aktif')) {
                    row.style.display = status === 'inactive' ? '' : 'none';
                }
            });
        });
    });
    
    // Show success message if redirected with success
    @if(session('success'))
        showSuccess('{{ session('success') }}');
    @endif
    
    // Show error message if redirected with error
    @if(session('error'))
        showError('{{ session('error') }}');
    @endif

    /* ========== Monitoring Frontend Logic ========== */
    const sensorGrid = document.getElementById('sensorGrid');
    const predictionBanner = document.getElementById('predictionBanner');
    const anomalyPanel = document.getElementById('anomalyPanel');
    const anomalyList = document.getElementById('anomalyList');
    const noAnomaly = document.getElementById('noAnomaly');

    function createSensorCard(key, label, value, unit, history, prediction){
      // Determine trend (compare last two points)
      const len = history.length;
      let trend = 'flat';
      if (len >= 2){
        const diff = history[len-1][key] - history[len-2][key];
        if (diff > 0.2) trend = 'up'; else if (diff < -0.2) trend = 'down';
      }
      return `<div class="sensor-card">
        <h6>${label}</h6>
        <p class="sensor-value">${value}<span class="sensor-unit">${unit}</span></p>
        <div class="trend-chip ${trend}">
          <i class="fa-solid fa-arrow-${trend==='down'?'down':'up'}"></i>
          ${trend==='flat'?'Stabil': trend==='up'?'Naik':'Turun'}
        </div>
      </div>`;
    }

    function buildBanner(latest, status, forecast6){
      // Simple health heuristic
      const titleEl = document.getElementById('envStatusTitle');
      const detailEl = document.getElementById('envStatusDetail');
      const forecastEl = document.getElementById('envForecastDetail');
      titleEl.textContent = 'Kondisi lingkungan ' + status.label;
      detailEl.textContent = status.message;
      // Build quick 6h forecast sentence from first two summaries (suhu & kelembaban)
      const suhuSummary = forecast6.find(f=>f.metric==='Suhu');
      const lembabSummary = forecast6.find(f=>f.metric==='Kelembaban');
      if (suhuSummary && lembabSummary){
        forecastEl.textContent = `Prediksi 6 jam: ${suhuSummary.trend} suhu (${suhuSummary.range.min}–${suhuSummary.range.max}°C) & ${lembabSummary.trend} kelembaban (${lembabSummary.range.min}–${lembabSummary.range.max}%).`;
      }
      predictionBanner.style.display = 'flex';
    }

    let trendChart;
    function buildChart(history, prediction){
      const ctx = document.getElementById('trendChart').getContext('2d');
      const labels = history.map(p => p.time).concat(prediction.temperature.map((_,i)=>'+'+(i+1)+'h'));
      const makeDataset = (label, key, color, predColor) => {
        const histData = history.map(p => p[key]);
        return [
          { label: label+' (Hist)', data: histData, borderColor: color, backgroundColor: color+'33', tension:.25 },
          { label: label+' (Pred)', data: Array(history.length).fill(null).concat(prediction[key]), borderColor: predColor, borderDash:[4,4], tension:.25 }
        ];
      };
      const datasets = [
        ...makeDataset('Suhu','temperature','#e63946','#e63946'),
        ...makeDataset('Kelembaban','humidity','#457b9d','#457b9d'),
        ...makeDataset('Amoniak','ammonia','#6d597a','#6d597a'),
        ...makeDataset('Cahaya','light','#2a9d8f','#2a9d8f')
      ];
      if (trendChart) trendChart.destroy();
      trendChart = new Chart(ctx, {
        type:'line',
        data:{ labels, datasets },
        options:{
          responsive:true,
          scales:{
            x:{ ticks:{ maxRotation:0, autoSkip:true } },
            y:{ beginAtZero:false }
          },
          plugins:{ legend:{ display:false } },
        }
      });
    }

    function renderAnomalies(anomalies){
      if (!anomalies.length){
        noAnomaly.style.display='block';
        anomalyPanel.style.display='none';
        return;
      }
      anomalyPanel.style.display='block';
      noAnomaly.style.display='none';
      anomalyList.innerHTML = anomalies.map(a=>`<div class='anomaly-item'>
        <span class='anomaly-tag'>${a.type}</span>
        <div>
          <div style='font-size:.7rem; color:#6c757d;'>${a.time}</div>
          <div>${a.message} (nilai: ${a.value})</div>
        </div>
      </div>`).join('');
    }

    // Client-side mock generator (used when API not available)
    function generateMockData(){
      const now = new Date();
      const pad = n => (n<10?'0':'')+n;
      const history = [];
      for(let i=23;i>=0;i--){
        const t = new Date(now.getTime()-i*3600*1000);
        const hour = `${t.getFullYear()}-${pad(t.getMonth()+1)}-${pad(t.getDate())} ${pad(t.getHours())}:00`;
        const temp = 24 + (Math.random()*6-3) + (i>12?0.5:0);
        const hum = 65 + (Math.random()*10-5);
        const ammo = Math.max(5, 10 + (Math.random()*7-3));
        const light = (i>=6 && i<=18)? 700 + (Math.random()*200-100) : 120 + (Math.random()*60-30);
        history.push({time:hour, temperature:+temp.toFixed(1), humidity:+hum.toFixed(1), ammonia:+ammo.toFixed(1), light:Math.round(light)});
      }
      const latest = history[history.length-1];
      const predict = arr => {
        const recent = arr.slice(-6);
        const deltas = [];
        for(let i=1;i<recent.length;i++) deltas.push(recent[i]-recent[i-1]);
        const avg = deltas.length? deltas.reduce((a,b)=>a+b,0)/deltas.length : 0;
        const base = arr[arr.length-1];
        return Array.from({length:6},(_,h)=> +(base+avg*(h+1)).toFixed(2));
      };
      const seq = key => history.map(p=>p[key]);
      const pred6 = { temperature:predict(seq('temperature')), humidity:predict(seq('humidity')), ammonia:predict(seq('ammonia')), light:predict(seq('light')) };
      const predict24 = series => {
        const p6 = predict(series);
        const base = series[series.length-1];
        const lastDelta = p6[p6.length-1]-base;
        const out = [];
        for(let h=1;h<=24;h++){
          const factor = h<=12?1:0.5;
          out.push( +(base + (lastDelta/6)*h*factor).toFixed(2) );
        }
        return out;
      };
      const pred24 = { temperature:predict24(seq('temperature')), humidity:predict24(seq('humidity')), ammonia:predict24(seq('ammonia')), light:predict24(seq('light')) };
      const status = { label:'baik', severity:'normal', message:'Semua parameter dalam batas aman (simulasi).' };
      const sum = (series, metric, unit, low, high)=>{
        const min = Math.min(...series), max = Math.max(...series);
        const trend = series[series.length-1] - series[0];
        const dir = trend>0.5?'meningkat':(trend<-0.5?'menurun':'stabil');
        const risk = (min<low||max>high)?'potensi keluar batas aman':'dalam kisaran aman';
        return { metric, summary:`${metric} ${dir} (${min.toFixed?min.toFixed(2):min}–${max.toFixed?max.toFixed(2):max} ${unit}) ${risk}`, range:{min,max,unit}, trend:dir, risk };
      };
      const forecast_summary_6h = [
        sum(pred6.temperature,'Suhu','°C',20,30),
        sum(pred6.humidity,'Kelembaban','%',55,75),
        sum(pred6.ammonia,'Amoniak','ppm',0,25),
        sum(pred6.light,'Cahaya','lux',200,900)
      ];
      const forecast_summary_24h = [
        sum(pred24.temperature,'Suhu','°C',20,30),
        sum(pred24.humidity,'Kelembaban','%',55,75),
        sum(pred24.ammonia,'Amoniak','ppm',0,25),
        sum(pred24.light,'Cahaya','lux',200,900)
      ];
      const anomalies = [];
      return { latest, history, prediction_6h:pred6, prediction_24h:pred24, status, anomalies, forecast_summary_6h, forecast_summary_24h };
    }

    function iconFor(metric){
      if(metric==='Suhu') return 'fa-temperature-half';
      if(metric==='Kelembaban') return 'fa-droplet';
      if(metric==='Amoniak') return 'fa-flask';
      return 'fa-sun';
    }
    function riskClass(risk){
      if(!risk) return 'risk-ok';
      if(String(risk).toLowerCase().includes('krit')) return 'risk-crit';
      if(String(risk).toLowerCase().includes('potensi') || String(risk).toLowerCase().includes('keluar')) return 'risk-warn';
      return 'risk-ok';
    }

    async function loadMonitoring(){
      sensorGrid.innerHTML = '<div class="loading-overlay">Memuat data sensor...</div>';
      try {
        const res = await fetch('/api/monitoring/tools?t=' + Date.now(), { headers:{ 'Accept':'application/json' } });
        if (!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        const { latest, history, prediction_6h, prediction_24h, status, anomalies, forecast_summary_6h, forecast_summary_24h } = data;
        buildBanner(latest, status, forecast_summary_6h);
        sensorGrid.innerHTML = [
          createSensorCard('temperature','Suhu', latest.temperature,'°C', history, prediction_6h.temperature),
          createSensorCard('humidity','Kelembaban', latest.humidity,'%', history, prediction_6h.humidity),
          createSensorCard('ammonia','Amoniak', latest.ammonia,'ppm', history, prediction_6h.ammonia),
          createSensorCard('light','Cahaya', latest.light,'lux', history, prediction_6h.light)
        ].join('');
        buildChart(history, prediction_6h);
        renderAnomalies(anomalies);
        // Forecast card
        const forecastCard = document.getElementById('forecastCard');
        const list6 = document.getElementById('forecastList6');
        const list24 = document.getElementById('forecastList24');
        list6.innerHTML = forecast_summary_6h.map(f=>`<div class="metric-item ${riskClass(f.risk)}">\n            <div class="metric-icon"><i class="fa-solid ${iconFor(f.metric)}"></i></div>\n            <div>${f.summary}</div>\n          </div>`).join('');
        list24.innerHTML = forecast_summary_24h.map(f=>`<div class="metric-item ${riskClass(f.risk)}">\n            <div class="metric-icon"><i class="fa-solid ${iconFor(f.metric)}"></i></div>\n            <div>${f.summary}</div>\n          </div>`).join('');
        forecastCard.style.display='block';

        // Data Preview panel
        const dataPreview = document.getElementById('dataPreview');
        const latestGrid = document.getElementById('latestGrid');
        const summary6 = document.getElementById('summary6');
        const summary24 = document.getElementById('summary24');
        const jsonDump = document.getElementById('jsonDump');
        latestGrid.innerHTML = `
          <div class="data-tile"><div class="kpi-icon"><i class="fa-solid fa-temperature-half"></i></div><div><div class="label">Suhu</div><div class="val">${latest.temperature} °C</div></div></div>
          <div class="data-tile"><div class="kpi-icon"><i class="fa-solid fa-droplet"></i></div><div><div class="label">Kelembaban</div><div class="val">${latest.humidity} %</div></div></div>
          <div class="data-tile"><div class="kpi-icon"><i class="fa-solid fa-flask"></i></div><div><div class="label">Amoniak</div><div class="val">${latest.ammonia} ppm</div></div></div>
          <div class="data-tile"><div class="kpi-icon"><i class="fa-solid fa-sun"></i></div><div><div class="label">Cahaya</div><div class="val">${latest.light} lux</div></div></div>
        `;
        summary6.innerHTML = forecast_summary_6h.map(f=>`<div>• ${f.summary}</div>`).join('');
        summary24.innerHTML = forecast_summary_24h.map(f=>`<div>• ${f.summary}</div>`).join('');
        jsonDump.textContent = JSON.stringify(data, null, 2);
        dataPreview.style.display = 'block';
      } catch (e){
        console.warn('API tidak tersedia, gunakan data simulasi.', e);
        const data = generateMockData();
        const { latest, history, prediction_6h, prediction_24h, status, anomalies, forecast_summary_6h, forecast_summary_24h } = data;
        buildBanner(latest, status, forecast_summary_6h);
        sensorGrid.innerHTML = [
          createSensorCard('temperature','Suhu', latest.temperature,'°C', history, prediction_6h.temperature),
          createSensorCard('humidity','Kelembaban', latest.humidity,'%', history, prediction_6h.humidity),
          createSensorCard('ammonia','Amoniak', latest.ammonia,'ppm', history, prediction_6h.ammonia),
          createSensorCard('light','Cahaya', latest.light,'lux', history, prediction_6h.light)
        ].join('');
        buildChart(history, prediction_6h);
        renderAnomalies(anomalies);
        const forecastCard = document.getElementById('forecastCard');
        const list6 = document.getElementById('forecastList6');
        const list24 = document.getElementById('forecastList24');
        list6.innerHTML = forecast_summary_6h.map(f=>`<div class="metric-item ${riskClass(f.risk)}">\n            <div class="metric-icon"><i class="fa-solid ${iconFor(f.metric)}"></i></div>\n            <div>${f.summary}</div>\n          </div>`).join('');
        list24.innerHTML = forecast_summary_24h.map(f=>`<div class="metric-item ${riskClass(f.risk)}">\n            <div class="metric-icon"><i class="fa-solid ${iconFor(f.metric)}"></i></div>\n            <div>${f.summary}</div>\n          </div>`).join('');
        forecastCard.style.display='block';

        const dataPreview = document.getElementById('dataPreview');
        const latestGrid = document.getElementById('latestGrid');
        const summary6 = document.getElementById('summary6');
        const summary24 = document.getElementById('summary24');
        const jsonDump = document.getElementById('jsonDump');
        latestGrid.innerHTML = `
          <div class="data-tile"><div class="kpi-icon"><i class="fa-solid fa-temperature-half"></i></div><div><div class="label">Suhu</div><div class="val">${latest.temperature} °C</div></div></div>
          <div class="data-tile"><div class="kpi-icon"><i class="fa-solid fa-droplet"></i></div><div><div class="label">Kelembaban</div><div class="val">${latest.humidity} %</div></div></div>
          <div class="data-tile"><div class="kpi-icon"><i class="fa-solid fa-flask"></i></div><div><div class="label">Amoniak</div><div class="val">${latest.ammonia} ppm</div></div></div>
          <div class="data-tile"><div class="kpi-icon"><i class="fa-solid fa-sun"></i></div><div><div class="label">Cahaya</div><div class="val">${latest.light} lux</div></div></div>
        `;
        summary6.innerHTML = data.forecast_summary_6h.map(f=>`<div>• ${f.summary}</div>`).join('');
        summary24.innerHTML = data.forecast_summary_24h.map(f=>`<div>• ${f.summary}</div>`).join('');
        jsonDump.textContent = JSON.stringify(data, null, 2);
        dataPreview.style.display = 'block';
      }
    }

    document.addEventListener('DOMContentLoaded', loadMonitoring);
  </script>
</body>
</html>
