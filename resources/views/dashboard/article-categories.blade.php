<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Kelola Kategori Artikel - ChickPatrol Seller</title>
  
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
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .page-header h1 {
        font-size: 1.25rem;
      }
      
      .table {
        font-size: 0.75rem;
      }
      
      .table th,
      .table td {
        padding: 0.75rem 0.5rem;
      }
      
      .btn-action {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
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
      text-decoration: none;
      cursor: pointer;
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
    
    /* Override global table styles untuk desktop */
    .table {
      margin: 0;
      width: 100%;
      border-collapse: collapse;
      display: table !important;
    }
    
    .table thead {
      background: #f8f9fa;
      display: table-header-group !important;
    }
    
    .table tbody {
      display: table-row-group !important;
    }
    
    .table tr {
      display: table-row !important;
    }
    
    .table th {
      border-bottom: 2px solid #e9ecef;
      padding: 1rem;
      font-weight: 600;
      font-size: 0.875rem;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: table-cell !important;
      vertical-align: middle;
      text-align: center;
    }
    
    .table td {
      padding: 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #f1f3f5;
      display: table-cell !important;
      text-align: center;
    }
    
    .table td::before {
      display: none !important;
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
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <div class="main-content">
    <div class="page-header">
      <h1><i class="fa-solid fa-tags me-2"></i>Kelola Kategori Artikel</h1>
      <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('dashboard.articles') }}" class="btn-add" style="background: #6c757d;">
          <i class="fa-solid fa-arrow-left"></i>
          Kembali
        </a>
        <button class="btn-add" onclick="openCreateModal()">
          <i class="fa-solid fa-plus"></i>
          Tambah Kategori
        </button>
      </div>
    </div>
    
    <div class="content-card">
      <table class="table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Slug</th>
            <th>Deskripsi</th>
            <th>Urutan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="categoriesTableBody">
          @forelse($categories as $category)
            <tr>
              <td>
                <div style="font-weight: 500; color: #2F2F2F;">{{ $category->name }}</div>
              </td>
              <td>
                <code style="font-size: 0.8rem; color: #6c757d;">{{ $category->slug }}</code>
              </td>
              <td>
                <div style="font-size: 0.875rem; color: #6c757d;">{{ Str::limit($category->description ?? '-', 50) }}</div>
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
              <td colspan="6" class="text-center py-5 text-muted">
                <i class="fa-solid fa-tags mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                <p class="mb-0">Belum ada kategori. Klik "Tambah Kategori" untuk membuat kategori pertama.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create/Edit Modal -->
  <div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Tambah Kategori Baru</h5>
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
              <label class="form-label">Deskripsi</label>
              <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Urutan</label>
                <input type="number" class="form-control" id="categorySortOrder" name="sort_order" value="0" min="0">
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
    let categoryModal;
    let isEditMode = false;
    
    // Initialize modal
    document.addEventListener('DOMContentLoaded', function() {
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
    });
    
    function openCreateModal() {
      isEditMode = false;
      document.getElementById('modalTitle').textContent = 'Tambah Kategori Baru';
      document.getElementById('categoryForm').reset();
      document.getElementById('categoryId').value = '';
      document.getElementById('categorySortOrder').value = '0';
      document.getElementById('categoryIsActive').value = '1';
      categoryModal.show();
    }
    
    function editCategory(categoryId) {
      isEditMode = true;
      document.getElementById('modalTitle').textContent = 'Edit Kategori';
      
      fetch(`/dashboard/article-categories/${categoryId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById('categoryId').value = data.category.category_id;
            document.getElementById('categoryName').value = data.category.name;
            document.getElementById('categorySlug').value = data.category.slug;
            document.getElementById('categoryDescription').value = data.category.description || '';
            document.getElementById('categorySortOrder').value = data.category.sort_order || 0;
            document.getElementById('categoryIsActive').value = data.category.is_active ? '1' : '0';
            categoryModal.show();
          } else {
            Swal.fire('Error', data.message || 'Gagal memuat kategori', 'error');
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire('Error', 'Gagal memuat kategori', 'error');
        });
    }
    
    function deleteCategory(categoryId) {
      Swal.fire({
        title: 'Hapus Kategori?',
        text: 'Kategori yang dihapus tidak dapat dikembalikan. Pastikan tidak ada artikel yang menggunakan kategori ini.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          
          fetch(`/dashboard/article-categories/${categoryId}`, {
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
    
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const name = document.getElementById('categoryName').value;
      const slug = document.getElementById('categorySlug').value;
      const description = document.getElementById('categoryDescription').value;
      const sortOrder = parseInt(document.getElementById('categorySortOrder').value) || 0;
      const isActive = document.getElementById('categoryIsActive').value === '1';
      
      if (!name.trim()) {
        Swal.fire('Error', 'Nama kategori harus diisi', 'error');
        return;
      }
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      const url = isEditMode 
        ? `/dashboard/article-categories/${document.getElementById('categoryId').value}`
        : '/dashboard/article-categories';
      const method = isEditMode ? 'PUT' : 'POST';
      
      fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
          name: name,
          slug: slug || null,
          description: description || null,
          sort_order: sortOrder,
          is_active: isActive
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire('Berhasil', isEditMode ? 'Kategori berhasil diperbarui' : 'Kategori berhasil dibuat', 'success').then(() => {
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
    });
  </script>
</body>
</html>

