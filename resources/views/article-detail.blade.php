<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $article->title }} - ChickPatrol</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
  <style>
    :root {
      --primary-green: #69B578;
      --dark-green: #5a8c64;
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    
    .article-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 2rem 1.5rem;
    }
    
    .article-header {
      margin-bottom: 2rem;
    }
    
    .article-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
      line-height: 1.3;
    }
    
    .article-meta {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 1rem;
    }
    
    .article-content {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      line-height: 1.8;
      color: #2F2F2F;
    }
    
    .article-content h1,
    .article-content h2,
    .article-content h3 {
      margin-top: 2rem;
      margin-bottom: 1rem;
      color: #2F2F2F;
    }
    
    .article-content p {
      margin-bottom: 1.5rem;
    }
    
    .article-content ul,
    .article-content ol {
      margin-bottom: 1.5rem;
      padding-left: 2rem;
    }
    
    .article-content img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      margin: 1.5rem 0;
    }
    
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--primary-green);
      text-decoration: none;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }
    
    .back-link:hover {
      color: var(--dark-green);
    }
    @media (max-width: 768px) {
      main {
        padding: 1rem !important;
      }
      .article-container {
        padding: 1rem !important;
      }
      .article-title {
        font-size: 1.75rem !important;
      }
      .article-meta {
        flex-direction: column;
        align-items: flex-start;
      }
      .article-image {
        height: 250px !important;
      }
      .comment-section {
        padding: 1rem !important;
      }
    }
  </style>
