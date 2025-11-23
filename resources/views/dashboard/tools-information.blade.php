<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Manajemen Informasi - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
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
      z-index: 1000;
      transition: transform 0.3s ease;
    }
    
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
    }
    
    .sidebar-overlay.show {
      display: block;
    }
    
    .mobile-menu-toggle {
      display: none;
      position: fixed;
      top: 1rem;
      left: 1rem;
      z-index: 1001;
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 0.5rem 0.75rem;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .mobile-menu-toggle i {
      font-size: 1.25rem;
      color: #2F2F2F;
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
    
    .chart-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
    }
    
    .chart-card h6 {
      margin: 0 0 .75rem;
      font-size: .8rem;
      font-weight: 600;
      color: #6c757d;
    }
    
    /* Professional Threshold Cards */
    .threshold-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    
    @media (min-width: 768px) {
      .threshold-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (min-width: 1200px) {
      .threshold-grid {
        grid-template-columns: repeat(4, 1fr);
      }
    }
    
    .threshold-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 1rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    
    .threshold-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
    }
    
    .threshold-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .threshold-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
    }
    
    .threshold-header > div:last-child {
      flex: 1;
      min-width: 0;
    }
    
    .threshold-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }
    
    .threshold-title {
      font-weight: 700;
      font-size: 0.875rem;
      color: #2F2F2F;
      margin: 0 0 0.15rem 0;
      line-height: 1.3;
    }
    
    .threshold-subtitle {
      font-size: 0.7rem;
      color: #6c757d;
      margin: 0;
      line-height: 1.3;
    }
    
    .threshold-ranges {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-top: 0.75rem;
      flex: 1;
    }
    
    .range-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.8rem;
      flex-wrap: wrap;
    }
    
    .range-badge {
      padding: 0.25rem 0.6rem;
      border-radius: 6px;
      font-size: 0.7rem;
      font-weight: 600;
      white-space: nowrap;
    }
    
    .range-badge.ideal {
      background: #d4edda;
      color: #155724;
    }
    
    .range-badge.warning {
      background: #fff3cd;
      color: #856404;
    }
    
    .range-badge.danger {
      background: #f8d7da;
      color: #721c24;
    }
    
    .range-value {
      color: #6c757d;
      flex: 1;
    }
    
    /* Professional Classification Cards */
    .classification-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .classification-card {
      border-radius: 12px;
      padding: 1.5rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      border: 1px solid transparent;
    }
    
    .classification-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
    }
    
    .classification-card.baik {
      background: linear-gradient(135deg, #f0f9f4 0%, #e8f5ed 100%);
      border-color: #c3e6cb;
    }
    
    .classification-card.baik::before {
      background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
    }
    
    .classification-card.perhatian {
      background: linear-gradient(135deg, #fffbf0 0%, #fff8e1 100%);
      border-color: #ffeaa7;
    }
    
    .classification-card.perhatian::before {
      background: linear-gradient(180deg, #ffc107 0%, #ffb300 100%);
    }
    
    .classification-card.buruk {
      background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
      border-color: #f5c6cb;
    }
    
    .classification-card.buruk::before {
      background: linear-gradient(180deg, #dc3545 0%, #c82333 100%);
    }
    
    .classification-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .classification-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
    }
    
    .classification-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }
    
    .classification-card.baik .classification-icon {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }
    
    .classification-card.perhatian .classification-icon {
      background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
      color: #000;
    }
    
    .classification-card.buruk .classification-icon {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }
    
    .classification-title {
      font-weight: 700;
      font-size: 1rem;
      margin: 0;
    }
    
    .classification-card.baik .classification-title {
      color: #155724;
    }
    
    .classification-card.perhatian .classification-title {
      color: #856404;
    }
    
    .classification-card.buruk .classification-title {
      color: #721c24;
    }
    
    .classification-description {
      font-size: 0.85rem;
      line-height: 1.6;
      color: #495057;
      margin: 0;
    }
    
    /* ML Model Information */
    .ml-info-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
      margin-top: 1rem;
    }
    
    @media (max-width: 768px) {
      .ml-info-grid {
        grid-template-columns: 1fr;
      }
    }
    
    @media (min-width: 769px) and (max-width: 1024px) {
      .ml-info-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    .ml-info-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 1.25rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .ml-info-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(180deg, #6c5ce7 0%, #a29bfe 100%);
    }
    
    .ml-info-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .ml-info-header {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
    }
    
    .ml-info-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
      color: white;
    }
    
    .ml-info-title {
      font-weight: 700;
      font-size: 0.9rem;
      color: #2F2F2F;
      margin: 0;
    }
    
    .ml-info-value {
      font-size: 0.85rem;
      color: #6c757d;
      margin: 0;
      margin-top: 0.25rem;
    }
    
    .ml-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.4rem 0.75rem;
      border-radius: 8px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-top: 0.5rem;
    }
    
    .ml-status-badge.connected {
      background: #d4edda;
      color: #155724;
    }
    
    .ml-status-badge.disconnected {
      background: #f8d7da;
      color: #721c24;
    }
    
    .ml-status-badge.fallback {
      background: #fff3cd;
      color: #856404;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .mobile-menu-toggle {
        display: block;
      }
      
      .sidebar {
        transform: translateX(-100%);
        width: 260px;
      }
      
      .sidebar.show {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0 !important;
        padding: 1rem !important;
        padding-top: 4rem;
      }
      
      .page-header {
        margin-top: 0;
      }
      
      .chart-card {
        margin-bottom: 1rem;
        padding: 1rem;
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
      
      .threshold-grid,
      .classification-grid {
        grid-template-columns: 1fr;
      }
      
      .threshold-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
      }
      
      .threshold-icon {
        width: 50px;
        height: 50px;
      }
    }
    
    @media (max-width: 480px) {
      .page-header h1 {
        font-size: 1.25rem;
      }
      
      .chart-card h6 {
        font-size: .75rem;
      }
      
      .chart-card form button {
        font-size: .75rem;
        padding: .4rem .75rem;
      }
      
      .threshold-card,
      .classification-card {
        padding: 1rem;
      }
      
      .threshold-icon,
      .classification-icon {
        width: 36px;
        height: 36px;
        font-size: 1rem;
      }
      
      .threshold-title,
      .classification-title {
        font-size: 0.85rem;
      }
    }
  </style>
  </head>
  <body>
  <!-- Mobile Menu Toggle -->
  <button class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="Toggle Menu">
    <i class="fa-solid fa-bars"></i>
  </button>
  
  <!-- Sidebar Overlay -->
  <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
  
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
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
        <a href="{{ route('dashboard.tools.monitoring') }}">Monitoring Alat</a>
        <a href="{{ route('dashboard.tools.information') }}" class="active">Manajemen Informasi</a>
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
    <div class="page-header">
      <h1>
        <i class="fa-solid fa-info-circle me-2"></i>
        Manajemen Informasi
      </h1>
    </div>

    <!-- Threshold Information -->
    <div class="chart-card">
      <h6>
        <i class="fa-solid fa-sliders me-2"></i>
        Threshold Parameter Sensor
      </h6>
      <div class="threshold-grid">
        <div class="threshold-card">
          <div class="threshold-header">
            <div class="threshold-icon">
              <i class="fa-solid fa-temperature-half"></i>
            </div>
            <div>
              <div class="threshold-title">Suhu (Temperature)</div>
              <div class="threshold-subtitle">Parameter Suhu Kandang</div>
            </div>
          </div>
          <div class="threshold-ranges">
            <div class="range-item">
              <span class="range-badge ideal">Ideal</span>
              <span class="range-value">23-34°C</span>
            </div>
            <div class="range-item">
              <span class="range-badge warning">Warning</span>
              <span class="range-value">20-23°C atau 34-37°C</span>
            </div>
            <div class="range-item">
              <span class="range-badge danger">Danger</span>
              <span class="range-value">&lt;20°C atau &gt;37°C</span>
            </div>
          </div>
        </div>
        
        <div class="threshold-card">
          <div class="threshold-header">
            <div class="threshold-icon">
              <i class="fa-solid fa-droplet"></i>
            </div>
            <div>
              <div class="threshold-title">Kelembaban (Humidity)</div>
              <div class="threshold-subtitle">Parameter Kelembaban Udara</div>
            </div>
          </div>
          <div class="threshold-ranges">
            <div class="range-item">
              <span class="range-badge ideal">Ideal</span>
              <span class="range-value">50-70%</span>
            </div>
            <div class="range-item">
              <span class="range-badge warning">Warning</span>
              <span class="range-value">&lt;50% atau &gt;70%</span>
            </div>
            <div class="range-item">
              <span class="range-badge danger">Danger</span>
              <span class="range-value">&gt;80%</span>
            </div>
          </div>
        </div>
        
        <div class="threshold-card">
          <div class="threshold-header">
            <div class="threshold-icon">
              <i class="fa-solid fa-wind"></i>
            </div>
            <div>
              <div class="threshold-title">Amoniak (Ammonia)</div>
              <div class="threshold-subtitle">Parameter Kadar Amoniak</div>
            </div>
          </div>
          <div class="threshold-ranges">
            <div class="range-item">
              <span class="range-badge ideal">Ideal</span>
              <span class="range-value">≤20 ppm</span>
            </div>
            <div class="range-item">
              <span class="range-badge warning">Warning</span>
              <span class="range-value">&gt;35 ppm</span>
            </div>
            <div class="range-item">
              <span class="range-badge danger">Danger</span>
              <span class="range-value">&gt;35 ppm</span>
            </div>
          </div>
        </div>
        
        <div class="threshold-card">
          <div class="threshold-header">
            <div class="threshold-icon">
              <i class="fa-solid fa-sun"></i>
            </div>
            <div>
              <div class="threshold-title">Cahaya (Light)</div>
              <div class="threshold-subtitle">Parameter Intensitas Cahaya</div>
            </div>
          </div>
          <div class="threshold-ranges">
            <div class="range-item">
              <span class="range-badge ideal">Ideal</span>
              <span class="range-value">20-40 lux</span>
            </div>
            <div class="range-item">
              <span class="range-badge warning">Warning</span>
              <span class="range-value">10-20 lux atau 40-60 lux</span>
            </div>
            <div class="range-item">
              <span class="range-badge danger">Danger</span>
              <span class="range-value">&lt;10 lux atau &gt;60 lux</span>
            </div>
          </div>
        </div>
      </div>
      <div style="margin-top:1rem; padding:.75rem; background:#fff3cd; border-radius:8px; font-size:.75rem; color:#856404; display:flex; align-items:center; gap:.5rem;">
        <i class="fa-solid fa-lock"></i>
        <span>Threshold ini tidak dapat diubah karena sudah diatur sesuai integrasi Machine Learning.</span>
      </div>
    </div>
    
    <!-- Classification Information -->
    <div class="chart-card">
      <h6>
        <i class="fa-solid fa-chart-line me-2"></i>
        Klasifikasi Status Kandang
      </h6>
      <div class="classification-grid">
        <div class="classification-card baik">
          <div class="classification-header">
            <div class="classification-icon">
              <i class="fa-solid fa-check-circle"></i>
            </div>
            <div>
              <div class="classification-title">BAIK</div>
            </div>
          </div>
          <p class="classification-description">
            Semua parameter sensor dalam batas aman sesuai threshold yang ditetapkan. Kondisi kandang optimal untuk pertumbuhan ayam.
          </p>
        </div>
        
        <div class="classification-card perhatian">
          <div class="classification-header">
            <div class="classification-icon">
              <i class="fa-solid fa-exclamation-triangle"></i>
            </div>
            <div>
              <div class="classification-title">PERHATIAN</div>
            </div>
          </div>
          <p class="classification-description">
            Beberapa parameter sensor perlu ditinjau karena mendekati atau melewati batas warning. Lakukan pengecekan dan penyesuaian.
          </p>
        </div>
        
        <div class="classification-card buruk">
          <div class="classification-header">
            <div class="classification-icon">
              <i class="fa-solid fa-times-circle"></i>
            </div>
            <div>
              <div class="classification-title">BURUK</div>
            </div>
          </div>
          <p class="classification-description">
            Banyak parameter sensor bermasalah, memerlukan pemeriksaan segera. Kondisi kandang tidak optimal dan dapat mempengaruhi kesehatan ayam.
          </p>
        </div>
      </div>
      <div style="margin-top:1rem; padding:.75rem; background:#fff3cd; border-radius:8px; font-size:.75rem; color:#856404; display:flex; align-items:center; gap:.5rem;">
        <i class="fa-solid fa-lock"></i>
        <span>Klasifikasi ini tidak dapat diubah karena sudah diatur sesuai model Machine Learning (Random Forest).</span>
      </div>
    </div>

    <!-- Informasi Model Machine Learning -->
    <div class="chart-card">
      <h6>
        <i class="fa-solid fa-brain me-2"></i>
        Informasi Model Machine Learning
      </h6>
      <div id="mlInfoContent" class="ml-info-grid">
        <div class="ml-info-card">
          <div class="ml-info-header">
            <div class="ml-info-icon">
              <i class="fa-solid fa-plug"></i>
            </div>
            <div>
              <div class="ml-info-title">Status Koneksi</div>
              <div class="ml-info-value" id="mlConnectionStatus">Memuat...</div>
            </div>
          </div>
        </div>
        <div class="ml-info-card">
          <div class="ml-info-header">
            <div class="ml-info-icon">
              <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
              <div class="ml-info-title">LSTM</div>
              <div class="ml-info-value">Prediksi Tren Sensor</div>
            </div>
          </div>
        </div>
        <div class="ml-info-card">
          <div class="ml-info-header">
            <div class="ml-info-icon">
              <i class="fa-solid fa-tree"></i>
            </div>
            <div>
              <div class="ml-info-title">Random Forest</div>
              <div class="ml-info-value">Klasifikasi Status</div>
            </div>
          </div>
        </div>
        <div class="ml-info-card">
          <div class="ml-info-header">
            <div class="ml-info-icon">
              <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
              <div class="ml-info-title">Isolation Forest</div>
              <div class="ml-info-value">Deteksi Anomali</div>
            </div>
          </div>
        </div>
        <div class="ml-info-card">
          <div class="ml-info-header">
            <div class="ml-info-icon">
              <i class="fa-solid fa-bullseye"></i>
            </div>
            <div>
              <div class="ml-info-title">Akurasi Model</div>
              <div class="ml-info-value" id="mlAccuracy">-</div>
            </div>
          </div>
        </div>
        <div class="ml-info-card">
          <div class="ml-info-header">
            <div class="ml-info-icon">
              <i class="fa-solid fa-clock"></i>
            </div>
            <div>
              <div class="ml-info-title">Waktu Prediksi</div>
              <div class="ml-info-value" id="mlPredictionTime">-</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pengaturan Telegram -->
    <div class="chart-card">
      <h6>
        <i class="fa-brands fa-telegram me-2"></i>
        Pengaturan Telegram
      </h6>
      <form id="telegramSettingsForm" style="margin-top:.75rem;">
        <div style="display:grid; gap:.75rem;">
          <div>
            <label style="display:block; font-size:.75rem; font-weight:600; color:#6c757d; margin-bottom:.25rem;">
              Token Bot
            </label>
            <input type="text" id="telegramBotToken" name="bot_token" 
                   placeholder="Masukkan token bot Telegram" 
                   style="width:100%; padding:.5rem; border:1px solid #ddd; border-radius:6px; font-size:.8rem;"
                   value="{{ old('bot_token', env('TELEGRAM_BOT_TOKEN', '')) }}">
            <small style="font-size:.7rem; color:#6c757d; display:block; margin-top:.25rem;">
              Dapatkan token dari <a href="https://t.me/BotFather" target="_blank" style="color:#007bff;">@BotFather</a>
            </small>
          </div>
          
          <div>
            <label style="display:block; font-size:.75rem; font-weight:600; color:#6c757d; margin-bottom:.25rem;">
              Chat ID Pengguna
            </label>
            <input type="text" id="telegramChatId" name="chat_id" 
                   placeholder="Masukkan Chat ID pengguna" 
                   style="width:100%; padding:.5rem; border:1px solid #ddd; border-radius:6px; font-size:.8rem;"
                   value="{{ old('chat_id', env('TELEGRAM_CHAT_ID', '')) }}">
            <small style="font-size:.7rem; color:#6c757d; display:block; margin-top:.25rem;">
              Dapatkan Chat ID dari <a href="https://t.me/userinfobot" target="_blank" style="color:#007bff;">@userinfobot</a>
            </small>
          </div>
          
          <div>
            <label style="display:block; font-size:.75rem; font-weight:600; color:#6c757d; margin-bottom:.25rem;">
              Status Koneksi
            </label>
            <div id="telegramStatus" style="padding:.5rem; border-radius:6px; font-size:.8rem; font-weight:600;">
              <span id="telegramStatusText">Memeriksa...</span>
            </div>
          </div>
          
          <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <button type="button" id="testTelegramBtn" 
                    style="padding:.5rem 1rem; background:#17a2b8; color:white; border:none; border-radius:6px; font-size:.8rem; cursor:pointer; font-weight:600;">
              <i class="fa-solid fa-paper-plane me-1"></i>
              Tes Kirim Pesan
            </button>
            <button type="submit" id="saveTelegramBtn"
                    style="padding:.5rem 1rem; background:#28a745; color:white; border:none; border-radius:6px; font-size:.8rem; cursor:pointer; font-weight:600;">
              <i class="fa-solid fa-save me-1"></i>
              Simpan
            </button>
          </div>
        </div>
      </form>
    </div>
  </main>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  
  <script>
    // Telegram Settings Functions
    async function checkTelegramStatus(){
      const statusEl = document.getElementById('telegramStatus');
      const statusText = document.getElementById('telegramStatusText');
      const botToken = document.getElementById('telegramBotToken').value;
      const chatId = document.getElementById('telegramChatId').value;
      
      if (!botToken || !chatId) {
        statusEl.style.background = '#fff3cd';
        statusEl.style.color = '#856404';
        statusText.innerHTML = '<i class="fa-solid fa-times-circle"></i> Belum terhubung ❌';
        return false;
      }
      
      try {
        const response = await fetch('/api/telegram/test', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ bot_token: botToken, chat_id: chatId })
        });
        
        const data = await response.json();
        
        if (data.success) {
          statusEl.style.background = '#d4edda';
          statusEl.style.color = '#155724';
          statusText.innerHTML = '<i class="fa-solid fa-check-circle"></i> Terhubung ✔️';
          return true;
        } else {
          statusEl.style.background = '#f8d7da';
          statusEl.style.color = '#721c24';
          statusText.innerHTML = '<i class="fa-solid fa-times-circle"></i> Belum terhubung ❌';
          return false;
        }
      } catch (error) {
        statusEl.style.background = '#f8d7da';
        statusEl.style.color = '#721c24';
        statusText.innerHTML = '<i class="fa-solid fa-times-circle"></i> Belum terhubung ❌';
        return false;
      }
    }
    
    async function testTelegramMessage(){
      const botToken = document.getElementById('telegramBotToken').value;
      const chatId = document.getElementById('telegramChatId').value;
      
      if (!botToken || !chatId) {
        Swal.fire({
          icon: 'warning',
          title: 'Data Belum Lengkap',
          text: 'Silakan isi Token Bot dan Chat ID terlebih dahulu.',
          confirmButtonColor: '#ffc107'
        });
        return;
      }
      
      const btn = document.getElementById('testTelegramBtn');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Mengirim...';
      
      try {
        const response = await fetch('/api/telegram/send-test', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ 
            bot_token: botToken, 
            chat_id: chatId,
            message: '✅ Test pesan dari ChickPatrol Monitoring System. Telegram bot berfungsi dengan baik!'
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Pesan Terkirim!',
            text: 'Pesan test berhasil dikirim ke Telegram Anda.',
            confirmButtonColor: '#28a745'
          });
          checkTelegramStatus();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal Mengirim',
            text: data.message || 'Terjadi kesalahan saat mengirim pesan.',
            confirmButtonColor: '#dc3545'
          });
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Terjadi kesalahan: ' + error.message,
          confirmButtonColor: '#dc3545'
        });
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }
    
    async function saveTelegramSettings(e){
      e.preventDefault();
      
      const botToken = document.getElementById('telegramBotToken').value;
      const chatId = document.getElementById('telegramChatId').value;
      
      if (!botToken || !chatId) {
        Swal.fire({
          icon: 'warning',
          title: 'Data Belum Lengkap',
          text: 'Silakan isi Token Bot dan Chat ID terlebih dahulu.',
          confirmButtonColor: '#ffc107'
        });
        return;
      }
      
      const btn = document.getElementById('saveTelegramBtn');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Menyimpan...';
      
      try {
        const response = await fetch('/api/telegram/save', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ bot_token: botToken, chat_id: chatId })
        });
        
        const data = await response.json();
        
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil Disimpan!',
            text: 'Pengaturan Telegram berhasil disimpan.',
            confirmButtonColor: '#28a745'
          });
          checkTelegramStatus();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan',
            text: data.message || 'Terjadi kesalahan saat menyimpan pengaturan.',
            confirmButtonColor: '#dc3545'
          });
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Terjadi kesalahan: ' + error.message,
          confirmButtonColor: '#dc3545'
        });
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }
    
    // Toggle Sidebar (Mobile)
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.querySelector('.sidebar-overlay');
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    }
    
    // Toggle Submenu
    // Toggle Sidebar (Mobile)
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.querySelector('.sidebar-overlay');
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    }
    
    // Toggle Submenu
    function toggleSubmenu() {
      const submenu = document.querySelector('.sidebar-submenu');
      const chevron = document.querySelector('.chevron-icon');
      submenu.classList.toggle('show');
      chevron.classList.toggle('rotate');
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
      const sidebar = document.getElementById('sidebar');
      const toggle = document.querySelector('.mobile-menu-toggle');
      const overlay = document.querySelector('.sidebar-overlay');
      
      if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('show')) {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        }
      }
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
      const sidebar = document.getElementById('sidebar');
      const toggle = document.querySelector('.mobile-menu-toggle');
      const overlay = document.querySelector('.sidebar-overlay');
      
      if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('show')) {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        }
      }
    });
    
    // Load ML Information
    async function loadMLInfo(){
      try {
        const res = await fetch('/api/monitoring/tools?t=' + Date.now(), { headers:{ 'Accept':'application/json' } });
        if (!res.ok) {
          console.error('Failed to load ML info:', res.status);
          return;
        }
        const data = await res.json();
        const { ml_metadata, meta } = data;
        
        // Use meta data if ml_metadata is empty
        const mlData = ml_metadata && Object.keys(ml_metadata).length > 0 ? ml_metadata : {
          model_name: meta?.ml_model_name || null,
          model_version: meta?.ml_model_version || null,
          accuracy: meta?.ml_accuracy || null,
          confidence: meta?.ml_confidence || null,
          prediction_time: meta?.ml_prediction_time || null,
          source: meta?.ml_source || 'fallback'
        };
        
        const source = meta?.ml_source || mlData.source || 'fallback';
        const connected = meta?.ml_connected || false;
        
        // Status Connection
        const statusEl = document.getElementById('mlConnectionStatus');
        if (statusEl) {
          if (connected && source === 'ml_service') {
            statusEl.innerHTML = '<span class="ml-status-badge connected"><i class="fa-solid fa-check-circle"></i> Terhubung ke ML Service</span>';
          } else {
            statusEl.innerHTML = `<span class="ml-status-badge ${source === 'fallback' ? 'fallback' : 'disconnected'}"><i class="fa-solid ${source === 'fallback' ? 'fa-exclamation-triangle' : 'fa-times-circle'}"></i> ${source === 'fallback' ? 'Menggunakan Prediksi Sederhana' : 'ML Service Tidak Tersedia'}</span>`;
          }
        }
        
        // Accuracy
        const accuracyEl = document.getElementById('mlAccuracy');
        if (accuracyEl) {
          const accuracy = mlData.accuracy !== null && mlData.accuracy !== undefined ? mlData.accuracy : meta?.ml_accuracy;
          if (accuracy !== null && accuracy !== undefined) {
            // If accuracy is already a percentage (0-100), use as is, otherwise multiply by 100
            const accuracyValue = accuracy > 1 ? accuracy : (accuracy * 100);
            accuracyEl.textContent = accuracyValue.toFixed(2) + '%';
          } else {
            accuracyEl.textContent = '-';
          }
        }
        
        // Prediction Time
        const predictionTimeEl = document.getElementById('mlPredictionTime');
        if (predictionTimeEl) {
          const predictionTime = mlData.prediction_time || meta?.ml_prediction_time;
          predictionTimeEl.textContent = predictionTime ? predictionTime + 'ms' : '-';
        }
        
        console.log('ML Info loaded:', { mlData, meta, source, connected });
      } catch (error) {
        console.error('Error loading ML info:', error);
        // Set default values on error
        const statusEl = document.getElementById('mlConnectionStatus');
        if (statusEl) {
          statusEl.innerHTML = '<span class="ml-status-badge disconnected"><i class="fa-solid fa-times-circle"></i> Error memuat data</span>';
        }
      }
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function(){
      // Load ML Information
      loadMLInfo();
      
      // Telegram settings
      const telegramForm = document.getElementById('telegramSettingsForm');
      const testBtn = document.getElementById('testTelegramBtn');
      const botTokenInput = document.getElementById('telegramBotToken');
      const chatIdInput = document.getElementById('telegramChatId');
      
      if (telegramForm) {
        telegramForm.addEventListener('submit', saveTelegramSettings);
      }
      
      if (testBtn) {
        testBtn.addEventListener('click', testTelegramMessage);
      }
      
      // Check status on input change
      if (botTokenInput && chatIdInput) {
        botTokenInput.addEventListener('blur', checkTelegramStatus);
        chatIdInput.addEventListener('blur', checkTelegramStatus);
      }
      
      // Initial status check
      setTimeout(checkTelegramStatus, 500);
    });
  </script>
</body>
</html>

