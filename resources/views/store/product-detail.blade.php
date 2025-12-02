<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $product->name ?? 'Detail Produk' }} - ChickPatrol</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
  <style>
    :root {
      --primary-green: #69B578;
      --dark-green: #5a8c64;
      --cream: #F5E6D3;
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    .btn-primary { padding: 0.75rem 2rem; border: none; border-radius: 8px; background: var(--primary-green); color: white; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-primary:hover { background: var(--dark-green); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(105, 181, 120, 0.3); }
    .product-image { width: 100%; height: 400px; object-fit: cover; border-radius: 12px; background: #f5f5f5; }
    .qty-input { width: 80px; text-align: center; padding: 0.5rem; border: 1px solid #e9ecef; border-radius: 6px; }
    .hidden { display: none !important; }
    
    /* Review Filter Styles */
    .review-filter-btn {
      padding: 0.625rem 1.25rem;
      border: 1.5px solid #E5E7EB;
      border-radius: 10px;
      background: #FFFFFF;
      color: #374151;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      white-space: nowrap;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    
    .review-filter-btn:hover {
      border-color: #69B578;
      background: #F0FDF4;
      color: #69B578;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(105, 181, 120, 0.2);
    }
    
    .review-filter-btn.active {
      background: linear-gradient(135deg, #69B578 0%, #5a9a68 100%);
      border-color: #69B578;
      color: #FFFFFF;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(105, 181, 120, 0.35);
    }
    
    .review-filter-btn.active:hover {
      background: linear-gradient(135deg, #5a9a68 0%, #4d8a5a 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(105, 181, 120, 0.4);
    }
    
    .review-filter-btn i {
      font-size: 0.75rem;
    }
    
    /* Rating Summary Card Enhancement */
    .rating-summary-card {
      background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%);
      border: 1px solid rgba(105, 181, 120, 0.2);
      box-shadow: 0 2px 8px rgba(105, 181, 120, 0.1);
    }
    
    /* Filter Section Labels */
    .filter-label {
      color: #374151;
      font-weight: 600;
      letter-spacing: -0.01em;
      margin-bottom: 0.75rem;
    }
    
    .review-item {
      transition: all 0.3s ease;
    }
    
    .review-item.hidden {
      display: none !important;
    }
    
    @media (max-width: 768px) {
      main {
        padding: 1rem !important;
      }
      .product-image {
        height: 300px !important;
      }
      .grid {
        gap: 1.5rem !important;
      }
      h1 {
        font-size: 1.5rem !important;
      }
    }
  </style>
</head>
<body class="min-h-screen">
  @include('partials.navbar')

  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Product Image -->
      <div>
        @php
          $img = optional($product->images->first())->url ?? null;
        @endphp
        @if($img)
          <img src="{{ $img }}" alt="{{ $product->name }}" class="product-image" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
          <div class="product-image flex items-center justify-center text-gray-400" style="display: none;">
            <i class="fa-solid fa-image fa-4x"></i>
          </div>
        @else
          <div class="product-image flex items-center justify-center text-gray-400">
            <i class="fa-solid fa-image fa-4x"></i>
          </div>
        @endif
      </div>

      <!-- Product Info & Order Form -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
        <p class="text-gray-600 mb-2">{{ $product->unit ?? 'per unit' }}</p>
        
        <!-- Rating Display -->
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            @if($totalReviews > 0)
              @php
                $fullStars = (int)floor($avgRating);
                $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
              @endphp
              <div class="flex items-center">
                @for($i = 1; $i <= 5; $i++)
                  @if($i <= $fullStars)
                    <i class="fa-solid fa-star text-warning" style="font-size: 1rem;"></i>
                  @elseif($i == ($fullStars + 1) && $hasHalfStar)
                    <i class="fa-solid fa-star-half-stroke text-warning" style="font-size: 1rem;"></i>
                  @else
                    <i class="fa-regular fa-star text-gray-300" style="font-size: 1rem;"></i>
                  @endif
                @endfor
              </div>
              <span class="text-gray-600 font-semibold">{{ number_format($avgRating, 1) }}</span>
              <span class="text-gray-500 text-sm">({{ $totalReviews }} ulasan)</span>
            @else
              <div class="flex items-center gap-2">
                <i class="fa-regular fa-star text-gray-300" style="font-size: 1rem;"></i>
                <span class="text-gray-500 text-sm">Belum ada rating</span>
              </div>
            @endif
          </div>
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-shopping-bag text-gray-500" style="font-size: 0.9rem;"></i>
            <span class="text-gray-600 font-medium text-sm">Terjual {{ number_format($totalSold ?? 0, 0, ',', '.') }}</span>
          </div>
        </div>
        
        <div class="text-3xl font-bold text-emerald-600 mb-6">
          Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}
        </div>

        @if($product->description)
        <div class="mb-6">
          <h3 class="font-semibold text-gray-900 mb-2">Deskripsi</h3>
          <p class="text-gray-600">{{ $product->description }}</p>
        </div>
        @endif

        <div class="mb-6">
          <h3 class="font-semibold text-gray-900 mb-2">Stok tersedia</h3>
          <p class="text-gray-600">{{ $product->stock ?? 0 }} {{ $product->unit ?? 'unit' }}</p>
        </div>

        <!-- Order Form -->
        <form id="orderForm" class="space-y-4">
          <input type="hidden" name="product_id" value="{{ $product->product_id }}">
          
          <div>
            <label class="block font-semibold text-gray-900 mb-2">Jumlah</label>
            <div class="flex items-center gap-3">
              <button type="button" onclick="changeQty(-1)" class="w-10 h-10 rounded-lg border border-gray-300 hover:bg-gray-100">-</button>
              <input type="number" id="qty" name="qty" value="1" min="1" max="{{ $product->stock ?? 999 }}" class="qty-input" required>
              <button type="button" onclick="changeQty(1)" class="w-10 h-10 rounded-lg border border-gray-300 hover:bg-gray-100">+</button>
            </div>
          </div>

          <div class="pt-4 mt-4">
            <div class="flex justify-between mb-4">
              <span class="font-semibold text-gray-900">Total</span>
              <span id="totalPrice" class="text-2xl font-bold text-emerald-600">Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex gap-3">
              <button type="button" onclick="addToCart()" class="btn-outline-secondary flex-1" style="padding: 0.75rem 2rem; border: 2px solid var(--primary-green); border-radius: 8px; background: white; color: var(--primary-green); font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                <i class="fa-solid fa-cart-plus me-2"></i>Tambah ke Keranjang
              </button>
              <button type="button" onclick="buyNow()" class="btn-primary flex-1">
                <i class="fa-solid fa-bolt me-2"></i>Beli Sekarang
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Reviews Section -->
    @if(isset($totalReviews) && $totalReviews > 0)
    <div class="mt-12 pt-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">
        <i class="fa-solid fa-star text-warning me-2"></i>
        Ulasan Produk ({{ $totalReviews }})
      </h2>
      
      <!-- Rating Summary & Filters -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Rating Summary -->
          <div class="flex-shrink-0 lg:w-64">
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-6">
              <div class="text-center">
                <div class="flex items-baseline justify-center gap-1 mb-3">
                  <span class="text-5xl font-bold text-emerald-600">{{ number_format($avgRating, 1) }}</span>
                  <span class="text-xl text-emerald-600 font-medium">dari 5</span>
                </div>
                <div class="flex items-center justify-center gap-1 mb-3">
                  @php
                    $fullStars = (int)floor($avgRating);
                    $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                  @endphp
                  @for($i = 1; $i <= 5; $i++)
                    @if($i <= $fullStars)
                      <i class="fa-solid fa-star text-warning" style="font-size: 1.5rem;"></i>
                    @elseif($i == ($fullStars + 1) && $hasHalfStar)
                      <i class="fa-solid fa-star-half-stroke text-warning" style="font-size: 1.5rem;"></i>
                    @else
                      <i class="fa-regular fa-star text-gray-300" style="font-size: 1.5rem;"></i>
                    @endif
                  @endfor
                </div>
                <p class="text-sm font-medium text-gray-700">{{ $totalReviews }} ulasan</p>
              </div>
            </div>
          </div>
          
          <!-- Filter Buttons -->
          <div class="flex-1">
            <!-- Rating Filters -->
            <div class="mb-4">
              <label class="block text-sm font-semibold text-gray-700 mb-3">Filter Rating</label>
              <div class="flex flex-wrap gap-2">
                <button onclick="filterReviews('all')" class="review-filter-btn active" data-filter="all">
                  Semua
                </button>
                @for($i = 5; $i >= 1; $i--)
                  <button onclick="filterReviews('{{ $i }}')" class="review-filter-btn" data-filter="{{ $i }}">
                    {{ $i }} Bintang ({{ $ratingStats[$i] ?? 0 }})
                  </button>
                @endfor
              </div>
            </div>
            
            <!-- Content Filters -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-3">Filter Konten</label>
              <div class="flex flex-wrap gap-2">
                <button onclick="filterReviews('with-comment')" class="review-filter-btn" data-filter="with-comment">
                  <i class="fa-solid fa-comment me-2"></i>Dengan Komentar ({{ $reviewsWithComments }})
                </button>
                <button onclick="filterReviews('with-media')" class="review-filter-btn" data-filter="with-media">
                  <i class="fa-solid fa-image me-2"></i>Dengan Media ({{ $reviewsWithMedia }})
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div id="reviewsContainer" class="space-y-4">
        @foreach($reviews as $review)
        <div class="review-item bg-white rounded-lg p-4 shadow-sm" 
             data-rating="{{ $review->rating }}" 
             data-has-comment="{{ !empty($review->review) ? '1' : '0' }}" 
             data-has-media="{{ (!empty($review->image_urls) && count($review->image_urls) > 0) ? '1' : '0' }}">
          <div class="flex items-start justify-between mb-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-semibold">
                {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
              </div>
              <div>
                <div class="font-semibold text-gray-900">{{ $review->user->name ?? 'User' }}</div>
                <div class="text-sm text-gray-500">{{ $review->created_at->format('d M Y') }}</div>
              </div>
            </div>
            <div class="flex items-center">
              @for($i = 1; $i <= 5; $i++)
                <i class="fa-star {{ $i <= $review->rating ? 'fa-solid text-warning' : 'fa-regular text-gray-300' }}" style="font-size: 0.875rem;"></i>
              @endfor
            </div>
          </div>
          @if($review->review)
          <p class="text-gray-700 mt-2">{{ $review->review }}</p>
          @endif
          @if(!empty($review->image_urls) && is_array($review->image_urls) && count($review->image_urls) > 0)
            <div class="mt-3 d-flex flex-wrap gap-2">
              @foreach($review->image_urls as $imgUrl)
                <img src="{{ $imgUrl }}" alt="Review Image" class="review-image" data-image-url="{{ $imgUrl }}" style="max-width: 300px; max-height: 300px; border-radius: 8px; object-fit: cover; cursor: pointer; border: 1px solid #e5e7eb;" onerror="this.onerror=null; this.style.display='none';">
              @endforeach
            </div>
          @endif
          
          <!-- Reply Button -->
          @auth
          <div class="mt-3">
            <button onclick="toggleReplyForm('{{ $review->review_id }}')" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
              <i class="fa-solid fa-reply me-1"></i> Balas
            </button>
          </div>
          @endauth
          
          <!-- Reply Form (hidden by default) -->
          @auth
          <div id="replyForm-{{ $review->review_id }}" class="mt-3 hidden">
            <form onsubmit="submitReply(event, '{{ $product->product_id }}', '{{ $review->review_id }}')" class="space-y-2">
              <textarea name="reply" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Tulis balasan..." required maxlength="1000"></textarea>
              <div class="flex justify-end gap-2">
                <button type="button" onclick="toggleReplyForm('{{ $review->review_id }}')" class="px-4 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-1.5 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Kirim</button>
              </div>
            </form>
          </div>
          @endauth
          
          <!-- Replies -->
          @if($review->replies && $review->replies->count() > 0)
          <div class="mt-4 ml-6 space-y-3 border-l-2 border-gray-200 pl-4">
            @foreach($review->replies as $reply)
            <div class="bg-gray-50 rounded-lg p-3" id="reply-{{ $reply->review_id }}">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-semibold text-sm">
                  {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                  <div class="font-semibold text-gray-900 text-sm">{{ $reply->user->name ?? 'User' }}</div>
                  <div class="text-xs text-gray-500">{{ $reply->created_at->format('d M Y') }}</div>
                </div>
                </div>
                @auth
                  @if($reply->user_id === Auth::id())
                    <button onclick="deleteReply('{{ $product->product_id }}', '{{ $review->review_id }}', '{{ $reply->review_id }}')" class="text-red-600 hover:text-red-700 text-sm" title="Hapus balasan">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  @endif
                @endauth
              </div>
              <p class="text-gray-700 text-sm">{{ $reply->review }}</p>
            </div>
            @endforeach
          </div>
          @endif
        </div>
        @endforeach
      </div>
    </div>
    @endif
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/navbar.js') }}"></script>
  <script>
    window.isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
    window.userProfile = {
      name: '{{ Auth::check() ? addslashes(Auth::user()->name) : '' }}',
      phone: '{{ Auth::check() ? addslashes(Auth::user()->phone) : '' }}',
      address: `{{ Auth::check() ? addslashes(Auth::user()->address) : '' }}`
    };
    
    // Load cart count on page load
    @if(Auth::check())
    document.addEventListener('DOMContentLoaded', function() {
      updateCartCount();
    });
    @endif
  </script>
  <script>
    const productPrice = {{ $product->price ?? 0 }};
    const maxStock = {{ $product->stock ?? 999 }};

    // Buy Now - redirect to cart with this product
    async function buyNow() {
      if(!window.isLoggedIn){
        Swal.fire({icon:'warning',title:'Login Diperlukan',text:'Silakan login dahulu untuk membeli produk.'});
        return;
      }
      
      const qty = parseInt(document.getElementById('qty').value) || 1;
      
      // Add to cart first, then redirect to cart page
      try {
        await addToCart({silent: true});
        // After adding to cart, redirect to cart page for checkout
        window.location.href = '{{ route("cart") }}';
      } catch (error) {
        // If add to cart fails, still redirect to cart
        window.location.href = '{{ route("cart") }}';
      }
    }

    function changeQty(delta) {
      const input = document.getElementById('qty');
      let val = parseInt(input.value) || 1;
      val += delta;
      if (val < 1) val = 1;
      if (val > maxStock) val = maxStock;
      input.value = val;
      updateTotal();
    }

    function updateTotal() {
      const qty = parseInt(document.getElementById('qty').value) || 1;
      const total = qty * productPrice;
      document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    document.getElementById('qty').addEventListener('change', updateTotal);

    async function addToCart(options = {}) {
      if(!window.isLoggedIn){
        Swal.fire({icon:'warning',title:'Login Diperlukan',text:'Silakan login dahulu untuk menambahkan produk ke keranjang.'});
        return Promise.reject('Not logged in');
      }
      
      const qty = parseInt(document.getElementById('qty').value) || 1;
      
      try {
        const response = await fetch('{{ route("cart.add") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            product_id: '{{ $product->product_id }}',
            qty: qty
          })
        });
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          const text = await response.text();
          console.error('Non-JSON response:', text.substring(0, 200));
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan: Server mengembalikan respons yang tidak valid',
            confirmButtonColor: '#dc3545'
          });
          return Promise.reject('Invalid response');
        }
        
        const result = await response.json();
        
        if (response.ok && result.success) {
          updateCartCount();
          // Don't show alert if called from buyNow (silent mode)
          if (!options.silent) {
            await Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: result.message || 'Produk berhasil ditambahkan ke keranjang',
              confirmButtonColor: '#69B578',
              showCancelButton: true,
              confirmButtonText: 'Lihat Keranjang',
              cancelButtonText: 'Lanjut Belanja'
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = '{{ route("cart") }}';
              }
            });
          }
          return Promise.resolve();
        } else {
          throw new Error(result.message || 'Gagal menambahkan ke keranjang');
        }
      } catch (error) {
        if (error instanceof SyntaxError) {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan: Server mengembalikan respons yang tidak valid',
            confirmButtonColor: '#dc3545'
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: error.message || 'Terjadi kesalahan',
            confirmButtonColor: '#dc3545'
          });
        }
        return Promise.reject(error);
      }
    }
    
    function updateCartCount() {
      fetch('{{ route("cart.count") }}', {
        headers: {
          'Accept': 'application/json'
        }
      })
        .then(async res => {
          const contentType = res.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            const text = await res.text();
            console.error('Non-JSON response in updateCartCount:', text.substring(0, 200));
            return null;
          }
          return res.json();
        })
        .then(data => {
          if (data) {
            const badge = document.getElementById('cartBadge');
            if (badge) {
              if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-block';
              } else {
                badge.style.display = 'none';
              }
            }
          }
        })
        .catch(error => {
          console.error('Error updating cart count:', error);
        });
    }

  </script>
  <script>
    // Reply functions
    function toggleReplyForm(reviewId) {
      const form = document.getElementById('replyForm-' + reviewId);
      if (form) {
        form.classList.toggle('hidden');
      }
    }
    
    async function submitReply(e, productId, reviewId) {
      e.preventDefault();
      const form = e.target;
      const replyText = form.querySelector('textarea[name="reply"]').value.trim();
      
      if (!replyText) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Silakan isi balasan terlebih dahulu',
          confirmButtonColor: '#69B578'
        });
        return;
      }
      
      const csrfMeta = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';
      
      if (!csrfToken) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'CSRF token tidak ditemukan. Silakan refresh halaman.',
          confirmButtonColor: '#dc3545'
        });
        return;
      }
      
      try {
        const response = await fetch(`/api/products/${productId}/reviews/${reviewId}/reply`, {
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
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          const text = await response.text();
          console.error('Non-JSON response:', text.substring(0, 200));
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Server mengembalikan respons yang tidak valid',
            confirmButtonColor: '#dc3545'
          });
          return;
        }
        
        const result = await response.json();
        
        if (response.ok && result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Balasan berhasil dikirim',
            confirmButtonColor: '#69B578',
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
  </script>
  <script>
    // Handle review image click
    document.addEventListener('DOMContentLoaded', function() {
      const reviewImages = document.querySelectorAll('.review-image');
      reviewImages.forEach(function(img) {
        img.addEventListener('click', function() {
          const imageUrl = this.getAttribute('data-image-url');
          if (imageUrl) {
            window.open(imageUrl, '_blank');
          }
        });
      });
    });
    
    // Delete reply function
    async function deleteReply(productId, reviewId, replyId) {
      const result = await Swal.fire({
        title: 'Hapus balasan?',
        text: 'Balasan yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      });
      
      if (result.isConfirmed) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';
        
        try {
          const response = await fetch(`/api/products/${productId}/reviews/${reviewId}/reply/${replyId}`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            }
          });
          
          // Check if response is JSON
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 200));
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Server mengembalikan respons yang tidak valid',
              confirmButtonColor: '#dc3545'
            });
            return;
          }
          
          const result = await response.json();
          
          if (response.ok && result.success) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: result.message || 'Balasan berhasil dihapus',
              confirmButtonColor: '#69B578',
              timer: 2000
            }).then(() => {
              location.reload();
            });
          } else {
            throw new Error(result.message || 'Gagal menghapus balasan');
          }
        } catch (error) {
          console.error('Error:', error);
          if (error instanceof SyntaxError) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Server mengembalikan respons yang tidak valid',
              confirmButtonColor: '#dc3545'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: error.message || 'Terjadi kesalahan saat menghapus balasan',
              confirmButtonColor: '#dc3545'
            });
          }
        }
      }
    }
  </script>
  <script>
    // Review Filtering Functionality
    let currentFilter = 'all';
    
    function filterReviews(filterType) {
      currentFilter = filterType;
      
      // Update active button
      document.querySelectorAll('.review-filter-btn').forEach(btn => {
        btn.classList.remove('active');
        const btnFilter = btn.getAttribute('data-filter');
        if (btnFilter == filterType) {
          btn.classList.add('active');
        }
      });
      
      // Get all review items
      const reviewItems = document.querySelectorAll('.review-item');
      let visibleCount = 0;
      
      reviewItems.forEach(item => {
        const rating = parseInt(item.getAttribute('data-rating'));
        const hasComment = item.getAttribute('data-has-comment') === '1';
        const hasMedia = item.getAttribute('data-has-media') === '1';
        
        let shouldShow = false;
        
        if (filterType === 'all') {
          shouldShow = true;
        } else if (filterType === 'with-comment') {
          shouldShow = hasComment;
        } else if (filterType === 'with-media') {
          shouldShow = hasMedia;
        } else if (['1', '2', '3', '4', '5'].includes(filterType)) {
          shouldShow = rating === parseInt(filterType);
        }
        
        if (shouldShow) {
          item.classList.remove('hidden');
          visibleCount++;
        } else {
          item.classList.add('hidden');
        }
      });
      
      // Show message if no reviews match
      const container = document.getElementById('reviewsContainer');
      let noResultsMsg = document.getElementById('noResultsMessage');
      
      if (visibleCount === 0) {
        if (!noResultsMsg) {
          noResultsMsg = document.createElement('div');
          noResultsMsg.id = 'noResultsMessage';
          noResultsMsg.className = 'text-center py-8 text-gray-500';
          noResultsMsg.innerHTML = '<i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300"></i><p>Tidak ada ulasan yang sesuai dengan filter yang dipilih</p>';
          container.appendChild(noResultsMsg);
        }
        noResultsMsg.style.display = 'block';
      } else {
        if (noResultsMsg) {
          noResultsMsg.style.display = 'none';
        }
      }
    }
    
    // Initialize - set "Semua" as active on page load
    document.addEventListener('DOMContentLoaded', function() {
      const allBtn = document.querySelector('.review-filter-btn[data-filter="all"]');
      if (allBtn) {
        allBtn.classList.add('active');
      }
    });
  </script>
</body>
</html>
