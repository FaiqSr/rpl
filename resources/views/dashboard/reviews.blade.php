<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Ulasan Produk - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  
  <!-- Google Fonts - Inter -->
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
    }
    
    .page-header {
      margin-bottom: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .page-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2F2F2F;
      margin: 0;
    }
    
    .stats-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .stat-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1.25rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
    }
    
    .stat-icon.total { background: #E3F2FD; color: #2196F3; }
    .stat-icon.unreplied { background: #FFF3E0; color: #FF9800; }
    .stat-icon.rating { background: #E8F5E9; color: #4CAF50; }
    .stat-icon.replied { background: #F3E5F5; color: #9C27B0; }
    
    .stat-info h3 {
      font-size: 1.5rem;
      font-weight: 700;
      margin: 0;
      color: #2F2F2F;
    }
    
    .stat-info p {
      font-size: 0.875rem;
      color: #6c757d;
      margin: 0;
    }
    
    .filter-bar {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1.25rem;
      margin-bottom: 1.5rem;
    }
    
    .filter-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1rem;
    }
    
    .filter-row:last-child {
      margin-bottom: 0;
    }
    
    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .filter-group label {
      font-size: 0.875rem;
      font-weight: 500;
      color: #2F2F2F;
    }
    
    .filter-group input,
    .filter-group select {
      padding: 0.5rem;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      font-size: 0.875rem;
    }
    
    .filter-actions {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-top: 1rem;
    }
    
    .content-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    
    .reviews-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .bulk-actions {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }
    
    .review-item {
      border-bottom: 1px solid #f0f0f0;
      padding: 1.25rem 0;
      position: relative;
    }
    
    .review-item:last-child {
      border-bottom: none;
    }
    
    .review-checkbox {
      position: absolute;
      top: 1.25rem;
      left: 0;
    }
    
    .review-content {
      margin-left: 2rem;
    }
    
    .review-header {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 0.75rem;
    }
    
    .review-product {
      flex: 1;
    }
    
    .review-product-name {
      font-size: 1rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .review-product-info {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .review-rating {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .review-rating-stars {
      display: flex;
      gap: 2px;
    }
    
    .review-rating-stars i {
      font-size: 0.875rem;
      color: #FFC107;
    }
    
    .review-rating-stars i.fa-regular {
      color: #E5E7EB;
    }
    
    .badge-unreplied {
      background: #FFF3E0;
      color: #FF9800;
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-left: 0.5rem;
    }
    
    .review-user {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
    }
    
    .review-user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #22C55E;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 0.875rem;
    }
    
    .review-user-info {
      flex: 1;
    }
    
    .review-user-name {
      font-size: 0.875rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.125rem;
    }
    
    .review-date {
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .review-text {
      font-size: 0.875rem;
      color: #4B5563;
      line-height: 1.6;
      margin-bottom: 0.75rem;
    }
    
    .review-images {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-top: 0.75rem;
    }
    
    .review-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
      cursor: pointer;
      loading: lazy;
    }
    
    .review-image:hover {
      opacity: 0.8;
    }
    
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: #6c757d;
    }
    
    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.3;
    }
    
    .pagination-wrapper {
      margin-top: 1.5rem;
      display: flex;
      justify-content: center;
    }
    
    /* Swipe Actions for Mobile - Hidden on Desktop */
    .swipe-actions {
      display: none !important; /* Hidden by default on desktop */
      visibility: hidden;
    }
    
    @media (max-width: 768px) {
      .swipe-actions {
        display: flex !important; /* Show on mobile */
        visibility: visible;
      }
      .review-item {
        position: relative;
        overflow: hidden;
        touch-action: pan-y;
      }
      
      .review-content {
        transition: transform 0.3s ease;
        background: white;
      }
      
      .swipe-actions {
        display: flex; /* Show on mobile */
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        align-items: center;
        gap: 0.5rem;
        padding: 0 1rem;
        background: #22C55E;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 10;
      }
      
      .review-item.swiped .swipe-actions {
        transform: translateX(0);
      }
      
      .review-item.swiped .review-content {
        transform: translateX(-120px);
      }
      
      .swipe-action-btn {
        background: white;
        color: #22C55E;
        border: none;
        padding: 0.5rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1.25rem;
      }
    }
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <h1>Ulasan Produk</h1>
      <div>
        <a href="{{ route('dashboard.reviews.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="btn btn-sm btn-outline-success me-2">
          <i class="fa-solid fa-file-csv me-1"></i> Export CSV
        </a>
        <a href="{{ route('dashboard.reviews.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="btn btn-sm btn-outline-danger">
          <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
        </a>
      </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-cards">
      <div class="stat-card">
        <div class="stat-icon total">
          <i class="fa-solid fa-star"></i>
        </div>
        <div class="stat-info">
          <h3>{{ number_format($stats['total']) }}</h3>
          <p>Total Ulasan</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon unreplied">
          <i class="fa-solid fa-exclamation-circle"></i>
        </div>
        <div class="stat-info">
          <h3>{{ number_format($stats['unreplied']) }}</h3>
          <p>Belum Dibalas</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon rating">
          <i class="fa-solid fa-star-half-stroke"></i>
        </div>
        <div class="stat-info">
          <h3>{{ number_format($stats['avg_rating'], 1) }}</h3>
          <p>Rating Rata-rata</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon replied">
          <i class="fa-solid fa-check-circle"></i>
        </div>
        <div class="stat-info">
          <h3>{{ number_format($stats['replied']) }}</h3>
          <p>Sudah Dibalas</p>
        </div>
      </div>
    </div>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
      <form method="GET" action="{{ route('dashboard.reviews') }}" id="filterForm">
        <div class="filter-row">
          <div class="filter-group" style="grid-column: 1 / -1;">
            <label>Pencarian</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk, pelanggan, atau teks ulasan..." class="form-control">
          </div>
        </div>
        <div class="filter-row">
          <div class="filter-group">
            <label>Rating</label>
            <select name="rating" class="form-select">
              <option value="">Semua Rating</option>
              @for($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Bintang</option>
              @endfor
            </select>
          </div>
          <div class="filter-group">
            <label>Produk</label>
            <select name="product_id" class="form-select">
              <option value="">Semua Produk</option>
              @foreach($products as $product)
                <option value="{{ $product->product_id }}" {{ request('product_id') == $product->product_id ? 'selected' : '' }}>{{ $product->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-group">
            <label>Status</label>
            <select name="status" class="form-select">
              <option value="">Semua Status</option>
              <option value="unreplied" {{ request('status') == 'unreplied' ? 'selected' : '' }}>Belum Dibalas</option>
              <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Sudah Dibalas</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Urutkan</label>
            <select name="sort" class="form-select">
              <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Terbaru</option>
              <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>Terlama</option>
              <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>Rating Tertinggi</option>
              <option value="rating_asc" {{ request('sort') == 'rating_asc' ? 'selected' : '' }}>Rating Terendah</option>
              <option value="product_asc" {{ request('sort') == 'product_asc' ? 'selected' : '' }}>Nama Produk A-Z</option>
              <option value="product_desc" {{ request('sort') == 'product_desc' ? 'selected' : '' }}>Nama Produk Z-A</option>
              <option value="user_asc" {{ request('sort') == 'user_asc' ? 'selected' : '' }}>Nama Pelanggan A-Z</option>
              <option value="user_desc" {{ request('sort') == 'user_desc' ? 'selected' : '' }}>Nama Pelanggan Z-A</option>
            </select>
          </div>
        </div>
        <div class="filter-row">
          <div class="filter-group">
            <label>Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
          </div>
          <div class="filter-group">
            <label>Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
          </div>
        </div>
        <div class="filter-actions">
          <button type="submit" class="btn btn-sm" style="background: #22C55E; color: white; border: none;">
            <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
          </button>
          <a href="{{ route('dashboard.reviews') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-times me-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
    
    <!-- Reviews List -->
    <div class="content-card">
      @if($reviews->count() > 0)
        <div class="reviews-header">
          <div>
            <strong>Menampilkan {{ $reviews->firstItem() }} - {{ $reviews->lastItem() }} dari {{ $reviews->total() }} ulasan</strong>
          </div>
          <div class="bulk-actions" id="bulkActions" style="display: none;">
            <span id="selectedCount" class="me-2"></span>
            <button onclick="openBulkReplyModal()" class="btn btn-sm" style="background: #22C55E; color: white; border: none;">
              <i class="fa-solid fa-reply-all me-1"></i> Balas Semua
            </button>
            <button onclick="clearSelection()" class="btn btn-sm btn-outline-secondary">
              <i class="fa-solid fa-times me-1"></i> Batal
            </button>
          </div>
        </div>
        
        @foreach($reviews as $review)
          <div class="review-item" data-review-id="{{ $review->review_id }}" data-product-id="{{ $review->product_id }}" ontouchstart="handleTouchStart(event, '{{ $review->review_id }}')" ontouchmove="handleTouchMove(event, '{{ $review->review_id }}')" ontouchend="handleTouchEnd(event, '{{ $review->review_id }}')">
            <input type="checkbox" class="review-checkbox form-check-input" value="{{ $review->review_id }}" onchange="updateBulkActions()">
            <div class="review-content">
              <div class="review-header">
                <div class="review-product">
                  <div class="review-product-name">
                    @if($review->product)
                      <a href="{{ route('product.detail', $review->product->product_id) }}" style="color: #22C55E; text-decoration: none;">
                        {{ $review->product->name }}
                      </a>
                    @else
                      <span style="color: #9CA3AF;">Produk telah dihapus</span>
                    @endif
                    @if($review->replies->count() == 0)
                      <span class="badge-unreplied">Belum Dibalas</span>
                    @endif
                  </div>
                  @if($review->order)
                  <div class="review-product-info">
                    Order #{{ substr($review->order->order_id, 0, 8) }} • {{ $review->order->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                  </div>
                  @endif
                </div>
                <div class="review-rating">
                  <div class="review-rating-stars">
                    @for($i = 1; $i <= 5; $i++)
                      <i class="fa-star {{ $i <= $review->rating ? 'fa-solid' : 'fa-regular' }}"></i>
                    @endfor
                  </div>
                  <span style="font-size: 0.875rem; color: #6c757d; font-weight: 500;">{{ $review->rating }}/5</span>
                </div>
              </div>
              
              <div class="review-user">
                <div class="review-user-avatar">
                  {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="review-user-info">
                  <div class="review-user-name">{{ $review->user->name ?? 'User' }}</div>
                  <div class="review-date">{{ $review->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                </div>
              </div>
              
              @if($review->review)
              <div class="review-text">
                {{ $review->review }}
              </div>
              @endif
              
              @if(!empty($review->image_urls) && is_array($review->image_urls) && count($review->image_urls) > 0)
              <div class="review-images">
                @foreach($review->image_urls as $imageUrl)
                  <img src="{{ $imageUrl }}" alt="Review Image" class="review-image" onclick="openImageModal('{{ $imageUrl }}')" onerror="this.onerror=null; this.style.display='none';" loading="lazy">
                @endforeach
              </div>
              @elseif($review->image && is_array($review->image) && count($review->image) > 0)
              <div class="review-images">
                @foreach($review->image as $img)
                  @php
                    $imageUrl = $img;
                    if ($imageUrl && !preg_match('/^(https?:\/\/|data:)/', $imageUrl)) {
                      if (strpos($imageUrl, 'storage/reviews/') !== false) {
                        $imageUrl = asset($imageUrl);
                      } else {
                        $imageUrl = asset('storage/' . $imageUrl);
                      }
                    }
                  @endphp
                  <img src="{{ $imageUrl }}" alt="Review Image" class="review-image" onclick="openImageModal('{{ $imageUrl }}')" onerror="this.onerror=null; this.style.display='none';" loading="lazy">
                @endforeach
              </div>
              @endif
              
              <!-- Reply Button -->
              <div class="mt-3">
                <button onclick="toggleReplyForm('{{ $review->review_id }}')" class="btn btn-sm btn-outline-primary" style="border-color: #22C55E; color: #22C55E;">
                  <i class="fa-solid fa-reply me-1"></i> Balas Ulasan
                </button>
              </div>
              
              <!-- Swipe Actions (Mobile Only) -->
              <div class="swipe-actions">
                <button class="swipe-action-btn" onclick="toggleReplyForm('{{ $review->review_id }}'); closeSwipe('{{ $review->review_id }}');" title="Balas">
                  <i class="fa-solid fa-reply"></i>
                </button>
              </div>
              
              <!-- Reply Form (hidden by default) -->
              <div id="replyForm-{{ $review->review_id }}" class="mt-3" style="display: none;">
                <form onsubmit="submitReply(event, '{{ $review->review_id }}')" class="space-y-2">
                  <div class="mb-2">
                    <label class="form-label" style="font-size: 0.75rem; color: #6c757d; margin-bottom: 0.25rem;">Template Balasan Cepat:</label>
                    <select class="form-select form-select-sm" id="quickReplyTemplate-{{ $review->review_id }}" onchange="applyQuickReply('{{ $review->review_id }}', this.value)">
                      <option value="">Pilih Template...</option>
                      <option value="thank_you">Terima kasih atas ulasannya</option>
                      <option value="apology">Mohon maaf atas ketidaknyamanan</option>
                      <option value="improvement">Kami akan perbaiki</option>
                      <option value="contact">Silakan hubungi kami untuk bantuan lebih lanjut</option>
                      <option value="positive">Senang mendengar pengalaman positif Anda</option>
                    </select>
                  </div>
                  <textarea name="reply" id="replyTextarea-{{ $review->review_id }}" rows="3" class="form-control" placeholder="Tulis balasan untuk ulasan ini..." required maxlength="1000"></textarea>
                  <div class="d-flex justify-content-end gap-2 mt-2">
                    <button type="button" onclick="toggleReplyForm('{{ $review->review_id }}')" class="btn btn-sm btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-sm" style="background: #22C55E; color: white; border: none;">Kirim Balasan</button>
                  </div>
                </form>
              </div>
              
              <!-- Replies -->
              @if($review->replies && $review->replies->count() > 0)
              <div class="mt-4 ml-4" style="border-left: 2px solid #e5e7eb; padding-left: 1rem;">
                @foreach($review->replies as $reply)
                <div class="bg-gray-50 rounded-lg p-3 mb-2" id="reply-{{ $reply->review_id }}">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <div class="review-user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">
                        {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                      </div>
                      <div>
                        <div style="font-size: 0.875rem; font-weight: 600; color: #2F2F2F;">{{ $reply->user->name ?? 'User' }}</div>
                        <div style="font-size: 0.75rem; color: #6c757d;">{{ $reply->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                      </div>
                    </div>
                    @if($reply->user_id === Auth::id())
                      <button onclick="deleteReply('{{ $review->review_id }}', '{{ $reply->review_id }}')" class="btn btn-sm btn-link text-danger p-0" title="Hapus balasan">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    @endif
                  </div>
                  <p style="font-size: 0.875rem; color: #4B5563; margin: 0;">{{ $reply->review }}</p>
                </div>
                @endforeach
              </div>
              @endif
            </div>
          </div>
        @endforeach
        
        <!-- Pagination -->
        <div class="pagination-wrapper">
          {{ $reviews->links() }}
        </div>
      @else
        <div class="empty-state">
          <i class="fa-solid fa-star"></i>
          <h3 style="font-size: 1.125rem; font-weight: 600; color: #2F2F2F; margin-bottom: 0.5rem;">Belum ada ulasan</h3>
          <p style="font-size: 0.875rem; color: #6c757d;">Belum ada ulasan produk dari pelanggan.</p>
        </div>
      @endif
    </div>
  </main>
  
  <!-- Image Modal -->
  <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0 text-center">
          <img id="modalImage" src="" alt="Review Image" style="max-width: 100%; max-height: 80vh; object-fit: contain;">
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bulk Reply Modal -->
  <div class="modal fade" id="bulkReplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Balas Ulasan Terpilih</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="bulkReplyForm">
            <div class="mb-3">
              <label class="form-label">Template Balasan Cepat:</label>
              <select class="form-select form-select-sm mb-2" id="bulkQuickReplyTemplate" onchange="applyBulkQuickReply(this.value)">
                <option value="">Pilih Template...</option>
                <option value="thank_you">Terima kasih atas ulasannya</option>
                <option value="apology">Mohon maaf atas ketidaknyamanan</option>
                <option value="improvement">Kami akan perbaiki</option>
                <option value="contact">Silakan hubungi kami untuk bantuan lebih lanjut</option>
                <option value="positive">Senang mendengar pengalaman positif Anda</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Balasan (akan dikirim ke semua ulasan terpilih)</label>
              <textarea name="reply" id="bulkReplyTextarea" rows="4" class="form-control" placeholder="Tulis balasan..." required maxlength="1000"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn" style="background: #22C55E; color: white; border: none;" onclick="submitBulkReply()">Kirim Balasan</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  
  <script>
    // Swipe Actions for Mobile
    let touchStartX = 0;
    let touchEndX = 0;
    let currentSwipeId = null;
    
    function handleTouchStart(e, reviewId) {
      if (window.innerWidth > 768) return; // Only on mobile
      touchStartX = e.changedTouches[0].screenX;
      currentSwipeId = reviewId;
    }
    
    function handleTouchMove(e, reviewId) {
      if (window.innerWidth > 768) return;
      e.preventDefault();
    }
    
    function handleTouchEnd(e, reviewId) {
      if (window.innerWidth > 768) return;
      touchEndX = e.changedTouches[0].screenX;
      const swipeThreshold = 50;
      const diff = touchStartX - touchEndX;
      
      const reviewItem = document.querySelector(`[data-review-id="${reviewId}"]`);
      if (!reviewItem) return;
      
      if (diff > swipeThreshold) {
        // Swipe left - show actions
        reviewItem.classList.add('swiped');
      } else if (diff < -swipeThreshold) {
        // Swipe right - hide actions
        reviewItem.classList.remove('swiped');
      }
    }
    
    function closeSwipe(reviewId) {
      const reviewItem = document.querySelector(`[data-review-id="${reviewId}"]`);
      if (reviewItem) {
        reviewItem.classList.remove('swiped');
      }
    }
    
    // Close swipe when clicking outside
    document.addEventListener('click', function(e) {
      if (window.innerWidth <= 768) {
        const reviewItems = document.querySelectorAll('.review-item.swiped');
        reviewItems.forEach(item => {
          if (!item.contains(e.target)) {
            item.classList.remove('swiped');
          }
        });
      }
    });
    
    function openImageModal(imageUrl) {
      document.getElementById('modalImage').src = imageUrl;
      const modal = new bootstrap.Modal(document.getElementById('imageModal'));
      modal.show();
    }
    
    // Bulk Actions
    function updateBulkActions() {
      const checkboxes = document.querySelectorAll('.review-checkbox:checked');
      const count = checkboxes.length;
      const bulkActions = document.getElementById('bulkActions');
      const selectedCount = document.getElementById('selectedCount');
      
      if (count > 0) {
        bulkActions.style.display = 'flex';
        selectedCount.textContent = count + ' ulasan terpilih';
      } else {
        bulkActions.style.display = 'none';
      }
    }
    
    function clearSelection() {
      document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = false);
      updateBulkActions();
    }
    
    function openBulkReplyModal() {
      const checkboxes = document.querySelectorAll('.review-checkbox:checked');
      if (checkboxes.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih setidaknya satu ulasan',
          confirmButtonColor: '#22C55E'
        });
        return;
      }
      const modal = new bootstrap.Modal(document.getElementById('bulkReplyModal'));
      modal.show();
    }
    
    // Bulk Quick Reply Templates (same as individual)
    function applyBulkQuickReply(templateKey) {
      if (templateKey && quickReplyTemplates[templateKey]) {
        const textarea = document.getElementById('bulkReplyTextarea');
        if (textarea) {
          textarea.value = quickReplyTemplates[templateKey];
          // Reset dropdown
          const select = document.getElementById('bulkQuickReplyTemplate');
          if (select) select.value = '';
        }
      }
    }
    
    async function submitBulkReply() {
      const checkboxes = document.querySelectorAll('.review-checkbox:checked');
      const reviewIds = Array.from(checkboxes).map(cb => cb.value);
      const replyText = document.getElementById('bulkReplyTextarea')?.value.trim() || '';
      
      if (!replyText) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Silakan isi balasan terlebih dahulu',
          confirmButtonColor: '#22C55E'
        });
        return;
      }
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      
      try {
        const response = await fetch('/api/dashboard/reviews/bulk-reply', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            review_ids: reviewIds,
            reply: replyText
          })
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: result.message,
            confirmButtonColor: '#22C55E',
            timer: 2000
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(result.message || 'Gagal mengirim balasan');
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: error.message || 'Terjadi kesalahan saat mengirim balasan',
          confirmButtonColor: '#dc3545'
        });
      }
    }
    
    // Quick Reply Templates
    const quickReplyTemplates = {
      'thank_you': 'Terima kasih atas ulasan dan feedback Anda. Kami sangat menghargai masukan ini dan akan terus berusaha memberikan pelayanan terbaik.',
      'apology': 'Mohon maaf atas ketidaknyamanan yang Anda alami. Kami akan segera menindaklanjuti dan memperbaiki pelayanan kami.',
      'improvement': 'Terima kasih atas feedback Anda. Kami akan menindaklanjuti dan melakukan perbaikan sesuai dengan saran Anda.',
      'contact': 'Terima kasih atas ulasan Anda. Jika ada yang perlu dibantu lebih lanjut, silakan hubungi kami melalui chat atau kontak yang tersedia.',
      'positive': 'Terima kasih banyak atas ulasan positif Anda! Kami senang mendengar bahwa Anda puas dengan produk dan pelayanan kami. Kami akan terus berusaha memberikan yang terbaik.'
    };
    
    function applyQuickReply(reviewId, templateKey) {
      if (templateKey && quickReplyTemplates[templateKey]) {
        const textarea = document.getElementById('replyTextarea-' + reviewId);
        if (textarea) {
          textarea.value = quickReplyTemplates[templateKey];
          // Reset dropdown
          const select = document.getElementById('quickReplyTemplate-' + reviewId);
          if (select) select.value = '';
        }
      }
    }
    
    // Reply functions
    function toggleReplyForm(reviewId) {
      const form = document.getElementById('replyForm-' + reviewId);
      if (form) {
        const isHidden = form.style.display === 'none';
        form.style.display = isHidden ? 'block' : 'none';
        // Reset form when closing
        if (!isHidden) {
          const textarea = document.getElementById('replyTextarea-' + reviewId);
          const select = document.getElementById('quickReplyTemplate-' + reviewId);
          if (textarea) textarea.value = '';
          if (select) select.value = '';
        }
      }
    }
    
    async function submitReply(e, reviewId) {
      e.preventDefault();
      const form = e.target;
      const replyText = form.querySelector('textarea[name="reply"]').value.trim();
      
      if (!replyText) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Silakan isi balasan terlebih dahulu',
          confirmButtonColor: '#22C55E'
        });
        return;
      }
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      
      try {
        const response = await fetch(`/api/dashboard/reviews/${reviewId}/reply`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            reply: replyText
          })
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Balasan berhasil dikirim',
            confirmButtonColor: '#22C55E',
            timer: 2000
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(result.message || 'Gagal mengirim balasan');
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: error.message || 'Terjadi kesalahan saat mengirim balasan',
          confirmButtonColor: '#dc3545'
        });
      }
    }
    
    async function deleteReply(reviewId, replyId) {
      const result = await Swal.fire({
        title: 'Hapus Balasan?',
        text: 'Balasan yang dihapus tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      });
      
      if (result.isConfirmed) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        try {
          const reviewElement = document.querySelector(`[data-review-id="${reviewId}"]`);
          const productId = reviewElement ? reviewElement.getAttribute('data-product-id') : null;
          
          if (!productId) {
            throw new Error('Product ID tidak ditemukan');
          }
          
          const response = await fetch(`/api/products/${productId}/reviews/${reviewId}/reply/${replyId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            }
          });
          
          const data = await response.json();
          
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: 'Balasan berhasil dihapus',
              confirmButtonColor: '#22C55E',
              timer: 2000
            }).then(() => {
              location.reload();
            });
          } else {
            throw new Error(data.message || 'Gagal menghapus balasan');
          }
        } catch (error) {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: error.message || 'Terjadi kesalahan saat menghapus balasan',
            confirmButtonColor: '#dc3545'
          });
        }
      }
    }
  </script>
</body>
</html>
