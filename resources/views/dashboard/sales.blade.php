<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Penjualan - ChickPatrol Seller</title>
  
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
    
    .order-item {
      padding: 1.5rem;
      border-bottom: 1px solid #f8f9fa;
    }
    
    .order-item:last-child {
      border-bottom: none;
    }
    
    .order-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #f8f9fa;
    }
    
    .order-header-left {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .order-header-left input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    
    .order-buyer {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: #2F2F2F;
    }
    
    .order-buyer i {
      font-size: 0.875rem;
    }
    
    .order-date {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .order-date i {
      font-size: 0.875rem;
    }
    
    .order-header-right {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .order-status {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .order-response-time {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .order-body {
      display: flex;
      gap: 1.5rem;
    }
    
    .order-product {
      flex: 1;
      display: flex;
      gap: 1rem;
    }
    
    .order-product-img {
      width: 80px;
      height: 80px;
      border-radius: 8px;
      background: #f8f9fa;
      flex-shrink: 0;
    }
    
    .order-product-info {
      flex: 1;
    }
    
    .order-product-name {
      font-size: 0.875rem;
      font-weight: 500;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .order-product-qty {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
    }
    
    .order-product-note {
      font-size: 0.75rem;
      color: #6c757d;
      font-style: italic;
    }
    
    .order-address {
      flex: 1;
    }
    
    .order-address-title {
      font-size: 0.75rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
    }
    
    .order-address-name {
      font-size: 0.75rem;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .order-address-text {
      font-size: 0.75rem;
      color: #6c757d;
      line-height: 1.5;
    }
    
    .order-courier {
      flex: 0 0 200px;
    }
    
    .order-courier-title {
      font-size: 0.75rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
    }
    
    .order-courier-info {
      font-size: 0.75rem;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .order-courier-link {
      font-size: 0.75rem;
      color: #69B578;
      text-decoration: none;
      display: inline-block;
      margin-top: 0.25rem;
    }
    
    .order-courier-link:hover {
      text-decoration: underline;
    }
    
    .order-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid #f8f9fa;
    }
    
    .order-footer-left {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .order-footer-left input[type="checkbox"] {
      width: 16px;
      height: 16px;
      cursor: pointer;
    }
    
    .order-footer-left label {
      font-size: 0.75rem;
      color: #6c757d;
      margin: 0;
      cursor: pointer;
    }
    
    .order-footer-right {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .order-total {
      font-size: 0.875rem;
      color: #2F2F2F;
    }
    
    .order-total strong {
      font-weight: 600;
    }
    
    .btn-accept {
      background: #69B578;
      color: white;
      border: none;
      padding: 0.6rem 1.75rem;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .btn-accept:hover {
      background: #5a9d66;
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
      <div class="sidebar-menu-item" onclick="toggleSubmenu()" style="cursor: pointer;">
        <i class="fa-solid fa-wrench"></i>
        <span>Alat</span>
        <i class="fa-solid fa-chevron-down chevron-icon"></i>
      </div>
      <div class="sidebar-submenu">
        <a href="{{ route('dashboard.tools') }}">Daftar alat</a>
        <a href="{{ route('dashboard.tools.monitoring') }}">Monitoring Alat</a>
      </div>
      <a href="{{ route('dashboard.sales') }}" class="sidebar-menu-item active">
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
    <div class="page-header">
      <h1>Penjualan</h1>
      <div class="search-box">
        <i class="fa-solid fa-search"></i>
        <input type="text" placeholder="Cari Produk">
      </div>
    </div>
    
    <!-- Content Card -->
    <div class="content-card">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-tabs">
          <button class="filter-tab active" data-filter="all">Semua Pesanan</button>
          <button class="filter-tab" data-filter="active">Aktif</button>
          <button class="filter-tab" data-filter="inactive">Tidak Aktif</button>
          <button class="filter-tab" data-filter="selesai">Pesanan Selesai</button>
          <button class="filter-tab" data-filter="dibatalkan">Dibatalkan</button>
        </div>
      </div>
      
      <!-- Order Items -->
      <div class="order-list">
        <!-- Order Item 1 -->
        <div class="order-item">
          <div class="order-header">
            <div class="order-header-left">
              <input type="checkbox">
              <div class="order-buyer">
                <i class="fa-solid fa-user"></i>
                <span>Ratna Sulawasti</span>
              </div>
              <div class="order-date">
                <i class="fa-regular fa-clock"></i>
                <span>18 Oktober 2015 12:00 WIB</span>
              </div>
            </div>
            <div class="order-header-right">
              <span class="order-status">Respon sebelum</span>
              <span class="order-response-time">18 Okt 2015 12:00</span>
            </div>
          </div>
          
          <div class="order-body">
            <div class="order-product">
              <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Crect width='80' height='80' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='30'%3E🍗%3C/text%3E%3C/svg%3E" alt="Product" class="order-product-img">
              <div class="order-product-info">
                <div class="order-product-name">Daging segar</div>
                <div class="order-product-qty">10 x Rp 40.000</div>
                <div class="order-product-note">"Jangan langsung di bumbui di rmh msh"</div>
              </div>
            </div>
            
            <div class="order-address">
              <div class="order-address-title">Alamat</div>
              <div class="order-address-name">Sulawasti (0812345678)</div>
              <div class="order-address-text">Jl. Melon Raya No. 27, Kel. Sukamaju, Kec. Cendana, Kota Nirwana, Jawa Barat 41234</div>
            </div>
            
            <div class="order-courier">
              <div class="order-courier-title">Kurir</div>
              <div class="order-courier-info">Reguler - JNE</div>
              <a href="#" class="order-courier-link">lihat detail</a>
            </div>
          </div>
          
          <div class="order-footer">
            <div class="order-footer-left">
              <input type="checkbox" id="chat-pembeli-1">
              <label for="chat-pembeli-1">Chat Pembeli</label>
            </div>
            <div class="order-footer-right">
              <div class="order-total">
                Total Harga <strong>(10 Barang)</strong>
                <span style="margin-left: 2rem; font-weight: 600;">Rp 400.000,00</span>
              </div>
              <button class="btn-accept">Terima Pesanan</button>
            </div>
          </div>
        </div>
        
        <!-- Order Item 2 -->
        <div class="order-item">
          <div class="order-header">
            <div class="order-header-left">
              <input type="checkbox">
              <div class="order-buyer">
                <i class="fa-solid fa-user"></i>
                <span>Ratna Sulawasti</span>
              </div>
              <div class="order-date">
                <i class="fa-regular fa-clock"></i>
                <span>18 Oktober 2015 12:00 WIB</span>
              </div>
            </div>
            <div class="order-header-right">
              <span class="order-status">Respon sebelum</span>
              <span class="order-response-time">18 Okt 2015 12:00</span>
            </div>
          </div>
          
          <div class="order-body">
            <div class="order-product">
              <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Crect width='80' height='80' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='30'%3E🍗%3C/text%3E%3C/svg%3E" alt="Product" class="order-product-img">
              <div class="order-product-info">
                <div class="order-product-name">Daging segar</div>
                <div class="order-product-qty">10 x Rp 40.000</div>
                <div class="order-product-note">"Jangan langsung di bumbui di rmh msh"</div>
              </div>
            </div>
            
            <div class="order-address">
              <div class="order-address-title">Alamat</div>
              <div class="order-address-name">Sulawasti (0812345678)</div>
              <div class="order-address-text">Jl. Melon Raya No. 27, Kel. Sukamaju, Kec. Cendana, Kota Nirwana, Jawa Barat 41234</div>
            </div>
            
            <div class="order-courier">
              <div class="order-courier-title">Kurir</div>
              <div class="order-courier-info">Reguler - JNE</div>
              <a href="#" class="order-courier-link">lihat detail</a>
            </div>
          </div>
          
          <div class="order-footer">
            <div class="order-footer-left">
              <input type="checkbox" id="chat-pembeli-2">
              <label for="chat-pembeli-2">Chat Pembeli</label>
            </div>
            <div class="order-footer-right">
              <div class="order-total">
                Total Harga <strong>(10 Barang)</strong>
                <span style="margin-left: 2rem; font-weight: 600;">Rp 400.000,00</span>
              </div>
              <button class="btn-accept">Terima Pesanan</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  
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
    
    // Accept Order
    document.querySelectorAll('.btn-accept').forEach(btn => {
        btn.addEventListener('click', function() {
            showSuccess('Pesanan berhasil diterima!');
        });
    });
    
    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
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
  </script>
</body>
</html>
