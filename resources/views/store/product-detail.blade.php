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
        @php($img = optional($product->images->first())->url ?? null)
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
        @if($totalReviews > 0)
        <div class="flex items-center gap-2 mb-4">
          <div class="flex items-center">
            @for($i = 1; $i <= 5; $i++)
              <i class="fa-star {{ $i <= round($avgRating) ? 'fa-solid text-warning' : 'fa-regular text-gray-300' }}" style="font-size: 1rem;"></i>
            @endfor
          </div>
          <span class="text-gray-600 font-semibold">{{ number_format($avgRating, 1) }}</span>
          <span class="text-gray-500 text-sm">({{ $totalReviews }} ulasan)</span>
        </div>
        @else
        <div class="flex items-center gap-2 mb-4">
          <div class="flex items-center">
            @for($i = 1; $i <= 5; $i++)
              <i class="fa-star fa-regular text-gray-300" style="font-size: 1rem;"></i>
            @endfor
          </div>
          <span class="text-gray-500 text-sm">Belum ada rating</span>
        </div>
        @endif
        
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

          <div class="border-t pt-4">
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
    <div class="mt-12 border-t pt-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">
        <i class="fa-solid fa-star text-warning me-2"></i>
        Ulasan Produk ({{ $totalReviews }})
      </h2>
      
      <div class="space-y-4">
        @foreach($reviews as $review)
        <div class="bg-white rounded-lg p-4 border border-gray-200">
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
</body>
</html>
