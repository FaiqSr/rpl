<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Daftar Alat - ChickPatrol Seller</title>
  
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
      color: #22C55E;
    }
    
    .sidebar-menu-item.active {
      color: #22C55E;
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
      color: #22C55E;
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
      overflow: visible;
    }
    
    .content-card .product-table {
      overflow: visible;
    }
    
    .content-card .product-table tbody {
      overflow: visible;
    }
    
    .content-card .product-table tbody tr {
      overflow: visible;
    }
    
    .content-card .product-table tbody td {
      overflow: visible;
      position: relative;
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
      position: relative;
      overflow: visible;
    }
    
    .product-table thead {
      background: white;
      border-bottom: 1px solid #e9ecef;
      position: relative;
      z-index: 1;
    }
    
    .product-table tbody {
      position: relative;
      overflow: visible;
    }
    
    .product-table th {
      padding: 1rem 1.5rem;
      font-size: 0.8rem;
      font-weight: 500;
      color: #6c757d;
      text-align: left;
      vertical-align: top;
    }
    
    .product-table td {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #f8f9fa;
      font-size: 0.875rem;
      color: #2F2F2F;
      vertical-align: top;
    }
    
    .product-info {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
      padding: 0;
      margin: 0;
    }
    
    .product-img {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      object-fit: cover;
      background: #f8f9fa;
      flex-shrink: 0;
      margin: 0;
      padding: 0;
    }
    
    .product-info > div {
      flex: 1;
      min-width: 0;
      padding: 0;
      margin: 0;
    }
    
    .product-name {
      font-weight: 600;
      color: #2F2F2F;
      font-size: 0.95rem;
      margin: 0 0 0.25rem 0;
      padding: 0;
      line-height: 1.4;
    }
    
    .product-subtitle {
      font-size: 0.8rem;
      color: #6c757d;
      margin: 0 0 0.5rem 0;
      padding: 0;
      line-height: 1.4;
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
    
    
    .product-table tbody tr {
      transition: all 0.2s;
    }
    
    .product-table tbody tr:hover {
      background: #f8f9fa;
    }
    
    /* Ensure consistent alignment */
    .product-table thead th,
    .product-table tbody td {
      padding-top: 1rem;
      padding-bottom: 1rem;
      vertical-align: top;
    }
    
    /* Info Alat column - align content with header */
    .product-table td:first-child {
      padding-top: 1rem;
    }
    
    .product-table td:first-child .product-info {
      margin-top: 0;
      padding-top: 0;
    }
    
    /* Status column styling */
    .product-table td:nth-child(3) {
      min-width: 220px;
    }
    
    .product-table td:nth-child(3) > div {
      padding: 0;
      margin: 0;
    }
    
    /* Action column styling */
    .product-table td:last-child {
      min-width: 200px;
      white-space: nowrap;
    }
    
    .product-table td:last-child .action-buttons {
      padding: 0;
      margin: 0;
    }
    
    /* Button styling improvements */
    .action-buttons .btn-sm {
      font-size: 0.8rem;
      padding: 0.4rem 0.75rem;
      border-radius: 6px;
      transition: all 0.2s;
    }
    
    .action-buttons .btn-sm:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .action-buttons .btn-outline-primary,
    .action-buttons .btn-outline-danger {
      border-width: 1.5px;
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
    
    .empty-state {
      padding: 3rem 1.5rem;
      text-align: center;
    }
    
    .empty-state-icon {
      width: 120px;
      height: 120px;
      margin: 0 auto 1.5rem;
      background: #f8f9fa;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      color: #dee2e6;
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
    
    /* Robot Status Indicator */
    .status-indicator {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
      animation: pulse 2s infinite;
      margin-right: 0.5rem;
    }
    
    .status-indicator.operating {
      background: #22C55E;
      box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    
    .status-indicator.idle {
      background: #FACC15;
      animation: none;
    }
    
    .status-indicator.maintenance {
      background: #FB923C;
      animation: none;
    }
    
    .status-indicator.offline {
      background: #6c757d;
      animation: none;
    }
    
    .status-indicator.charging {
      background: #3B82F6;
      animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
      }
      70% {
        box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
      }
    }
    
    .status-badge.status-operating {
      background: #D1FAE5;
      color: #065F46;
    }
    
    .status-badge.status-idle {
      background: #FEF3C7;
      color: #92400E;
    }
    
    .status-badge.status-maintenance {
      background: #FED7AA;
      color: #9A3412;
    }
    
    .status-badge.status-offline {
      background: #F3F4F6;
      color: #6c757d;
    }
    
    .status-badge.status-charging {
      background: #DBEAFE;
      color: #1E40AF;
    }
    
    .action-buttons {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex-wrap: wrap;
      position: relative;
    }
    
    .action-buttons .btn {
      white-space: nowrap;
    }
    
    .action-buttons .dropdown-toggle {
      font-weight: 500;
    }
    
    /* Fix dropdown menu positioning */
    .action-buttons .dropdown {
      position: relative;
    }
    
    .action-buttons .dropdown-menu {
      position: absolute !important;
      top: 100% !important;
      left: 0 !important;
      right: auto !important;
      transform: none !important;
      margin-top: 0.25rem;
      z-index: 1050 !important;
      min-width: 180px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      border: 1px solid #e9ecef;
      border-radius: 6px;
      background: white;
    }
    
    /* Ensure dropdown is not clipped by table or content-card */
    .product-table tbody td:last-child {
      position: relative;
      overflow: visible;
    }
    
    .product-table tbody td:last-child .action-buttons {
      position: relative;
      overflow: visible;
    }
    
    .product-table tbody td:last-child .dropdown {
      position: relative;
      overflow: visible;
    }
    
    /* Prevent clipping by parent containers */
    .content-card,
    .content-card > * {
      overflow: visible !important;
    }
    
    /* Table wrapper for proper overflow handling */
    .product-table-wrapper {
      overflow: visible;
      position: relative;
    }
    
    .robot-status-info {
      margin-top: 0.5rem;
      padding-top: 0;
    }
    
    .robot-status-info small {
      display: block;
      margin-top: 0.25rem;
      line-height: 1.5;
      font-size: 0.75rem;
    }
    
    .robot-status-info .d-flex {
      margin-bottom: 0.25rem;
    }
    
    .robot-stats-mini {
      font-size: 0.8rem;
      min-width: 120px;
      padding: 0;
      margin: 0;
    }
    
    .robot-stats-mini .mb-2 {
      margin-bottom: 0.75rem !important;
    }
    
    .robot-stats-mini .mb-2:last-child {
      margin-bottom: 0 !important;
    }
    
    .robot-stats-mini small {
      font-size: 0.75rem;
    }
    
    .progress {
      background: #F3F4F6;
      border-radius: 4px;
      height: 6px;
    }
    
    .progress-bar {
      transition: width 0.3s ease;
      border-radius: 4px;
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
      <div class="sidebar-menu-item active" onclick="toggleSubmenu()">
        <i class="fa-solid fa-wrench"></i>
        <span>Alat</span>
        <i class="fa-solid fa-chevron-down chevron-icon rotate"></i>
      </div>
      <div class="sidebar-submenu show">
        <a href="{{ route('dashboard.tools') }}" class="active">Daftar alat</a>
        <a href="{{ route('dashboard.tools.monitoring') }}">Monitoring Alat</a>
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
    <div class="page-header">
      <h1>Daftar Alat</h1>
      <button class="btn-add" onclick="addTool()">
        <i class="fa-solid fa-plus"></i>
        Tambah Alat
      </button>
    </div>
    
    <!-- Content Card -->
    <div class="content-card">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-tabs" style="display: none;">
          <!-- Filter tabs dihapus karena tidak berfungsi -->
        </div>
        <div class="filter-right">
          <div class="search-box">
            <i class="fa-solid fa-search"></i>
            <input type="text" placeholder="Cari Alat" id="searchTool">
          </div>
        </div>
      </div>
      
      <!-- Product Table -->
      <div class="product-table-wrapper">
      <table class="product-table">
        <thead>
          <tr>
            <th>Info Alat</th>
            <th>Statistik</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="toolTableBody">
          <!-- Tools akan di-render via JavaScript dari database -->
          <tr>
            <td colspan="4" class="text-center py-4">
              <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <div class="empty-state-title">Memuat data...</div>
                <div class="empty-state-text">Mohon tunggu</div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>
  </main>
  
  <!-- Add/Edit Tool Modal -->
  <div class="modal fade" id="toolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" id="toolModalTitle">Tambah Alat</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-2">
          <form id="toolForm">
            <input type="hidden" id="toolId">
            
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="toolName" class="form-label">Nama Alat</label>
                <input type="text" class="form-control" id="toolName" required>
              </div>
              <div class="col-md-6">
                <label for="toolModel" class="form-label">Model/Tipe</label>
                <input type="text" class="form-control" id="toolModel" required placeholder="Contoh: ChickPatrol Kamura">
              </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-12">
                <label for="toolLocation" class="form-label">Lokasi <span class="text-muted">(Opsional)</span></label>
                <input type="text" class="form-control" id="toolLocation" placeholder="Contoh: Kandang A">
              </div>
            </div>
            
            <div class="mb-3">
              <label for="toolDescription" class="form-label">Deskripsi <span class="text-muted">(Opsional)</span></label>
              <textarea class="form-control" id="toolDescription" rows="3" placeholder="Deskripsi alat..."></textarea>
            </div>
            
            <div class="mb-3">
              <label for="toolStatus" class="form-label">Status Awal</label>
              <select class="form-select" id="toolStatus" required>
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
              </select>
              <small class="text-muted">Status dapat diubah setelah alat dibuat</small>
            </div>
          </form>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" onclick="saveTool()" style="background: #22C55E; border: none;">Simpan</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/dashboard-alerts.js') }}"></script>
  
  <script>
    // Tool data storage (loaded from database)
    let tools = [];
    let editingToolId = null;
    
    // Load tools from database
    async function loadTools() {
        try {
            const response = await fetch('/api/tools', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    tools = data.tools;
                    renderTools();
                    
                    // Start polling setelah tools loaded
                    if (!robotStatusInterval) {
                        startRobotStatusPolling();
                    }
                }
            }
        } catch (error) {
            console.error('Error loading tools:', error);
        }
    }
    
    // Initialize saat page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTools();
    });
    
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
    
    // Add Tool Function
    function addTool() {
        editingToolId = null;
        document.getElementById('toolModalTitle').textContent = 'Tambah Alat';
        document.getElementById('toolForm').reset();
        document.getElementById('toolId').value = '';
        document.getElementById('toolStatus').value = 'active'; // Default status
        
        const modal = new bootstrap.Modal(document.getElementById('toolModal'));
        modal.show();
    }
    
    // Edit Tool Function (integrated with database)
    function editTool(id) {
        const tool = tools.find(t => t.id === id);
        if (!tool) {
            showError('Alat tidak ditemukan');
            return;
        }
        
        editingToolId = id;
        document.getElementById('toolModalTitle').textContent = 'Edit Alat';
        document.getElementById('toolId').value = tool.id;
        document.getElementById('toolName').value = tool.name;
        document.getElementById('toolModel').value = tool.model;
        document.getElementById('toolLocation').value = tool.location || '';
        document.getElementById('toolDescription').value = tool.description || '';
        document.getElementById('toolStatus').value = tool.status || 'active';
        
        const modal = new bootstrap.Modal(document.getElementById('toolModal'));
        modal.show();
    }
    
    // Save Tool Function (integrated with database)
    async function saveTool() {
        const form = document.getElementById('toolForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const name = document.getElementById('toolName').value.trim();
        const model = document.getElementById('toolModel').value.trim();
        const location = document.getElementById('toolLocation').value.trim();
        const description = document.getElementById('toolDescription').value.trim();
        const status = document.getElementById('toolStatus').value;
        
        // Validasi
        if (!name || !model) {
            showError('Nama dan Model alat wajib diisi');
            return;
        }
        
        try {
            Swal.fire({
                title: editingToolId ? 'Memperbarui...' : 'Menambahkan...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const url = editingToolId ? `/api/tools/${editingToolId}` : '/api/tools';
            const method = editingToolId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                name,
                model,
                    category: 'Robot', // Default category untuk robot
                    location: location || null,
                    description: description || null,
                    status
                })
            });
            
            const data = await response.json();
            
            Swal.close();
            
            if (data.success) {
                showSuccess(data.message);
        bootstrap.Modal.getInstance(document.getElementById('toolModal')).hide();
                // Reload tools from database
                await loadTools();
            } else {
                showError(data.message || 'Gagal menyimpan alat');
            }
        } catch (error) {
            Swal.close();
            showError('Terjadi kesalahan: ' + error.message);
        }
    }
    
    // Delete Tool Function (integrated with database)
    async function deleteTool(id) {
        Swal.fire({
            title: 'Hapus Alat?',
            text: 'Alat yang dihapus tidak dapat dikembalikan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    Swal.fire({
                        title: 'Menghapus...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    const response = await fetch(`/api/tools/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    Swal.close();
                    
                    if (data.success) {
                        showSuccess(data.message);
                        // Reload tools from database
                        await loadTools();
                    } else {
                        showError(data.message || 'Gagal menghapus alat');
                    }
                } catch (error) {
                    Swal.close();
                    showError('Terjadi kesalahan: ' + error.message);
                }
            }
        });
    }
    
    // Render Tools Function (integrated with database and real-time status)
    function renderTools() {
        const tbody = document.getElementById('toolTableBody');
        const searchTerm = document.getElementById('searchTool')?.value.toLowerCase() || '';
        
        // Filter by search term
        let filteredTools = tools;
        if (searchTerm) {
            filteredTools = tools.filter(t => 
                t.name.toLowerCase().includes(searchTerm) ||
                t.model.toLowerCase().includes(searchTerm) ||
                (t.location && t.location.toLowerCase().includes(searchTerm))
            );
        }
        
        if (filteredTools.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <div class="empty-state-title">Tidak ada alat</div>
                            <div class="empty-state-text">Klik "Tambah Alat" untuk menambahkan alat baru</div>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = filteredTools.map(tool => {
            const robotId = tool.robot_id || 'CHICKPATROL-001'; // Fallback untuk kompatibilitas
            
            return `
                <tr data-tool-id="${tool.id}" data-robot-id="${robotId}" data-status="${tool.status}">
                    <td>
                        <div class="product-info">
                            <img src="${tool.image || "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23ffeaa7'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23fdcb6e' font-size='20'%3E🐔%3C/text%3E%3C/svg%3E"}" alt="Tool" class="product-img">
                            <div>
                                <div class="product-name">${tool.name}</div>
                                <div class="product-subtitle">${tool.model}</div>
                                <!-- Robot Status Info (Real-time) -->
                                <div class="robot-status-info">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="status-indicator" id="status-indicator-${robotId}"></span>
                                        <small class="text-muted" id="status-text-${robotId}">Memuat...</small>
                                    </div>
                                    <small class="text-muted" id="last-activity-${robotId}">Memuat...</small>
                                    <small class="text-muted" id="current-position-${robotId}" style="display: none;"></small>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <!-- Robot Stats Mini (Real-time) -->
                        <div class="robot-stats-mini">
                            <!-- Battery Level -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Battery</small>
                                    <small class="text-muted" id="battery-level-${robotId}">-</small>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" id="battery-bar-${robotId}" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            
                        </div>
                    </td>
                    <td>
                        <!-- Status Badge dengan Health -->
                        <div>
                            <span class="status-badge" id="status-badge-${robotId}">Memuat...</span>
                            <div class="mt-1">
                                <small class="text-muted" id="health-summary-${robotId}">-</small>
                            </div>
                            <!-- Maintenance Info -->
                            <div class="maintenance-info mt-2" id="maintenance-info-${robotId}">
                                <div class="maintenance-alert" id="maintenance-alert-${robotId}" style="display: none;">
                                    <small class="text-danger">
                                        <i class="fa-solid fa-exclamation-triangle"></i>
                                        <span id="maintenance-text-${robotId}"></span>
                                    </small>
                                </div>
                                <small class="text-muted" style="cursor: pointer;" onclick="showMaintenanceModal('${robotId}')">
                                    <i class="fa-solid fa-wrench"></i> Lihat Maintenance
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <!-- Quick Actions Dropdown -->
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-success dropdown-toggle" type="button" 
                                        id="actionsDropdown-${robotId}" 
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        style="min-width: 90px;">
                                    <i class="fa-solid fa-play"></i> Aksi
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="actionsDropdown-${robotId}">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="startPatrol('${robotId}'); return false;">
                                            <i class="fa-solid fa-play text-success me-2"></i> Mulai Patrol
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="stopPatrol('${robotId}'); return false;">
                                            <i class="fa-solid fa-stop text-warning me-2"></i> Berhenti
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="returnToBase('${robotId}'); return false;">
                                            <i class="fa-solid fa-home text-primary me-2"></i> Kembali ke Base
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="emergencyStop('${robotId}'); return false;">
                                            <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Emergency Stop
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Edit & Delete Buttons -->
                            <button class="btn btn-sm btn-outline-primary ms-1" onclick="editTool(${tool.id})" title="Edit" style="width: 38px; height: 38px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                            <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteTool(${tool.id})" title="Hapus" style="width: 38px; height: 38px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    // Toggle Submenu
    function toggleSubmenu() {
        const submenu = document.querySelector('.sidebar-submenu');
        const chevron = document.querySelector('.chevron-icon');
        submenu.classList.toggle('show');
        chevron.classList.toggle('rotate');
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchTool');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            renderTools();
        });
    }
    
    
    // ========== ROBOT STATUS REAL-TIME POLLING ==========
    
    let robotStatusInterval = null;
    
    // Function untuk update status real-time
    async function updateRobotStatus() {
        try {
            const response = await fetch('/api/robots/status?t=' + Date.now(), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (!response.ok) {
                console.error('Failed to fetch robot status');
                return;
            }
            
            const data = await response.json();
            
            if (data.success && data.robots && data.robots.length > 0) {
                data.robots.forEach(robot => {
                    // Update status indicator
                    const statusIndicator = document.getElementById(`status-indicator-${robot.robot_id}`);
                    const statusText = document.getElementById(`status-text-${robot.robot_id}`);
                    const statusBadge = document.getElementById(`status-badge-${robot.robot_id}`);
                    const lastActivity = document.getElementById(`last-activity-${robot.robot_id}`);
                    const currentPosition = document.getElementById(`current-position-${robot.robot_id}`);
                    const batteryLevel = document.getElementById(`battery-level-${robot.robot_id}`);
                    const batteryBar = document.getElementById(`battery-bar-${robot.robot_id}`);
                    const healthSummary = document.getElementById(`health-summary-${robot.robot_id}`);
                    
                    if (statusIndicator) {
                        statusIndicator.className = `status-indicator ${robot.operational_status}`;
                    }
                    if (statusText) {
                        statusText.textContent = robot.status_text;
                    }
                    if (statusBadge) {
                        statusBadge.textContent = robot.status_text;
                        statusBadge.className = `status-badge ${robot.status_badge_class}`;
                    }
                    if (lastActivity) {
                        lastActivity.textContent = robot.last_activity_text;
                    }
                    if (currentPosition && robot.current_position) {
                        currentPosition.textContent = `📍 ${robot.current_position}`;
                        currentPosition.style.display = 'block';
                    }
                    if (batteryLevel) {
                        batteryLevel.textContent = `${robot.battery_level}%`;
                    }
                    if (batteryBar) {
                        batteryBar.style.width = `${robot.battery_level}%`;
                        // Change color based on battery level
                        if (robot.battery_level > 50) {
                            batteryBar.className = 'progress-bar bg-success';
                        } else if (robot.battery_level > 20) {
                            batteryBar.className = 'progress-bar bg-warning';
                        } else {
                            batteryBar.className = 'progress-bar bg-danger';
                        }
                    }
                    if (healthSummary) {
                        healthSummary.textContent = robot.health_summary;
                    }
                    
                    // Load maintenance info untuk setiap robot
                    loadMaintenanceInfo(robot.robot_id);
                });
            }
        } catch (error) {
            console.error('Error updating robot status:', error);
        }
    }
    
    // Start polling setiap 10 detik
    function startRobotStatusPolling() {
        // Initial update
        updateRobotStatus();
        
        // Poll every 10 seconds
        robotStatusInterval = setInterval(updateRobotStatus, 10000);
    }
    
    // Stop polling (optional, untuk cleanup)
    function stopRobotStatusPolling() {
        if (robotStatusInterval) {
            clearInterval(robotStatusInterval);
            robotStatusInterval = null;
        }
    }
    
    // Start polling saat page load
    document.addEventListener('DOMContentLoaded', function() {
        startRobotStatusPolling();
    });
    
    // Stop polling saat page unload
    window.addEventListener('beforeunload', function() {
        stopRobotStatusPolling();
    });
    
    // ========== KONTROL ROBOT (POIN 3) ==========
    
    async function startPatrol(robotId) {
        try {
            Swal.fire({
                title: 'Memulai Patrol...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const response = await fetch(`/api/robots/${robotId}/start-patrol`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            Swal.close();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonColor: '#22C55E'
                });
                
                // Update status immediately
                updateRobotStatus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                    confirmButtonColor: '#EF4444'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan: ' + error.message,
                confirmButtonColor: '#EF4444'
            });
        }
    }
    
    async function stopPatrol(robotId) {
        Swal.fire({
            title: 'Hentikan Patrol?',
            text: 'Robot akan berhenti dan kembali ke mode idle',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FACC15',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hentikan',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    Swal.fire({
                        title: 'Menghentikan...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    const response = await fetch(`/api/robots/${robotId}/stop-patrol`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    Swal.close();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#22C55E'
                        });
                        updateRobotStatus();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            confirmButtonColor: '#EF4444'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan: ' + error.message,
                        confirmButtonColor: '#EF4444'
                    });
                }
            }
        });
    }
    
    async function returnToBase(robotId) {
        Swal.fire({
            title: 'Kembali ke Base?',
            text: 'Robot akan kembali ke base station untuk charging',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3B82F6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Kembali',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/robots/${robotId}/return-to-base`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#22C55E'
                        });
                        updateRobotStatus();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            confirmButtonColor: '#EF4444'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan: ' + error.message,
                        confirmButtonColor: '#EF4444'
                    });
                }
            }
        });
    }
    
    async function emergencyStop(robotId) {
        Swal.fire({
            title: '⚠️ EMERGENCY STOP',
            text: 'Robot akan dihentikan segera. Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hentikan Sekarang!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/robots/${robotId}/emergency-stop`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'EMERGENCY STOP Aktif',
                            text: data.message,
                            confirmButtonColor: '#EF4444'
                        });
                        updateRobotStatus();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message,
                            confirmButtonColor: '#EF4444'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan: ' + error.message,
                        confirmButtonColor: '#EF4444'
                    });
                }
            }
        });
    }
    
    // ========== MAINTENANCE TRACKER (POIN 4) ==========
    
    async function loadMaintenanceInfo(robotId) {
        try {
            const response = await fetch(`/api/robots/${robotId}/maintenance`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const alertDiv = document.getElementById(`maintenance-alert-${robotId}`);
                const textSpan = document.getElementById(`maintenance-text-${robotId}`);
                
                if (data.next_service) {
                    const daysUntil = data.next_service.days_until;
                    const isOverdue = data.next_service.is_overdue;
                    
                    if (isOverdue) {
                        alertDiv.style.display = 'block';
                        textSpan.textContent = `Service terlambat! (${data.next_service.title})`;
                        alertDiv.className = 'maintenance-alert text-danger';
                    } else if (daysUntil !== null && daysUntil <= 7) {
                        alertDiv.style.display = 'block';
                        textSpan.textContent = `Service dalam ${daysUntil} hari (${data.next_service.title})`;
                        alertDiv.className = daysUntil <= 3 ? 
                            'maintenance-alert text-danger' : 
                            'maintenance-alert text-warning';
                    } else {
                        alertDiv.style.display = 'none';
                    }
                } else {
                    alertDiv.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error loading maintenance info:', error);
        }
    }
    
    async function showMaintenanceModal(robotId) {
        try {
            const response = await fetch(`/api/robots/${robotId}/maintenance`);
            const data = await response.json();
            
            if (data.success) {
                let html = '';
                
                // Next Service
                if (data.next_service) {
                    html += `
                        <div class="alert ${data.next_service.is_overdue ? 'alert-danger' : 'alert-warning'}">
                            <h6><i class="fa-solid fa-calendar"></i> Service Berikutnya</h6>
                            <p class="mb-0">
                                <strong>${data.next_service.title}</strong><br>
                                Tanggal: ${data.next_service.date_formatted}<br>
                                ${data.next_service.is_overdue ? 
                                    '<span class="text-danger">TERLAMBAT!</span>' : 
                                    `Dalam ${data.next_service.days_until} hari`
                                }
                            </p>
                        </div>
                    `;
                }
                
                // Upcoming Maintenance
                if (data.upcoming.length > 0) {
                    html += '<h6 class="mt-3">Maintenance Terjadwal</h6>';
                    html += '<ul class="list-group">';
                    data.upcoming.forEach(m => {
                        html += `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>${m.title}</strong><br>
                                        <small class="text-muted">${m.type_text}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge ${m.status_badge_class}">${m.status}</span><br>
                                        <small>${m.scheduled_date_formatted}</small>
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                    html += '</ul>';
                }
                
                // History
                if (data.history.length > 0) {
                    html += '<h6 class="mt-3">Riwayat Maintenance</h6>';
                    html += '<ul class="list-group">';
                    data.history.forEach(m => {
                        html += `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>${m.title}</strong><br>
                                        <small class="text-muted">${m.type_text}</small>
                                    </div>
                                    <div class="text-end">
                                        <small>${m.completed_date}</small><br>
                                        ${m.cost ? `<small class="text-muted">Rp ${parseFloat(m.cost).toLocaleString('id-ID')}</small>` : ''}
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                    html += '</ul>';
                }
                
                if (!html) {
                    html = '<p class="text-muted">Tidak ada data maintenance</p>';
                }
                
                // Create or update modal
                let modalBody = document.getElementById('maintenanceModalBody');
                if (!modalBody) {
                    // Create modal if doesn't exist
                    const modalHtml = `
                        <div class="modal fade" id="maintenanceModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Jadwal Maintenance</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" id="maintenanceModalBody"></div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    modalBody = document.getElementById('maintenanceModalBody');
                }
                
                modalBody.innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('maintenanceModal'));
                modal.show();
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat data maintenance'
            });
        }
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
