<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Penjualan - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  
  <!-- Google Fonts - Inter (Premium Typography) -->
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
    
    .content-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      overflow: hidden;
    }
    
    .filter-bar {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
    }
    
    .filter-tabs {
      display: flex;
      gap: 0;
      border-bottom: 2px solid #e9ecef;
      position: relative;
    }
    
    .filter-tab {
      padding: 0.75rem 1.5rem;
      border: none;
      background: transparent;
      color: #6c757d;
      font-size: 0.875rem;
      cursor: pointer;
      transition: all 0.2s;
      position: relative;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
    }
    
    .filter-tab.active {
      color: #22C55E;
      border-bottom-color: #22C55E;
      font-weight: 500;
    }
    
    .filter-tab:hover:not(.active) {
      color: #2F2F2F;
    }
    
    .filter-right {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .search-box {
      position: relative;
      width: 200px;
    }
    
    .search-box input {
      width: 100%;
      padding: 0.5rem 0.75rem 0.5rem 2.25rem;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      font-size: 0.875rem;
      background: #f8f9fa;
    }
    
    .search-box input:focus {
      outline: none;
      border-color: #22C55E;
      background: white;
    }
    
    .search-box i {
      position: absolute;
      left: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      font-size: 0.75rem;
    }
    
    .order-item {
      padding: 1.5rem;
      border-bottom: 1px solid #f8f9fa;
    }
    
    .order-item:last-child {
      border-bottom: none;
    }
    
    .order-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #f8f9fa;
    }
    
    .order-header-left {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .order-buyer {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: #2F2F2F;
    }
    
    .order-buyer i {
      font-size: 0.875rem;
    }
    
    .order-date {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .order-date i {
      font-size: 0.875rem;
    }
    
    .order-header-right {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .order-status {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .order-response-time {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .order-body {
      display: flex;
      gap: 1.5rem;
    }
    
    .order-product {
      flex: 1;
      display: flex;
      gap: 1rem;
    }
    
    .order-product-img {
      width: 80px;
      height: 80px;
      border-radius: 8px;
      background: #f8f9fa;
      flex-shrink: 0;
    }
    
    .order-product-info {
      flex: 1;
    }
    
    .order-product-name {
      font-size: 0.875rem;
      font-weight: 500;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .order-product-qty {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
    }
    
    .order-product-note {
      font-size: 0.75rem;
      color: #6c757d;
      font-style: italic;
    }
    
    .order-address {
      flex: 1;
    }
    
    .order-address-title {
      font-size: 0.75rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
    }
    
    .order-address-name {
      font-size: 0.75rem;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .order-address-text {
      font-size: 0.75rem;
      color: #6c757d;
      line-height: 1.5;
    }
    
    .order-courier {
      flex: 0 0 200px;
    }
    
    .order-courier-title {
      font-size: 0.75rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
    }
    
    .order-courier-info {
      font-size: 0.75rem;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .order-courier-link {
      font-size: 0.75rem;
      color: #22C55E;
      text-decoration: none;
      display: inline-block;
      margin-top: 0.25rem;
    }
    
    .order-courier-link:hover {
      text-decoration: underline;
    }
    
    .order-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid #f8f9fa;
    }
    
    .order-footer-left {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .order-footer-right {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .order-total {
      font-size: 0.875rem;
      color: #2F2F2F;
    }
    
    .order-total strong {
      font-weight: 600;
    }
    
    .btn-accept {
      background: #22C55E;
      color: white;
      border: none;
      padding: 0.6rem 1.75rem;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .btn-accept:hover {
      background: #5a9d66;
    }
    
    .performa-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.25rem 0.75rem;
      background: #f8f9fa;
      border-radius: 6px;
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .performa-value {
      font-weight: 700;
      color: #2F2F2F;
    }
  </style>
</head>
<body>
  @include('layouts.sidebar')
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <h1>Penjualan</h1>
      <div class="search-box">
        <i class="fa-solid fa-search"></i>
        <input type="text" placeholder="Cari Produk">
      </div>
    </div>
    
    <!-- Content Card -->
    <div class="content-card">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-tabs">
          <button class="filter-tab {{ ($filter ?? 'all') === 'all' ? 'active' : '' }}" data-filter="all" onclick="filterOrders('all')">Semua Pesanan</button>
          <button class="filter-tab {{ ($filter ?? 'all') === 'dikirim' ? 'active' : '' }}" data-filter="dikirim" onclick="filterOrders('dikirim')">Pesanan Dikirim</button>
          <button class="filter-tab {{ ($filter ?? 'all') === 'selesai' ? 'active' : '' }}" data-filter="selesai" onclick="filterOrders('selesai')">Pesanan Selesai</button>
        </div>
      </div>
      
      <!-- Order Items -->
      <div class="order-list">
        @forelse($orders as $order)
        @php
          $detail = $order->orderDetail->first();
          $product = $detail?->product;
          $qtyTotal = $order->orderDetail->sum('qty');
          $unitPrice = $detail ? ($detail->price) : 0;
          $productName = $product?->name ?? 'Produk';
          $image = $product?->images?->first()?->url ?? null;
        @endphp
        <div class="order-item">
          <div class="order-header">
            <div class="order-header-left">
              <div class="order-buyer">
                <i class="fa-solid fa-user"></i>
                <span>{{ $order->buyer_name }}</span>
              </div>
              <div class="order-date">
                <i class="fa-regular fa-clock"></i>
                <span>{{ $order->created_at?->format('d M Y H:i') }} WIB</span>
              </div>
            </div>
            <div class="order-header-right">
              <span class="order-status">Status</span>
              <span class="order-response-time">{{ ucfirst($order->status ?? 'pending') }}</span>
            </div>
          </div>
          <div class="order-body">
            <div class="order-product">
              @if($image)
                <img src="{{ $image }}" alt="{{ $productName }}" class="order-product-img">
              @else
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Crect width='80' height='80' fill='%23f8d7da'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23721c24' font-size='30'%3E🍗%3C/text%3E%3C/svg%3E" class="order-product-img" />
              @endif
              <div class="order-product-info">
                <div class="order-product-name">{{ $productName }}</div>
                <div class="order-product-qty">{{ $qtyTotal }} x Rp {{ number_format($unitPrice,0,',','.') }}</div>
                @if($order->notes)
                  <div class="order-product-note">"{{ $order->notes }}"</div>
                @endif
              </div>
            </div>
            <div class="order-address">
              <div class="order-address-title">Alamat</div>
              <div class="order-address-name">{{ $order->buyer_name }} ({{ $order->buyer_phone }})</div>
              <div class="order-address-text">{{ $order->buyer_address }}</div>
            </div>
            <div class="order-courier">
              <div class="order-courier-title">Kurir</div>
              <div class="order-courier-info">{{ $order->shipping_service ?? 'Belum dipilih' }}</div>
              @if($order->payment_status === 'paid' && $order->tracking_number)
                <div class="order-courier-info mt-2">
                  <strong>Resi:</strong> {{ $order->tracking_number }}
                </div>
                <a href="https://cekresi.com/?resi={{ $order->tracking_number }}" target="_blank" class="order-courier-link">Cek Resi</a>
              @elseif($order->payment_status === 'paid')
                <div class="order-courier-info mt-2 text-muted">Resi akan muncul setelah pesanan dikirim</div>
              @else
                <div class="order-courier-info mt-2 text-muted">Resi akan muncul setelah pembayaran diterima dan pesanan dikirim</div>
              @endif
            </div>
          </div>
          <div class="order-footer">
            <div class="order-footer-left">
              <a href="{{ route('dashboard.chat') }}" class="btn btn-sm btn-outline-primary" title="Chat Pembeli">
                <i class="fa-solid fa-comment"></i> Chat Pembeli
              </a>
            </div>
            <div class="order-footer-right">
              <div class="order-total">
                Total Harga <strong>({{ $qtyTotal }} Barang)</strong>
                <span style="margin-left: 2rem; font-weight: 600;">Rp {{ number_format($order->total_price,0,',','.') }}</span>
              </div>
              <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                @if($order->payment_method)
                  <span class="badge {{ $order->payment_method === 'QRIS' ? 'bg-success' : 'bg-info' }}">
                    <i class="fa-solid fa-{{ $order->payment_method === 'QRIS' ? 'qrcode' : 'building-columns' }} me-1"></i>
                    {{ $order->payment_method }}
                  </span>
                @endif
                @if($order->payment_status === 'paid')
                  <span class="badge bg-success">
                    <i class="fa-solid fa-check-circle me-1"></i>Lunas
                  </span>
                @elseif($order->payment_status === 'processing')
                  <span class="badge bg-info">
                    <i class="fa-solid fa-hourglass-half me-1"></i>Proses
                  </span>
                  <button class="btn btn-sm btn-success ms-2" onclick="validatePayment('{{ $order->order_id }}')" title="Validasi Pembayaran">
                    <i class="fa-solid fa-check me-1"></i> Validasi Pembayaran
                  </button>
                @else
                  <span class="badge bg-warning text-dark">
                    <i class="fa-solid fa-clock me-1"></i>Menunggu Pembayaran
                  </span>
                @endif
                @if($order->status === 'pending')
                  <button class="btn-accept" onclick="shipOrder('{{ $order->order_id }}', '{{ $order->payment_status }}')" style="{{ $order->payment_status !== 'paid' ? 'opacity: 0.7;' : '' }}">Kirim Pesanan</button>
                @elseif($order->status === 'dikirim')
                  <span class="badge bg-info">Pesanan Dikirim</span>
                @elseif($order->status === 'selesai')
                  <span class="badge bg-success">Pesanan Selesai</span>
                @elseif($order->status === 'dibatalkan')
                  <span class="badge bg-danger">Dibatalkan</span>
                @endif
              </div>
            </div>
          </div>
        </div>
        @empty
          <div class="p-4 text-center text-muted">Belum ada pesanan.</div>
        @endforelse
      </div>
    </div>
  </main>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/dashboard-alerts.js') }}"></script>
  
  <script>
    // SweetAlert Helper Functions
    window.showSuccess = function(message) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            confirmButtonColor: '#22C55E',
            confirmButtonText: 'OK'
        });
    };
    
    window.showError = function(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'OK'
        });
    };
    
    // Toggle Submenu
    function toggleSubmenu() {
        const submenu = document.querySelector('.sidebar-submenu');
        const chevron = document.querySelector('.chevron-icon');
        submenu.classList.toggle('show');
        chevron.classList.toggle('rotate');
    }
    
    // Validasi Pembayaran (Admin)
    async function validatePayment(orderId) {
        const result = await Swal.fire({
            title: 'Validasi Pembayaran',
            html: '<p>Apakah Anda yakin sudah menerima pembayaran dari pembeli?</p><p class="text-muted mt-2"><small>Setelah divalidasi, status akan berubah menjadi "Lunas" dan pembeli dapat melihat nomor resi setelah pesanan dikirim.</small></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#22C55E',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Sudah Menerima Pembayaran',
            cancelButtonText: 'Batal'
        });
        
        if (result.isConfirmed) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/dashboard/orders/${orderId}/validate-payment`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    await showSuccess(data.message || 'Pembayaran berhasil divalidasi');
                    location.reload();
                } else {
                    await showError(data.message || 'Gagal memvalidasi pembayaran');
                }
            } catch (error) {
                console.error('Error:', error);
                await showError('Terjadi kesalahan saat memvalidasi pembayaran');
            }
        }
    }
    
    // Ship Order (Kirim Pesanan)
    async function shipOrder(orderId, paymentStatus) {
        // Check if payment is completed
        if (paymentStatus !== 'paid') {
            await Swal.fire({
                icon: 'warning',
                title: 'Pesanan Belum Dibayar!',
                html: '<p>Pesanan ini belum dibayar oleh pembeli. Anda tidak dapat mengirim pesanan sebelum pembayaran selesai.</p><p class="text-muted mt-2"><strong>Status Pembayaran:</strong> Menunggu Pembayaran</p>',
                confirmButtonColor: '#FACC15',
                confirmButtonText: 'Mengerti'
            });
            return;
        }
        
        const result = await Swal.fire({
            title: 'Kirim Pesanan?',
            text: 'Pesanan akan dikirim dan stok produk akan dikurangi. Tindakan ini tidak dapat dibatalkan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#22C55E',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Kirim Pesanan',
            cancelButtonText: 'Batal'
        });
        
        if (result.isConfirmed) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/dashboard/orders/${orderId}/ship`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                // Check if response is OK and content type is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 200));
                    showError('Terjadi kesalahan: Server mengembalikan respons yang tidak valid');
                    return;
                }
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess(data.message);
                    // Reload page to refresh data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showError(data.message || 'Terjadi kesalahan saat mengirim pesanan');
                }
            } catch (error) {
                console.error('Error shipping order:', error);
                if (error instanceof SyntaxError) {
                    showError('Terjadi kesalahan: Server mengembalikan respons yang tidak valid');
                } else {
                    showError('Terjadi kesalahan saat mengirim pesanan');
                }
            }
        }
    }
    
    // Filter Orders
    function filterOrders(filter) {
        window.location.href = `{{ route('dashboard.sales') }}?filter=${filter}`;
    }
    
    // Show success message if redirected with success
    @if(session('success'))
        showSuccess('{{ session('success') }}');
    @endif
    
    // Show error message if redirected with error
    @if(session('error'))
        showError('{{ session('error') }}');
    @endif
  </script>
</body>
</html>
