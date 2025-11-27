<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Keranjang Belanja - ChickPatrol Store</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
  <style>
    :root {
      --primary-green: #69B578;
      --dark-green: #5a8c64;
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    
    .cart-header {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 1rem 1.5rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .cart-header-left {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .cart-header-checkbox {
      width: 20px;
      height: 20px;
      cursor: pointer;
      accent-color: var(--primary-green);
    }
    
    .cart-header-label {
      font-size: 0.9375rem;
      font-weight: 500;
      color: #2F2F2F;
      cursor: pointer;
      user-select: none;
    }
    
    .cart-header-delete {
      color: var(--primary-green);
      background: none;
      border: none;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 500;
      padding: 0.25rem 0.5rem;
      transition: all 0.2s;
    }
    
    .cart-header-delete:hover {
      color: var(--dark-green);
      text-decoration: underline;
    }
    
    .cart-item {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      display: flex;
      gap: 1rem;
      align-items: center;
    }
    
    .cart-item-checkbox {
      width: 20px;
      height: 20px;
      cursor: pointer;
      accent-color: var(--primary-green);
      flex-shrink: 0;
    }
    
    .cart-item-img {
      width: 80px;
      height: 80px;
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
    
    .cart-item-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-left: auto;
    }
    
    .cart-item-total {
      font-size: 1.125rem;
      font-weight: 700;
      color: #2F2F2F;
      min-width: 120px;
      text-align: right;
    }
    
    .cart-item-delete {
      background: none;
      border: none;
      color: #dc3545;
      cursor: pointer;
      padding: 0.5rem;
      font-size: 1.125rem;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .cart-item-delete:hover {
      color: #c82333;
    }
    
    .cart-item-wishlist {
      background: none;
      border: none;
      color: #6c757d;
      cursor: pointer;
      padding: 0.5rem;
      font-size: 1.125rem;
      transition: all 0.2s;
    }
    
    .cart-item-wishlist:hover {
      color: #dc3545;
    }
    
    .cart-item-wishlist.active {
      color: #dc3545;
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
  @include('partials.navbar')

  <main class="container py-5">
    <div class="row">
      <div class="col-lg-8">
        <h2 class="mb-3">Keranjang</h2>
        
        @if($cartItems->count() > 0)
        <div class="cart-header">
          <div class="cart-header-left">
            <input type="checkbox" id="selectAll" class="cart-header-checkbox" onchange="toggleSelectAll()">
            <label for="selectAll" class="cart-header-label">Pilih Semua ({{ $cartItems->count() }})</label>
          </div>
          <button class="cart-header-delete" onclick="deleteSelected()">
            Hapus
          </button>
        </div>
        @endif
        
        @forelse($cartItems as $item)
          <div class="cart-item" data-cart-id="{{ $item->cart_id }}">
            <input type="checkbox" 
                   class="cart-item-checkbox item-checkbox" 
                   id="item-{{ $item->cart_id }}"
                   data-cart-id="{{ $item->cart_id }}"
                   data-price="{{ $item->product->price }}"
                   data-qty="{{ $item->qty }}"
                   onchange="updateSelection()"
                   checked>
            @php
              $image = $item->product->images->first();
              $imageUrl = $image ? $image->url : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect width='100' height='100' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='40'%3E🍗%3C/text%3E%3C/svg%3E";
            @endphp
            
            <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="cart-item-img" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCI+PHJlY3Qgd2lkdGg9IjEwMCIgaGVpZ2h0PSIxMDAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0IiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+8J+RozwvdGV4dD48L3N2Zz4=';">
            
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
                       id="qty-{{ $item->cart_id }}"
                       value="{{ $item->qty }}" 
                       min="1" 
                       max="{{ $item->product->stock }}"
                       onchange="updateQty('{{ $item->cart_id }}', this.value)">
                <button class="qty-btn" onclick="updateQty('{{ $item->cart_id }}', {{ $item->qty + 1 }})">
                  <i class="fa-solid fa-plus"></i>
                </button>
              </div>
            </div>
            
            <div class="cart-item-actions">
              <div class="cart-item-total" id="total-{{ $item->cart_id }}">
                Rp {{ number_format($item->product->price * $item->qty, 0, ',', '.') }}
              </div>
              <button class="cart-item-wishlist" title="Tambah ke Wishlist">
                <i class="fa-regular fa-heart"></i>
              </button>
              <button class="cart-item-delete" onclick="deleteItem('{{ $item->cart_id }}')" title="Hapus">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
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
          <h4 class="mb-4">Ringkasan belanja</h4>
          
          <div class="summary-row summary-total">
            <span>Total</span>
            <span id="total">Rp 0</span>
          </div>
          
          <button class="btn-checkout" id="checkoutBtn" onclick="checkout()" disabled>
            <i class="fa-solid fa-shopping-bag me-2"></i>Beli (<span id="selectedCount">0</span>)
          </button>
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
  <script src="{{ asset('js/navbar.js') }}"></script>
  
  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Selection management
    function updateSelection() {
      const checkboxes = document.querySelectorAll('.item-checkbox:checked');
      const selectedCount = checkboxes.length;
      let total = 0;
      
      checkboxes.forEach(checkbox => {
        const price = parseFloat(checkbox.dataset.price);
        const qty = parseInt(checkbox.dataset.qty);
        total += price * qty;
      });
      
      document.getElementById('selectedCount').textContent = selectedCount;
      document.getElementById('total').textContent = 'Rp ' + total.toLocaleString('id-ID');
      
      const checkoutBtn = document.getElementById('checkoutBtn');
      if (selectedCount > 0) {
        checkoutBtn.disabled = false;
      } else {
        checkoutBtn.disabled = true;
      }
      
      // Update select all checkbox
      const allCheckboxes = document.querySelectorAll('.item-checkbox');
      const selectAll = document.getElementById('selectAll');
      if (selectAll) {
        selectAll.checked = selectedCount === allCheckboxes.length && allCheckboxes.length > 0;
        selectAll.indeterminate = selectedCount > 0 && selectedCount < allCheckboxes.length;
      }
    }
    
    function toggleSelectAll() {
      const selectAll = document.getElementById('selectAll');
      const checkboxes = document.querySelectorAll('.item-checkbox');
      
      checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
      });
      
      updateSelection();
    }
    
    function deleteSelected() {
      const checkboxes = document.querySelectorAll('.item-checkbox:checked');
      if (checkboxes.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih produk yang ingin dihapus terlebih dahulu'
        });
        return;
      }
      
      Swal.fire({
        title: 'Hapus produk terpilih?',
        text: `Anda akan menghapus ${checkboxes.length} produk dari keranjang`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          const deletePromises = Array.from(checkboxes).map(checkbox => {
            return fetch(`/cart/delete/${checkbox.dataset.cartId}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': csrfToken
              }
            });
          });
          
          Promise.all(deletePromises)
            .then(() => {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Produk berhasil dihapus dari keranjang'
              }).then(() => {
                location.reload();
              });
            })
            .catch(err => {
              Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan saat menghapus produk'
              });
            });
        }
      });
    }
    
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
          // Update checkbox data-qty and item total
          const checkbox = document.querySelector(`.item-checkbox[data-cart-id="${cartId}"]`);
          if (checkbox) {
            checkbox.dataset.qty = qty;
            const totalEl = document.getElementById(`total-${cartId}`);
            if (totalEl) {
              const price = parseFloat(checkbox.dataset.price);
              totalEl.textContent = 'Rp ' + (price * qty).toLocaleString('id-ID');
            }
            updateSelection();
          }
          // Update qty input
          const qtyInput = document.getElementById(`qty-${cartId}`);
          if (qtyInput) {
            qtyInput.value = qty;
          }
          // Don't reload, just update
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
      const checkboxes = document.querySelectorAll('.item-checkbox:checked');
      if (checkboxes.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih produk yang ingin dibeli terlebih dahulu'
        });
        return;
      }
      
      const selectedCartIds = Array.from(checkboxes).map(cb => cb.dataset.cartId);
      window.selectedCartIds = selectedCartIds;
      
      const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
      modal.show();
    }
    
    document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      if (!window.selectedCartIds || window.selectedCartIds.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih produk yang ingin dibeli terlebih dahulu'
        });
        return;
      }
      
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);
      data.selected_cart_ids = window.selectedCartIds;
      
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
    
    // Initialize selection on page load
    document.addEventListener('DOMContentLoaded', function() {
      updateSelection();
    });
  </script>
</body>
</html>

