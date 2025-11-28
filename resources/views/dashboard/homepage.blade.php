<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Manajemen Homestore - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  
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
      
      .page-header h1 {
        font-size: 1.25rem;
      }
      
      .tab-nav {
        flex-direction: column;
        border-bottom: none;
      }
      
      .tab-btn {
        border-bottom: none;
        border-left: 3px solid transparent;
        border-radius: 0;
      }
      
      .tab-btn.active {
        border-left-color: #22C55E;
        border-bottom: none;
      }
      
      .table {
        font-size: 0.875rem;
        overflow-x: auto;
        display: block;
      }
      
      .table thead,
      .table tbody,
      .table tr {
        display: block;
      }
      
      .table th,
      .table td {
        display: block;
        padding: 0.5rem;
        text-align: left;
      }
      
      .table th {
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
      }
      
      .table td::before {
        content: attr(data-label) ": ";
        font-weight: 600;
        display: inline-block;
        width: 100px;
      }
    }
    
    .page-header {
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
    
    .tab-nav {
      display: flex;
      border-bottom: 2px solid #e9ecef;
      background: #f8f9fa;
    }
    
    .tab-btn {
      padding: 1rem 1.5rem;
      background: transparent;
      border: none;
      border-bottom: 3px solid transparent;
      font-weight: 500;
      color: #6c757d;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .tab-btn:hover {
      color: #2F2F2F;
      background: #f1f3f5;
    }
    
    .tab-btn.active {
      color: #22C55E;
      border-bottom-color: #22C55E;
      background: white;
    }
    
    .tab-content {
      display: none;
      padding: 1.5rem;
    }
    
    .tab-content.active {
      display: block;
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
      text-decoration: none;
      cursor: pointer;
    }
    
    .btn-add:hover {
      background: #5a9d66;
      color: white;
    }
    
    .table {
      margin: 0;
    }
    
    .table thead {
      background: #f8f9fa;
    }
    
    .table th {
      border-bottom: 2px solid #e9ecef;
      padding: 1rem;
      font-weight: 600;
      font-size: 0.875rem;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .table td {
      padding: 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #f1f3f5;
    }
    
    .btn-action {
      padding: 0.4rem 0.8rem;
      border: none;
      border-radius: 4px;
      font-size: 0.8rem;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-edit {
      background: #3b82f6;
      color: white;
    }
    
    .btn-edit:hover {
      background: #2563eb;
      color: white;
    }
    
    .btn-delete {
      background: #ef4444;
      color: white;
    }
    
    .btn-delete:hover {
      background: #dc2626;
      color: white;
    }
    
    .badge {
      padding: 0.35rem 0.75rem;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .badge-success {
      background: #d1fae5;
      color: #065f46;
    }
    
    .badge-secondary {
      background: #e5e7eb;
      color: #374151;
    }
    
    .form-label {
      font-weight: 500;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 0.6rem 0.75rem;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #22C55E;
      box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
    }
    
    .banner-preview, .category-preview {
      max-width: 200px;
      max-height: 100px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #e5e7eb;
    }
    
    .category-preview {
      width: 80px;
      height: 80px;
      object-fit: cover;
    }
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <div class="main-content">
    <div class="page-header">
      <h1><i class="fa-solid fa-home me-2"></i>Manajemen Homestore</h1>
    </div>
    
    <div class="content-card">
      <!-- Tab Navigation -->
      <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('banners')">
          <i class="fa-solid fa-images me-2"></i>Banner Slider
        </button>
        <button class="tab-btn" onclick="switchTab('categories')">
          <i class="fa-solid fa-tags me-2"></i>Kategori Produk
        </button>
      </div>
      
      <!-- Banner Slider Tab -->
      <div id="tabBanners" class="tab-content active">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h3 style="font-size: 1.125rem; font-weight: 600; color: #2F2F2F; margin: 0;">Banner & Promo</h3>
          <button class="btn-add" onclick="openBannerModal()">
            <i class="fa-solid fa-plus"></i>
            Tambah Banner
          </button>
        </div>
        
        <table class="table">
          <thead>
            <tr>
              <th>Gambar</th>
              <th>Judul</th>
              <th>Jenis</th>
              <th>Link URL</th>
              <th>Urutan</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="bannersTableBody">
            @forelse($banners as $banner)
              <tr>
                <td>
                  <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="banner-preview" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMjAwIDEwMCI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIxMDAiIGZpbGw9IiNmOGQ3ZGEiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzcyMWMyNCIgZm9udC1zaXplPSIxNHB4IiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+'">
                </td>
                <td>{{ $banner->title ?? '-' }}</td>
                <td>
                  @if(isset($banner->banner_type))
                    @if($banner->banner_type === 'square')
                      <span class="badge" style="background: #dbeafe; color: #1e40af;">Persegi</span>
                    @elseif($banner->banner_type === 'rectangle_top')
                      <span class="badge" style="background: #dcfce7; color: #166534;">Persegi Panjang Atas</span>
                    @elseif($banner->banner_type === 'rectangle_bottom')
                      <span class="badge" style="background: #fef3c7; color: #92400e;">Persegi Panjang Bawah</span>
                    @else
                      <span style="color: #9ca3af;">-</span>
                    @endif
                  @else
                    <span class="badge" style="background: #dbeafe; color: #1e40af;">Persegi</span>
                  @endif
                </td>
                <td>
                  @if($banner->link_url)
                    <a href="{{ $banner->link_url }}" target="_blank" style="color: #3b82f6; text-decoration: none;">{{ Str::limit($banner->link_url, 30) }}</a>
                  @else
                    <span style="color: #9ca3af;">-</span>
                  @endif
                </td>
                <td>{{ $banner->sort_order }}</td>
                <td>
                  @if($banner->is_active)
                    <span class="badge badge-success">Aktif</span>
                  @else
                    <span class="badge badge-secondary">Nonaktif</span>
                  @endif
                </td>
                <td>
                  <button class="btn-action btn-edit me-2" onclick="editBanner('{{ $banner->banner_id }}')">
                    <i class="fa-solid fa-edit"></i> Edit
                  </button>
                  <button class="btn-action btn-delete" onclick="deleteBanner('{{ $banner->banner_id }}')">
                    <i class="fa-solid fa-trash"></i> Hapus
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="fa-solid fa-images mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                  <p class="mb-0">Belum ada banner. Klik "Tambah Banner" untuk membuat banner pertama.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      
      <!-- Categories Tab -->
      <div id="tabCategories" class="tab-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h3 style="font-size: 1.125rem; font-weight: 600; color: #2F2F2F; margin: 0;">Kategori Homepage</h3>
          <button class="btn-add" onclick="openCategoryModal()">
            <i class="fa-solid fa-plus"></i>
            Tambah Kategori
          </button>
        </div>
        
        <table class="table">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Nama</th>
              <th>Slug</th>
              <th>Link URL</th>
              <th>Urutan</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="categoriesTableBody">
            @forelse($categories as $category)
              <tr>
                <td>
                  <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="category-preview" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iODAiIGZpbGw9IiNmOGQ3ZGEiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzcyMWMyNCIgZm9udC1zaXplPSIxMnB4IiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+'">
                </td>
                <td>
                  <div style="font-weight: 500; color: #2F2F2F;">{{ $category->name }}</div>
                </td>
                <td>
                  <code style="font-size: 0.8rem; color: #6c757d;">{{ $category->slug }}</code>
                </td>
                <td>
                  @if($category->link_url)
                    <a href="{{ $category->link_url }}" target="_blank" style="color: #3b82f6; text-decoration: none;">{{ Str::limit($category->link_url, 30) }}</a>
                  @else
                    <span style="color: #9ca3af;">-</span>
                  @endif
                </td>
                <td>{{ $category->sort_order }}</td>
                <td>
                  @if($category->is_active)
                    <span class="badge badge-success">Aktif</span>
                  @else
                    <span class="badge badge-secondary">Nonaktif</span>
                  @endif
                </td>
                <td>
                  <button class="btn-action btn-edit me-2" onclick="editCategory('{{ $category->category_id }}')">
                    <i class="fa-solid fa-edit"></i> Edit
                  </button>
                  <button class="btn-action btn-delete" onclick="deleteCategory('{{ $category->category_id }}')">
                    <i class="fa-solid fa-trash"></i> Hapus
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="fa-solid fa-tags mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                  <p class="mb-0">Belum ada kategori. Klik "Tambah Kategori" untuk membuat kategori pertama.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Banner Modal -->
  <div class="modal fade" id="bannerModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bannerModalTitle">Tambah Banner Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="bannerForm">
            <input type="hidden" id="bannerId" name="banner_id">
            
            <div class="mb-3">
              <label class="form-label">Judul Banner (Opsional)</label>
              <input type="text" class="form-control" id="bannerTitle" name="title" placeholder="Contoh: Promo Spesial">
            </div>
            
            <div class="mb-3">
              <label class="form-label">Jenis Banner *</label>
              <select class="form-select" id="bannerType" name="banner_type" required>
                <option value="square">Banner Persegi (Kiri)</option>
                <option value="rectangle_top">Banner Persegi Panjang Atas (Kanan Atas)</option>
                <option value="rectangle_bottom">Banner Persegi Panjang Bawah (Kanan Bawah)</option>
              </select>
              <small class="text-muted">Pilih posisi banner di homepage</small>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Gambar Banner *</label>
              <div class="mb-2">
                <div class="btn-group w-100" role="group">
                  <input type="radio" class="btn-check" name="bannerImageSource" id="bannerImageSourceUrl" value="url" checked>
                  <label class="btn btn-outline-secondary" for="bannerImageSourceUrl">URL</label>
                  
                  <input type="radio" class="btn-check" name="bannerImageSource" id="bannerImageSourceUpload" value="upload">
                  <label class="btn btn-outline-secondary" for="bannerImageSourceUpload">Upload File</label>
                </div>
              </div>
              
              <!-- URL Input -->
              <div id="bannerImageUrlSection">
                <input type="text" class="form-control" id="bannerImageUrl" name="image_url" placeholder="https://example.com/image.jpg atau data:image/...">
                <small class="text-muted">Masukkan URL gambar atau data URI (base64)</small>
              </div>
              
              <!-- File Upload Input -->
              <div id="bannerImageUploadSection" style="display: none;">
                <input type="file" class="form-control" id="bannerImageFile" name="image_file" accept="image/*">
                <small class="text-muted">Pilih file gambar (JPG, PNG, GIF, maks 5MB)</small>
                <div id="bannerImagePreview" class="mt-2" style="display: none;">
                  <img id="bannerPreviewImg" src="" alt="Preview" style="max-width: 300px; max-height: 200px; border-radius: 6px; border: 1px solid #e5e7eb;">
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Link URL (Opsional)</label>
              <input type="text" class="form-control" id="bannerLinkUrl" name="link_url" placeholder="https://example.com atau /products">
              <small class="text-muted">URL yang akan dibuka saat banner diklik</small>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Urutan</label>
                <input type="number" class="form-control" id="bannerSortOrder" name="sort_order" value="0" min="0">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="bannerIsActive" name="is_active">
                  <option value="1">Aktif</option>
                  <option value="0">Nonaktif</option>
                </select>
              </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-save me-1"></i> Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Category Modal -->
  <div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="categoryModalTitle">Tambah Kategori Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="categoryForm">
            <input type="hidden" id="categoryId" name="category_id">
            
            <div class="mb-3">
              <label class="form-label">Nama Kategori *</label>
              <input type="text" class="form-control" id="categoryName" name="name" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Slug</label>
              <input type="text" class="form-control" id="categorySlug" name="slug" placeholder="Akan di-generate otomatis jika kosong">
            </div>
            
            <div class="mb-3">
              <label class="form-label">Foto Kategori *</label>
              <div class="mb-2">
                <div class="btn-group w-100" role="group">
                  <input type="radio" class="btn-check" name="imageSource" id="imageSourceUrl" value="url" checked>
                  <label class="btn btn-outline-secondary" for="imageSourceUrl">URL</label>
                  
                  <input type="radio" class="btn-check" name="imageSource" id="imageSourceUpload" value="upload">
                  <label class="btn btn-outline-secondary" for="imageSourceUpload">Upload File</label>
                </div>
              </div>
              
              <!-- URL Input -->
              <div id="imageUrlSection">
                <input type="text" class="form-control" id="categoryImageUrl" name="image_url" placeholder="https://example.com/image.jpg atau data:image/...">
                <small class="text-muted">Masukkan URL gambar atau data URI (base64)</small>
              </div>
              
              <!-- File Upload Input -->
              <div id="imageUploadSection" style="display: none;">
                <input type="file" class="form-control" id="categoryImageFile" name="image_file" accept="image/*">
                <small class="text-muted">Pilih file gambar (JPG, PNG, GIF, maks 2MB)</small>
                <div id="imagePreview" class="mt-2" style="display: none;">
                  <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 6px; border: 1px solid #e5e7eb;">
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Urutan <small class="text-muted">(Otomatis jika kosong)</small></label>
                <input type="number" class="form-control" id="categorySortOrder" name="sort_order" value="" min="0" placeholder="Kosongkan untuk otomatis">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="categoryIsActive" name="is_active">
                  <option value="1">Aktif</option>
                  <option value="0">Nonaktif</option>
                </select>
              </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-save me-1"></i> Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  
  <script>
    let bannerModal, categoryModal;
    let isBannerEditMode = false;
    let isCategoryEditMode = false;
    
    // Initialize modals
    document.addEventListener('DOMContentLoaded', function() {
      bannerModal = new bootstrap.Modal(document.getElementById('bannerModal'));
      categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
      
      // Auto-generate slug from name
      document.getElementById('categoryName').addEventListener('input', function() {
        const slugInput = document.getElementById('categorySlug');
        if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
          const slug = this.value.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
          slugInput.value = slug;
          slugInput.dataset.autoGenerated = 'true';
        }
      });
      
      document.getElementById('categorySlug').addEventListener('input', function() {
        this.dataset.autoGenerated = 'false';
      });
      
      // Banner image source toggle
      document.querySelectorAll('input[name="bannerImageSource"]').forEach(radio => {
        radio.addEventListener('change', function() {
          const urlSection = document.getElementById('bannerImageUrlSection');
          const uploadSection = document.getElementById('bannerImageUploadSection');
          const urlInput = document.getElementById('bannerImageUrl');
          const fileInput = document.getElementById('bannerImageFile');
          
          if (this.value === 'url') {
            urlSection.style.display = 'block';
            uploadSection.style.display = 'none';
            urlInput.required = true;
            fileInput.required = false;
            fileInput.value = '';
            document.getElementById('bannerImagePreview').style.display = 'none';
          } else {
            urlSection.style.display = 'none';
            uploadSection.style.display = 'block';
            urlInput.required = false;
            fileInput.required = true;
            urlInput.value = '';
          }
        });
      });
      
      // Banner image preview
      document.getElementById('bannerImageFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          if (file.size > 5 * 1024 * 1024) {
            Swal.fire('Error', 'Ukuran file maksimal 5MB', 'error');
            this.value = '';
            return;
          }
          
          const reader = new FileReader();
          reader.onload = function(e) {
            document.getElementById('bannerPreviewImg').src = e.target.result;
            document.getElementById('bannerImagePreview').style.display = 'block';
          };
          reader.readAsDataURL(file);
        }
      });
      
      // Category image source toggle
      document.querySelectorAll('input[name="imageSource"]').forEach(radio => {
        radio.addEventListener('change', function() {
          const urlSection = document.getElementById('imageUrlSection');
          const uploadSection = document.getElementById('imageUploadSection');
          const urlInput = document.getElementById('categoryImageUrl');
          const fileInput = document.getElementById('categoryImageFile');
          
          if (this.value === 'url') {
            urlSection.style.display = 'block';
            uploadSection.style.display = 'none';
            urlInput.required = true;
            fileInput.required = false;
            fileInput.value = '';
            document.getElementById('imagePreview').style.display = 'none';
          } else {
            urlSection.style.display = 'none';
            uploadSection.style.display = 'block';
            urlInput.required = false;
            fileInput.required = true;
            urlInput.value = '';
          }
        });
      });
      
      // Image preview
      document.getElementById('categoryImageFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Error', 'Ukuran file maksimal 2MB', 'error');
            this.value = '';
            return;
          }
          
          const reader = new FileReader();
          reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
          };
          reader.readAsDataURL(file);
        }
      });
    });
    
    // Tab switching
    function switchTab(tab) {
      // Hide all tabs
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
      });
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      
      // Show selected tab
      if (tab === 'banners') {
        document.getElementById('tabBanners').classList.add('active');
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
      } else {
        document.getElementById('tabCategories').classList.add('active');
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
      }
    }
    
    // Banner functions
    function openBannerModal() {
      isBannerEditMode = false;
      document.getElementById('bannerModalTitle').textContent = 'Tambah Banner Baru';
      document.getElementById('bannerForm').reset();
      document.getElementById('bannerId').value = '';
      document.getElementById('bannerType').value = 'square';
      document.getElementById('bannerSortOrder').value = '0';
      document.getElementById('bannerIsActive').value = '1';
      bannerModal.show();
    }
    
    function editBanner(bannerId) {
      isBannerEditMode = true;
      document.getElementById('bannerModalTitle').textContent = 'Edit Banner';
      
      fetch(`/dashboard/homepage/banners/${bannerId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById('bannerId').value = data.banner.banner_id;
            document.getElementById('bannerTitle').value = data.banner.title || '';
            document.getElementById('bannerType').value = data.banner.banner_type || 'square';
            document.getElementById('bannerImageUrl').value = data.banner.image_url || '';
            document.getElementById('bannerLinkUrl').value = data.banner.link_url || '';
            document.getElementById('bannerSortOrder').value = data.banner.sort_order || 0;
            document.getElementById('bannerIsActive').value = data.banner.is_active ? '1' : '0';
            
            // Set image source to URL and show URL input
            document.getElementById('bannerImageSourceUrl').checked = true;
            document.getElementById('bannerImageUrlSection').style.display = 'block';
            document.getElementById('bannerImageUploadSection').style.display = 'none';
            document.getElementById('bannerImageUrl').required = true;
            document.getElementById('bannerImageFile').required = false;
            document.getElementById('bannerImagePreview').style.display = 'none';
            
            bannerModal.show();
          } else {
            Swal.fire('Error', data.message || 'Gagal memuat banner', 'error');
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire('Error', 'Gagal memuat banner', 'error');
        });
    }
    
    function deleteBanner(bannerId) {
      Swal.fire({
        title: 'Hapus Banner?',
        text: 'Banner yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          
          fetch(`/dashboard/homepage/banners/${bannerId}`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              Swal.fire('Berhasil', 'Banner berhasil dihapus', 'success').then(() => {
                location.reload();
              });
            } else {
              Swal.fire('Error', data.message || 'Gagal menghapus banner', 'error');
            }
          })
          .catch(err => {
            console.error('Error:', err);
            Swal.fire('Error', 'Gagal menghapus banner', 'error');
          });
        }
      });
    }
    
    // Category functions
    function openCategoryModal() {
      isCategoryEditMode = false;
      document.getElementById('categoryModalTitle').textContent = 'Tambah Kategori Baru';
      document.getElementById('categoryForm').reset();
      document.getElementById('categoryId').value = '';
      document.getElementById('categorySortOrder').value = '0';
      document.getElementById('categoryIsActive').value = '1';
      document.getElementById('imageSourceUrl').checked = true;
      document.getElementById('imageUrlSection').style.display = 'block';
      document.getElementById('imageUploadSection').style.display = 'none';
      document.getElementById('categoryImageUrl').required = true;
      document.getElementById('categoryImageFile').required = false;
      document.getElementById('imagePreview').style.display = 'none';
      categoryModal.show();
    }
    
    function editCategory(categoryId) {
      isCategoryEditMode = true;
      document.getElementById('categoryModalTitle').textContent = 'Edit Kategori';
      
      console.log('Fetching category with ID:', categoryId);
      
      fetch(`/dashboard/homepage/categories/${categoryId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })
        .then(res => {
          console.log('Response status:', res.status);
          if (!res.ok) {
            return res.json().then(data => {
              throw new Error(data.message || 'HTTP error! status: ' + res.status);
            });
          }
          return res.json();
        })
        .then(data => {
          console.log('Response data:', data);
          if (data.success && data.category) {
            document.getElementById('categoryId').value = data.category.category_id;
            document.getElementById('categoryName').value = data.category.name;
            document.getElementById('categorySlug').value = data.category.slug;
            document.getElementById('categoryImageUrl').value = data.category.image_url || '';
            document.getElementById('categorySortOrder').value = data.category.sort_order || '';
            document.getElementById('categoryIsActive').value = data.category.is_active ? '1' : '0';
            
            // Set image source to URL and show URL input
            document.getElementById('imageSourceUrl').checked = true;
            document.getElementById('imageUrlSection').style.display = 'block';
            document.getElementById('imageUploadSection').style.display = 'none';
            document.getElementById('categoryImageUrl').required = true;
            document.getElementById('categoryImageFile').required = false;
            document.getElementById('imagePreview').style.display = 'none';
            
            categoryModal.show();
          } else {
            Swal.fire('Error', data.message || 'Gagal memuat kategori', 'error');
          }
        })
        .catch(err => {
          console.error('Error details:', err);
          Swal.fire('Error', err.message || 'Gagal memuat kategori', 'error');
        });
    }
    
    function deleteCategory(categoryId) {
      Swal.fire({
        title: 'Hapus Kategori?',
        text: 'Kategori yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          
          fetch(`/dashboard/homepage/categories/${categoryId}`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              Swal.fire('Berhasil', 'Kategori berhasil dihapus', 'success').then(() => {
                location.reload();
              });
            } else {
              Swal.fire('Error', data.message || 'Gagal menghapus kategori', 'error');
            }
          })
          .catch(err => {
            console.error('Error:', err);
            Swal.fire('Error', 'Gagal menghapus kategori', 'error');
          });
        }
      });
    }
    
    // Form submissions
    document.getElementById('bannerForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const title = document.getElementById('bannerTitle').value;
      const bannerType = document.getElementById('bannerType').value;
      const imageSource = document.querySelector('input[name="bannerImageSource"]:checked').value;
      const imageUrl = document.getElementById('bannerImageUrl').value;
      const imageFile = document.getElementById('bannerImageFile').files[0];
      const linkUrl = document.getElementById('bannerLinkUrl').value;
      const sortOrder = parseInt(document.getElementById('bannerSortOrder').value) || 0;
      const isActive = document.getElementById('bannerIsActive').value === '1';
      
      if (!bannerType) {
        Swal.fire('Error', 'Jenis banner harus dipilih', 'error');
        return;
      }
      
      if (imageSource === 'url' && !imageUrl.trim()) {
        Swal.fire('Error', 'URL gambar harus diisi', 'error');
        return;
      }
      
      if (imageSource === 'upload' && !imageFile) {
        Swal.fire('Error', 'File gambar harus dipilih', 'error');
        return;
      }
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      const url = isBannerEditMode 
        ? `/dashboard/homepage/banners/${document.getElementById('bannerId').value}`
        : '/dashboard/homepage/banners';
      const method = isBannerEditMode ? 'PUT' : 'POST';
      
      // Use FormData if uploading file, otherwise use JSON
      if (imageSource === 'upload' && imageFile) {
        const formData = new FormData();
        formData.append('title', title || '');
        formData.append('banner_type', bannerType);
        formData.append('image_file', imageFile);
        formData.append('link_url', linkUrl || '');
        formData.append('sort_order', sortOrder);
        formData.append('is_active', isActive ? '1' : '0');
        
        fetch(url, {
          method: method,
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Berhasil', isBannerEditMode ? 'Banner berhasil diperbarui' : 'Banner berhasil dibuat', 'success').then(() => {
              location.reload();
            });
          } else {
            Swal.fire('Error', data.message || 'Gagal menyimpan banner', 'error');
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire('Error', 'Gagal menyimpan banner', 'error');
        });
      } else {
        // Use JSON for URL
        fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            title: title || null,
            banner_type: bannerType,
            image_url: imageUrl,
            link_url: linkUrl || null,
            sort_order: sortOrder,
            is_active: isActive
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Berhasil', isBannerEditMode ? 'Banner berhasil diperbarui' : 'Banner berhasil dibuat', 'success').then(() => {
              location.reload();
            });
          } else {
            Swal.fire('Error', data.message || 'Gagal menyimpan banner', 'error');
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire('Error', 'Gagal menyimpan banner', 'error');
        });
      }
    });
    
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const name = document.getElementById('categoryName').value;
      const slug = document.getElementById('categorySlug').value;
      const imageSource = document.querySelector('input[name="imageSource"]:checked').value;
      const imageUrl = document.getElementById('categoryImageUrl').value;
      const imageFile = document.getElementById('categoryImageFile').files[0];
      const sortOrderInput = document.getElementById('categorySortOrder').value;
      const sortOrder = sortOrderInput && sortOrderInput.trim() !== '' ? parseInt(sortOrderInput) : null;
      const isActive = document.getElementById('categoryIsActive').value === '1';
      
      if (!name.trim()) {
        Swal.fire('Error', 'Nama kategori harus diisi', 'error');
        return;
      }
      
      if (imageSource === 'url' && !imageUrl.trim()) {
        Swal.fire('Error', 'URL foto kategori harus diisi', 'error');
        return;
      }
      
      if (imageSource === 'upload' && !imageFile) {
        Swal.fire('Error', 'File foto kategori harus dipilih', 'error');
        return;
      }
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      const url = isCategoryEditMode 
        ? `/dashboard/homepage/categories/${document.getElementById('categoryId').value}`
        : '/dashboard/homepage/categories';
      const method = isCategoryEditMode ? 'PUT' : 'POST';
      
      // Use FormData if uploading file, otherwise use JSON
      if (imageSource === 'upload' && imageFile) {
        const formData = new FormData();
        formData.append('name', name);
        if (slug) formData.append('slug', slug);
        formData.append('image_file', imageFile);
        if (sortOrder !== null) {
          formData.append('sort_order', sortOrder);
        }
        formData.append('is_active', isActive ? '1' : '0');
        
        fetch(url, {
          method: method,
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Berhasil', isCategoryEditMode ? 'Kategori berhasil diperbarui' : 'Kategori berhasil dibuat', 'success').then(() => {
              location.reload();
            });
          } else {
            Swal.fire('Error', data.message || 'Gagal menyimpan kategori', 'error');
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire('Error', 'Gagal menyimpan kategori', 'error');
        });
      } else {
        // Use JSON for URL
        fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            name: name,
            slug: slug || null,
            image_url: imageUrl,
            sort_order: sortOrder !== null ? sortOrder : null,
            is_active: isActive
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Berhasil', isCategoryEditMode ? 'Kategori berhasil diperbarui' : 'Kategori berhasil dibuat', 'success').then(() => {
              location.reload();
            });
          } else {
            Swal.fire('Error', data.message || 'Gagal menyimpan kategori', 'error');
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire('Error', 'Gagal menyimpan kategori', 'error');
        });
      }
    });
  </script>
</body>
</html>

