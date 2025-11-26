<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pesanan Saya - ChickPatrol Store</title>
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
    
    .order-card {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      transition: all 0.2s;
    }
    
    .order-card:hover {
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .order-id {
      font-size: 0.875rem;
      color: #6c757d;
      font-weight: 500;
    }
    
    .order-date {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .status-badge {
      padding: 0.375rem 0.75rem;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    .status-pending {
      background: #fff3cd;
      color: #856404;
    }
    
    .status-dikirim {
      background: #d1ecf1;
      color: #0c5460;
    }
    
    .status-selesai {
      background: #d4edda;
      color: #155724;
    }
    
    .order-product {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
    }
    
    .order-product-img {
      width: 80px;
      height: 80px;
      border-radius: 8px;
      object-fit: cover;
      background: #f8f9fa;
    }
    
    .order-product-info {
      flex: 1;
    }
    
    .order-product-name {
      font-size: 0.95rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .order-product-qty {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .order-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid #f0f0f0;
    }
    
    .order-total {
      font-size: 1rem;
      font-weight: 600;
      color: #2F2F2F;
    }
    
    .btn-confirm {
      background: #69B578;
      color: white;
      border: none;
      padding: 0.6rem 1.5rem;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .btn-confirm:hover {
      background: #5a9d66;
    }
    
    /* WhatsApp-style chat messages - Buyer Chat (Orders Page) */
    #buyerChatMessages {
      display: flex !important;
      flex-direction: column !important;
      gap: 0.5rem !important;
      width: 100% !important;
      min-height: 0 !important;
    }
    
    #buyerChatMessages .chat-message {
      display: flex !important;
      flex-direction: column !important;
      margin-bottom: 0.5rem !important;
      width: 100% !important;
      box-sizing: border-box !important;
    }
    
    /* Message Left (Received from Admin) - White background, LEFT aligned */
    #buyerChatMessages .chat-message.message-left {
      align-self: flex-start !important;
      max-width: 70% !important;
      align-items: flex-start !important;
      margin-right: auto !important;
      margin-left: 0 !important;
    }
    
    /* Message Right (Sent by Buyer) - Green background, RIGHT aligned */
    #buyerChatMessages .chat-message.message-right {
      align-self: flex-end !important;
      max-width: 70% !important;
      align-items: flex-end !important;
      margin-left: auto !important;
      margin-right: 0 !important;
    }
    
    .message-sender-name {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.25rem;
      font-weight: 500;
      padding: 0 0.5rem;
    }
    
    .message-bubble {
      padding: 0.625rem 0.875rem;
      border-radius: 12px;
      font-size: 0.875rem;
      line-height: 1.4;
      word-wrap: break-word;
      max-width: 100%;
      box-shadow: 0 1px 2px rgba(0,0,0,0.1);
      display: inline-block;
    }
    
    /* Left message (received from admin) - white background */
    #buyerChatMessages .message-left .message-bubble {
      background: #ffffff !important;
      color: #2F2F2F !important;
      border: 1px solid #e5e7eb !important;
    }
    
    /* Right message (sent by buyer) - green background like WhatsApp */
    #buyerChatMessages .message-right .message-bubble {
      background: #dcf8c6 !important;
      color: #2F2F2F !important;
      border: none !important;
    }
    
    .message-time {
      font-size: 0.6875rem;
      color: #6c757d;
      margin-top: 0.25rem;
      padding: 0 0.5rem;
    }
    
    .message-left .message-time {
      text-align: left;
    }
    
    .message-right .message-time {
      text-align: right;
    }
  </style>
