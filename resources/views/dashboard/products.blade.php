<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Daftar Produk - ChickPatrol Seller</title>
  
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
    
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
        padding: 1rem;
        margin-top: 60px;
      }
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .page-header h1 {
        font-size: 1.25rem;
      }
      
      .filter-bar {
        flex-direction: column;
        align-items: stretch;
      }
      
      .filter-tabs {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
      }
      
      .filter-right {
        flex-direction: column;
        width: 100%;
      }
      
      .search-box {
        width: 100%;
      }
      
      .table {
        font-size: 0.875rem;
      }
      
      .table th,
      .table td {
        padding: 0.75rem 0.5rem;
      }
      
      .product-info {
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .product-img {
        width: 48px !important;
        height: 48px !important;
      }
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
    
    .sort-dropdown {
      position: relative;
    }
    
    .sort-dropdown select {
      padding: 0.5rem 2rem 0.5rem 0.75rem;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      font-size: 0.875rem;
      background: #f8f9fa;
      color: #2F2F2F;
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236c757d' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 0.75rem center;
      padding-right: 2.5rem;
    }
    
    .sort-dropdown select:focus {
      outline: none;
      border-color: #22C55E;
      background-color: white;
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
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #f8f9fa;
      font-size: 0.95rem;
      color: #2F2F2F;
      vertical-align: middle;
    }
    
    .product-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .product-img {
      width: 64px;
      height: 64px;
      border-radius: 8px;
      object-fit: cover;
      background: #f8f9fa;
    }
    
    .product-name {
      font-weight: 600;
      font-size: 1rem;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .product-weight {
      font-size: 0.875rem;
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
    
    .stock-alert-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      margin-left: 0.5rem;
      padding: 0.25rem 0.5rem;
      background: #FFF3E0;
      color: #FF9800;
      border-radius: 4px;
      font-size: 0.7rem;
      font-weight: 600;
    }
    
    .status-badge.inactive {
      background: #f0f0f0;
      color: #6c757d;
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
  @include('layouts.sidebar')
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <h1>Produk</h1>
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
          <div class="sort-dropdown">
            <select id="categoryFilter">
              <option value="">Semua Kategori</option>
            </select>
          </div>
          <div class="sort-dropdown">
            <select id="sortFilter">
              <option value="">Urutkan</option>
              <option value="name_asc">Nama A-Z</option>
              <option value="name_desc">Nama Z-A</option>
              <option value="price_asc">Harga Terendah</option>
              <option value="price_desc">Harga Tertinggi</option>
              <option value="stock_asc">Stok Terendah</option>
              <option value="stock_desc">Stok Tertinggi</option>
            </select>
          </div>
          <div class="search-box">
            <i class="fa-solid fa-search"></i>
            <input type="text" id="searchInput" placeholder="Cari Produk">
          </div>
        </div>
      </div>
      
      <!-- Product Table -->
      <table class="product-table">
        <thead>
          <tr>
            <th>Info Produk</th>
            <th>Rating</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="productTableBody">
          <!-- Product rows will be loaded dynamically -->
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
                  <option value="Ayam Potong Segar">Ayam Potong Segar</option>
                  <option value="Dada Ayam">Dada Ayam</option>
                  <option value="Ayam Karkas">Ayam Karkas</option>
                  <option value="Jeroan Ayam">Jeroan Ayam</option>
                  <option value="Produk Frozen">Produk Frozen</option>
                  <option value="Produk Olahan Ayam">Produk Olahan Ayam</option>
                  <option value="Obat & Vitamin Ayam">Obat & Vitamin Ayam</option>
                  <option value="Pakan Ayam">Pakan Ayam</option>
                  <option value="Peralatan Kandang">Peralatan Kandang</option>
                  <option value="Robot ChickPatrol">Robot ChickPatrol</option>
                  <option value="Lainnya">Lainnya</option>
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
                  <option value="kg">kg (Kilogram)</option>
                  <option value="ekor">ekor</option>
                  <option value="butir">butir</option>
                  <option value="pack">pack</option>
                  <option value="botol">botol</option>
                  <option value="liter">liter</option>
                  <option value="dosis">dosis</option>
                  <option value="unit">unit</option>
                  <option value="buah">buah</option>
                  <option value="meter">meter</option>
                  <option value="set">set</option>
                  <option value="box">box</option>
                  <option value="kaleng">kaleng</option>
                  <option value="sachet">sachet</option>
                  <option value="pcs">pcs (pieces)</option>
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
          <button type="button" class="btn btn-primary" onclick="saveProduct()" style="background: #22C55E; border: none;">Simpan</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/dashboard-alerts.js') }}"></script>
  
  <script>
    // Product data from database
    @php
        $productsData = $products->map(function($product) {
            $firstImage = $product->images->first();
            $imageUrl = null;
            
            if ($firstImage && $firstImage->url) {
                $imageUrl = $firstImage->url;
                // Jika URL tidak dimulai dengan http atau data:, tambahkan asset()
                if ($imageUrl && !preg_match('/^(https?:\/\/|data:)/', $imageUrl)) {
                    // Jika sudah ada storage/products, gunakan asset
                    if (strpos($imageUrl, 'storage/products/') !== false) {
                        // Pastikan menggunakan URL lengkap
                        if (strpos($imageUrl, '/') !== 0) {
                            $imageUrl = '/' . $imageUrl;
                        }
                        $imageUrl = asset($imageUrl);
                    } elseif (strpos($imageUrl, 'storage/') !== false) {
                        if (strpos($imageUrl, '/') !== 0) {
                            $imageUrl = '/' . $imageUrl;
                        }
                        $imageUrl = asset($imageUrl);
                    } else {
                        $imageUrl = asset('storage/' . $imageUrl);
                    }
                }
            }
            
            // Fallback ke placeholder jika tidak ada gambar
            if (!$imageUrl) {
                $imageUrl = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDY0IDY0Ij48cmVjdCB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0IiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZHVjdDwvdGV4dD48L3N2Zz4=";
            }
            
            $avgRating = $product->average_rating;
            $totalReviews = $product->total_reviews;
            
            // Tentukan kategori berdasarkan nama produk (lebih fleksibel)
            $categoryName = 'Lainnya';
            $productName = strtolower($product->name);
            
            // Produk Frozen - cek dulu karena bisa overlap dengan kategori lain
            if (strpos($productName, 'beku') !== false || strpos($productName, 'frozen') !== false) {
                $categoryName = 'Produk Frozen';
            }
            // Produk Olahan Ayam - cek sebelum kategori umum
            elseif (strpos($productName, 'nugget') !== false || strpos($productName, 'sosis') !== false || 
                    strpos($productName, 'karage') !== false || strpos($productName, 'popcorn') !== false || 
                    strpos($productName, 'wings') !== false || strpos($productName, 'chicken wings') !== false) {
                $categoryName = 'Produk Olahan Ayam';
            }
            // Dada Ayam - cek sebelum kategori umum ayam potong
            elseif (strpos($productName, 'dada') !== false || strpos($productName, 'fillet') !== false || 
                    strpos($productName, 'tenderloin') !== false || strpos($productName, 'slice') !== false || 
                    strpos($productName, 'cube') !== false || strpos($productName, 'skinless') !== false || 
                    strpos($productName, 'boneless') !== false || strpos($productName, 'premium') !== false) {
                $categoryName = 'Dada Ayam';
            }
            // Ayam Karkas
            elseif (strpos($productName, 'karkas') !== false || strpos($productName, 'carcass') !== false) {
                $categoryName = 'Ayam Karkas';
            }
            // Jeroan Ayam
            elseif (strpos($productName, 'hati') !== false || strpos($productName, 'ampela') !== false || 
                    strpos($productName, 'jantung') !== false || strpos($productName, 'usus') !== false || 
                    strpos($productName, 'paru') !== false || strpos($productName, 'jeroan') !== false ||
                    strpos($productName, 'paket jeroan') !== false) {
                $categoryName = 'Jeroan Ayam';
            }
            // Ayam Potong Segar - kategori umum untuk potongan ayam
            elseif (strpos($productName, 'ayam potong') !== false || strpos($productName, 'ayam broiler') !== false || 
                    strpos($productName, 'paha') !== false || strpos($productName, 'drumstick') !== false ||
                    strpos($productName, 'thigh') !== false || strpos($productName, 'sayap') !== false || 
                    strpos($productName, 'kulit') !== false || strpos($productName, 'kepala') !== false || 
                    strpos($productName, 'ceker') !== false || strpos($productName, 'ayam utuh') !== false ||
                    (strpos($productName, 'ayam') !== false && (strpos($productName, 'potong') !== false || 
                     strpos($productName, 'segar') !== false || strpos($productName, 'utuh') !== false))) {
                $categoryName = 'Ayam Potong Segar';
            }
            // Obat & Vitamin Ayam
            elseif (strpos($productName, 'vitamin') !== false || strpos($productName, 'antibiotik') !== false || 
                    strpos($productName, 'obat') !== false || strpos($productName, 'probiotik') !== false || 
                    strpos($productName, 'multivitamin') !== false || strpos($productName, 'disinfectant') !== false || 
                    strpos($productName, 'disinfektan') !== false || strpos($productName, 'electrolyte') !== false || 
                    strpos($productName, 'suplemen') !== false || strpos($productName, 'antistress') !== false ||
                    strpos($productName, 'vitachick') !== false || strpos($productName, 'vitamix') !== false) {
                $categoryName = 'Obat & Vitamin Ayam';
            }
            // Pakan Ayam
            elseif (strpos($productName, 'pakan') !== false || strpos($productName, 'vaksin') !== false || 
                    strpos($productName, 'desinfektan air') !== false || strpos($productName, 'mineral feed') !== false ||
                    strpos($productName, 'starter') !== false || strpos($productName, 'finisher') !== false ||
                    strpos($productName, 'nd/ib') !== false) {
                $categoryName = 'Pakan Ayam';
            }
            // Peralatan Kandang
            elseif (strpos($productName, 'tempat') !== false || strpos($productName, 'nipple') !== false || 
                    strpos($productName, 'selang') !== false || strpos($productName, 'lampu') !== false || 
                    strpos($productName, 'pemanas') !== false || strpos($productName, 'timbangan') !== false || 
                    strpos($productName, 'sensor') !== false || strpos($productName, 'tirai') !== false || 
                    strpos($productName, 'keranjang') !== false || strpos($productName, 'kandang') !== false || 
                    strpos($productName, 'sprayer') !== false || strpos($productName, 'mesin') !== false || 
                    strpos($productName, 'knapsack') !== false || strpos($productName, 'termometer') !== false || 
                    strpos($productName, 'exhaust') !== false || strpos($productName, 'blower') !== false ||
                    strpos($productName, 'feeder') !== false || strpos($productName, 'drinker') !== false ||
                    strpos($productName, 'brooder') !== false || strpos($productName, 'gasolec') !== false ||
                    strpos($productName, 'infrared') !== false || strpos($productName, 'doc') !== false ||
                    strpos($productName, 'plastik uv') !== false || strpos($productName, 'pencabut bulu') !== false) {
                $categoryName = 'Peralatan Kandang';
            }
            // Robot ChickPatrol
            elseif (strpos($productName, 'robot') !== false || strpos($productName, 'chickpatrol') !== false ||
                    strpos($productName, 'chick patrol') !== false) {
                $categoryName = 'Robot ChickPatrol';
            }
            
            return [
                'id' => $product->product_id,
                'name' => $product->name,
                'category' => $categoryName,
                'category_id' => $product->category_id,
                'price' => (float)$product->price,
                'stock' => (int)$product->stock,
                'unit' => $product->unit ?? 'kg',
                'rating' => round($avgRating, 1),
                'total_reviews' => $totalReviews,
                'description' => $product->description ?? '',
                'image' => $imageUrl
            ];
        });
    @endphp
    let products = @json($productsData);
    
    // Get unique categories from products
    const categories = [...new Set(products.map(p => p.category))].sort();
    
    // Calculate nextId for new products (using numeric counter for compatibility)
    let nextId = products.length > 0 ? products.length + 1 : 1;
    let editingProductId = null;
    
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
            confirmButtonColor: '#EF4444',
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
    
    // Sort Products Function
    function sortProducts(productsArray, sortType) {
        if (!sortType) return productsArray;
        
        const sorted = [...productsArray];
        
        switch(sortType) {
            case 'name_asc':
                sorted.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'name_desc':
                sorted.sort((a, b) => b.name.localeCompare(a.name));
                break;
            case 'price_asc':
                sorted.sort((a, b) => a.price - b.price);
                break;
            case 'price_desc':
                sorted.sort((a, b) => b.price - a.price);
                break;
            case 'stock_asc':
                sorted.sort((a, b) => (a.stock || 0) - (b.stock || 0));
                break;
            case 'stock_desc':
                sorted.sort((a, b) => (b.stock || 0) - (a.stock || 0));
                break;
            default:
                return sorted;
        }
        
        return sorted;
    }
    
    // Filter Products by Search
    function filterProductsBySearch(productsArray, searchTerm) {
        if (!searchTerm) return productsArray;
        
        const term = searchTerm.toLowerCase();
        return productsArray.filter(product => 
            product.name.toLowerCase().includes(term)
        );
    }
    
    // Filter Products by Category
    function filterProductsByCategory(productsArray, categoryName) {
        if (!categoryName) return productsArray;
        
        return productsArray.filter(product => 
            product.category === categoryName
        );
    }
    
    // Render Products Function
    function renderProducts() {
        const tbody = document.getElementById('productTableBody');
        const sortType = document.getElementById('sortFilter').value;
        const searchTerm = document.getElementById('searchInput').value;
        const categoryName = document.getElementById('categoryFilter').value;
        
        // Filter by category first
        let filteredProducts = filterProductsByCategory(products, categoryName);
        
        // Then filter by search
        filteredProducts = filterProductsBySearch(filteredProducts, searchTerm);
        
        // Finally sort filtered products
        filteredProducts = sortProducts(filteredProducts, sortType);
        
        if (!filteredProducts || filteredProducts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada produk yang ditemukan</td></tr>';
            return;
        }
        
        tbody.innerHTML = filteredProducts.map(product => {
            const rating = product.rating || 0;
            const totalReviews = product.total_reviews || 0;
            const stars = Array(5).fill(0).map((_, i) => 
                i < Math.round(rating)
                    ? '<i class="fa-solid fa-star text-warning"></i>'
                    : '<i class="fa-regular fa-star text-gray-300"></i>'
            ).join('');
            
            const ratingDisplay = totalReviews > 0 
                ? `<div class="rating-stars">${stars}</div><small class="text-muted">${rating.toFixed(1)} (${totalReviews})</small>`
                : '<div class="rating-stars"><i class="fa-regular fa-star text-gray-300"></i><i class="fa-regular fa-star text-gray-300"></i><i class="fa-regular fa-star text-gray-300"></i><i class="fa-regular fa-star text-gray-300"></i><i class="fa-regular fa-star text-gray-300"></i></div><small class="text-muted">Belum ada rating</small>';
            
            return `
                <tr data-product-id="${product.id}">
                    <td>
                        <div class="product-info">
                            <img src="${product.image || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDY0IDY0Ij48cmVjdCB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0IiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZHVjdDwvdGV4dD48L3N2Zz4='}" alt="Product" class="product-img" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDY0IDY0Ij48cmVjdCB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0IiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZHVjdDwvdGV4dD48L3N2Zz4='; this.style.display='block';">
                            <div>
                                <div class="product-name">${product.name}</div>
                                <div class="product-weight">${product.unit || '-'}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        ${ratingDisplay}
                    </td>
                    <td style="font-weight: 600; color: #22C55E; font-size: 1rem;">Rp ${product.price.toLocaleString('id-ID')}</td>
                    <td style="font-weight: 500;">
                        ${product.stock || 0}
                        ${(product.stock || 0) < 10 ? '<span class="stock-alert-badge" title="Stok Rendah"><i class="fa-solid fa-exclamation-triangle"></i> Rendah</span>' : ''}
                    </td>
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
        const searchTerm = document.getElementById('searchInput').value;
        const categoryName = document.getElementById('categoryFilter').value;
        
        let filteredProducts = filterProductsByCategory(products, categoryName);
        filteredProducts = filterProductsBySearch(filteredProducts, searchTerm);
        const allCount = filteredProducts.length;
        
        document.querySelectorAll('.filter-tab').forEach(tab => {
            const text = tab.textContent.trim().toLowerCase();
            if (text.includes('semua')) {
                tab.textContent = `Semua Produk (${allCount})`;
            }
        });
    }
    
    // Populate Category Filter
    function populateCategoryFilter() {
        const categoryFilter = document.getElementById('categoryFilter');
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category;
            option.textContent = category;
            categoryFilter.appendChild(option);
        });
    }
    
    // Toggle Submenu
    
    // Category filter functionality
    document.getElementById('categoryFilter').addEventListener('change', function() {
        renderProducts();
    });
    
    // Sort functionality
    document.getElementById('sortFilter').addEventListener('change', function() {
        renderProducts();
    });
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        renderProducts();
    });
    
    // Initialize category filter on page load
    populateCategoryFilter();
    
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
