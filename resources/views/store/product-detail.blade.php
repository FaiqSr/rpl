<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $product->name ?? 'Detail Produk' }} - ChickPatrol</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
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
      <div class="ms-auto">
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

          <div>
            <label class="block font-semibold text-gray-900 mb-2">Nama Pembeli</label>
            <input type="text" name="buyer_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
          </div>

          <div>
            <label class="block font-semibold text-gray-900 mb-2">Alamat Pengiriman</label>
            <textarea name="address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required></textarea>
          </div>

          <div>
            <label class="block font-semibold text-gray-900 mb-2">Nomor Telepon</label>
            <input type="tel" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
          </div>

          <div>
            <label class="block font-semibold text-gray-900 mb-2">Jasa Pengiriman</label>
            <select name="shipping_service" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
              <option value="">Pilih Jasa Pengiriman</option>
              <option value="JNE Reguler">JNE Reguler</option>
              <option value="JNE Express">JNE Express</option>
              <option value="J&T Reguler">J&T Reguler</option>
              <option value="J&T Express">J&T Express</option>
              <option value="SiCepat Reguler">SiCepat Reguler</option>
              <option value="SiCepat HALU">SiCepat HALU</option>
              <option value="GoSend">GoSend</option>
              <option value="Grab Express">Grab Express</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-gray-900 mb-2">Metode Pembayaran</label>
            <div class="space-y-2">
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="payment_method" value="QRIS" class="me-3" required>
                <div class="flex items-center">
                  <i class="fa-solid fa-qrcode text-2xl me-2 text-emerald-600"></i>
                  <div>
                    <div class="font-semibold">QRIS</div>
                    <div class="text-xs text-gray-500">Scan QR Code untuk pembayaran</div>
                  </div>
                </div>
              </label>
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="payment_method" value="Transfer Bank" class="me-3" required>
                <div class="flex items-center">
                  <i class="fa-solid fa-building-columns text-2xl me-2 text-blue-600"></i>
                  <div>
                    <div class="font-semibold">Transfer Bank</div>
                    <div class="text-xs text-gray-500">BCA, Mandiri, BRI, BNI</div>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <div>
            <label class="block font-semibold text-gray-900 mb-2">Catatan (Opsional)</label>
            <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Jangan langsung di bumbu di rmh msh"></textarea>
          </div>

          <div class="border-t pt-4">
            <div class="flex justify-between mb-4">
              <span class="font-semibold text-gray-900">Total</span>
              <span id="totalPrice" class="text-2xl font-bold text-emerald-600">Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</span>
            </div>
            <button type="submit" class="btn-primary w-full">
              <i class="fa-solid fa-shopping-cart me-2"></i>Pesan Sekarang
            </button>
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
  </script>
  <script>
    const productPrice = {{ $product->price ?? 0 }};
    const maxStock = {{ $product->stock ?? 999 }};

    // Auto-fill form if logged in
    document.addEventListener('DOMContentLoaded', () => {
      if(window.isLoggedIn){
        const f = document.getElementById('orderForm');
        if(window.userProfile.name) f.querySelector('[name="buyer_name"]').value = window.userProfile.name;
        if(window.userProfile.phone) f.querySelector('[name="phone"]').value = window.userProfile.phone;
        if(window.userProfile.address) f.querySelector('[name="address"]').value = window.userProfile.address;
      }
    });

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

      document.getElementById('orderForm').addEventListener('submit', async function(e){
      e.preventDefault();
        if(!window.isLoggedIn){
          Swal.fire({icon:'warning',title:'Login Diperlukan',text:'Silakan login dahulu untuk membuat pesanan.'});
          return;
        }
      
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);
      data.qty = parseInt(data.qty);
      data.total_price = data.qty * productPrice;

      try {
        const response = await fetch('{{ route("order.create") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (response.ok) {
          await Swal.fire({
            icon: 'success',
            title: 'Pesanan Berhasil!',
            text: 'Pesanan Anda telah diterima dan sedang diproses.',
            confirmButtonColor: '#69B578'
          });
          window.location.href = '{{ route("home") }}';
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
