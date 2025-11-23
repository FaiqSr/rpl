<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Keranjang Belanja - ChickPatrol Store</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <style>
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    
    .navbar {
      background: white;
      border-bottom: 1px solid #e9ecef;
      padding: 0.875rem 0;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .navbar-container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1.5rem;
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }
    
    .navbar-brand {
      font-size: 1.125rem;
      font-weight: 700;
      color: #2F2F2F;
      text-decoration: none;
      white-space: nowrap;
      margin-right: 1rem;
    }
    
    .cart-item {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      display: flex;
      gap: 1.5rem;
      align-items: center;
    }
    
    .cart-item-img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
      flex-shrink: 0;
    }
    
    .cart-item-info {
      flex: 1;
    }
    
    .cart-item-name {
      font-size: 1.125rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
    }
    
    .cart-item-price {
      font-size: 1rem;
      color: #69B578;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    
    .cart-item-stock {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .cart-item-qty {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .qty-input {
      width: 80px;
      text-align: center;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      padding: 0.5rem;
    }
    
    .qty-btn {
      width: 36px;
      height: 36px;
      border: 1px solid #e9ecef;
      background: white;
      border-radius: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    
    .qty-btn:hover {
      background: #f8f9fa;
      border-color: #69B578;
    }
    
    .cart-item-total {
      font-size: 1.25rem;
      font-weight: 700;
      color: #2F2F2F;
      min-width: 150px;
      text-align: right;
    }
    
    .cart-item-delete {
      background: none;
      border: none;
      color: #dc3545;
      cursor: pointer;
      padding: 0.5rem;
      font-size: 1.25rem;
      transition: all 0.2s;
    }
    
    .cart-item-delete:hover {
      color: #c82333;
    }
    
    .cart-summary {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 1.5rem;
      position: sticky;
      top: 80px;
    }
    
    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
      font-size: 1rem;
    }
    
    .summary-total {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2F2F2F;
      padding-top: 1rem;
      border-top: 2px solid #e9ecef;
    }
    
    .btn-checkout {
      width: 100%;
      padding: 0.875rem;
      background: #69B578;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 1rem;
    }
    
    .btn-checkout:hover {
      background: #5a8c64;
    }
    
    .btn-checkout:disabled {
      background: #ccc;
      cursor: not-allowed;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-container">
      <a href="{{ route('home') }}" class="navbar-brand">ChickPatrol</a>
      
      <div style="flex: 1;"></div>
      
      <div class="navbar-actions">
        @if(Auth::check())
          <a href="{{ route('cart') }}" class="text-gray-600 text-sm me-3 text-decoration-none position-relative" title="Keranjang">
            <i class="fa-solid fa-shopping-cart me-1"></i> Keranjang
            <span id="cartBadge" class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="display: none;">0</span>
          </a>
          <a href="{{ route('orders') }}" class="text-gray-600 text-sm me-3 text-decoration-none" title="Pesanan Saya">
            <i class="fa-solid fa-shopping-bag me-1"></i> Pesanan Saya
          </a>
          <a href="{{ route('profile') }}" class="text-gray-600 text-sm me-2 text-decoration-none">Halo, {{ Auth::user()->name }}</a>
          <a href="{{ route('logout') }}" class="btn-outline-secondary">Logout</a>
        @else
          <a href="{{ route('login') }}" class="btn-outline-secondary">Masuk</a>
          <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
        @endif
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <div class="row">
      <div class="col-lg-8">
        <h2 class="mb-4">Keranjang Belanja</h2>
        
        @forelse($cartItems as $item)
          <div class="cart-item" data-cart-id="{{ $item->cart_id }}">
            @php
              $image = $item->product->images->first();
              $imageUrl = $image ? $image->url : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect width='100' height='100' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='40'%3E🍗%3C/text%3E%3C/svg%3E";
            @endphp
            
            <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="cart-item-img">
            
            <div class="cart-item-info">
              <div class="cart-item-name">{{ $item->product->name }}</div>
              <div class="cart-item-price">Rp {{ number_format($item->product->price, 0, ',', '.') }} / {{ $item->product->unit ?? 'kg' }}</div>
              <div class="cart-item-stock">Stok: {{ $item->product->stock }}</div>
              
              <div class="cart-item-qty mt-3">
                <button class="qty-btn" onclick="updateQty('{{ $item->cart_id }}', {{ $item->qty - 1 }})">
                  <i class="fa-solid fa-minus"></i>
                </button>
                <input type="number" 
                       class="qty-input" 
                       value="{{ $item->qty }}" 
                       min="1" 
                       max="{{ $item->product->stock }}"
                       onchange="updateQty('{{ $item->cart_id }}', this.value)">
                <button class="qty-btn" onclick="updateQty('{{ $item->cart_id }}', {{ $item->qty + 1 }})">
                  <i class="fa-solid fa-plus"></i>
                </button>
              </div>
            </div>
            
            <div class="cart-item-total">
              Rp {{ number_format($item->product->price * $item->qty, 0, ',', '.') }}
            </div>
            
            <button class="cart-item-delete" onclick="deleteItem('{{ $item->cart_id }}')">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        @empty
          <div class="text-center py-12">
            <i class="fa-solid fa-shopping-cart text-gray-300" style="font-size: 4rem;"></i>
            <p class="text-gray-500 mt-4">Keranjang Anda kosong</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-4">Mulai Belanja</a>
          </div>
        @endforelse
      </div>
      
      <div class="col-lg-4">
        <div class="cart-summary">
          <h4 class="mb-4">Ringkasan Belanja</h4>
          
          <div class="summary-row">
            <span>Subtotal</span>
            <span id="subtotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
          </div>
          
          <div class="summary-row">
            <span>Ongkir</span>
            <span>Dihitung saat checkout</span>
          </div>
          
          <div class="summary-row summary-total">
            <span>Total</span>
            <span id="total">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
          </div>
          
          @if($cartItems->count() > 0)
            <button class="btn-checkout" onclick="checkout()">
              <i class="fa-solid fa-shopping-bag me-2"></i>Checkout
            </button>
          @else
            <button class="btn-checkout" disabled>
              <i class="fa-solid fa-shopping-bag me-2"></i>Checkout
            </button>
          @endif
        </div>
      </div>
    </div>
  </main>

  <!-- Checkout Modal -->
  <div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Checkout</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="checkoutForm">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nama Penerima</label>
              <input type="text" name="buyer_name" class="form-control" value="{{ Auth::user()->name }}" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Alamat</label>
              <textarea name="address" class="form-control" rows="3" required>{{ Auth::user()->address ?? '' }}</textarea>
            </div>
            
            <div class="mb-3">
              <label class="form-label">No. Telepon</label>
              <input type="text" name="phone" class="form-control" value="{{ Auth::user()->phone ?? '' }}" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Catatan (Opsional)</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Jasa Pengiriman</label>
              <select name="shipping_service" class="form-control" required>
                <option value="">Pilih Jasa Pengiriman</option>
                <option value="JNE">JNE</option>
                <option value="JNT">J&T Express</option>
                <option value="SiCepat">SiCepat</option>
                <option value="Gojek">Gojek Instant</option>
                <option value="Grab">GrabExpress</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Metode Pembayaran</label>
              <select name="payment_method" class="form-control" required>
                <option value="">Pilih Metode Pembayaran</option>
                <option value="QRIS">QRIS</option>
                <option value="Transfer Bank">Transfer Bank</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Buat Pesanan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  
  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    function updateQty(cartId, newQty) {
      const qty = parseInt(newQty);
      if (qty < 1) return;
      
      fetch(`/cart/update/${cartId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ qty: qty })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: data.message || 'Gagal memperbarui keranjang'
          });
        }
      })
      .catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'Terjadi kesalahan'
        });
      });
    }
    
    function deleteItem(cartId) {
      Swal.fire({
        title: 'Hapus dari keranjang?',
        text: 'Produk ini akan dihapus dari keranjang Anda',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(`/cart/delete/${cartId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrfToken
            }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Produk berhasil dihapus dari keranjang'
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message || 'Gagal menghapus produk'
              });
            }
          })
          .catch(err => {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'Terjadi kesalahan'
            });
          });
        }
      });
    }
    
    function checkout() {
      const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
      modal.show();
    }
    
    document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);
      
      try {
        const response = await fetch('{{ route("cart.checkout") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
          await Swal.fire({
            icon: 'success',
            title: 'Pesanan Berhasil!',
            text: 'Pesanan Anda telah dibuat dan sedang diproses.',
            confirmButtonColor: '#69B578'
          });
          window.location.href = result.redirect;
        } else {
          throw new Error(result.message || 'Gagal membuat pesanan');
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: error.message || 'Terjadi kesalahan saat membuat pesanan',
          confirmButtonColor: '#dc3545'
        });
      }
    });
  </script>
</body>
</html>

