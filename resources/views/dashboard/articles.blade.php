<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Konten Artikel - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
  
  <!-- Quill Editor -->
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  
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
        font-size: 0.875rem;
        overflow-x: auto;
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
    
    .article-title {
      font-weight: 500;
      color: #2F2F2F;
      max-width: 400px;
    }
    
    .article-excerpt {
      color: #6c757d;
      font-size: 0.875rem;
      max-width: 300px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
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
    
    .modal-content {
      border-radius: 8px;
      border: none;
    }
    
    .modal-header {
      border-bottom: 1px solid #e9ecef;
      padding: 1.25rem 1.5rem;
    }
    
    .modal-body {
      padding: 1.5rem;
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
    
    #editor {
      min-height: 300px;
    }
    
    .ql-editor {
      min-height: 300px;
    }
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <div class="main-content">
        <div class="page-header">
      <h1><i class="fa-solid fa-newspaper me-2"></i>Konten Artikel</h1>
      <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('dashboard.article-categories') }}" class="btn-add" style="background: #3b82f6;">
          <i class="fa-solid fa-tags"></i>
          Kelola Kategori
        </a>
        <button class="btn-add" onclick="openCreateModal()">
          <i class="fa-solid fa-plus"></i>
          Buat Artikel Baru
        </button>
      </div>
    </div>
    
    <div class="content-card">
      <table class="table">
        <thead>
          <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Konten</th>
            <th>Penulis</th>
            <th>Tanggal Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="articlesTableBody">
          @forelse($articles as $article)
            <tr>
              <td>
                <div class="article-title">{{ $article->title }}</div>
              </td>
              <td>
                @if($article->categories && $article->categories->count() > 0)
                  @foreach($article->categories as $category)
                    <span class="badge bg-secondary me-1">{{ $category->name }}</span>
                  @endforeach
                @else
                  <span class="badge bg-secondary">Tidak ada kategori</span>
                @endif
              </td>
              <td>
                <div class="article-excerpt">{{ Str::limit(strip_tags($article->content), 100) }}</div>
              </td>
              <td>{{ $article->user->name ?? 'Admin' }}</td>
              <td>{{ $article->created_at->format('d M Y') }}</td>
              <td>
                <button class="btn-action btn-edit me-2" onclick="editArticle('{{ $article->article_id }}')">
                  <i class="fa-solid fa-edit"></i> Edit
                </button>
                <button class="btn-action btn-delete" onclick="deleteArticle('{{ $article->article_id }}')">
                  <i class="fa-solid fa-trash"></i> Hapus
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-5 text-muted">
                <i class="fa-solid fa-newspaper mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                <p class="mb-0">Belum ada artikel. Klik "Buat Artikel Baru" untuk membuat artikel pertama.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create/Edit Modal -->
  <div class="modal fade" id="articleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Buat Artikel Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="articleForm">
            <input type="hidden" id="articleId" name="article_id">
            
            <div class="mb-3">
              <label class="form-label">Judul Artikel *</label>
              <input type="text" class="form-control" id="articleTitle" name="title" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Foto Artikel (Featured Image)</label>
              <div class="mb-2">
                <input type="radio" name="image_source" value="url" id="imageUrl" checked onchange="toggleImageSource()">
                <label for="imageUrl" class="ms-1 me-3">URL</label>
                <input type="radio" name="image_source" value="upload" id="imageUpload" onchange="toggleImageSource()">
                <label for="imageUpload" class="ms-1">Upload File</label>
              </div>
              <div id="imageUrlInput">
                <input type="url" class="form-control" id="articleImageUrl" name="featured_image_url" placeholder="https://example.com/image.jpg">
              </div>
              <div id="imageUploadInput" style="display: none;">
                <input type="file" class="form-control" id="articleImageFile" name="featured_image_file" accept="image/*" onchange="previewImage(this)">
                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 5MB</small>
                <div id="imagePreview" class="mt-2" style="display: none;">
                  <img id="previewImg" src="" alt="Preview" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 1px solid #e9ecef;">
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Kategori Artikel *</label>
              <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                @foreach($categories as $category)
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="category_ids[]" value="{{ $category->category_id }}" id="category_{{ $category->category_id }}">
                    <label class="form-check-label" for="category_{{ $category->category_id }}">
                      {{ $category->name }}
                    </label>
                  </div>
                @endforeach
              </div>
              <small class="text-muted">Pilih satu atau lebih kategori</small>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Konten Artikel *</label>
              <div id="editor"></div>
              <textarea id="articleContent" name="content" style="display: none;"></textarea>
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
  <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
  
  <script>
    let quill;
    let articleModal;
    let isEditMode = false;
    
    // Initialize Quill Editor
    document.addEventListener('DOMContentLoaded', function() {
      quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
          toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'color': [] }, { 'background': [] }],
            ['link', 'image'],
            ['clean']
          ]
        }
      });
      
      articleModal = new bootstrap.Modal(document.getElementById('articleModal'));
    });
    
    function openCreateModal() {
      isEditMode = false;
      document.getElementById('modalTitle').textContent = 'Buat Artikel Baru';
      document.getElementById('articleForm').reset();
      document.getElementById('articleId').value = '';
      document.getElementById('articleImageUrl').value = '';
      document.getElementById('articleImageFile').value = '';
      document.getElementById('imagePreview').style.display = 'none';
      document.getElementById('imageUrl').checked = true;
      toggleImageSource();
      document.querySelectorAll('input[name="category_ids[]"]').forEach(cb => cb.checked = false);
      quill.setContents([]);
      articleModal.show();
    }
    
    function toggleImageSource() {
      const imageUrl = document.getElementById('imageUrl').checked;
      document.getElementById('imageUrlInput').style.display = imageUrl ? 'block' : 'none';
      document.getElementById('imageUploadInput').style.display = imageUrl ? 'none' : 'block';
      if (imageUrl) {
        document.getElementById('imagePreview').style.display = 'none';
      }
    }
    
    function previewImage(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('previewImg').src = e.target.result;
          document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
      }
    }
    
    function editArticle(articleId) {
      isEditMode = true;
      document.getElementById('modalTitle').textContent = 'Edit Artikel';
      
      fetch(`/dashboard/articles/${articleId}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.getElementById('articleId').value = data.article.article_id;
            document.getElementById('articleTitle').value = data.article.title;
            document.getElementById('articleImageUrl').value = data.article.featured_image || '';
            
            // Clear and set checkboxes for categories
            document.querySelectorAll('input[name="category_ids[]"]').forEach(cb => cb.checked = false);
            if (data.article.categories && data.article.categories.length > 0) {
              data.article.categories.forEach(cat => {
                const checkbox = document.getElementById('category_' + cat.category_id);
                if (checkbox) checkbox.checked = true;
              });
            }
            
            quill.root.innerHTML = data.article.content;
            articleModal.show();
          } else {
            Swal.fire('Error', data.message || 'Gagal memuat artikel', 'error');
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire('Error', 'Gagal memuat artikel', 'error');
        });
    }
    
    function deleteArticle(articleId) {
      Swal.fire({
        title: 'Hapus Artikel?',
        text: 'Artikel yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          
          fetch(`/dashboard/articles/${articleId}`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              Swal.fire('Berhasil', 'Artikel berhasil dihapus', 'success').then(() => {
                location.reload();
              });
            } else {
              Swal.fire('Error', data.message || 'Gagal menghapus artikel', 'error');
            }
          })
          .catch(err => {
            console.error('Error:', err);
            Swal.fire('Error', 'Gagal menghapus artikel', 'error');
          });
        }
      });
    }
    
    document.getElementById('articleForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const title = document.getElementById('articleTitle').value;
      const content = quill.root.innerHTML;
      const articleId = document.getElementById('articleId').value;
      
      // Get selected categories
      const selectedCategories = Array.from(document.querySelectorAll('input[name="category_ids[]"]:checked')).map(cb => cb.value);
      
      if (!title.trim()) {
        Swal.fire('Error', 'Judul artikel harus diisi', 'error');
        return;
      }
      
      if (selectedCategories.length === 0) {
        Swal.fire('Error', 'Pilih minimal satu kategori', 'error');
        return;
      }
      
      if (!content.trim() || content === '<p><br></p>') {
        Swal.fire('Error', 'Konten artikel harus diisi', 'error');
        return;
      }
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      const url = isEditMode 
        ? `/dashboard/articles/${articleId}`
        : '/dashboard/articles';
      const method = isEditMode ? 'POST' : 'POST'; // Use POST for both, Laravel will handle PUT via _method
      
      // Use FormData for file upload
      const formData = new FormData();
      formData.append('title', title);
      formData.append('content', content);
      formData.append('category_ids', JSON.stringify(selectedCategories));
      
      if (isEditMode) {
        formData.append('_method', 'PUT');
      }
      
      // Handle image
      const imageSource = document.querySelector('input[name="image_source"]:checked').value;
      if (imageSource === 'url') {
        const imageUrl = document.getElementById('articleImageUrl').value;
        if (imageUrl) {
          formData.append('featured_image_url', imageUrl);
        }
      } else {
        const imageFile = document.getElementById('articleImageFile').files[0];
        if (imageFile) {
          formData.append('featured_image_file', imageFile);
        }
      }
      
      fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken
        },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire('Berhasil', isEditMode ? 'Artikel berhasil diperbarui' : 'Artikel berhasil dibuat', 'success').then(() => {
            location.reload();
          });
        } else {
          Swal.fire('Error', data.message || 'Gagal menyimpan artikel', 'error');
        }
      })
      .catch(err => {
        console.error('Error:', err);
        Swal.fire('Error', 'Gagal menyimpan artikel', 'error');
      });
    });
  </script>
</body>
</html>

