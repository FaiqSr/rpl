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
              <input type="checkbox" id="selectAll">
            </th>
            <th>Info Alat</th>
            <th>Statistik</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="toolTableBody">
          <!-- Tool Row 1 -->
          <tr data-tool-id="1" data-status="active">
            <td class="checkbox-cell">
              <input type="checkbox" class="tool-checkbox">
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
              <button class="btn btn-sm btn-outline-primary me-1" onclick="editTool(1)" title="Edit">
                <i class="fa-solid fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteTool(1)" title="Hapus">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
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
              <div class="col-md-6">
                <label for="toolCategory" class="form-label">Kategori</label>
                <select class="form-select" id="toolCategory" required>
                  <option value="">Pilih Kategori</option>
                  <option value="Kandang">Kandang</option>
                  <option value="Sensor">Sensor</option>
                  <option value="Feeder">Feeder (Tempat Pakan)</option>
                  <option value="Drinker">Drinker (Tempat Minum)</option>
                  <option value="Climate Control">Climate Control</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="toolLocation" class="form-label">Lokasi</label>
                <input type="text" class="form-control" id="toolLocation" placeholder="Contoh: Kandang A">
              </div>
            </div>
            
            <div class="mb-3">
              <label for="toolDescription" class="form-label">Deskripsi</label>
              <textarea class="form-control" id="toolDescription" rows="3" placeholder="Deskripsi alat..."></textarea>
            </div>
            
            <div class="mb-3">
              <label for="toolImage" class="form-label">Gambar Alat</label>
              <input type="file" class="form-control" id="toolImage" accept="image/*">
              <div id="toolImagePreview" class="mt-2" style="display: none;">
                <img id="toolPreviewImg" style="max-width: 200px; border-radius: 8px;">
              </div>
            </div>
            
            <div class="mb-3">
              <label for="toolStatus" class="form-label">Status</label>
              <select class="form-select" id="toolStatus" required>
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" onclick="saveTool()" style="background: #69B578; border: none;">Simpan</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  
  <script>
    // Tool data storage (in-memory, not persisted)
    let tools = [
      {
        id: 1,
        name: 'Kandang Ayam',
        model: 'ChickPatrol Kamura',
        category: 'Kandang',
        location: 'Kandang A',
        status: 'active',
        rating: 4,
        description: 'Kandang ayam otomatis dengan sistem monitoring',
        image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23ffeaa7'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23fdcb6e' font-size='20'%3E🐔%3C/text%3E%3C/svg%3E"
      }
    ];
    
    let nextId = 2;
    let editingToolId = null;
    
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
    
    // Add Tool Function
    function addTool() {
        editingToolId = null;
        document.getElementById('toolModalTitle').textContent = 'Tambah Alat';
        document.getElementById('toolForm').reset();
        document.getElementById('toolId').value = '';
        document.getElementById('toolImagePreview').style.display = 'none';
        
        const modal = new bootstrap.Modal(document.getElementById('toolModal'));
        modal.show();
    }
    
    // Edit Tool Function
    function editTool(id) {
        const tool = tools.find(t => t.id === id);
        if (!tool) return;
        
        editingToolId = id;
        document.getElementById('toolModalTitle').textContent = 'Edit Alat';
        document.getElementById('toolId').value = tool.id;
        document.getElementById('toolName').value = tool.name;
        document.getElementById('toolModel').value = tool.model;
        document.getElementById('toolCategory').value = tool.category;
        document.getElementById('toolLocation').value = tool.location || '';
        document.getElementById('toolDescription').value = tool.description || '';
        document.getElementById('toolStatus').value = tool.status;
        
        if (tool.image) {
            document.getElementById('toolImagePreview').style.display = 'block';
            document.getElementById('toolPreviewImg').src = tool.image;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('toolModal'));
        modal.show();
    }
    
    // Save Tool Function
    function saveTool() {
        const form = document.getElementById('toolForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const name = document.getElementById('toolName').value;
        const model = document.getElementById('toolModel').value;
        const category = document.getElementById('toolCategory').value;
        const location = document.getElementById('toolLocation').value;
        const description = document.getElementById('toolDescription').value;
        const status = document.getElementById('toolStatus').value;
        const imageFile = document.getElementById('toolImage').files[0];
        
        if (editingToolId) {
            // Update existing tool
            const toolIndex = tools.findIndex(t => t.id === editingToolId);
            if (toolIndex !== -1) {
                tools[toolIndex] = {
                    ...tools[toolIndex],
                    name,
                    model,
                    category,
                    location,
                    description,
                    status
                };
                
                if (imageFile) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        tools[toolIndex].image = e.target.result;
                        renderTools();
                    };
                    reader.readAsDataURL(imageFile);
                } else {
                    renderTools();
                }
                
                showSuccess('Alat berhasil diperbarui!');
            }
        } else {
            // Add new tool
            const newTool = {
                id: nextId++,
                name,
                model,
                category,
                location,
                description,
                status,
                rating: 0,
                image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23ffeaa7'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23fdcb6e' font-size='20'%3E🐔%3C/text%3E%3C/svg%3E"
            };
            
            if (imageFile) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    newTool.image = e.target.result;
                    tools.push(newTool);
                    renderTools();
                };
                reader.readAsDataURL(imageFile);
            } else {
                tools.push(newTool);
                renderTools();
            }
            
            showSuccess('Alat berhasil ditambahkan!');
        }
        
        bootstrap.Modal.getInstance(document.getElementById('toolModal')).hide();
    }
    
    // Delete Tool Function
    function deleteTool(id) {
        Swal.fire({
            title: 'Hapus Alat?',
            text: 'Alat yang dihapus tidak dapat dikembalikan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                tools = tools.filter(t => t.id !== id);
                renderTools();
                showSuccess('Alat berhasil dihapus!');
            }
        });
    }
    
    // Render Tools Function
    function renderTools() {
        const tbody = document.getElementById('toolTableBody');
        const activeTab = document.querySelector('.filter-tab.active');
        const filterText = activeTab ? activeTab.textContent.trim().toLowerCase() : 'semua';
        
        let filteredTools = tools;
        if (filterText.includes('aktif') && !filterText.includes('tidak')) {
            filteredTools = tools.filter(t => t.status === 'active');
        } else if (filterText.includes('tidak aktif')) {
            filteredTools = tools.filter(t => t.status === 'inactive');
        }
        
        tbody.innerHTML = filteredTools.map(tool => {
            const stars = Array(5).fill(0).map((_, i) => 
                i < tool.rating 
                    ? '<i class="fa-solid fa-star"></i>'
                    : '<i class="fa-regular fa-star"></i>'
            ).join('');
            
            const statusText = tool.status === 'active' ? 'Aktif' : tool.status === 'inactive' ? 'Tidak Aktif' : 'Maintenance';
            
            return `
                <tr data-tool-id="${tool.id}" data-status="${tool.status}">
                    <td class="checkbox-cell">
                        <input type="checkbox" class="tool-checkbox">
                    </td>
                    <td>
                        <div class="product-info">
                            <img src="${tool.image}" alt="Tool" class="product-img">
                            <div>
                                <div class="product-name">${tool.name}</div>
                                <div class="product-subtitle">${tool.model}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="rating-stars">${stars}</div>
                    </td>
                    <td>
                        <span class="status-badge">${statusText}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editTool(${tool.id})" title="Edit">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTool(${tool.id})" title="Hapus">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        updateTabCounts();
    }
    
    // Update Tab Counts
    function updateTabCounts() {
        const allCount = tools.length;
        const activeCount = tools.filter(t => t.status === 'active').length;
        const inactiveCount = tools.filter(t => t.status === 'inactive').length;
        
        document.querySelectorAll('.filter-tab').forEach(tab => {
            const text = tab.textContent.trim().toLowerCase();
            if (text.includes('semua')) {
                tab.textContent = `Semua Alat (${allCount})`;
            } else if (text.includes('aktif') && !text.includes('tidak')) {
                tab.textContent = `Aktif (${activeCount})`;
            } else if (text.includes('tidak')) {
                tab.textContent = `Tidak Aktif (${inactiveCount})`;
            }
        });
    }
    
    // Toggle Submenu
    function toggleSubmenu() {
        const submenu = document.querySelector('.sidebar-submenu');
        const chevron = document.querySelector('.chevron-icon');
        submenu.classList.toggle('show');
        chevron.classList.toggle('rotate');
    }
    
    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            renderTools();
        });
    });
    
    // Search functionality
    document.querySelector('.search-box input').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#toolTableBody tr');
        
        rows.forEach(row => {
            const toolName = row.querySelector('.product-name').textContent.toLowerCase();
            row.style.display = toolName.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.tool-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });
    
    // Image preview
    document.getElementById('toolImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('toolImagePreview').style.display = 'block';
                document.getElementById('toolPreviewImg').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Initialize
    updateTabCounts();
    
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
