<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $product->name ?? 'Detail Produk' }} - ChickPatrol</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <style>
    :root {
      --primary-green: #69B578;
      --dark-green: #5a8c64;
      --cream: #F5E6D3;
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    .navbar { background: white; border-bottom: 1px solid #e9ecef; padding: 0.875rem 0; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .navbar-container { width: 100%; padding: 0 1.5rem; display: flex; align-items: center; gap: 1.5rem; }
    .navbar-brand { font-size: 1.125rem; font-weight: 700; color: #2F2F2F; text-decoration: none; white-space: nowrap; margin-right: 1rem; flex-shrink: 0; }
    .btn-primary { padding: 0.75rem 2rem; border: none; border-radius: 8px; background: var(--primary-green); color: white; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-primary:hover { background: var(--dark-green); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(105, 181, 120, 0.3); }
    .product-image { width: 100%; height: 400px; object-fit: cover; border-radius: 12px; background: #f5f5f5; }
    .qty-input { width: 80px; text-align: center; padding: 0.5rem; border: 1px solid #e9ecef; border-radius: 6px; }
  </style>
</head>
<body class="min-h-screen">
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-container">
      <a href="{{ route('home') }}" class="navbar-brand">ChickPatrol</a>
      <div class="ms-auto d-flex align-items-center gap-3">
        @if(Auth::check())
          <a href="{{ route('cart') }}" class="text-gray-600 text-sm text-decoration-none position-relative" title="Keranjang">
            <i class="fa-solid fa-shopping-cart me-1"></i> Keranjang
            <span id="cartBadge" class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
          </a>
        @endif
        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900"><i class="fa-solid fa-arrow-left me-2"></i>Kembali</a>
      </div>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Product Image -->
      <div>
        @php($img = optional($product->images->first())->url ?? null)
        @if($img)
          <img src="{{ $img }}" alt="{{ $product->name }}" class="product-image">
        @else
          <div class="product-image flex items-center justify-center text-gray-400">
            <i class="fa-solid fa-image fa-4x"></i>
          </div>
        @endif
      </div>

      <!-- Product Info & Order Form -->
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
        <p class="text-gray-600 mb-4">{{ $product->unit ?? 'per unit' }}</p>
        
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
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
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
</body>
</html>
