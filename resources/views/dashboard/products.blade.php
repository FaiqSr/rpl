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
          <button class="filter-tab active" data-filter="all">Semua Produk</button>
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
            <th>Info Produk</th>
            <th>Rating</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="productTableBody">
          <!-- Product Row 1 -->
          <tr data-product-id="1" data-status="active">
            <td class="checkbox-cell">
              <input type="checkbox" class="product-checkbox">
            </td>
            <td>
              <div class="product-info">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='20'%3E🍗%3C/text%3E%3C/svg%3E" alt="Product" class="product-img">
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
            <td>
              <button class="btn btn-sm btn-outline-primary me-1" onclick="editProduct(1)" title="Edit">
                <i class="fa-solid fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(1)" title="Hapus">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
          
          <!-- Product Row 2 -->
          <tr data-product-id="2" data-status="inactive">
            <td class="checkbox-cell">
              <input type="checkbox" class="product-checkbox">
            </td>
            <td>
              <div class="product-info">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='20'%3E🍗%3C/text%3E%3C/svg%3E" alt="Product" class="product-img">
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
            <td>
              <button class="btn btn-sm btn-outline-primary me-1" onclick="editProduct(2)" title="Edit">
                <i class="fa-solid fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(2)" title="Hapus">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>
  
  <!-- Add/Edit Product Modal -->
  <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" id="productModalTitle">Tambah Produk</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-2">
          <form id="productForm">
            <input type="hidden" id="productId">
            
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="productName" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="productName" required>
              </div>
              <div class="col-md-6">
                <label for="productCategory" class="form-label">Kategori</label>
                <select class="form-select" id="productCategory" required>
                  <option value="">Pilih Kategori</option>
                  <option value="Daging Segar">Daging Segar</option>
                  <option value="Telur">Telur</option>
                  <option value="Daging Olahan">Daging Olahan</option>
                </select>
              </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="productPrice" class="form-label">Harga (Rp)</label>
                <input type="number" class="form-control" id="productPrice" required min="0">
              </div>
              <div class="col-md-4">
                <label for="productStock" class="form-label">Stok</label>
                <input type="number" class="form-control" id="productStock" required min="0">
              </div>
              <div class="col-md-4">
                <label for="productUnit" class="form-label">Satuan</label>
                <select class="form-select" id="productUnit" required>
                  <option value="kg">kg</option>
                  <option value="butir">butir</option>
                  <option value="pack">pack</option>
                </select>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="productDescription" class="form-label">Deskripsi</label>
              <textarea class="form-control" id="productDescription" rows="3"></textarea>
            </div>
            
            <div class="mb-3">
              <label for="productImage" class="form-label">Gambar Produk</label>
              <input type="file" class="form-control" id="productImage" accept="image/*">
              <div id="imagePreview" class="mt-2" style="display: none;">
                <img id="previewImg" style="max-width: 200px; border-radius: 8px;">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" onclick="saveProduct()" style="background: #69B578; border: none;">Simpan</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  
  <script>
    // Product data from database
    @php
        $productsData = $products->map(function($product) {
            $firstImage = $product->images->first();
            $imageUrl = $firstImage ? $firstImage->url : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='20'%3E🍗%3C/text%3E%3C/svg%3E";
            
            return [
                'id' => $product->product_id,
                'name' => $product->name,
                'category' => 'Daging Segar',
                'price' => (float)$product->price,
                'stock' => (int)$product->stock,
                'unit' => $product->unit ?? 'kg',
                'rating' => 0,
                'description' => $product->description ?? '',
                'image' => $imageUrl
            ];
        });
    @endphp
    let products = @json($productsData);
    
    // Calculate nextId for new products (using numeric counter for compatibility)
    let nextId = products.length > 0 ? products.length + 1 : 1;
    let editingProductId = null;
    
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
        editingProductId = null;
        document.getElementById('productModalTitle').textContent = 'Tambah Produk';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        
        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();
    }
    
    // Edit Product Function
    function editProduct(id) {
        const product = products.find(p => p.id === id);
        if (!product) return;
        
        editingProductId = id;
        document.getElementById('productModalTitle').textContent = 'Edit Produk';
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productCategory').value = product.category;
        document.getElementById('productPrice').value = product.price;
        document.getElementById('productStock').value = product.stock;
        document.getElementById('productUnit').value = product.unit;
        document.getElementById('productDescription').value = product.description || '';
        
        if (product.image) {
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('previewImg').src = product.image;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();
    }
    
    // Save Product Function
    async function saveProduct() {
        const form = document.getElementById('productForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const name = document.getElementById('productName').value;
        const category = document.getElementById('productCategory').value;
        const price = parseFloat(document.getElementById('productPrice').value);
        const stock = parseInt(document.getElementById('productStock').value);
        const unit = document.getElementById('productUnit').value;
        const description = document.getElementById('productDescription').value;
        const imageFile = document.getElementById('productImage').files[0];
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Process image
        let imageData = null;
        if (imageFile) {
            const reader = new FileReader();
            imageData = await new Promise((resolve) => {
                reader.onload = function(e) {
                    resolve(e.target.result);
                };
                reader.readAsDataURL(imageFile);
            });
        } else if (editingProductId) {
            // Keep existing image if editing and no new image
            const existingProduct = products.find(p => p.id === editingProductId);
            if (existingProduct && existingProduct.image) {
                imageData = existingProduct.image;
            }
        }
        
        const payload = {
            name: name,
            description: description,
            price: price,
            stock: stock,
            unit: unit,
            image: imageData || "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect width='50' height='50' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='20'%3E🍗%3C/text%3E%3C/svg%3E"
        };
        
        try {
            let response;
            if (editingProductId) {
                // Update existing product
                response = await fetch(`/dashboard/products/${editingProductId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
            } else {
                // Create new product
                response = await fetch('/dashboard/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
            }
            
            const result = await response.json();
            
            if (result.success) {
                showSuccess(editingProductId ? 'Produk berhasil diperbarui!' : 'Produk berhasil ditambahkan!');
                bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
                // Reload page to refresh data from database
                window.location.reload();
            } else {
                showError(result.message || 'Terjadi kesalahan saat menyimpan produk');
            }
        } catch (error) {
            console.error('Error saving product:', error);
            showError('Terjadi kesalahan saat menyimpan produk');
        }
    }
    
    // Delete Product Function
    async function deleteProduct(id) {
        const result = await Swal.fire({
            title: 'Hapus Produk?',
            text: 'Produk yang dihapus tidak dapat dikembalikan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        });
        
        if (result.isConfirmed) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/dashboard/products/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess('Produk berhasil dihapus!');
                    // Reload page to refresh data from database
                    window.location.reload();
                } else {
                    showError(data.message || 'Terjadi kesalahan saat menghapus produk');
                }
            } catch (error) {
                console.error('Error deleting product:', error);
                showError('Terjadi kesalahan saat menghapus produk');
            }
        }
    }
    
    // Render Products Function
    function renderProducts() {
        const tbody = document.getElementById('productTableBody');
        
        tbody.innerHTML = products.map(product => {
            const stars = Array(5).fill(0).map((_, i) => 
                i < product.rating 
                    ? '<i class="fa-solid fa-star"></i>'
                    : '<i class="fa-regular fa-star"></i>'
            ).join('');
            
            return `
                <tr data-product-id="${product.id}">
                    <td class="checkbox-cell">
                        <input type="checkbox" class="product-checkbox">
                    </td>
                    <td>
                        <div class="product-info">
                            <img src="${product.image}" alt="Product" class="product-img">
                            <div>
                                <div class="product-name">${product.name}</div>
                                <div class="product-weight">${product.unit}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="rating-stars">${stars}</div>
                    </td>
                    <td>Rp ${product.price.toLocaleString('id-ID')}</td>
                    <td>${product.stock}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editProduct('${product.id}')" title="Edit">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct('${product.id}')" title="Hapus">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Update tab counts
        updateTabCounts();
    }
    
    // Update Tab Counts
    function updateTabCounts() {
        const allCount = products.length;
        document.querySelectorAll('.filter-tab').forEach(tab => {
            const text = tab.textContent.trim().toLowerCase();
            if (text.includes('semua')) {
                tab.textContent = `Semua Produk (${allCount})`;
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
    
    // Search functionality
    document.querySelector('.search-box input').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#productTableBody tr');
        
        rows.forEach(row => {
            const productName = row.querySelector('.product-name').textContent.toLowerCase();
            row.style.display = productName.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.product-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });
    
    // Image preview
    document.getElementById('productImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('previewImg').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Initialize: render products from database
    updateTabCounts();
    renderProducts();
    
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
