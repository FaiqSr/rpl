<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Dashboard Monitoring - ChickPatrol Seller</title>
  
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
  
  <style>
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    body { background: #F8F9FB; margin: 0; }
    
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
      background: #22C55E;
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
      color: #22C55E;
      border-bottom-color: #22C55E;
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
      border-color: #22C55E;
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
      .prediction-banner .prob-badge {
        font-size: 0.7rem !important;
        padding: 0.2rem 0.5rem !important;
      }
      .prediction-banner p div {
        font-size: 0.7rem !important;
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
    .trend-chip.down { color:#EF4444; }
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
    .prediction-banner p div {
      margin-bottom: 0.5rem;
    }
    .prediction-banner p div:last-child {
      margin-bottom: 0;
    }
    /* Styling untuk probabilitas badge di banner */
    .prediction-banner .prob-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.625rem;
      border-radius: 0.375rem;
      font-size: 0.8125rem;
      font-weight: 600;
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .prediction-banner .prob-badge.badge-baik {
      background: rgba(34, 197, 94, 0.25);
      color: #ffffff;
      border: 1px solid rgba(34, 197, 94, 0.4);
    }
    .prediction-banner .prob-badge.badge-perhatian {
      background: rgba(250, 204, 21, 0.25);
      color: #ffffff;
      border: 1px solid rgba(250, 204, 21, 0.4);
    }
    .prediction-banner .prob-badge.badge-buruk {
      background: rgba(239, 68, 68, 0.25);
      color: #ffffff;
      border: 1px solid rgba(239, 68, 68, 0.4);
    }
    
    /* Sticky Alert Bar dihapus - hanya pop-up alert yang digunakan */
    .chart-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
    }
    .chart-card h6 { margin:0 0 .75rem; font-size:.8rem; font-weight:600; color:#6c757d; }
    /* Forecast layout */
    /* Premium Forecast Grid - Compact & Efficient */
    .forecast-grid{ 
      display:grid; 
      grid-template-columns:1fr 1fr; 
      gap:0.75rem; 
    }
    .forecast-col h5{ 
      margin:0 0 0.5rem; 
      font-size:0.75rem; 
      font-weight:700; 
      color:#2F2F2F; 
      text-transform:uppercase;
      letter-spacing:0.05em;
    }
    .metric-item{ 
      display:flex; 
      align-items:center; 
      gap:0.5rem; 
      padding:0.5rem 0.75rem; 
      border-radius:0.5rem; 
      background:white;
      border:1px solid #E5E7EB;
      font-size:0.75rem;
      transition:all 0.15s ease;
    }
    .metric-item:hover {
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    .metric-icon{ 
      width:24px; 
      height:24px; 
      border-radius:50%; 
      display:flex; 
      align-items:center; 
      justify-content:center; 
      color:white; 
      background:#69B578; 
      font-size:0.75rem; 
      flex:0 0 24px;
    }
    /* Warna sesuai kondisi: hijau=aman, kuning=perhatian, merah=bahaya */
    .metric-item.risk-ok{ 
      border-left:3px solid #22C55E; 
      background:#F0FDF4; 
    }
    .metric-item.risk-ok .metric-icon{ 
      background:#22C55E; 
    }
    .metric-item.risk-warn{ 
      border-left:3px solid #F59E0B; 
      background:#FFFBEB; 
    }
    .metric-item.risk-warn .metric-icon{ 
      background:#F59E0B; 
      color:#000; 
    }
    .metric-item.risk-crit{ 
      border-left:3px solid #EF4444; 
      background:#FEF2F2; 
    }
    .metric-item.risk-crit .metric-icon{ 
      background:#EF4444; 
    }
    .metric-text {
      flex:1;
      line-height:1.4;
      color:#374151;
      font-weight:500;
    }
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
    /* Premium Anomaly Card Design - Compact & Efficient */
    .anomaly-item {
      background: white;
      border-radius: 0.625rem; /* rounded-lg */
      box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm lebih halus */
      padding: 0.75rem 1rem; /* p-3 p-4 - lebih compact */
      margin-bottom: 0.5rem;
      border-left: 3px solid #EF4444; /* Border lebih tipis */
      transition: all 0.15s ease;
    }
    .anomaly-item:hover {
      box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
    }
    .anomaly-item:last-child { margin-bottom: 0; }
    
    /* Anomaly Card Header - Compact */
    .anomaly-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }
    
    /* Badge Kategori Sensor - Compact */
    .anomaly-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.25rem 0.5rem;
      border-radius: 0.375rem;
      font-size: 0.6875rem; /* 11px */
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.025em;
    }
    .anomaly-badge.light {
      background-color: #FEF3C7; /* yellow-100 */
      color: #A16207; /* yellow-700 */
    }
    .anomaly-badge.temperature {
      background-color: #DBEAFE; /* blue-100 */
      color: #1E40AF; /* blue-700 */
    }
    .anomaly-badge.ammonia {
      background-color: #E9D5FF; /* purple-100 */
      color: #6B21A8; /* purple-700 */
    }
    .anomaly-badge.humidity {
      background-color: #CFFAFE; /* cyan-100 */
      color: #0E7490; /* cyan-700 */
    }
    .anomaly-badge.unknown {
      background-color: #F3F4F6; /* gray-100 */
      color: #374151; /* gray-700 */
    }
    
    /* Icon dalam badge - Compact */
    .anomaly-icon {
      font-size: 0.75rem; /* 12px */
      line-height: 1;
    }
    
    /* Timestamp - Compact */
    .anomaly-timestamp {
      font-size: 0.6875rem; /* 11px */
      color: #9CA3AF; /* gray-400 */
      font-weight: 500;
    }
    
    /* Anomaly Content - Compact */
    .anomaly-content {
      margin-top: 0.25rem;
    }
    .anomaly-title {
      font-size: 0.8125rem; /* 13px */
      font-weight: 600; /* font-semibold */
      color: #111827; /* gray-900 */
      line-height: 1.4;
    }
    
    /* Border kiri berdasarkan severity */
    .anomaly-item[data-severity="critical"] {
      border-left-color: #EF4444; /* red-500 */
    }
    .anomaly-item[data-severity="warning"] {
      border-left-color: #F59E0B; /* amber-500 */
    }
    .anomaly-item[data-severity="normal"] {
      border-left-color: #10B981; /* emerald-500 */
    }
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
  @include('layouts.sidebar')
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <h1>Dashboard Monitoring</h1>
    </div>
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

    <!-- Premium Forecast Summary - Compact -->
    <div class="chart-card" id="forecastCard" style="display:none;">
      <h6 class="text-sm font-semibold text-gray-700 mb-3">Ringkasan Prediksi</h6>
      <div class="forecast-grid">
        <div class="forecast-col">
          <h5>6 Jam</h5>
          <div id="forecastList6" style="display:grid; gap:0.5rem;"></div>
        </div>
        <div class="forecast-col">
          <h5>24 Jam</h5>
          <div id="forecastList24" style="display:grid; gap:0.5rem;"></div>
        </div>
      </div>
    </div>



    <!-- Anomaly List - Compact -->
    <div class="anomaly-card" id="anomalyPanel" style="display:none;">
      <h6 class="text-sm font-semibold text-gray-700 mb-3">Deteksi Anomali Sensor</h6>
      <div id="anomalyList" class="space-y-2"></div>
      <!-- Premium Pagination - Compact -->
      <div id="anomalyPagination" style="display:none; margin-top:1rem; padding-top:1rem; border-top:1px solid #E5E7EB;">
        <div class="flex justify-between items-center gap-4">
          <div class="text-xs text-gray-600">
            <span id="anomalyPageInfo">Halaman 1 dari 1</span>
          </div>
          <div class="flex gap-2">
            <button id="anomalyPrevBtn" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
              <i class="fa-solid fa-chevron-left mr-1"></i> Sebelumnya
            </button>
            <button id="anomalyNextBtn" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
              Selanjutnya <i class="fa-solid fa-chevron-right ml-1"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div id="noAnomaly" style="display:none;" class="anomaly-card">
      <h6 class="text-sm font-semibold text-gray-700 mb-3">Deteksi Anomali Sensor</h6>
      <div class="flex items-center justify-center py-8 px-4">
        <div class="text-center">
          <div class="text-3xl mb-2">✅</div>
          <p class="text-xs text-gray-600 font-medium">Tidak ada anomali terdeteksi</p>
          <p class="text-xs text-gray-500 mt-1">Semua sensor dalam batas aman</p>
        </div>
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

    // Global variable untuk menyimpan thresholds dari API
    let globalThresholds = {
      temperature: { ideal_min: 23, ideal_max: 34, danger_low: 20, danger_high: 37 },
      humidity: { ideal_min: 50, ideal_max: 70, warn_low: 50, warn_high: 80, danger_high: 80 },
      ammonia: { ideal_max: 20, warn_max: 35, danger_max: 35 },
      light: { ideal_low: 20, ideal_high: 40, warn_low: 10, warn_high: 60 }
    };
    
    // Fungsi untuk menentukan status sensor berdasarkan threshold (Premium Colors)
    function getSensorStatus(key, value){
      const thresholds = globalThresholds;
      
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
      // Semua sensor menggunakan 1 desimal untuk konsistensi
      const formattedValue = parseFloat(value).toFixed(1);
      
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
      
      // Format status label lebih profesional dan informatif
      const statusLabels = {
        'baik': 'Kandang dalam Kondisi Optimal',
        'perhatian': 'Perhatian: Kondisi Kandang Perlu Tindakan',
        'buruk': 'Peringatan: Kondisi Kandang Membahayakan',
        'tidak diketahui': 'Status Tidak Dapat Ditentukan'
      };
      
      const statusLabel = status.label || 'tidak diketahui';
      const statusText = statusLabels[statusLabel] || 'Kondisi Kandang ' + statusLabel.charAt(0).toUpperCase() + statusLabel.slice(1);
      
      // Title: hanya status (hilangkan confidence dari title untuk lebih clean)
      if (titleEl) {
        titleEl.innerHTML = statusText + ' <span id="mlActiveBadge" class="badge bg-success ms-2" style="display:none;">ML Active</span>';
      }
      
      // Detail: message yang informatif dengan struktur yang lebih profesional
      if (detailEl) {
        let htmlContent = '';
        
        // 1. Message utama (action items)
        let mainMessage = '';
        if (status.message) {
          // Gunakan message dari ML service (sudah informatif)
          mainMessage = status.message;
        } else {
          // Fallback message berdasarkan status
          const fallbackMessages = {
            'baik': 'Semua parameter sensor dalam batas aman. Tidak ada tindakan yang diperlukan saat ini.',
            'perhatian': 'Beberapa parameter sensor di luar batas optimal. Lakukan pengecekan ventilasi, pakan, dan air minum.',
            'buruk': 'Kondisi lingkungan membahayakan kesehatan ayam. Segera lakukan penyesuaian suhu, kelembaban, atau ventilasi.',
          };
          mainMessage = fallbackMessages[statusLabel] || 'Status tidak dapat ditentukan. Silakan refresh halaman.';
        }
        
        // Pisahkan message utama dari confidence/probabilitas jika ada
        // Hapus bagian "(Tingkat keyakinan sistem: ...)" dari message jika ada
        let cleanMessage = mainMessage;
        if (cleanMessage.includes('(Tingkat keyakinan sistem:')) {
          cleanMessage = cleanMessage.split('(Tingkat keyakinan sistem:')[0].trim();
        }
        
        htmlContent += `<div style="margin-bottom: 0.5rem; line-height: 1.6;">${cleanMessage}</div>`;
        
        // 2. Informasi teknis (confidence & probabilitas) - dalam container terpisah
        let techInfo = [];
        
        // Confidence level
        if (status.confidence !== undefined && status.confidence !== null) {
          const confidencePercent = Math.round(status.confidence * 100);
          let confidenceLevel = '';
          let confidenceIcon = '';
          if (confidencePercent >= 80) {
            confidenceLevel = 'Sangat yakin';
            confidenceIcon = '✓';
          } else if (confidencePercent >= 60) {
            confidenceLevel = 'Cukup yakin';
            confidenceIcon = '⚠';
          } else {
            confidenceLevel = 'Perlu verifikasi manual';
            confidenceIcon = '⚠';
          }
          techInfo.push(`<span style="display: inline-flex; align-items: center; gap: 0.25rem;"><strong>${confidenceIcon} Keyakinan:</strong> ${confidenceLevel}</span>`);
        }
        
        // Tampilkan manual review flag jika diperlukan (tanpa perbandingan detail)
        if (status.needs_manual_review) {
          techInfo.push(`<span style="display: inline-flex; align-items: center; gap: 0.25rem; color: #FEF3C7;"><strong>⚠️ Perlu Verifikasi Manual</strong></span>`);
        }
        
        // Probabilitas detail (dari ML original) HANYA jika confidence < 80%
        if (status.probability && status.confidence !== undefined && status.confidence < 0.8) {
          const prob = status.probability;
          const probBAIK = (prob.BAIK * 100).toFixed(1);
          const probPERHATIAN = (prob.PERHATIAN * 100).toFixed(1);
          const probBURUK = (prob.BURUK * 100).toFixed(1);
          
          // Format probabilitas dengan badge visual yang lebih profesional
          const probHTML = `
            <span style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem; flex-wrap: wrap;">
              <strong style="white-space: nowrap;">Probabilitas:</strong>
              <span style="display: inline-flex; gap: 0.5rem; flex-wrap: wrap;">
                <span class="prob-badge badge-baik">BAIK ${probBAIK}%</span>
                <span class="prob-badge badge-perhatian">PERHATIAN ${probPERHATIAN}%</span>
                <span class="prob-badge badge-buruk">BURUK ${probBURUK}%</span>
              </span>
            </span>
          `;
          techInfo.push(probHTML);
        }
        
        // Gabungkan informasi teknis dengan separator yang lebih jelas
        if (techInfo.length > 0) {
          htmlContent += `<div style="margin-top: 0.625rem; padding-top: 0.625rem; border-top: 1px solid rgba(255, 255, 255, 0.25); display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.8125rem; opacity: 0.95; line-height: 1.5;">${techInfo.join('')}</div>`;
        }
        
        detailEl.innerHTML = htmlContent;
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
          // Tentukan warna banner berdasarkan status label (prioritas utama)
          const statusLabelLower = (status.label || '').toLowerCase();
          if (statusLabelLower === 'buruk' || statusLabelLower.includes('buruk') || severity === 'critical' || severity === 'bahaya' || status.label?.includes('tidak optimal')) {
              predictionBanner.style.background = 'linear-gradient(90deg, #EF4444, #DC2626)'; // Merah premium untuk bahaya
          } else if (statusLabelLower === 'perhatian' || statusLabelLower.includes('perhatian') || severity === 'warning') {
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
      // Gunakan waktu real-time saat ini untuk semua label
      const now = new Date();
      const nowYear = now.getFullYear();
      const nowMonth = String(now.getMonth() + 1).padStart(2, '0');
      const nowDay = String(now.getDate()).padStart(2, '0');
      
      const historyLabels = history.map((p, i) => {
        // Hitung jam yang lalu dari waktu saat ini
        const hoursAgo = history.length - 1 - i;
        const labelTime = new Date(now.getTime() - hoursAgo * 3600 * 1000);
        
        const labelDay = String(labelTime.getDate()).padStart(2, '0');
        const labelMonth = String(labelTime.getMonth() + 1).padStart(2, '0');
        const labelHour = String(labelTime.getHours()).padStart(2, '0');
        
        // Format: "DD/MM HH:00"
        return `${labelDay}/${labelMonth} ${labelHour}:00`;
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
              min: 0,
              max: 100,
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
                stepSize: 10,
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
      // Helper function untuk mendapatkan icon dan badge class berdasarkan type
      function getAnomalyTypeInfo(type) {
        const typeLower = (type || 'unknown').toLowerCase();
        const typeMap = {
          'light': { icon: '💡', badgeClass: 'light', label: 'LIGHT' },
          'temperature': { icon: '🌡️', badgeClass: 'temperature', label: 'TEMPERATURE' },
          'ammonia': { icon: '🧪', badgeClass: 'ammonia', label: 'AMMONIA' },
          'humidity': { icon: '💧', badgeClass: 'humidity', label: 'HUMIDITY' }
        };
        return typeMap[typeLower] || { icon: '⚠️', badgeClass: 'unknown', label: (type || 'UNKNOWN').toUpperCase() };
      }
      
      // Helper function untuk format deskripsi ringkas dengan threshold dari database
      function formatAnomalyDescription(message, value, sensorType) {
        if (!message) return 'Anomali terdeteksi';
        
        // Extract nilai dari message
        const valueMatch = message.match(/nilai:\s*([\d.]+)/i);
        const unitMatch = message.match(/(lux|ppm|°C|%)/i);
        const unit = unitMatch ? unitMatch[1] : '';
        
        // Tentukan threshold berdasarkan sensor type dari globalThresholds
        // Pastikan menggunakan nilai yang sudah dikonversi ke number
        let safeThreshold = '';
        const sensorTypeLower = (sensorType || '').toLowerCase();
        if (sensorTypeLower === 'temperature' || sensorTypeLower === 'suhu') {
          const max = typeof globalThresholds.temperature?.ideal_max === 'number' 
            ? globalThresholds.temperature.ideal_max 
            : parseFloat(globalThresholds.temperature?.ideal_max) || 34;
          safeThreshold = max.toFixed(1);
        } else if (sensorTypeLower === 'humidity' || sensorTypeLower === 'kelembaban') {
          const max = typeof globalThresholds.humidity?.ideal_max === 'number' 
            ? globalThresholds.humidity.ideal_max 
            : parseFloat(globalThresholds.humidity?.ideal_max) || 70;
          safeThreshold = max.toFixed(1);
        } else if (sensorTypeLower === 'ammonia' || sensorTypeLower === 'amoniak') {
          const max = typeof globalThresholds.ammonia?.ideal_max === 'number' 
            ? globalThresholds.ammonia.ideal_max 
            : parseFloat(globalThresholds.ammonia?.ideal_max) || 20;
          safeThreshold = max.toFixed(1);
        } else if (sensorTypeLower === 'light' || sensorTypeLower === 'cahaya') {
          const max = typeof globalThresholds.light?.ideal_high === 'number' 
            ? globalThresholds.light.ideal_high 
            : parseFloat(globalThresholds.light?.ideal_high) || 40;
          safeThreshold = max.toFixed(1);
        }
        
        if (valueMatch) {
          const val = parseFloat(valueMatch[1]).toFixed(1);
          
          // Jika ada z-score, format dengan z-score
          const zScoreMatch = message.match(/z-score:\s*([\d.]+)/i);
          if (zScoreMatch) {
            const zScore = parseFloat(zScoreMatch[1]).toFixed(2);
            return `Nilai: ${val} ${unit} (z-score: ${zScore})`;
          }
          
          // Jika ada threshold dari database, gunakan itu
          if (safeThreshold) {
            return `Nilai: ${val} ${unit} (batas aman: ${safeThreshold} ${unit})`;
          }
          
          // Fallback: coba extract dari message
          const thresholdMatch = message.match(/(?:di atas|di bawah|batas aman|batas)\s*([\d.]+)/i);
          if (thresholdMatch) {
            const threshold = parseFloat(thresholdMatch[1]).toFixed(0);
            return `Nilai: ${val} ${unit} (batas aman: ${threshold} ${unit})`;
          }
        }
        
        // Fallback: gunakan message asli tapi ringkas (hapus duplikasi nilai)
        return message.replace(/\s*\(nilai:.*?\)/g, '').substring(0, 100);
      }
      
      // Helper function untuk format timestamp anomali menjadi real-time
      function formatAnomalyTime(anomalyTime, index) {
        const now = new Date();
        
        // Jika ada waktu dari anomaly, coba parse dan hitung jam yang lalu
        if (anomalyTime) {
          try {
            const anomalyDate = new Date(anomalyTime);
            // Hitung perkiraan jam yang lalu berdasarkan index (anomali terbaru = 0 jam lalu)
            // Asumsi: anomali terbaru terjadi dalam 1-2 jam terakhir
            const hoursAgo = Math.min(index, 24); // Max 24 jam lalu
            const displayTime = new Date(now.getTime() - (hoursAgo * 3600 * 1000));
            
            const day = String(displayTime.getDate()).padStart(2, '0');
            const month = String(displayTime.getMonth() + 1).padStart(2, '0');
            const hour = String(displayTime.getHours()).padStart(2, '0');
            
            return `${displayTime.getFullYear()}-${month}-${day} ${hour}:00`;
          } catch (e) {
            // Fallback jika parsing gagal
          }
        }
        
        // Fallback: gunakan waktu real-time dikurangi index (jam yang lalu)
        const hoursAgo = Math.min(index, 24);
        const displayTime = new Date(now.getTime() - (hoursAgo * 3600 * 1000));
        const day = String(displayTime.getDate()).padStart(2, '0');
        const month = String(displayTime.getMonth() + 1).padStart(2, '0');
        const hour = String(displayTime.getHours()).padStart(2, '0');
        
        return `${displayTime.getFullYear()}-${month}-${day} ${hour}:00`;
      }
      
      anomalyList.innerHTML = displayAnomalies.map((a, idx) => {
        // Tentukan severity berdasarkan type atau severity dari data
        const severity = a.severity || (a.type === 'unknown' ? 'warning' : 'critical');
        const typeInfo = getAnomalyTypeInfo(a.type);
        // Pastikan sensorType menggunakan format yang benar (lowercase)
        const sensorType = (a.type || a.sensor_type || '').toLowerCase();
        const description = formatAnomalyDescription(a.message || '', a.value, sensorType);
        const formattedTime = formatAnomalyTime(a.time, startIndex + idx);
        
        return `
          <div class="anomaly-item" data-severity="${severity}">
            <div class="anomaly-header">
              <span class="anomaly-badge ${typeInfo.badgeClass}">
                <span class="anomaly-icon">${typeInfo.icon}</span>
                <span>${typeInfo.label}</span>
              </span>
              <span class="anomaly-timestamp">${formattedTime}</span>
            </div>
            <div class="anomaly-content">
              <div class="anomaly-title">${description}</div>
            </div>
          </div>
        `;
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
        history.push({time:hour, temperature:+temp.toFixed(1), humidity:+hum.toFixed(1), ammonia:+ammo.toFixed(1), light:+light.toFixed(1)});
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
        // Gunakan threshold dari globalThresholds untuk forecast summary
        const forecast_summary_6h = [
          sum(pred6.temperature,'Suhu','°C', globalThresholds.temperature.ideal_min, globalThresholds.temperature.ideal_max),
          sum(pred6.humidity,'Kelembaban','%', globalThresholds.humidity.ideal_min, globalThresholds.humidity.ideal_max),
          sum(pred6.ammonia,'Amoniak','ppm', 0, globalThresholds.ammonia.ideal_max),
          sum(pred6.light,'Cahaya','lux', globalThresholds.light.ideal_low, globalThresholds.light.ideal_high)
        ];
        const forecast_summary_24h = [
          sum(pred24.temperature,'Suhu','°C', globalThresholds.temperature.ideal_min, globalThresholds.temperature.ideal_max),
          sum(pred24.humidity,'Kelembaban','%', globalThresholds.humidity.ideal_min, globalThresholds.humidity.ideal_max),
          sum(pred24.ammonia,'Amoniak','ppm', 0, globalThresholds.ammonia.ideal_max),
          sum(pred24.light,'Cahaya','lux', globalThresholds.light.ideal_low, globalThresholds.light.ideal_high)
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
          <div style="margin-top: 8px; padding: 8px; background: rgba(255, 193, 7, 0.1); border-left: 3px solid #ffc107; border-radius: 4px; font-size: 0.75rem; line-height: 1.4;">
            <strong>⚠️ Perhatian:</strong> ML Service tidak terhubung. Untuk menggunakan Random Forest, LSTM, dan Isolation Forest, jalankan <code>START_ML_SERVICE.bat</code> di folder root project.
          </div>
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

    // ========== URGENT ALERT SYSTEM ==========
    
    // Check for urgent alerts
    function checkUrgentAlerts(data) {
      const { status, prediction_6h, anomalies, forecast_summary_6h } = data;
      const alerts = [];
      
      // 1. Check status BURUK dengan confidence tinggi
      // Status sudah menggunakan threshold dari database (dihitung di routes/web.php berdasarkan data sensor aktual)
      if (status.label === 'buruk' && status.confidence >= 0.6) {
        alerts.push({
          type: 'critical',
          title: '🚨 Peringatan: Kondisi Kandang Membahayakan',
          message: status.message || 'Kondisi lingkungan tidak optimal dan berpotensi membahayakan kesehatan ayam berdasarkan threshold yang tersimpan.',
          action: 'Segera lakukan penyesuaian suhu, kelembaban, ventilasi, atau pencahayaan sesuai threshold yang tersimpan. Jika perlu, hubungi dokter hewan.',
          urgency: 'high'
        });
      }
      
      // 2. Check prediksi 6h menunjukkan BURUK (probability > 0.55 dari threshold optimal)
      // Status probability sudah menggunakan threshold dari database (dihitung di routes/web.php)
      if (status.probability && status.probability.BURUK > 0.55) {
        const burukProbability = (status.probability.BURUK * 100).toFixed(1);
        const urgency = status.probability.BURUK > 0.70 ? 'high' : 'medium';
        const type = status.probability.BURUK > 0.70 ? 'critical' : 'warning';
        
        alerts.push({
          type: type,
          title: '⚠️ Prediksi: Risiko Meningkat dalam 6 Jam',
          message: `Model ML memprediksi kondisi kandang berpotensi memburuk (${burukProbability}% kemungkinan BURUK) berdasarkan threshold yang tersimpan.`,
          action: 'Lakukan tindakan pencegahan: periksa ventilasi, suhu, kelembaban, dan cahaya sesuai threshold yang tersimpan.',
          urgency: urgency
        });
      }
      
      // 3. Check anomali critical
      // Anomali sudah menggunakan threshold dari database (melalui formatAnomalyDescription)
      const criticalAnomalies = anomalies.filter(a => a.severity === 'critical');
      if (criticalAnomalies.length > 0) {
        // Ambil detail anomali dengan threshold yang benar
        const anomalyDetails = criticalAnomalies.slice(0, 3).map(a => {
          const sensorType = (a.type || a.sensor_type || '').toLowerCase();
          let thresholdInfo = '';
          
          // Tentukan threshold info berdasarkan sensor type
          if (sensorType === 'temperature' || sensorType === 'suhu') {
            thresholdInfo = ` (batas aman: ${globalThresholds.temperature?.ideal_max || 34}°C)`;
          } else if (sensorType === 'humidity' || sensorType === 'kelembaban') {
            thresholdInfo = ` (batas aman: ${globalThresholds.humidity?.ideal_max || 70}%)`;
          } else if (sensorType === 'ammonia' || sensorType === 'amoniak') {
            thresholdInfo = ` (batas aman: ${globalThresholds.ammonia?.ideal_max || 20} ppm)`;
          } else if (sensorType === 'light' || sensorType === 'cahaya') {
            thresholdInfo = ` (batas aman: ${globalThresholds.light?.ideal_high || 40} lux)`;
          }
          
          return (a.message || a.type) + thresholdInfo;
        }).join(', ');
        
        alerts.push({
          type: 'critical',
          title: `🚨 ${criticalAnomalies.length} Anomali Kritis Terdeteksi`,
          message: anomalyDetails || criticalAnomalies.slice(0, 3).map(a => a.message || a.type).join(', '),
          action: 'Segera periksa sensor dan kondisi kandang sesuai threshold yang tersimpan.',
          urgency: 'high'
        });
      }
      
      // 4. Check forecast menunjukkan threshold akan dilampaui
      // Pastikan menggunakan forecast_summary yang sudah menggunakan threshold dari database
      if (forecast_summary_6h && Array.isArray(forecast_summary_6h)) {
        forecast_summary_6h.forEach(forecast => {
          // Cek apakah risk menunjukkan kondisi di luar batas aman atau potensi keluar batas
          // forecast.risk sudah menggunakan threshold dari database (melalui globalThresholds)
          if (forecast.risk && (forecast.risk.includes('di luar batas aman') || forecast.risk.includes('potensi keluar batas aman'))) {
            // Tentukan urgency berdasarkan risk level
            const urgency = forecast.risk.includes('di luar batas aman') ? 'high' : 'medium';
            const type = forecast.risk.includes('di luar batas aman') ? 'critical' : 'warning';
            
            alerts.push({
              type: type,
              title: `⚠️ ${forecast.metric} Diprediksi ${forecast.risk.includes('di luar batas aman') ? 'Keluar Batas Aman' : 'Berpotensi Keluar Batas Aman'}`,
              message: forecast.summary || `${forecast.metric} diprediksi ${forecast.risk.includes('di luar batas aman') ? 'keluar batas aman' : 'berpotensi keluar batas aman'} dalam 6 jam ke depan.`,
              action: `Periksa dan sesuaikan ${forecast.metric.toLowerCase()} dalam beberapa jam ke depan sesuai threshold yang tersimpan.`,
              urgency: urgency
            });
          }
        });
      }
      
      return alerts;
    }
    
    // Show urgent alert pop-up
    function showUrgentAlert(alert) {
      const icon = alert.type === 'critical' ? 'error' : 'warning';
      const confirmButtonColor = alert.type === 'critical' ? '#dc2626' : '#facc15';
      
      Swal.fire({
        icon: icon,
        title: alert.title,
        html: `
          <div style="text-align: left; margin-top: 1rem;">
            <p style="margin-bottom: 0.75rem; font-size: 0.95rem; line-height: 1.6;">${alert.message}</p>
            <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 8px; border-left: 4px solid ${confirmButtonColor};">
              <strong style="color: ${confirmButtonColor}; display: block; margin-bottom: 0.5rem;">Tindakan Disarankan:</strong>
              <p style="margin: 0; font-size: 0.875rem; line-height: 1.5;">${alert.action}</p>
            </div>
          </div>
        `,
        confirmButtonText: 'Saya Mengerti',
        confirmButtonColor: confirmButtonColor,
        allowOutsideClick: false,
        allowEscapeKey: true,
        showCloseButton: true,
        width: '600px',
        customClass: {
          popup: 'alert-popup',
          title: 'alert-title',
          htmlContainer: 'alert-content'
        }
      });
    }
    
    // Sticky Bar dihapus - hanya pop-up alert yang digunakan di halaman monitoring
    
    // Browser Push Notification
    let notificationPermission = Notification.permission;
    
    async function requestNotificationPermission() {
      if ('Notification' in window && Notification.permission === 'default') {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
          notificationPermission = 'granted';
          localStorage.setItem('notificationPermission', 'granted');
        }
      }
    }
    
    function showBrowserNotification(title, options) {
      if (!('Notification' in window)) {
        return;
      }
      
      if (Notification.permission === 'granted') {
        new Notification(title, {
          icon: '/favicon.ico',
          badge: '/favicon.ico',
          body: options.body || '',
          tag: options.tag || 'chickpatrol-alert',
          requireInteraction: options.requireInteraction || false,
          ...options
        });
      }
    }
    
    function notifyUrgentAlert(alert) {
      showBrowserNotification(alert.title, {
        body: alert.message + '\n\n' + alert.action,
        tag: `alert-${alert.type}-${Date.now()}`,
        requireInteraction: alert.urgency === 'high'
      });
    }
    
    // Request notification permission on page load
    if ('Notification' in window && Notification.permission === 'default') {
      // Request permission after a short delay
      setTimeout(() => {
        requestNotificationPermission();
      }, 2000);
    }
    
    async function loadMonitoring(forceRefresh = false){
      sensorGrid.innerHTML = '<div class="loading-overlay">Memuat data sensor...</div>';
      try {
        // Ambil profile yang dipilih dari localStorage (jika ada), atau gunakan 'default'
        const selectedProfile = localStorage.getItem('selectedThresholdProfile') || 'default';
        const res = await fetch(`/api/monitoring/tools?t=${Date.now()}&profile=${selectedProfile}`, { 
          headers:{ 'Accept':'application/json' } 
        });
        
        // Check if response is ok
        if (!res.ok) {
          let errorMessage = 'HTTP ' + res.status;
          try {
            const errorData = await res.json();
            errorMessage = errorData.message || errorData.error || errorMessage;
            console.error('API Error:', errorData);
          } catch (e) {
            const errorText = await res.text();
            console.error('API Error (text):', errorText);
            errorMessage = errorText || errorMessage;
          }
          throw new Error(errorMessage);
        }
        
        const data = await res.json();
        
        // Check if response has error
        if (data.error) {
          throw new Error(data.message || data.error || 'Unknown error');
        }
        // Use let instead of const for forecast_summary variables since they may be reassigned
        let { latest, history, prediction_6h, prediction_24h, status, anomalies, forecast_summary_6h, forecast_summary_24h, ml_metadata, meta, thresholds } = data;
        
        // Log informasi ML Status (Random Forest) untuk debugging
        console.log('=== ML STATUS INFORMATION (Random Forest) ===');
        console.log('Status Label:', status?.label || 'not set');
        console.log('Status Confidence:', status?.confidence || 'not set');
        console.log('Status Probability:', status?.probability || 'not set');
        if (status?.probability) {
          console.log('  - BAIK:', (status.probability.BAIK * 100).toFixed(1) + '%');
          console.log('  - PERHATIAN:', (status.probability.PERHATIAN * 100).toFixed(1) + '%');
          console.log('  - BURUK:', (status.probability.BURUK * 100).toFixed(1) + '%');
        }
        console.log('ML Metadata:', ml_metadata || 'not set');
        if (ml_metadata) {
          console.log('  - Model Name:', ml_metadata.model_name || 'not set');
          console.log('  - Model Version:', ml_metadata.model_version || 'not set');
          console.log('  - Source:', ml_metadata.source || 'not set');
          console.log('  - Accuracy:', ml_metadata.accuracy ? (ml_metadata.accuracy * 100).toFixed(2) + '%' : 'N/A');
          console.log('  - Prediction Time:', ml_metadata.prediction_time ? ml_metadata.prediction_time + 'ms' : 'N/A');
          // Note: ml_metadata.confidence adalah string ("medium"/"low"/"high") dari ML service
          // Confidence yang benar (angka 0-1) ada di status.confidence yang sudah di-adjust
          console.log('  - Confidence (from ML service, string):', ml_metadata.confidence || 'N/A');
          console.log('  - Confidence (adjusted, numeric):', status?.confidence ? (status.confidence * 100).toFixed(1) + '%' : 'N/A');
        }
        console.log('ML Source (from meta):', meta?.ml_source || 'not set');
        console.log('ML Connected:', meta?.ml_connected || false);
        console.log('==========================================');
        
        // Update global thresholds dengan data dari API (REPLACE, bukan merge)
        if (thresholds) {
          // Replace seluruh threshold dengan data dari API
          // IMPORTANT: Convert string to number untuk perbandingan yang benar
          if (thresholds.temperature) {
            globalThresholds.temperature = {
              ideal_min: parseFloat(thresholds.temperature.ideal_min) || globalThresholds.temperature.ideal_min,
              ideal_max: parseFloat(thresholds.temperature.ideal_max) || globalThresholds.temperature.ideal_max,
              danger_low: parseFloat(thresholds.temperature.danger_low) || globalThresholds.temperature.danger_low,
              danger_high: parseFloat(thresholds.temperature.danger_high) || globalThresholds.temperature.danger_high
            };
          }
          if (thresholds.humidity) {
            globalThresholds.humidity = {
              ideal_min: parseFloat(thresholds.humidity.ideal_min) || globalThresholds.humidity.ideal_min,
              ideal_max: parseFloat(thresholds.humidity.ideal_max) || globalThresholds.humidity.ideal_max,
              warn_low: parseFloat(thresholds.humidity.warn_low) || globalThresholds.humidity.warn_low,
              warn_high: parseFloat(thresholds.humidity.warn_high) || globalThresholds.humidity.warn_high,
              danger_high: parseFloat(thresholds.humidity.danger_high) || globalThresholds.humidity.danger_high
            };
          }
          if (thresholds.ammonia) {
            globalThresholds.ammonia = {
              ideal_max: parseFloat(thresholds.ammonia.ideal_max) || globalThresholds.ammonia.ideal_max,
              warn_max: parseFloat(thresholds.ammonia.warn_max) || globalThresholds.ammonia.warn_max,
              danger_max: parseFloat(thresholds.ammonia.danger_max) || globalThresholds.ammonia.danger_max
            };
          }
          if (thresholds.light) {
            globalThresholds.light = {
              ideal_low: parseFloat(thresholds.light.ideal_low) || globalThresholds.light.ideal_low,
              ideal_high: parseFloat(thresholds.light.ideal_high) || globalThresholds.light.ideal_high,
              warn_low: parseFloat(thresholds.light.warn_low) || globalThresholds.light.warn_low,
              warn_high: parseFloat(thresholds.light.warn_high) || globalThresholds.light.warn_high
            };
          }
          
          // Debug log untuk memastikan threshold ter-update
          console.log('Thresholds updated from API (as numbers):', globalThresholds);
          console.log('Using profile:', selectedProfile);
        }
        
        // Selalu regenerate forecast_summary menggunakan globalThresholds yang sudah ter-update
        // Ini memastikan forecast_summary selalu sesuai dengan threshold yang aktif
        // Helper function untuk generate forecast summary
        const generateForecastSummary = (predictionData) => {
            const sum = (series, metric, unit, low, high) => {
            if (!series || !Array.isArray(series) || series.length === 0) {
              return { metric, summary: `${metric} tidak tersedia`, range: {min: 0, max: 0, unit}, trend: 'stabil', risk: 'dalam kisaran aman' };
            }
            const min = Math.min(...series);
            const max = Math.max(...series);
            const trend = series[series.length-1] - series[0];
            const dir = trend > 0.5 ? 'meningkat' : (trend < -0.5 ? 'menurun' : 'stabil');
            const risk = (min < low || max > high) ? 'potensi keluar batas aman' : 'dalam kisaran aman';
            return { 
              metric, 
              summary: `${metric} ${dir} (${min.toFixed ? min.toFixed(2) : min}–${max.toFixed ? max.toFixed(2) : max} ${unit}) ${risk}`, 
              range: {min, max, unit}, 
              trend: dir, 
              risk 
            };
          };
          
          return [
            sum(predictionData.temperature, 'Suhu', '°C', globalThresholds.temperature.ideal_min, globalThresholds.temperature.ideal_max),
            sum(predictionData.humidity, 'Kelembaban', '%', globalThresholds.humidity.ideal_min, globalThresholds.humidity.ideal_max),
            sum(predictionData.ammonia, 'Amoniak', 'ppm', 0, globalThresholds.ammonia.ideal_max),
            sum(predictionData.light, 'Cahaya', 'lux', globalThresholds.light.ideal_low, globalThresholds.light.ideal_high)
          ];
        };
        
        // Regenerate forecast_summary menggunakan globalThresholds yang sudah ter-update
        // Ini memastikan konsistensi antara threshold dan forecast summary
        if (prediction_6h && prediction_24h) {
          console.log('Regenerating forecast summary dengan threshold yang ter-update...');
          forecast_summary_6h = generateForecastSummary(prediction_6h);
          forecast_summary_24h = generateForecastSummary(prediction_24h);
          console.log('Forecast Summary regenerated dengan threshold:', globalThresholds);
          console.log('Forecast Summary 6h:', forecast_summary_6h);
        } else {
          console.warn('Prediction data tidak tersedia, menggunakan forecast_summary dari backend jika ada');
          // Fallback: jika prediction tidak ada, gunakan dari backend atau buat default
          if (!forecast_summary_6h || forecast_summary_6h.length === 0) {
            forecast_summary_6h = [];
            forecast_summary_24h = [];
          }
        }
        
        // Pastikan threshold sudah ter-update sebelum membuat sensor cards
        buildBanner(latest, status, forecast_summary_6h, meta);
        sensorGrid.innerHTML = [
          createSensorCard('temperature','Suhu', latest.temperature,'°C', history, prediction_6h.temperature),
          createSensorCard('humidity','Kelembaban', latest.humidity,'%', history, prediction_6h.humidity),
          createSensorCard('ammonia','Amoniak', latest.ammonia,'ppm', history, prediction_6h.ammonia),
          createSensorCard('light','Cahaya', latest.light,'lux', history, prediction_6h.light)
        ].join('');
        buildChart(history, prediction_6h);
        // Pastikan globalThresholds sudah ter-update sebelum render anomalies
        renderAnomalies(anomalies);
        
        // ========== URGENT ALERT SYSTEM ==========
        // Check for urgent alerts
        const alerts = checkUrgentAlerts(data);
        if (alerts.length > 0) {
          // Show most urgent first
          alerts.sort((a, b) => {
            const urgencyOrder = { 'high': 3, 'medium': 2, 'low': 1 };
            return urgencyOrder[b.urgency] - urgencyOrder[a.urgency];
          });
          
          // Show first alert immediately (pop-up)
          setTimeout(() => {
            showUrgentAlert(alerts[0]);
            
            // Send browser notification
            notifyUrgentAlert(alerts[0]);
            
            // Queue other alerts (show after user closes first)
            if (alerts.length > 1) {
              setTimeout(() => {
                alerts.slice(1).forEach((alert, index) => {
                  setTimeout(() => {
                    showUrgentAlert(alert);
                    notifyUrgentAlert(alert);
                  }, index * 3000);
                });
              }, 3000);
            }
          }, 1000);
        }
        
        // Forecast card
        const forecastCard = document.getElementById('forecastCard');
        const list6 = document.getElementById('forecastList6');
        const list24 = document.getElementById('forecastList24');
        // Premium forecast rendering - compact & efficient
        list6.innerHTML = forecast_summary_6h.map(f => {
          const risk = riskClass(f.risk);
          const icon = iconFor(f.metric);
          // Extract hanya informasi penting dari summary
          const summaryText = f.summary || '';
          return `
            <div class="metric-item ${risk}">
              <div class="metric-icon"><i class="fa-solid ${icon}"></i></div>
              <div class="metric-text">${summaryText}</div>
            </div>
          `;
        }).join('');
        list24.innerHTML = forecast_summary_24h.map(f => {
          const risk = riskClass(f.risk);
          const icon = iconFor(f.metric);
          const summaryText = f.summary || '';
          return `
            <div class="metric-item ${risk}">
              <div class="metric-icon"><i class="fa-solid ${icon}"></i></div>
              <div class="metric-text">${summaryText}</div>
            </div>
          `;
        }).join('');
        forecastCard.style.display='block';
        
        // ML Info Card - moved to Information Management page

        // Data Preview panel
      } catch (e){
        console.error('Error loading monitoring data:', e);
        
        // Get error message
        const errorMessage = e.message || 'Unknown error';
        const isMLConnectionError = errorMessage.includes('ML Service') || 
                                     errorMessage.includes('Connection') || 
                                     errorMessage.includes('timeout') ||
                                     errorMessage.includes('Failed to connect') ||
                                     errorMessage.includes('Connection refused');
        
        sensorGrid.innerHTML = `<div class="alert alert-danger">
          <strong>Error:</strong> ${errorMessage}
          <br><small>Gagal memuat data monitoring. Pastikan ML Service berjalan dan coba refresh halaman.</small>
        </div>`;
        
        // Tampilkan pesan error yang jelas
        const errorBanner = document.getElementById('predictionBanner');
        if (errorBanner) {
          errorBanner.style.display = 'block';
          errorBanner.style.background = '#EF4444';
          
          if (isMLConnectionError) {
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
                <p style="margin-top:0.5rem; font-size:0.85rem; color:#fee;"><strong>Detail Error:</strong> ${errorMessage}</p>
              </div>
            `;
          } else {
            errorBanner.innerHTML = `
              <i class="fa-solid fa-exclamation-triangle"></i>
              <div>
                <h5>Error Memuat Data</h5>
                <p>Terjadi kesalahan saat memuat data monitoring:</p>
                <p style="margin:0.5rem 0; font-size:0.9rem;"><strong>${errorMessage}</strong></p>
                <p style="margin-top:0.5rem; font-size:0.85rem;">Silakan refresh halaman atau hubungi administrator jika masalah berlanjut.</p>
              </div>
            `;
          }
        }
        
        // Jangan tampilkan data preview jika error
      }
    }

    document.addEventListener('DOMContentLoaded', loadMonitoring);
  </script>
</body>
</html>
