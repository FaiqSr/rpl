<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Daftar Produk - ChickPatrol Seller</title>
  
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
    }
    
    .sidebar-menu-item:hover,
    .sidebar-menu-item.active {
      background: #f8f9fa;
      color: #2F2F2F;
    }
    
    .sidebar-menu-item.active {
      border-left: 3px solid #69B578;
      padding-left: calc(1rem - 3px);
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
      padding: 0.5rem 1.25rem;
      border-radius: 8px;
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
      border-radius: 10px;
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
    }
    
    .product-weight {
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
    }
    
    .status-badge.active {
      background: #f0f0f0;
      color: #6c757d;
    }
    
    .status-badge.inactive {
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
      <a href="{{ route('dashboard.products') }}" class="sidebar-menu-item active">
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
    <div class="page-header">
      <h1>Daftar Produk</h1>
      <button class="btn-add" onclick="addProduct()">
        <i class="fa-solid fa-plus"></i>
        Tambah Produk
      </button>
    </div>
    
    <!-- Content Card -->
    <div class="content-card">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-tabs">
          <button class="filter-tab active" data-filter="all">Semua Produk (4)</button>
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
            <th>Info Produk</th>
            <th>Statistik</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <!-- Product Row 1 -->
          <tr>
            <td class="checkbox-cell">
              <input type="checkbox">
            </td>
            <td>
              <div class="product-info">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='20'%3E%F0%9F%8D%97%3C/text%3E%3C/svg%3E" alt="Product" class="product-img">
                <div>
                  <div class="product-name">Daging segar</div>
                  <div class="product-weight">kg</div>
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
            <td>Rp 40.000</td>
            <td>15</td>
            <td>
              <span class="status-badge active">Aktif</span>
            </td>
          </tr>
          
          <!-- Product Row 2 -->
          <tr>
            <td class="checkbox-cell">
              <input type="checkbox">
            </td>
            <td>
              <div class="product-info">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='20'%3E%F0%9F%8D%97%3C/text%3E%3C/svg%3E" alt="Product" class="product-img">
                <div>
                  <div class="product-name">Daging segar</div>
                  <div class="product-weight">kg</div>
                </div>
              </div>
            </td>
            <td>
              <div class="rating-stars">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
                <i class="fa-regular fa-star"></i>
              </div>
            </td>
            <td>Rp 40.000</td>
            <td>15</td>
            <td>
              <span class="status-badge inactive">Tidak Aktif</span>
            </td>
          </tr>
        </tbody>
      </table>
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
    
    // Add Product Function
    function addProduct() {
        showSuccess('Fitur tambah produk akan segera tersedia!');
    }
    
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
            
            // Filter products
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
  </script>
</body>
</html>
