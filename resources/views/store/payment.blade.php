<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pembayaran - ChickPatrol Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <style>
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    .payment-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 2rem;
      margin-bottom: 1.5rem;
    }
    .account-box {
      background: #f8f9fa;
      border: 2px dashed #69B578;
      border-radius: 8px;
      padding: 1.5rem;
      text-align: center;
      margin: 1rem 0;
    }
    .account-number {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2F2F2F;
      letter-spacing: 2px;
      margin: 0.5rem 0;
    }
    .qris-placeholder {
      width: 200px;
      height: 200px;
      background: #f0f0f0;
      border: 2px dashed #ccc;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 1rem auto;
      color: #999;
    }
    .timer-box {
      background: #fff3cd;
      border: 1px solid #ffc107;
      border-radius: 8px;
      padding: 1rem;
      text-align: center;
      margin: 1rem 0;
    }
    .timer-text {
      font-size: 1.25rem;
      font-weight: 600;
      color: #856404;
    }
  </style>
</head>
<body class="min-h-screen">
  <!-- Navbar -->
  <nav class="navbar bg-white border-bottom">
    <div class="container">
      <a href="{{ route('home') }}" class="navbar-brand text-decoration-none fw-bold">ChickPatrol</a>
      <div class="ms-auto">
        <a href="{{ route('home') }}" class="text-gray-600 text-decoration-none">
          <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <!-- Order Summary -->
        <div class="payment-card">
          <h3 class="mb-4">
            <i class="fa-solid fa-receipt me-2 text-primary"></i>
            Detail Pesanan
          </h3>
          <div class="row mb-3">
            <div class="col-6">
              <small class="text-muted">Nomor Pesanan</small>
              <div class="fw-bold">#{{ substr($order->order_id, 0, 8) }}</div>
            </div>
            <div class="col-6 text-end">
              <small class="text-muted">Tanggal</small>
              <div class="fw-bold">{{ $order->created_at->format('d M Y H:i') }}</div>
            </div>
          </div>
          <hr>
          @php
            $detail = $order->orderDetail->first();
            $product = $detail?->product;
            $qtyTotal = $order->orderDetail->sum('qty');
          @endphp
          <div class="d-flex gap-3 mb-3">
            @if($product && $product->images->first())
              <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
            @endif
            <div class="flex-grow-1">
              <div class="fw-bold">{{ $product->name ?? 'Produk' }}</div>
              <div class="text-muted">{{ $qtyTotal }} x Rp {{ number_format($detail->price ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="fw-bold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
          </div>
          <hr>
          <div class="d-flex justify-content-between">
            <span class="fw-bold">Total Pembayaran</span>
            <span class="fw-bold fs-5 text-primary">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="payment-card">
          <h4 class="mb-4">
            <i class="fa-solid fa-credit-card me-2 text-success"></i>
            Metode Pembayaran: {{ $order->payment_method }}
          </h4>
          
          @if($order->payment_method === 'QRIS')
            <div class="account-box">
              <div class="mb-3">
                <i class="fa-solid fa-qrcode fa-3x text-primary"></i>
              </div>
              <div class="qris-placeholder">
                <div>
                  <i class="fa-solid fa-qrcode fa-4x text-muted mb-2"></i>
                  <div class="text-muted">QR Code</div>
                </div>
              </div>
              <div class="mt-3">
                <small class="text-muted">Scan QR Code dengan aplikasi pembayaran Anda</small>
              </div>
              <div class="account-number mt-3">{{ $paymentAccount['account'] }}</div>
            </div>
          @elseif($order->payment_method === 'Transfer Bank')
            <div class="account-box">
              <div class="mb-3">
                <i class="fa-solid fa-building-columns fa-3x text-primary"></i>
              </div>
              <div>
                <small class="text-muted d-block">Bank</small>
                <div class="fw-bold fs-5">{{ $paymentAccount['name'] }}</div>
              </div>
              <div class="mt-3">
                <small class="text-muted d-block">Nomor Rekening</small>
                <div class="account-number">{{ number_format($paymentAccount['account'], 0, '.', '.') }}</div>
              </div>
              <div class="mt-3">
                <small class="text-muted d-block">Atas Nama</small>
                <div class="fw-bold">{{ $paymentAccount['account_name'] }}</div>
              </div>
            </div>
          @endif

          <div class="timer-box">
            <div class="text-muted mb-2">Batas Waktu Pembayaran</div>
            <div class="timer-text" id="countdown">23:59:59</div>
            <small class="text-muted">Selesaikan pembayaran sebelum waktu habis</small>
          </div>

          <div class="alert alert-info mt-3">
            <i class="fa-solid fa-info-circle me-2"></i>
            <strong>Penting:</strong> Setelah melakukan pembayaran, klik tombol "Konfirmasi Pembayaran" di bawah untuk mempercepat proses verifikasi.
          </div>
        </div>

        <!-- Payment Status -->
        @if($order->payment_status === 'paid')
          <div class="alert alert-success">
            <i class="fa-solid fa-check-circle me-2"></i>
            <strong>Pembayaran Diterima!</strong> Pesanan Anda sedang diproses.
          </div>
        @else
          <div class="payment-card">
            <button class="btn btn-success btn-lg w-100" onclick="confirmPayment('{{ $order->order_id }}')">
              <i class="fa-solid fa-check me-2"></i>
              Saya Sudah Membayar
            </button>
            <div class="text-center mt-3">
              <small class="text-muted">
                Klik tombol ini setelah Anda melakukan transfer atau scan QRIS
              </small>
            </div>
          </div>
        @endif
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script>
    // Countdown timer (24 hours from order creation)
    const orderDate = new Date('{{ $order->created_at }}');
    const expiryDate = new Date(orderDate.getTime() + 24 * 60 * 60 * 1000);
    
    function updateCountdown() {
      const now = new Date();
      const diff = expiryDate - now;
      
      if (diff <= 0) {
        document.getElementById('countdown').textContent = '00:00:00';
        return;
      }
      
      const hours = Math.floor(diff / (1000 * 60 * 60));
      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((diff % (1000 * 60)) / 1000);
      
      document.getElementById('countdown').textContent = 
        String(hours).padStart(2, '0') + ':' + 
        String(minutes).padStart(2, '0') + ':' + 
        String(seconds).padStart(2, '0');
    }
    
    setInterval(updateCountdown, 1000);
    updateCountdown();
    
    async function confirmPayment(orderId) {
      const result = await Swal.fire({
        title: 'Konfirmasi Pembayaran?',
        text: 'Pastikan Anda sudah melakukan pembayaran sesuai dengan instruksi di atas',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#69B578',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Saya Sudah Membayar',
        cancelButtonText: 'Batal'
      });
      
      if (result.isConfirmed) {
        try {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          const response = await fetch(`/order/${orderId}/confirm-payment`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            }
          });
          
          const data = await response.json();
          
          if (data.success) {
            await Swal.fire({
              icon: 'success',
              title: 'Pembayaran Diterima!',
              text: data.message,
              confirmButtonColor: '#69B578',
              confirmButtonText: 'OK'
            });
            
            if (data.redirect) {
              window.location.href = data.redirect;
            } else {
              window.location.reload();
            }
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
          console.error('Error confirming payment:', error);
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan saat mengonfirmasi pembayaran',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
          });
        }
      }
    }
  </script>
</body>
</html>

