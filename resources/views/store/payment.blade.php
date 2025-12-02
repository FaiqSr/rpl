<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pembayaran - ChickPatrol Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS via Vite -->
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
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
      width: 250px;
      height: 250px;
      background: white;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 1rem auto;
      color: #999;
      overflow: hidden;
    }
    .timer-box {
      background: #fff8e1;
      border: 2px solid #69B578;
      border-radius: 8px;
      padding: 1rem;
      text-align: center;
      margin: 1rem 0;
    }
    .timer-text {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2F2F2F;
    }
    
    @media (max-width: 768px) {
      main {
        padding: 1rem !important;
      }
      .payment-card {
        padding: 1rem !important;
      }
      .account-box {
        padding: 1rem !important;
      }
      .account-number {
        font-size: 1.25rem !important;
        letter-spacing: 1px !important;
      }
      .qris-placeholder {
        width: 200px !important;
        height: 200px !important;
      }
      .timer-box {
        padding: 0.75rem !important;
      }
      .timer-text {
        font-size: 1rem !important;
      }
      .row {
        margin: 0 !important;
      }
      .col-lg-8 {
        padding: 0 !important;
      }
    }
  </style>
</head>
<body class="min-h-screen">
  @include('partials.navbar')

  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <!-- Order Summary -->
        <div class="payment-card">
          <h3 class="mb-4" style="color: #2F2F2F; font-weight: 600;">
            <i class="fa-solid fa-receipt me-2" style="color: #69B578;"></i>
            Detail Pesanan
          </h3>
          <div class="row mb-3">
            <div class="col-6">
              <small class="text-muted">Nomor Pesanan</small>
              <div class="fw-bold">#{{ substr($order->order_id, 0, 8) }}</div>
            </div>
            <div class="col-6 text-end">
              <small class="text-muted">Tanggal</small>
              <div class="fw-bold">{{ $order->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</div>
            </div>
          </div>
          <hr>
          @foreach($order->orderDetail as $detail)
            @php
              $product = $detail->product;
              $imageObj = $product?->images?->first();
              $imageUrl = null;
              if ($imageObj) {
                $imageUrl = $imageObj->url;
                // Jika URL tidak dimulai dengan http atau data:, tambahkan asset()
                if ($imageUrl && !preg_match('/^(https?:\/\/|data:)/', $imageUrl)) {
                  // Jika sudah ada storage/products, gunakan asset
                  if (strpos($imageUrl, 'storage/products/') !== false) {
                    $imageUrl = asset($imageUrl);
                  } else {
                    $imageUrl = asset('storage/' . $imageUrl);
                  }
                }
              }
            @endphp
            <div class="d-flex gap-3 mb-3">
              @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iODAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZHVjdDwvdGV4dD48L3N2Zz4='; this.style.display='block';">
              @else
                <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                  <i class="fa-solid fa-image text-gray-400"></i>
                </div>
              @endif
              <div class="flex-grow-1">
                <div class="fw-bold">{{ $product->name ?? 'Produk' }}</div>
                <div class="text-muted">{{ $detail->qty }} x Rp {{ number_format($detail->price ?? 0, 0, ',', '.') }} = Rp {{ number_format($detail->qty * $detail->price, 0, ',', '.') }}</div>
              </div>
            </div>
          @endforeach
          <hr>
          <div class="d-flex justify-content-between">
            <span class="fw-bold">Total Pembayaran</span>
            <span class="fw-bold fs-5" style="color: #69B578;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="payment-card">
          <h4 class="mb-4" style="color: #2F2F2F; font-weight: 600;">
            <i class="fa-solid fa-credit-card me-2" style="color: #69B578;"></i>
            Metode Pembayaran: {{ $order->payment_method }}
          </h4>
          
          @if($order->payment_method === 'QRIS')
            <div class="account-box">
              <div class="qris-placeholder" style="border: none; background: white; padding: 1rem;">
                <img src="https://d2v6npc8wmnkqk.cloudfront.net/storage/26035/conversions/Tipe-QRIS-statis-small-large.jpg" alt="QR Code Pembayaran QRIS" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="display: none; flex-direction: column; align-items: center; justify-content: center;">
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
                <i class="fa-solid fa-building-columns fa-3x" style="color: #69B578;"></i>
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

          <div class="alert alert-info mt-3" style="background-color: #e7f3ff; border-color: #69B578; color: #2F2F2F;">
            <i class="fa-solid fa-info-circle me-2" style="color: #69B578;"></i>
            <strong>Penting:</strong> Setelah melakukan pembayaran, klik tombol "Konfirmasi Pembayaran" di bawah untuk mempercepat proses verifikasi.
          </div>
        </div>

        <!-- Payment Status -->
        @if($order->payment_status === 'paid')
          <div class="alert alert-success" style="background-color: #d4edda; border-color: #69B578; color: #155724;">
            <i class="fa-solid fa-check-circle me-2" style="color: #69B578;"></i>
            <strong>Pembayaran Diterima!</strong> Pesanan Anda sedang diproses.
          </div>
        @elseif($order->payment_status === 'processing')
          <div class="alert alert-info" style="background-color: #e7f3ff; border-color: #69B578; color: #2F2F2F;">
            <i class="fa-solid fa-hourglass-half me-2" style="color: #69B578;"></i>
            <strong>Pembayaran Sedang Diproses</strong> Admin sedang memvalidasi pembayaran Anda. Mohon tunggu.
          </div>
        @else
          <div class="payment-card">
            <button class="btn btn-success btn-lg w-100" onclick="confirmPayment('{{ $order->order_id }}')" style="background-color: #69B578; border-color: #69B578; font-weight: 600;">
              <i class="fa-solid fa-check me-2"></i>
              Saya Sudah Membayar
            </button>
            <div class="text-center mt-3">
              <small class="text-muted">
                Klik tombol ini setelah Anda melakukan transfer atau scan QRIS. Admin akan memvalidasi pembayaran Anda.
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
    
    // Update countdown every second
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
          if (!csrfToken) {
            throw new Error('CSRF token tidak ditemukan. Silakan refresh halaman.');
          }
          
          const response = await fetch(`/order/${orderId}/confirm-payment`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            }
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
              confirmButtonColor: '#dc3545',
              confirmButtonText: 'OK'
            });
            return;
          }
          
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
          if (error instanceof SyntaxError) {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'Terjadi kesalahan: Server mengembalikan respons yang tidak valid',
              confirmButtonColor: '#dc3545',
              confirmButtonText: 'OK'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: error.message || 'Terjadi kesalahan saat mengonfirmasi pembayaran',
              confirmButtonColor: '#dc3545',
              confirmButtonText: 'OK'
            });
          }
        }
      }
    }
    
    // Update cart and chat badge count
    @if(Auth::check())
    function updateCartCount() {
      fetch('{{ route("cart.count") }}')
        .then(res => res.json())
        .then(data => {
          const badge = document.getElementById('cartBadge');
          if (badge) {
            if (data.count > 0) {
              badge.textContent = data.count;
              badge.style.display = 'inline-block';
            } else {
              badge.style.display = 'none';
            }
          }
        })
        .catch(err => {
          console.error('Error loading cart count:', err);
        });
    }
    
    function updateChatCount() {
      const csrfToken = document.querySelector('meta[name="csrf-token"]');
      if (!csrfToken) {
        console.error('CSRF token not found');
        return;
      }
      
      fetch('/api/chat/unread-count', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken.content
        },
        credentials: 'same-origin'
      })
        .then(res => {
          if (!res.ok) {
            throw new Error('Network response was not ok');
          }
          return res.json();
        })
        .then(data => {
          const badge = document.getElementById('chatBadge');
          if (badge) {
            const unreadCount = data.unread_count || 0;
            if (unreadCount > 0) {
              badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
              badge.style.display = 'inline-block';
            } else {
              badge.style.display = 'none';
            }
          }
        })
        .catch(err => {
          console.error('Error loading chat count:', err);
          const badge = document.getElementById('chatBadge');
          if (badge) {
            badge.style.display = 'none';
          }
        });
    }
    
    // Update cart and chat count on page load and every 30 seconds
    document.addEventListener('DOMContentLoaded', function() {
      updateCartCount();
      updateChatCount();
      setInterval(updateCartCount, 30000);
      setInterval(updateChatCount, 30000);
    });
    @endif
  </script>
</body>
</html>