</head>
<body class="min-h-screen">
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-container">
      <a href="{{ route('home') }}" class="navbar-brand">ChickPatrol</a>
      <div class="ms-auto">
        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 text-decoration-none me-3">
          <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Beranda
        </a>
        @if(Auth::check())
          <a href="{{ route('profile') }}" class="text-gray-600 text-sm me-2 text-decoration-none">Halo, {{ Auth::user()->name }}</a>
          <a href="{{ route('logout') }}" class="btn btn-outline-secondary btn-sm">Logout</a>
        @endif
      </div>
    </div>
  </nav>

  <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Pesanan Saya</h1>
      <p class="text-gray-600">Lihat dan kelola semua pesanan Anda</p>
    </div>
    
    @forelse($orders as $order)
      @php
        $qtyTotal = $order->orderDetail->sum('qty');
      @endphp
      <div class="order-card">
        <div class="order-header">
          <div>
            <div class="order-id">Pesanan #{{ substr($order->order_id, 0, 8) }}</div>
            <div class="order-date">
              <i class="fa-regular fa-clock me-1"></i>
              {{ $order->created_at?->format('d M Y H:i') }} WIB
            </div>
          </div>
          <div>
            @if($order->status === 'pending')
              <span class="status-badge status-pending">Menunggu Pengiriman</span>
            @elseif($order->status === 'dikirim')
              <span class="status-badge status-dikirim">Sedang Dikirim</span>
            @elseif($order->status === 'selesai')
              <span class="status-badge status-selesai">Selesai</span>
            @elseif($order->status === 'dibatalkan')
              <span class="status-badge bg-danger text-white">Dibatalkan</span>
            @endif
          </div>
        </div>
        
        <div class="mb-3">
          <h6 class="mb-2 fw-semibold">Produk yang Dipesan:</h6>
          @foreach($order->orderDetail as $detail)
            @php
              $product = $detail->product;
              $image = $product?->images?->first()?->url ?? null;
            @endphp
            <div class="order-product mb-2">
              @if($image)
                <img src="{{ $image }}" alt="{{ $product->name }}" class="order-product-img">
              @else
                <div class="order-product-img d-flex align-items-center justify-content-center">
                  <i class="fa-solid fa-image text-gray-400"></i>
                </div>
              @endif
              <div class="order-product-info">
                <div class="order-product-name">{{ $product->name }}</div>
                <div class="order-product-qty">{{ $detail->qty }} x Rp {{ number_format($detail->price,0,',','.') }} = Rp {{ number_format($detail->qty * $detail->price,0,',','.') }}</div>
              </div>
            </div>
          @endforeach
          @if($order->notes)
            <div class="text-sm text-gray-500 mt-2 p-2 bg-gray-50 rounded">
              <i class="fa-solid fa-note-sticky me-1"></i> <strong>Catatan:</strong> {{ $order->notes }}
            </div>
          @endif
        </div>
        
        <div class="mb-3 p-3 bg-gray-50 rounded-lg">
          <div class="row">
            <div class="col-md-6 mb-2">
              <strong>Jasa Pengiriman:</strong> 
              <span>{{ $order->shipping_service ?? 'Belum dipilih' }}</span>
            </div>
            <div class="col-md-6 mb-2">
              <strong>Metode Pembayaran:</strong> 
              @if($order->payment_method === 'QRIS')
                <span class="badge bg-success"><i class="fa-solid fa-qrcode me-1"></i>QRIS</span>
              @elseif($order->payment_method === 'Transfer Bank')
                <span class="badge bg-info"><i class="fa-solid fa-building-columns me-1"></i>Transfer Bank</span>
              @else
                <span class="text-muted">Belum dipilih</span>
              @endif
            </div>
            @if($order->tracking_number)
              <div class="col-md-12">
                <strong>Nomor Resi:</strong> 
                <span class="text-primary">{{ $order->tracking_number }}</span>
                <a href="https://cekresi.com/?resi={{ $order->tracking_number }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                  <i class="fa-solid fa-external-link me-1"></i>Cek Resi
                </a>
              </div>
            @endif
            @if($order->payment_status === 'paid')
              <div class="col-md-12 mt-2">
                <span class="badge bg-success">
                  <i class="fa-solid fa-check-circle me-1"></i>Pembayaran Diterima
                </span>
                @if($order->paid_at)
                  <small class="text-muted ms-2">Dibayar pada {{ $order->paid_at instanceof \Carbon\Carbon ? $order->paid_at->format('d M Y H:i') : \Carbon\Carbon::parse($order->paid_at)->format('d M Y H:i') }}</small>
                @endif
              </div>
            @else
              <div class="col-md-12 mt-2">
                <span class="badge bg-warning text-dark">
                  <i class="fa-solid fa-clock me-1"></i>Menunggu Pembayaran
                </span>
                @if($order->payment_method)
                  <a href="{{ route('order.payment', $order->order_id) }}" class="btn btn-sm btn-primary ms-2">
                    <i class="fa-solid fa-credit-card me-1"></i>Bayar Sekarang
                  </a>
                @endif
              </div>
            @endif
          </div>
        </div>
        
        <div class="order-footer">
          <div class="order-total">
            Total: Rp {{ number_format($order->total_price,0,',','.') }}
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="openChatForOrder('{{ $order->order_id }}')" title="Chat dengan Penjual">
              <i class="fa-solid fa-comments me-1"></i> Chat Penjual
            </button>
            @if($order->status === 'dikirim')
              <button class="btn-confirm" onclick="confirmReceived('{{ $order->order_id }}')">
                <i class="fa-solid fa-check me-1"></i> Konfirmasi Diterima
              </button>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="text-center py-12">
        <i class="fa-solid fa-shopping-bag text-gray-300" style="font-size: 4rem;"></i>
        <p class="text-gray-500 mt-4">Belum ada pesanan</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-4">Mulai Belanja</a>
      </div>
    @endforelse
  </main>
  
  <!-- Chat Modal -->
  @if(Auth::check())
  <div class="modal fade" id="chatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="height: 600px; display: flex; flex-direction: column;">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa-solid fa-comments me-2"></i>Chat dengan Penjual
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0" style="flex: 1; display: flex; flex-direction: column;">
          <div id="buyerChatMessages" style="flex: 1; overflow-y: auto; padding: 1rem; background: #f8f9fa; display: flex; flex-direction: column; gap: 0.5rem; min-height: 0;">
            <div class="text-center p-4 text-gray-500">
              <i class="fa-solid fa-spinner fa-spin"></i> Memuat pesan...
            </div>
          </div>
          <div class="p-3 border-top bg-white">
            <div class="d-flex gap-2">
              <input type="text" id="buyerChatInput" class="form-control" placeholder="Ketik pesan disini..." onkeypress="if(event.key==='Enter') sendBuyerMessage()">
              <button class="btn btn-success" onclick="sendBuyerMessage()">
                <i class="fa-solid fa-paper-plane"></i> Kirim
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  @if(Auth::check())
  <script>
    // Set current user for chat
    window.currentUser = @json(Auth::user());
    // Set currentUserId for WhatsApp-style positioning
    window.currentUserId = @json(Auth::user()?->user_id);
  </script>
  <script src="{{ asset('js/chat-buyer.js') }}"></script>
  @endif
  
  <script>
    
    async function confirmReceived(orderId) {
      const result = await Swal.fire({
        title: 'Konfirmasi Pesanan Diterima?',
        text: 'Pastikan Anda sudah menerima pesanan dengan baik',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#69B578',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Saya Sudah Menerima',
        cancelButtonText: 'Batal'
      });
      
      if (result.isConfirmed) {
        try {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          const response = await fetch(`/order/${orderId}/confirm-received`, {
            method: 'POST',
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
              text: data.message,
              confirmButtonColor: '#69B578',
              confirmButtonText: 'OK'
            }).then(() => {
              window.location.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: data.message || 'Terjadi kesalahan',
              confirmButtonColor: '#dc3545',
              confirmButtonText: 'OK'
            });
          }
        } catch (error) {
          console.error('Error confirming order:', error);
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan saat mengonfirmasi pesanan',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
          });
        }
      }
    }
    
    // Show success message if redirected with success
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#69B578',
        confirmButtonText: 'OK'
      });
    @endif
    
    // Show error message if redirected with error
    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '{{ session('error') }}',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK'
      });
    @endif
  </script>
</body>
</html>