</head>
<body class="min-h-screen">
  @include('partials.navbar')

  <main class="article-container">
    <a href="/articles" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      Kembali ke Daftar Artikel
    </a>
    
    @if($article->featured_image)
    @php
      $imageUrl = null;
      if ($article->featured_image) {
        if (strpos($article->featured_image, 'http') === 0 || strpos($article->featured_image, 'https') === 0) {
          $imageUrl = $article->featured_image;
        } else {
          $path = ltrim($article->featured_image, '/');
          $imageUrl = asset($path);
        }
      }
    @endphp
    <div class="mb-4" style="width: 100%; height: 450px; overflow: hidden; border-radius: 12px; margin-bottom: 2rem;">
      <img src="{{ $imageUrl }}" alt="{{ $article->title }}" class="w-100 h-100" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none';">
    </div>
    @endif
    
    <div class="article-header">
      @if($article->categories && $article->categories->count() > 0)
        <div class="mb-3">
          @foreach($article->categories as $category)
            <span class="badge bg-primary me-2" style="text-transform: uppercase; font-size: 0.75rem; padding: 0.5rem 1rem; font-weight: 600; letter-spacing: 0.5px; background-color: #3b82f6 !important; border: none;">{{ strtoupper($category->name) }}</span>
          @endforeach
        </div>
      @endif
      <h1 class="article-title">{{ $article->title }}</h1>
      <div class="article-meta" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 1rem;">
        <span style="font-weight: 600;">BY {{ strtoupper($article->user->name ?? 'ADMIN') }}</span>
        <span>•</span>
        <span>{{ strtoupper($article->created_at->format('d M Y')) }}</span>
      </div>
    </div>
    
    <div class="article-content">
      {!! $article->content !!}
    </div>
    
    <!-- Comments Section -->
    <div class="comments-section" style="margin-top: 3rem;">
      <h3 style="font-size: 1.5rem; font-weight: 600; color: #2F2F2F; margin-bottom: 1.5rem;">
        <i class="fa-solid fa-comments me-2" style="color: var(--primary-green);"></i>
        Komentar ({{ $article->allComments()->count() }})
      </h3>
      
      @auth
      <!-- Comment Form -->
      <div class="comment-form-card" style="background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <form id="commentForm">
          <textarea id="commentContent" class="form-control" rows="3" placeholder="Tulis komentar..." style="border: 1px solid #e9ecef; border-radius: 8px; padding: 0.75rem; font-size: 0.9375rem; resize: none;" maxlength="1000"></textarea>
          <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted"><span id="charCount">0</span>/1000</small>
            <button type="submit" class="btn btn-primary" style="background: var(--primary-green); border: none; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;">
              <i class="fa-solid fa-paper-plane me-1"></i> Kirim
            </button>
          </div>
        </form>
      </div>
      @else
      <div class="alert alert-info" style="background: #f0f9f2; border: 1px solid var(--primary-green); color: #155724; border-radius: 8px; padding: 1rem;">
        <i class="fa-solid fa-info-circle me-2"></i>
        <a href="{{ route('login') }}" style="color: var(--primary-green); text-decoration: underline;">Login</a> untuk menulis komentar
      </div>
      @endauth
      
      <!-- Comments List -->
      <div id="commentsList">
        @forelse($article->comments as $comment)
          @include('partials.comment-item', ['comment' => $comment, 'level' => 0])
        @empty
          <div class="text-center py-5" style="color: #6c757d;">
            <i class="fa-solid fa-comments" style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;"></i>
            <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
          </div>
        @endforelse
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white mt-12 border-t border-gray-200">
    <div class="container py-4">
      <p class="text-center text-sm text-gray-600 mb-0">©2025, ChickPatrol. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/navbar.js') }}"></script>
  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const articleId = '{{ $article->article_id }}';
    
    // Character counter
    document.getElementById('commentContent')?.addEventListener('input', function() {
      document.getElementById('charCount').textContent = this.value.length;
    });
    
    // Submit comment
    document.getElementById('commentForm')?.addEventListener('submit', async function(e) {
      e.preventDefault();
      const content = document.getElementById('commentContent').value.trim();
      
      if (!content) return;
      
      try {
        const response = await fetch(`/api/articles/${articleId}/comments`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ content })
        });
        
        const result = await response.json();
        
        if (result.success) {
          location.reload();
        } else {
          alert('Gagal mengirim komentar');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
      }
    });
    
    // Show reply form
    function showReplyForm(commentId) {
      document.getElementById(`replyForm-${commentId}`).style.display = 'block';
    }
    
    // Hide reply form
    function hideReplyForm(commentId) {
      document.getElementById(`replyForm-${commentId}`).style.display = 'none';
      document.querySelector(`#replyForm-${commentId} textarea`).value = '';
    }
    
    // Submit reply
    async function submitReply(e, parentId) {
      e.preventDefault();
      const content = e.target.querySelector('textarea').value.trim();
      
      if (!content) return;
      
      try {
        const response = await fetch(`/api/articles/${articleId}/comments`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ content, parent_id: parentId })
        });
        
        const result = await response.json();
        
        if (result.success) {
          location.reload();
        } else {
          alert('Gagal mengirim balasan');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
      }
    }
    
    // Delete comment
    async function deleteComment(commentId) {
      const result = await Swal.fire({
        title: 'Hapus komentar ini?',
        text: 'Komentar yang dihapus tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      });
      
      if (!result.isConfirmed) return;
      
      try {
        const response = await fetch(`/api/comments/${commentId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Remove comment from DOM with animation
          const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
          if (commentElement) {
            commentElement.style.transition = 'opacity 0.3s';
            commentElement.style.opacity = '0';
            setTimeout(() => {
              commentElement.remove();
              // Update comment count
              const commentCount = document.querySelectorAll('.comment-item').length;
              const countText = document.querySelector('.comments-section h3');
              if (countText) {
                countText.innerHTML = `<i class="fa-solid fa-comments me-2" style="color: var(--primary-green);"></i> Komentar (${commentCount})`;
              }
            }, 300);
          } else {
            location.reload();
          }
          
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Komentar berhasil dihapus',
            confirmButtonColor: '#69B578',
            timer: 2000,
            showConfirmButton: false
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: data.message || 'Gagal menghapus komentar',
            confirmButtonColor: '#EF4444'
          });
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Terjadi kesalahan',
          text: 'Gagal menghapus komentar',
          confirmButtonColor: '#EF4444'
        });
      }
    }
  </script>
</body>
</html>

