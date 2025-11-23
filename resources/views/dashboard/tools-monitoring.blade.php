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
  
  <!-- Google Fonts - Inter (Premium Typography) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
  
  <style>
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    body { background: #F8F9FB; margin: 0; }
    
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
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 1.25rem;
      margin-bottom: 1.5rem;
    }
    
    @media (min-width: 768px) {
      .monitoring-grid {
        grid-template-columns: repeat(4, 1fr);
      }
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        position: relative;
        min-height: auto;
      }
      
      .main-content {
        margin-left: 0 !important;
        padding: 1rem !important;
      }
      
      .monitoring-grid {
        grid-template-columns: 1fr;
      }
      
      .sensor-card {
        min-width: 100%;
      }
      
      .chart-card, .anomaly-card {
        margin-bottom: 1rem;
      }
      
      .forecast-grid {
        grid-template-columns: 1fr !important;
      }
      
      .prediction-banner {
        flex-direction: column;
        text-align: center;
      }
      
      .filter-bar {
        flex-direction: column;
        gap: .5rem;
      }
      
      .chart-card form {
        display: grid;
        gap: .75rem;
      }
      
      .chart-card form > div {
        width: 100%;
      }
      
      .chart-card form button {
        width: 100%;
      }
    }
    
    @media (max-width: 480px) {
      .sensor-card h6 {
        font-size: .7rem;
      }
      
      .sensor-value {
        font-size: 1.5rem !important;
      }
      
      .trend-chip {
        font-size: .65rem !important;
        padding: .2rem .4rem !important;
      }
      
      .chart-card h6, .anomaly-card h6 {
        font-size: .75rem;
      }
      
      .prediction-banner {
        padding: .75rem 1rem !important;
      }
      
      .prediction-banner h5 {
        font-size: .85rem !important;
      }
      
      .prediction-banner p {
        font-size: .65rem !important;
      }
    }
    .sensor-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 16px;
      padding: 1.5rem;
      position: relative;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,0.08);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sensor-card:hover {
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      transform: translateY(-4px);
    }
    .sensor-card h6 {
      margin: 0 0 1rem;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
      color: #374151;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .sensor-card h6 .sensor-icon {
      font-size: 1.125rem;
    }
    .sensor-value {
      font-size: 2.25rem;
      font-weight: 700;
      line-height: 1.1;
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
      background: linear-gradient(90deg, #FACC15, #FB923C);
      color: white;
      border-radius: 16px;
      padding: 1.5rem 1.75rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 12px rgba(250, 204, 21, 0.2);
    }
    .prediction-banner i { 
      font-size: 2rem; 
      flex-shrink: 0;
      opacity: 0.95;
    }
    .prediction-banner > div {
      flex: 1;
      min-width: 0;
    }
    .prediction-banner h5 { 
      margin:0 0 .5rem; 
      font-size: 1.25rem; 
      font-weight:700; 
      line-height: 1.3;
      letter-spacing: -0.02em;
    }
    .prediction-banner p { 
      margin:0; 
      font-size:.875rem; 
      opacity:.9; 
      line-height:1.5; 
      font-weight: 400;
    }
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
    /* Warna sesuai kondisi: hijau=aman, kuning=perhatian, merah=bahaya */
    .metric-item.risk-ok{ border-left:3px solid #28a745; background:#f0f9f4; }
    .metric-item.risk-ok .metric-icon{ background:#28a745; } /* Hijau untuk aman */
    .metric-item.risk-warn{ border-left:3px solid #ffc107; background:#fffbf0; }
    .metric-item.risk-warn .metric-icon{ background:#ffc107; color:#000; } /* Kuning untuk perhatian */
    .metric-item.risk-crit{ border-left:3px solid #dc3545; background:#fff0f0; }
    .metric-item.risk-crit .metric-icon{ background:#dc3545; } /* Merah untuk bahaya */
    /* Pagination button styles */
    #anomalyPrevBtn:disabled, #anomalyNextBtn:disabled {
      opacity: 0.5;
      cursor: not-allowed !important;
      background: #f5f5f5 !important;
    }
    #anomalyPrevBtn:hover:not(:disabled), #anomalyNextBtn:hover:not(:disabled) {
      background: #f0f0f0 !important;
      border-color: #999 !important;
    }
    
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
      background:#dc3545; /* Default merah untuk bahaya */
      color:white;
      border-radius:4px;
      text-transform:uppercase;
      letter-spacing:.5px;
      font-weight:600;
    }
    /* Warna anomaly tag berdasarkan severity */
    .anomaly-item[data-severity="critical"] .anomaly-tag { background:#dc3545; } /* Merah untuk bahaya */
    .anomaly-item[data-severity="warning"] .anomaly-tag { background:#ffc107; color:#000; } /* Kuning untuk perhatian */
    .anomaly-item[data-severity="normal"] .anomaly-tag { background:#28a745; } /* Hijau untuk aman */
    .loading-overlay { text-align:center; padding:2rem 0; font-size:.8rem; color:#6c757d; }

    /* Data preview */
    .data-card { background:white; border:1px solid #e9ecef; border-radius:10px; padding:1rem 1.25rem; }
    .data-card h6 { margin:0 0 .6rem; font-size:.8rem; font-weight:600; color:#6c757d; }
    .data-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap:.75rem; }
    .data-tile { background:#f8f9fa; border-radius:10px; padding:.7rem .8rem; display:flex; align-items:flex-start; gap:.6rem; }
    .data-tile .kpi-icon{ width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; background:#69B578; flex-shrink:0; }
    .data-tile .label { font-size:.68rem; color:#6c757d; margin-bottom:.15rem; }
    .data-tile .val { font-size:1.1rem; font-weight:700; color:#2F2F2F; margin-bottom:.25rem; }
    /* ML Info */
    .ml-info-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: .6rem .8rem;
      background: #f8f9fa;
      border-radius: 8px;
      font-size: .75rem;
    }
    .ml-info-label {
      color: #6c757d;
      font-weight: 500;
    }
    .ml-info-value {
      color: #2F2F2F;
      font-weight: 600;
    }
    .ml-badge {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      padding: .3rem .6rem;
      border-radius: 6px;
      font-size: .7rem;
      font-weight: 500;
    }
    .ml-badge.connected {
      background: #d4edda;
      color: #155724;
    }
    .ml-badge.disconnected {
      background: #f8d7da;
      color: #721c24;
    }
    .ml-badge.fallback {
      background: #fff3cd;
      color: #856404;
    }
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
        <h6>Fantastic F</h6>
        <p>Founder</p>
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
        <a href="{{ route('dashboard.tools.information') }}">Manajemen Informasi</a>
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
      <a href="{{ route('logout') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </div>
  </aside>
  
  <!-- Main Content -->
  <main class="main-content">
    <!-- Monitoring Summary Banner (dynamic) -->
    <div id="predictionBanner" class="prediction-banner" style="display:none;">
      <span style="font-size: 2rem; opacity: 0.95;">📊</span>
      <div>
        <h5 id="envStatusTitle" style="margin: 0 0 0.5rem; font-size: 1.25rem; font-weight: 700; line-height: 1.3; letter-spacing: -0.02em;">Memuat... <span id="mlActiveBadge" class="badge bg-success ms-2" style="display:none;">ML Active</span></h5>
        <p id="envStatusDetail" style="margin: 0; font-size: 0.875rem; opacity: 0.9; line-height: 1.5; font-weight: 400;">Analisis kondisi lingkungan kandang akan muncul di sini.</p>
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



    <!-- Anomaly List -->
    <div class="anomaly-card" id="anomalyPanel" style="display:none;">
      <h6>Deteksi Anomali Sensor</h6>
      <div id="anomalyList"></div>
      <div id="anomalyPagination" style="display:none; margin-top:.75rem; padding-top:.75rem; border-top:1px solid #e0e0e0;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:.5rem;">
          <div style="font-size:.75rem; color:#6c757d;">
            <span id="anomalyPageInfo">Halaman 1 dari 1</span>
          </div>
          <div style="display:flex; gap:.25rem;">
            <button id="anomalyPrevBtn" style="padding:.25rem .5rem; font-size:.75rem; border:1px solid #ddd; background:#fff; border-radius:4px; cursor:pointer;" disabled>
              <i class="fa-solid fa-chevron-left"></i> Sebelumnya
            </button>
            <button id="anomalyNextBtn" style="padding:.25rem .5rem; font-size:.75rem; border:1px solid #ddd; background:#fff; border-radius:4px; cursor:pointer;" disabled>
              Selanjutnya <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
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
    const anomalyPagination = document.getElementById('anomalyPagination');
    const anomalyPrevBtn = document.getElementById('anomalyPrevBtn');
    const anomalyNextBtn = document.getElementById('anomalyNextBtn');
    const anomalyPageInfo = document.getElementById('anomalyPageInfo');
    
    // State untuk pagination anomali
    let allAnomalies = [];
    let currentAnomalyPage = 1;
    const ANOMALIES_PER_PAGE = 5;
    const noAnomaly = document.getElementById('noAnomaly');

    // Fungsi untuk menentukan status sensor berdasarkan threshold (Premium Colors)
    function getSensorStatus(key, value){
      const thresholds = {
        temperature: { ideal_min: 23, ideal_max: 34, danger_low: 20, danger_high: 37 },
        humidity: { ideal_min: 50, ideal_max: 70, warn_low: 50, warn_high: 80, danger_high: 80 },
        ammonia: { ideal_max: 20, warn_max: 35, danger_max: 35 },
        light: { ideal_low: 20, ideal_high: 40, warn_low: 10, warn_high: 60 }
      };
      
      // Premium colors: #22C55E (hijau), #FACC15 (kuning), #EF4444 (merah)
      const premiumGreen = '#22C55E';
      const premiumYellow = '#FACC15';
      const premiumRed = '#EF4444';
      
      const th = thresholds[key];
      if (!th) return { status: 'tidak diketahui', color: '#6c757d' };
      
      if (key === 'temperature') {
        if (value >= th.ideal_min && value <= th.ideal_max) {
          return { status: 'aman', color: premiumGreen };
        } else if (value < th.danger_low || value > th.danger_high) {
          return { status: 'di luar batas aman', color: premiumRed };
        } else {
          return { status: 'perlu perhatian', color: premiumYellow };
        }
      } else if (key === 'humidity') {
        if (value >= th.ideal_min && value <= th.ideal_max) {
          return { status: 'aman', color: premiumGreen };
        } else if (value > th.danger_high) {
          return { status: 'di luar batas aman', color: premiumRed };
        } else if (value < th.warn_low || (value > th.ideal_max && value <= th.warn_high)) {
          return { status: 'perlu perhatian', color: premiumYellow };
        }
      } else if (key === 'ammonia') {
        if (value <= th.ideal_max) {
          return { status: 'aman', color: premiumGreen };
        } else if (value > th.danger_max) {
          return { status: 'di luar batas aman', color: premiumRed };
        } else if (value > th.warn_max) {
          return { status: 'perlu perhatian', color: premiumYellow };
        }
      } else if (key === 'light') {
        // Untuk cahaya, nilai aktual ratusan dibandingkan dengan threshold 10-60
        if (value >= th.ideal_low && value <= th.ideal_high) {
          return { status: 'aman', color: premiumGreen };
        } else if (value < th.warn_low || value > th.warn_high) {
          return { status: 'di luar batas aman', color: premiumRed };
        } else {
          return { status: 'perlu perhatian', color: premiumYellow };
        }
      }
      
      return { status: 'tidak diketahui', color: '#6c757d' };
    }
    
    function createSensorCard(key, label, value, unit, history, prediction){
      // Determine trend (compare last 3 points untuk lebih akurat)
      const len = history.length;
      let trend = 'flat';
      let trendText = 'Stabil';
      let trendIcon = 'fa-minus';
      
      if (len >= 3){
        // Bandingkan 3 titik terakhir untuk menentukan trend
        const val1 = history[len-3][key];
        const val2 = history[len-2][key];
        const val3 = history[len-1][key];
        const avgDiff = ((val3 - val2) + (val2 - val1)) / 2;
        
        // Threshold untuk menentukan trend (sesuai dengan unit)
        const threshold = (key === 'temperature') ? 0.3 : (key === 'humidity') ? 1 : (key === 'ammonia') ? 0.5 : 5;
        
        if (avgDiff > threshold) {
          trend = 'up';
          trendText = 'Naik';
          trendIcon = 'fa-arrow-up';
        } else if (avgDiff < -threshold) {
          trend = 'down';
          trendText = 'Turun';
          trendIcon = 'fa-arrow-down';
        } else {
          trend = 'flat';
          trendText = 'Stabil';
          trendIcon = 'fa-minus';
        }
      } else if (len >= 2){
        const diff = history[len-1][key] - history[len-2][key];
        const threshold = (key === 'temperature') ? 0.3 : (key === 'humidity') ? 1 : (key === 'ammonia') ? 0.5 : 5;
        if (diff > threshold) {
          trend = 'up';
          trendText = 'Naik';
          trendIcon = 'fa-arrow-up';
        } else if (diff < -threshold) {
          trend = 'down';
          trendText = 'Turun';
          trendIcon = 'fa-arrow-down';
        }
      }
      
      // Tentukan status sensor berdasarkan threshold
      const sensorStatus = getSensorStatus(key, value);
      
      // Format nilai dengan presisi yang sesuai
      const formattedValue = (key === 'temperature' || key === 'humidity') ? 
        parseFloat(value).toFixed(1) : 
        (key === 'ammonia') ? parseFloat(value).toFixed(1) : 
        Math.round(parseFloat(value));
      
      // Tentukan ikon sensor
      const sensorIcons = {
        'temperature': '🌡️',
        'humidity': '💧',
        'ammonia': '💨',
        'light': '💡'
      };
      const sensorIcon = sensorIcons[key] || '📊';
      
      // Tentukan warna premium sesuai status
      const premiumColors = {
        safe: { border: '#22C55E', bg: '#D1FAE5', text: '#065F46', icon: '✔' },
        warning: { border: '#FACC15', bg: '#FEF3C7', text: '#92400E', icon: '⚠' },
        danger: { border: '#EF4444', bg: '#FEE2E2', text: '#991B1B', icon: '❗' }
      };
      
      let premiumColor;
      if (sensorStatus.color === '#22C55E' || sensorStatus.status === 'aman') {
        premiumColor = premiumColors.safe;
      } else if (sensorStatus.color === '#EF4444' || sensorStatus.status === 'di luar batas aman') {
        premiumColor = premiumColors.danger;
      } else {
        premiumColor = premiumColors.warning;
      }
      
      // Format status text
      const statusText = sensorStatus.status === 'aman' ? 'Aman' : 
                        sensorStatus.status === 'di luar batas aman' ? 'Di Luar Batas Aman' :
                        'Perlu Perhatian';
      
      return `<div class="sensor-card" style="border-left: 4px solid ${premiumColor.border};">
        <h6 style="margin: 0 0 1rem; font-size: 0.75rem; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 0.5rem;">
          <span class="sensor-icon" style="font-size: 1.125rem;">${sensorIcon}</span>
          ${label}
        </h6>
        <p class="sensor-value" style="margin: 0 0 1rem; font-size: 2.25rem; font-weight: 700; color: #111827; line-height: 1.1;">
          ${formattedValue}<span class="sensor-unit" style="font-size: 1.125rem; font-weight: 500; color: #6B7280; margin-left: 0.25rem;">${unit}</span>
        </p>
        <div style="margin-top: 0.75rem;">
          <span style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.75rem; border-radius: 9999px; background: ${premiumColor.bg}; color: ${premiumColor.text}; font-size: 0.875rem; font-weight: 600;">
            ${premiumColor.icon} ${statusText}
          </span>
        </div>
      </div>`;
    }

    function buildBanner(latest, status, forecast6, meta){
      try {
        const titleEl = document.getElementById('envStatusTitle');
        const detailEl = document.getElementById('envStatusDetail');
        const forecastEl = document.getElementById('envForecastDetail');
        const mlActiveBadge = document.getElementById('mlActiveBadge');
        
        // Validasi elemen
        if (!titleEl || !detailEl) {
          console.error('Banner elements not found', { titleEl: !!titleEl, detailEl: !!detailEl });
          return;
        }
      
      // Format status label lebih profesional
      const statusLabels = {
        'baik': 'Kondisi Kandang Optimal',
        'perhatian': 'Kondisi Kandang Perlu Perhatian',
        'buruk': 'Kondisi Kandang Tidak Optimal',
        'tidak diketahui': 'Status Tidak Dapat Ditentukan'
      };
      
      const statusLabel = status.label || 'tidak diketahui';
      const statusText = statusLabels[statusLabel] || 'Kondisi Kandang ' + statusLabel.charAt(0).toUpperCase() + statusLabel.slice(1);
      
      // Format confidence - hanya di title
      let confidenceText = '';
      if (status.confidence !== undefined && status.confidence !== null) {
        const confidencePercent = Math.round(status.confidence * 100);
        confidenceText = ` (Tingkat Keyakinan: ${confidencePercent}%)`;
      }
      
      // Title: hanya status dan confidence
      if (titleEl) {
        titleEl.innerHTML = statusText + confidenceText + ' <span id="mlActiveBadge" class="badge bg-success ms-2" style="display:none;">ML Active</span>';
      }
      
      // Detail: hanya message dan sumber (HILANGKAN duplikasi keyakinan dan prediksi)
      if (detailEl) {
        if (status.message) {
          detailEl.textContent = status.message + ' | Hasil Analisis Machine Learning';
        } else {
          detailEl.textContent = 'Hasil Analisis Machine Learning';
        }
      }
      
      // HILANGKAN prediksi 6 jam dari banner (jika elemen masih ada)
      if (forecastEl) {
        forecastEl.textContent = '';
        forecastEl.style.display = 'none';
      }
      
      // ML Active badge
      if (meta && meta.ml_connected) {
          if (mlActiveBadge) {
              mlActiveBadge.style.display = 'inline-block';
              mlActiveBadge.textContent = 'ML Active';
              mlActiveBadge.className = 'badge bg-success ms-2';
          }
      } else {
          if (mlActiveBadge) mlActiveBadge.style.display = 'none';
      }
      
        // Set warna banner berdasarkan severity dengan gradient premium
        const predictionBanner = document.getElementById('predictionBanner');
        if (predictionBanner) {
          const severity = status.severity || 'normal';
          if (severity === 'critical' || severity === 'bahaya' || status.label?.includes('buruk') || status.label?.includes('tidak optimal')) {
              predictionBanner.style.background = 'linear-gradient(90deg, #EF4444, #DC2626)'; // Merah premium untuk bahaya
          } else if (severity === 'warning' || severity === 'perhatian' || status.label?.includes('perhatian')) {
              predictionBanner.style.background = 'linear-gradient(90deg, #FACC15, #FB923C)'; // Kuning-orange premium untuk perhatian
          } else {
              predictionBanner.style.background = 'linear-gradient(90deg, #22C55E, #16A34A)'; // Hijau premium untuk aman
          }
          
          predictionBanner.style.display = 'flex';
        }
      } catch (error) {
        console.error('Error in buildBanner:', error);
      }
    }

    let trendChart;
    function buildChart(history, prediction){
      const ctx = document.getElementById('trendChart').getContext('2d');
      
      // Format labels: show every hour dengan format WIB (realtime)
      const historyLabels = history.map((p, i) => {
        // Untuk data terakhir, gunakan waktu realtime saat ini
        if (i === history.length - 1) {
          const now = new Date();
          const nowDay = String(now.getDate()).padStart(2, '0');
          const nowMonth = String(now.getMonth() + 1).padStart(2, '0');
          const nowHour = String(now.getHours()).padStart(2, '0');
          const nowMinute = String(now.getMinutes()).padStart(2, '0');
          return `${nowDay}/${nowMonth} ${nowHour}:${nowMinute}`;
        }
        
        // Untuk data history lainnya, parse dari timestamp
        const [datePart, timePart] = p.time.split(' ');
        const [year, month, day] = datePart.split('-');
        const [hour] = timePart.split(':');
        
        // Format: "DD/MM HH:00"
        return `${day}/${month} ${hour}:00`;
      });
      const predictionLabels = prediction.temperature.map((_,i)=>'+'+(i+1)+'h');
      const labels = [...historyLabels, ...predictionLabels];
      
      const makeDataset = (label, key, color, predColor) => {
        const histData = history.map(p => p[key]);
        return [
          { 
            label: label+' (Hist)', 
            data: histData, 
            borderColor: color, 
            backgroundColor: color+'20', 
            borderWidth: 2,
            pointRadius: 2,
            pointHoverRadius: 4,
            pointBackgroundColor: color,
            pointBorderColor: '#fff',
            pointBorderWidth: 1,
            tension: 0.4,
            fill: false
          },
          { 
            label: label+' (Pred)', 
            data: Array(history.length).fill(null).concat(prediction[key]), 
            borderColor: predColor, 
            borderDash: [5, 5],
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: predColor,
            pointBorderColor: '#fff',
            pointBorderWidth: 1,
            tension: 0.4,
            fill: false
          }
        ];
      };
      
      const datasets = [
        ...makeDataset('Suhu','temperature','#e63946','#ff6b6b'),
        ...makeDataset('Kelembaban','humidity','#457b9d','#6c9bcf'),
        ...makeDataset('Amoniak','ammonia','#6d597a','#9d7ba8'),
        ...makeDataset('Cahaya','light','#2a9d8f','#4ecdc4')
      ];
      
      if (trendChart) trendChart.destroy();
      trendChart = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          interaction: {
            intersect: false,
            mode: 'index'
          },
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                  size: 11,
                  family: "'Inter', -apple-system, sans-serif",
                  weight: '500'
                },
                color: '#2F2F2F'
              }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              padding: 12,
              titleFont: {
                size: 13,
                weight: '600'
              },
              bodyFont: {
                size: 12
              },
              borderColor: 'rgba(255, 255, 255, 0.1)',
              borderWidth: 1,
              displayColors: true,
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  label += context.parsed.y.toFixed(1);
                  if (context.dataset.label.includes('Suhu')) label += '°C';
                  else if (context.dataset.label.includes('Kelembaban')) label += '%';
                  else if (context.dataset.label.includes('Amoniak')) label += ' ppm';
                  else if (context.dataset.label.includes('Cahaya')) label += ' lux';
                  return label;
                }
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: true,
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false
              },
              ticks: {
                maxRotation: 45,
                minRotation: 45,
                autoSkip: true,
                maxTicksLimit: 12,
                font: {
                  size: 10,
                  family: "'Inter', -apple-system, sans-serif"
                },
                color: '#6c757d'
              },
              title: {
                display: true,
                text: 'Waktu',
                font: {
                  size: 12,
                  weight: '600'
                },
                color: '#2F2F2F',
                padding: { top: 10, bottom: 5 }
              }
            },
            y: {
              beginAtZero: false,
              grid: {
                display: true,
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false
              },
              ticks: {
                font: {
                  size: 10,
                  family: "'Inter', -apple-system, sans-serif"
                },
                color: '#6c757d',
                callback: function(value) {
                  return value.toFixed(0);
                }
              },
              title: {
                display: true,
                text: 'Nilai Sensor',
                font: {
                  size: 12,
                  weight: '600'
                },
                color: '#2F2F2F',
                padding: { top: 5, bottom: 10 }
              }
            }
          }
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
      
      // Sort anomali: critical dulu, lalu warning, lalu urutkan berdasarkan waktu (terbaru)
      const sortedAnomalies = [...anomalies].sort((a, b) => {
        const severityOrder = { 'critical': 0, 'warning': 1, 'normal': 2 };
        const aSeverity = severityOrder[a.severity] ?? 2;
        const bSeverity = severityOrder[b.severity] ?? 2;
        
        if (aSeverity !== bSeverity) {
          return aSeverity - bSeverity; // Critical dulu
        }
        
        // Jika severity sama, urutkan berdasarkan waktu (terbaru dulu)
        const aTime = new Date(a.time || 0).getTime();
        const bTime = new Date(b.time || 0).getTime();
        return bTime - aTime;
      });
      
      // Simpan semua anomali untuk pagination
      allAnomalies = sortedAnomalies;
      currentAnomalyPage = 1;
      
      // Render halaman pertama
      renderAnomalyPage();
    }
    
    function renderAnomalyPage(){
      if (!allAnomalies.length) return;
      
      const totalPages = Math.ceil(allAnomalies.length / ANOMALIES_PER_PAGE);
      const startIndex = (currentAnomalyPage - 1) * ANOMALIES_PER_PAGE;
      const endIndex = startIndex + ANOMALIES_PER_PAGE;
      const displayAnomalies = allAnomalies.slice(startIndex, endIndex);
      
      // Render anomali
      anomalyList.innerHTML = displayAnomalies.map(a=>{
        // Tentukan severity berdasarkan type atau severity dari data
        const severity = a.severity || (a.type === 'unknown' ? 'warning' : 'critical');
        return `<div class='anomaly-item' data-severity="${severity}">
          <span class='anomaly-tag'>${a.type || 'unknown'}</span>
          <div>
            <div style='font-size:.7rem; color:#6c757d;'>${a.time}</div>
            <div>${a.message}${a.value !== undefined ? ` (nilai: ${a.value})` : ''}</div>
          </div>
        </div>`;
      }).join('');
      
      // Update pagination info
      if (anomalyPageInfo) {
        anomalyPageInfo.textContent = `Halaman ${currentAnomalyPage} dari ${totalPages} (Total: ${allAnomalies.length} anomali)`;
      }
      
      // Update tombol pagination
      if (anomalyPrevBtn) {
        anomalyPrevBtn.disabled = currentAnomalyPage === 1;
      }
      if (anomalyNextBtn) {
        anomalyNextBtn.disabled = currentAnomalyPage === totalPages;
      }
      
      // Tampilkan/sembunyikan pagination
      if (anomalyPagination) {
        if (totalPages > 1) {
          anomalyPagination.style.display = 'block';
        } else {
          anomalyPagination.style.display = 'none';
        }
      }
    }
    
    function changeAnomalyPage(direction){
      const totalPages = Math.ceil(allAnomalies.length / ANOMALIES_PER_PAGE);
      // Pastikan perubahan halaman hanya +1 atau -1, tidak melompat
      const newPage = currentAnomalyPage + (direction > 0 ? 1 : -1);
      
      if (newPage >= 1 && newPage <= totalPages) {
        currentAnomalyPage = newPage;
        renderAnomalyPage();
        // Scroll ke atas daftar anomali
        if (anomalyList) {
          anomalyList.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    }
    
    // Attach event listeners untuk tombol pagination setelah elemen tersedia
    if (anomalyPrevBtn && anomalyNextBtn) {
      anomalyPrevBtn.addEventListener('click', () => changeAnomalyPage(-1));
      anomalyNextBtn.addEventListener('click', () => changeAnomalyPage(1));
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
      // Merah (bahaya): kritik, tidak optimal, bahaya, DI LUAR BATAS AMAN (bukan potensi lagi)
      if(String(risk).toLowerCase().includes('krit') || String(risk).toLowerCase().includes('bahaya') || String(risk).toLowerCase().includes('tidak optimal') || String(risk).toLowerCase().includes('di luar batas aman')) return 'risk-crit';
      // Kuning (perhatian): potensi keluar batas, perlu perhatian
      if(String(risk).toLowerCase().includes('potensi') || String(risk).toLowerCase().includes('perhatian')) return 'risk-warn';
      // Hijau (aman): dalam kisaran aman
      return 'risk-ok';
    }

    function renderMLInfo(mlMetadata, meta){
      const mlInfoCard = document.getElementById('mlInfoCard');
      const mlInfoContent = document.getElementById('mlInfoContent');
      
      if(!mlMetadata || Object.keys(mlMetadata).length === 0){
        mlInfoCard.style.display = 'none';
        return;
      }
      
      const source = meta?.ml_source || 'fallback';
      const connected = meta?.ml_connected || false;
      
      let html = '';
      
      // Status Connection
      if(connected && source === 'ml_service'){
        html += `<div class="ml-info-item">
          <span class="ml-info-label">Status Koneksi</span>
          <span class="ml-badge connected">
            <i class="fa-solid fa-check-circle"></i>
            Terhubung ke ML Service
          </span>
        </div>`;
      } else {
        html += `<div class="ml-info-item">
          <span class="ml-info-label">Status Koneksi</span>
          <span class="ml-badge ${source === 'fallback' ? 'fallback' : 'disconnected'}">
            <i class="fa-solid ${source === 'fallback' ? 'fa-exclamation-triangle' : 'fa-times-circle'}"></i>
            ${source === 'fallback' ? 'Menggunakan Prediksi Sederhana' : 'ML Service Tidak Tersedia'}
          </span>
        </div>`;
      }
      
      // Model Name
      if(mlMetadata.model_name){
        html += `<div class="ml-info-item">
          <span class="ml-info-label">Nama Model</span>
          <span class="ml-info-value">${mlMetadata.model_name}</span>
        </div>`;
      }
      
      // Model Version
      if(mlMetadata.model_version){
        html += `<div class="ml-info-item">
          <span class="ml-info-label">Versi Model</span>
          <span class="ml-info-value">v${mlMetadata.model_version}</span>
        </div>`;
      }
      
      // Accuracy
      if(mlMetadata.accuracy !== null && mlMetadata.accuracy !== undefined){
        html += `<div class="ml-info-item">
          <span class="ml-info-label">Akurasi Model</span>
          <span class="ml-info-value">${(mlMetadata.accuracy * 100).toFixed(2)}%</span>
        </div>`;
      }
      
      // Confidence
      if(mlMetadata.confidence){
        html += `<div class="ml-info-item">
          <span class="ml-info-label">Tingkat Keyakinan</span>
          <span class="ml-info-value" style="text-transform: capitalize;">${mlMetadata.confidence}</span>
        </div>`;
      }
      
      // Prediction Time
      if(mlMetadata.prediction_time){
        html += `<div class="ml-info-item">
          <span class="ml-info-label">Waktu Prediksi</span>
          <span class="ml-info-value">${mlMetadata.prediction_time}ms</span>
        </div>`;
      }
      
      mlInfoContent.innerHTML = html;
      mlInfoCard.style.display = 'block';
    }

    async function loadMonitoring(){
      sensorGrid.innerHTML = '<div class="loading-overlay">Memuat data sensor...</div>';
      try {
        const res = await fetch('/api/monitoring/tools?t=' + Date.now(), { headers:{ 'Accept':'application/json' } });
        if (!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        const { latest, history, prediction_6h, prediction_24h, status, anomalies, forecast_summary_6h, forecast_summary_24h, ml_metadata, meta } = data;
        buildBanner(latest, status, forecast_summary_6h, meta);
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
        
        // ML Info Card - moved to Information Management page

        // Data Preview panel
      } catch (e){
        console.error('Error loading monitoring data:', e);
        sensorGrid.innerHTML = '<div class="alert alert-danger">Gagal memuat data monitoring. Pastikan ML Service berjalan dan coba refresh halaman.</div>';
        
        // Tampilkan pesan error yang jelas
        const errorBanner = document.getElementById('predictionBanner');
        if (errorBanner) {
          errorBanner.style.display = 'block';
          errorBanner.style.background = '#dc3545';
          errorBanner.innerHTML = `
            <i class="fa-solid fa-exclamation-triangle"></i>
            <div>
              <h5>Error Memuat Data</h5>
              <p>Gagal terhubung ke ML Service. Pastikan:</p>
              <ul style="margin:0.5rem 0; padding-left:1.5rem; font-size:0.9rem;">
                <li>ML Service berjalan di http://localhost:5000</li>
                <li>Service dapat diakses (test: curl http://localhost:5000/health)</li>
                <li>Refresh halaman ini setelah service running</li>
              </ul>
            </div>
        `;
        }
        
        // Jangan tampilkan data preview jika error
      }
    }

    document.addEventListener('DOMContentLoaded', loadMonitoring);
  </script>
</body>
</html>
