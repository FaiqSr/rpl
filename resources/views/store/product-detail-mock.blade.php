<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Detail Produk - ChickPatrol</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <style>
    :root {
      --primary-green: #69B578;
      --dark-green: #5a8c64;
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    .navbar { background: white; border-bottom: 1px solid #e9ecef; padding: 0.875rem 0; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .navbar-container { width: 100%; padding: 0 1.5rem; display: flex; align-items: center; gap: 1.5rem; }
    .navbar-brand { font-size: 1.125rem; font-weight: 700; color: #2F2F2F; text-decoration: none; }
    .btn-primary { padding: 0.75rem 2rem; border: none; border-radius: 8px; background: var(--primary-green); color: white; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-primary:hover { background: var(--dark-green); transform: translateY(-1px); }
    .product-image { width: 100%; height: 400px; object-fit: cover; border-radius: 12px; background: #f5f5f5; }
    .qty-input { width: 80px; text-align: center; padding: 0.5rem; border: 1px solid #e9ecef; border-radius: 6px; }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="navbar-container">
      <a href="{{ route('home') }}" class="navbar-brand">ChickPatrol</a>
      <div class="ms-auto">
        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900"><i class="fa-solid fa-arrow-left me-2"></i>Kembali</a>
      </div>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto px-4 py-8">
    <div id="productContainer">
      <div class="text-center py-8">
        <div class="spinner-border text-success" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-gray-600">Memuat produk...</p>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script>
    const productId = {{ $productId }};
    
    document.addEventListener('DOMContentLoaded', () => {
      try {
        const raw = localStorage.getItem('cp_products');
        const items = raw ? JSON.parse(raw) : [];
        const product = items.find(p => p.id == productId);
        
        if (!product) {
          document.getElementById('productContainer').innerHTML = `
            <div class="text-center py-8">
              <p class="text-red-600">Produk tidak ditemukan</p>
              <a href="{{ route('home') }}" class="btn btn-primary mt-3">Kembali ke Home</a>
            </div>
          `;
          return;
        }
        
        renderProduct(product);
      } catch (e) {
        console.error('Error loading product:', e);
      }
    });
    
    function renderProduct(p) {
      const container = document.getElementById('productContainer');
      container.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            ${p.image ? `<img src="${p.image}" alt="${p.name}" class="product-image">` : '<div class="product-image flex items-center justify-center text-gray-400"><i class="fa-solid fa-image fa-4x"></i></div>'}
          </div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">${p.name}</h1>
            <p class="text-gray-600 mb-4">${p.unit || 'per unit'}</p>
            <div class="text-3xl font-bold text-emerald-600 mb-6">Rp ${(p.price || 0).toLocaleString('id-ID')}</div>
            ${p.description ? `<div class="mb-6"><h3 class="font-semibold mb-2">Deskripsi</h3><p class="text-gray-600">${p.description}</p></div>` : ''}
            <div class="mb-6"><h3 class="font-semibold mb-2">Stok tersedia</h3><p class="text-gray-600">${p.stock || 0} ${p.unit || 'unit'}</p></div>
            <form id="orderForm" class="space-y-4">
              <div>
                <label class="block font-semibold mb-2">Jumlah</label>
                <div class="flex items-center gap-3">
                  <button type="button" onclick="changeQty(-1)" class="w-10 h-10 rounded-lg border hover:bg-gray-100">-</button>
                  <input type="number" id="qty" value="1" min="1" max="${p.stock || 999}" class="qty-input">
                  <button type="button" onclick="changeQty(1)" class="w-10 h-10 rounded-lg border hover:bg-gray-100">+</button>
                </div>
              </div>
              <div><label class="block font-semibold mb-2">Nama Pembeli</label><input type="text" id="buyer_name" class="w-full px-4 py-2 border rounded-lg" required></div>
              <div><label class="block font-semibold mb-2">Alamat</label><textarea id="address" rows="3" class="w-full px-4 py-2 border rounded-lg" required></textarea></div>
              <div><label class="block font-semibold mb-2">Telepon</label><input type="tel" id="phone" class="w-full px-4 py-2 border rounded-lg" required></div>
              <div><label class="block font-semibold mb-2">Catatan (Opsional)</label><textarea id="notes" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea></div>
              <div class="border-t pt-4">
                <div class="flex justify-between mb-4">
                  <span class="font-semibold">Total</span>
                  <span id="totalPrice" class="text-2xl font-bold text-emerald-600">Rp ${(p.price || 0).toLocaleString('id-ID')}</span>
                </div>
                <button type="submit" class="btn-primary w-full"><i class="fa-solid fa-shopping-cart me-2"></i>Pesan Sekarang</button>
              </div>
            </form>
          </div>
        </div>
      `;
      
      setupOrderForm(p);
    }
    
    let currentProduct = null;
    function setupOrderForm(p) {
      currentProduct = p;
      document.getElementById('orderForm').addEventListener('submit', submitOrder);
    }
    
    function changeQty(delta) {
      const input = document.getElementById('qty');
      let val = parseInt(input.value) || 1;
      val += delta;
      if (val < 1) val = 1;
      if (val > (currentProduct.stock || 999)) val = currentProduct.stock || 999;
      input.value = val;
      updateTotal();
    }
    
    function updateTotal() {
      if (!currentProduct) return;
      const qty = parseInt(document.getElementById('qty').value) || 1;
      const total = qty * (currentProduct.price || 0);
      document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
    
    async function submitOrder(e) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Tidak Bisa Dipesan',
        text: 'Produk ini hanya contoh (localStorage) atau Anda belum login. Silakan login dan gunakan produk asli.',
        confirmButtonColor: '#69B578'
      });
    }
  </script>
</body>
</html>
