<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pesanan Saya - ChickPatrol Store</title>
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
      background: #FEF3C7;
      color: #D97706;
    }
    
    .status-dikirim {
      background: #DBEAFE;
      color: #1E40AF;
    }
    
    .status-selesai {
      background: #D1FAE5;
      color: #065F46;
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
      background: #22C55E;
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
      background: #16a34a;
    }
    
    /* WhatsApp-style chat messages - Buyer Chat (Orders Page) */
    #buyerChatMessages {
      display: flex !important;
      flex-direction: column !important;
      gap: 0.5rem !important;
      width: 100% !important;
      min-height: 0 !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
      scrollbar-width: thin;
      scrollbar-color: #cbd5e0 #f8f9fa;
    }
    
    /* Custom scrollbar for WebKit browsers (Chrome, Safari, Edge) */
    #buyerChatMessages::-webkit-scrollbar {
      width: 8px;
    }
    
    #buyerChatMessages::-webkit-scrollbar-track {
      background: #f8f9fa;
      border-radius: 4px;
    }
    
    #buyerChatMessages::-webkit-scrollbar-thumb {
      background: #cbd5e0;
      border-radius: 4px;
    }
    
    #buyerChatMessages::-webkit-scrollbar-thumb:hover {
      background: #a0aec0;
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
    @media (max-width: 768px) {
      main {
        padding: 1rem !important;
      }
      .order-card {
        padding: 1rem !important;
      }
      .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
      }
      .order-product {
        flex-direction: column;
        gap: 0.75rem;
      }
      .order-product-img {
        width: 100% !important;
        height: 200px !important;
      }
      .order-product-info {
        width: 100% !important;
      }
      .order-actions {
        flex-direction: column;
        width: 100%;
      }
      .order-actions button {
        width: 100%;
      }
      .review-form {
        padding: 0.75rem !important;
      }
    }
  </style>
</head>
<body class="min-h-screen">
  @include('partials.navbar')

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
            <div class="order-date" data-timestamp="{{ $order->created_at?->timestamp ?? time() }}">
              <i class="fa-regular fa-clock me-1"></i>
              <span class="order-time-text">{{ $order->created_at?->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
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
                <img src="{{ $image }}" alt="{{ $product->name }}" class="order-product-img" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iODAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjMwIiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+8J+RozwvdGV4dD48L3N2Zz4=';">
              @else
                <div class="order-product-img d-flex align-items-center justify-content-center" style="background: #f3f4f6;">
                  <i class="fa-solid fa-image text-gray-400"></i>
                </div>
              @endif
              <div class="order-product-info" style="flex: 1;">
                <div class="order-product-name">{{ $product->name }}</div>
                <div class="order-product-qty">{{ $detail->qty }} x Rp {{ number_format($detail->price,0,',','.') }} = Rp {{ number_format($detail->qty * $detail->price,0,',','.') }}</div>
                
                @if($order->status === 'selesai')
                  @php
                    $userReview = $product->reviews->where('user_id', Auth::id())->where('order_id', $order->order_id)->first();
                  @endphp
                  <div class="mt-2" id="reviewSection-{{ $detail->order_detail_id }}">
                    @if($userReview)
                      <div class="review-display" style="background: #f0f9f2; padding: 0.75rem; border-radius: 8px; margin-top: 0.5rem;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                          <div class="rating-display">
                            @for($i = 1; $i <= 5; $i++)
                              <i class="fa-star {{ $i <= $userReview->rating ? 'fa-solid text-warning' : 'fa-regular text-muted' }}"></i>
                            @endfor
                          </div>
                          <small class="text-muted">{{ $userReview->created_at->format('d M Y') }}</small>
                        </div>
                        @if($userReview->review)
                          <p class="mb-0" style="font-size: 0.875rem; color: #2F2F2F;">{{ $userReview->review }}</p>
                        @endif
                        @if($userReview->image)
                          @php
                            // Handle both array (new format) and string (old format)
                            $images = is_array($userReview->image) ? $userReview->image : [$userReview->image];
                            $processedImages = [];
                            foreach ($images as $img) {
                              if ($img && is_string($img)) {
                                if (!preg_match('/^(https?:\/\/|data:)/', $img)) {
                                  if (strpos($img, 'storage/reviews/') !== false) {
                                    $processedImages[] = asset($img);
                                  } else {
                                    $processedImages[] = asset('storage/' . $img);
                                  }
                                } else {
                                  $processedImages[] = $img;
                                }
                              }
                            }
                          @endphp
                          @if(count($processedImages) > 0)
                            <div class="mt-2 d-flex flex-wrap gap-2">
                              @foreach($processedImages as $imgUrl)
                                <img src="{{ $imgUrl }}" alt="Review Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; object-fit: cover; cursor: pointer;" onclick="window.open('{{ $imgUrl }}', '_blank')" onerror="this.onerror=null; this.style.display='none';">
                              @endforeach
                            </div>
                          @endif
                        @endif
                        <button class="btn btn-sm btn-link p-0 mt-1" onclick="editReview('{{ $product->product_id }}', '{{ $order->order_id }}', '{{ $detail->order_detail_id }}')" style="font-size: 0.75rem;">
                          <i class="fa-solid fa-edit me-1"></i> Edit Ulasan
                        </button>
                      </div>
                    @else
                      <button class="btn btn-sm" onclick="showReviewForm('{{ $product->product_id }}', '{{ $order->order_id }}', '{{ $detail->order_detail_id }}')" style="font-size: 0.75rem; margin-top: 0.5rem; border: 1px solid #F59E0B; color: #F59E0B; background: transparent;" onmouseover="this.style.background='#F59E0B'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#F59E0B';">
                        <i class="fa-solid fa-star me-1"></i> Beri Rating & Ulasan
                      </button>
                    @endif
                    
                    <!-- Review Form (hidden by default) -->
                    <div id="reviewForm-{{ $detail->order_detail_id }}" class="review-form" style="display: none; background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 1rem; margin-top: 0.75rem;">
                      <form onsubmit="submitReview(event, '{{ $product->product_id }}', '{{ $order->order_id }}', '{{ $detail->order_detail_id }}')">
                        <div class="mb-2">
                          <label class="form-label" style="font-size: 0.875rem; font-weight: 500;">Rating:</label>
                          <div class="rating-input">
                            @php
                              $currentRating = isset($userReview) && $userReview ? $userReview->rating : 5;
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                              <i class="fa-star {{ $i <= $currentRating ? 'fa-solid' : 'fa-regular' }} rating-star" data-rating="{{ $i }}" onclick="setRating({{ $i }}, '{{ $detail->order_detail_id }}')" style="cursor: pointer; font-size: 1.25rem; color: #ffc107; margin-right: 0.25rem;"></i>
                            @endfor
                            <input type="hidden" name="rating" id="rating-{{ $detail->order_detail_id }}" value="{{ $currentRating }}" required>
                          </div>
                        </div>
                        <div class="mb-2">
                          <label class="form-label" style="font-size: 0.875rem; font-weight: 500;">Ulasan (opsional):</label>
                          <textarea name="review" id="reviewText-{{ $detail->order_detail_id }}" class="form-control" rows="3" placeholder="Bagikan pengalaman Anda..." style="font-size: 0.875rem; resize: none;" maxlength="1000">{{ isset($userReview) && $userReview ? $userReview->review : '' }}</textarea>
                        </div>
                        <div class="mb-2">
                          <label class="form-label" style="font-size: 0.875rem; font-weight: 500;">Foto (opsional, maks 5 foto):</label>
                          <input type="file" name="review_images[]" id="reviewImages-{{ $detail->order_detail_id }}" accept="image/*" multiple class="form-control" style="font-size: 0.875rem;" onchange="previewReviewImages('{{ $detail->order_detail_id }}', this)">
                          <div id="reviewImagesPreview-{{ $detail->order_detail_id }}" class="mt-2">
                            <div class="d-flex flex-wrap gap-2" id="reviewImagesList-{{ $detail->order_detail_id }}">
                              @if(isset($userReview) && $userReview && $userReview->image)
                                @php
                                  $existingImages = is_array($userReview->image) ? $userReview->image : [$userReview->image];
                                  $processedExistingImages = [];
                                  foreach ($existingImages as $img) {
                                    if ($img && is_string($img)) {
                                      $originalPath = $img; // Keep original path for removal tracking
                                      if (!preg_match('/^(https?:\/\/|data:)/', $img)) {
                                        if (strpos($img, 'storage/reviews/') !== false) {
                                          $displayUrl = asset($img);
                                        } else {
                                          $displayUrl = asset('storage/' . $img);
                                        }
                                      } else {
                                        $displayUrl = $img;
                                      }
                                      $processedExistingImages[] = [
                                        'original' => $originalPath,
                                        'display' => $displayUrl
                                      ];
                                    }
                                  }
                                @endphp
                                @foreach($processedExistingImages as $idx => $imgData)
                                  <div class="position-relative existing-image-container" data-original-path="{{ $imgData['original'] }}" style="display: inline-block; margin-right: 8px; margin-bottom: 8px;">
                                    <img src="{{ $imgData['display'] }}" alt="Existing Image" style="max-width: 100px; max-height: 100px; border-radius: 8px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: -5px; right: -5px; padding: 2px 6px; border-radius: 50%; font-size: 10px;" onclick="removeExistingReviewImage('{{ $detail->order_detail_id }}', '{{ $imgData['original'] }}', this)" title="Hapus foto">
                                      <i class="fa-solid fa-times"></i>
                                    </button>
                                  </div>
                                @endforeach
                              @endif
                            </div>
                          </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideReviewForm('{{ $detail->order_detail_id }}')">Batal</button>
                          <button type="submit" class="btn btn-sm btn-primary" style="background: var(--primary-green); border: none;">Kirim</button>
                        </div>
                      </form>
                    </div>
                  </div>
                @endif
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
                <span class="badge" style="background: #8B5CF6; color: white;"><i class="fa-solid fa-qrcode me-1"></i>QRIS</span>
              @elseif($order->payment_method === 'Transfer Bank')
                <span class="badge" style="background: #6B7280; color: white;"><i class="fa-solid fa-building-columns me-1"></i>Transfer Bank</span>
              @else
                <span class="text-muted">Belum dipilih</span>
              @endif
            </div>
            @if($order->payment_status === 'paid' && $order->tracking_number)
              <div class="col-md-12">
                <strong>Nomor Resi:</strong> 
                <span class="text-primary">{{ $order->tracking_number }}</span>
                <a href="https://cekresi.com/?resi={{ $order->tracking_number }}" target="_blank" class="btn btn-sm ms-2" style="border: 1px solid #3B82F6; color: #3B82F6; background: transparent;" onmouseover="this.style.background='#3B82F6'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#3B82F6';">
                  <i class="fa-solid fa-external-link me-1"></i>Cek Resi
                </a>
              </div>
            @elseif($order->payment_status === 'paid' && !$order->tracking_number)
              <div class="col-md-12">
                <strong>Nomor Resi:</strong> 
                <span class="text-muted">Resi akan muncul setelah pesanan dikirim</span>
              </div>
            @elseif($order->payment_status === 'processing')
              <div class="col-md-12">
                <strong>Nomor Resi:</strong> 
                <span class="text-muted">Resi akan muncul setelah pembayaran divalidasi admin dan pesanan dikirim</span>
              </div>
            @else
              <div class="col-md-12">
                <strong>Nomor Resi:</strong> 
                <span class="text-muted">Resi akan muncul setelah pembayaran divalidasi admin dan pesanan dikirim</span>
              </div>
            @endif
            @if($order->payment_status === 'paid')
              <div class="col-md-12 mt-2">
                <span class="badge bg-success">
                  <i class="fa-solid fa-check-circle me-1"></i>Pembayaran Diterima
                </span>
                @if($order->paid_at)
                  <small class="text-muted ms-2">Dibayar pada <span class="paid-time-text" data-timestamp="{{ $order->paid_at instanceof \Carbon\Carbon ? $order->paid_at->timestamp : \Carbon\Carbon::parse($order->paid_at)->timestamp }}">{{ $order->paid_at instanceof \Carbon\Carbon ? $order->paid_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') : \Carbon\Carbon::parse($order->paid_at)->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span></small>
                @endif
              </div>
            @elseif($order->payment_status === 'processing')
              <div class="col-md-12 mt-2">
                <span class="badge bg-info">
                  <i class="fa-solid fa-hourglass-half me-1"></i>Pembayaran di Proses
                </span>
                <small class="text-muted ms-2">Admin sedang memvalidasi pembayaran Anda</small>
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
            <button class="btn btn-sm" style="border: 1px solid #22C55E; color: #22C55E; background: transparent;" onmouseover="this.style.background='#22C55E'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#22C55E';" onclick="openChatForOrder('{{ $order->order_id }}')" title="Chat dengan Penjual">
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
        <div class="modal-body p-0" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
          <div id="buyerChatMessages" style="flex: 1; overflow-y: auto; padding: 1rem; background: #f8f9fa; display: flex; flex-direction: column; gap: 0.5rem; min-height: 0;">
            <div class="text-center p-4 text-gray-500">
              <i class="fa-solid fa-spinner fa-spin"></i> Memuat pesan...
            </div>
          </div>
          <div class="border-top p-3 bg-white">
            <div class="input-group">
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
  <script src="{{ asset('js/navbar.js') }}"></script>
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
    
    // Review functions
    function showReviewForm(productId, orderId, detailId) {
      document.getElementById(`reviewForm-${detailId}`).style.display = 'block';
      const btn = document.getElementById(`reviewSection-${detailId}`).querySelector('.btn');
      if (btn) btn.style.display = 'none';
    }
    
    function hideReviewForm(detailId) {
      document.getElementById(`reviewForm-${detailId}`).style.display = 'none';
      const btn = document.getElementById(`reviewSection-${detailId}`).querySelector('.btn');
      if (btn) btn.style.display = 'inline-block';
    }
    
    function setRating(rating, detailId) {
      document.getElementById(`rating-${detailId}`).value = rating;
      const stars = document.querySelectorAll(`#reviewForm-${detailId} .rating-star`);
      stars.forEach((star, index) => {
        if (index < rating) {
          star.classList.remove('fa-regular');
          star.classList.add('fa-solid');
        } else {
          star.classList.remove('fa-solid');
          star.classList.add('fa-regular');
        }
      });
    }
    
    function previewReviewImages(detailId, input) {
      const files = input.files;
      const previewContainer = document.getElementById(`reviewImagesPreview-${detailId}`);
      const imagesList = document.getElementById(`reviewImagesList-${detailId}`);
      
      if (files.length > 5) {
        Swal.fire({
          icon: 'warning',
          title: 'Terlalu banyak foto',
          text: 'Maksimal 5 foto yang dapat diupload',
          confirmButtonColor: '#69B578'
        });
        input.value = '';
        previewContainer.style.display = 'none';
        imagesList.innerHTML = '';
        return;
      }
      
      imagesList.innerHTML = '';
      
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        reader.onload = function(e) {
          const imgDiv = document.createElement('div');
          imgDiv.className = 'position-relative';
          imgDiv.style.cssText = 'display: inline-block; margin-right: 8px; margin-bottom: 8px;';
          imgDiv.innerHTML = `
            <img src="${e.target.result}" style="max-width: 100px; max-height: 100px; border-radius: 8px; object-fit: cover;">
            <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: -5px; right: -5px; padding: 2px 6px; border-radius: 50%; font-size: 10px;" onclick="removeReviewImageFromPreview('${detailId}', ${i})">
              <i class="fa-solid fa-times"></i>
            </button>
          `;
          imagesList.appendChild(imgDiv);
        };
        reader.readAsDataURL(file);
      }
      
      if (files.length > 0) {
        previewContainer.style.display = 'block';
      }
    }
    
    function removeReviewImageFromPreview(detailId, index) {
      const input = document.getElementById(`reviewImages-${detailId}`);
      const dt = new DataTransfer();
      const files = Array.from(input.files);
      
      files.splice(index, 1);
      files.forEach(file => dt.items.add(file));
      input.files = dt.files;
      
      previewReviewImages(detailId, input);
    }
    
    async function submitReview(e, productId, orderId, detailId) {
      e.preventDefault();
      const form = e.target;
      const rating = form.querySelector('input[name="rating"]').value;
      const review = form.querySelector('textarea[name="review"]').value.trim();
      const imageInput = form.querySelector('input[name="review_images[]"]');
      
      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      
      // Create FormData for file upload
      const formData = new FormData();
      formData.append('order_id', orderId);
      formData.append('rating', parseInt(rating));
      formData.append('review', review);
      
      // Add new images
      if (imageInput && imageInput.files.length > 0) {
        for (let i = 0; i < imageInput.files.length; i++) {
          formData.append('images[]', imageInput.files[i]);
        }
      }
      
      // Add removed images
      const removedInput = document.getElementById(`removedImages-${detailId}`);
      if (removedInput && removedInput.value) {
        const removedImages = removedInput.value.split(',');
        removedImages.forEach(imgPath => {
          if (imgPath.trim()) {
            formData.append('removed_images[]', imgPath.trim());
          }
        });
      }
      
      try {
        const response = await fetch(`/api/products/${productId}/reviews`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Rating dan ulasan berhasil dikirim',
            confirmButtonColor: '#69B578',
            timer: 2000
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(result.message || 'Gagal mengirim review');
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: error.message || 'Terjadi kesalahan saat mengirim review',
          confirmButtonColor: '#dc3545'
        });
      }
    }
    
    function editReview(productId, orderId, detailId) {
      showReviewForm(productId, orderId, detailId);
      // Show existing images preview if any
      const previewContainer = document.getElementById(`reviewImagesPreview-${detailId}`);
      if (previewContainer) {
        const imagesList = document.getElementById(`reviewImagesList-${detailId}`);
        if (imagesList && imagesList.querySelectorAll('.existing-image-container').length > 0) {
          previewContainer.style.display = 'block';
        }
      }
    }
    
    // Real-time time display with WIB timezone
    function formatWIBTime(timestamp) {
      const date = new Date(timestamp * 1000);
      const wibOffset = 7 * 60; // WIB is UTC+7
      const utc = date.getTime() + (date.getTimezoneOffset() * 60000);
      const wibTime = new Date(utc + (wibOffset * 60000));
      
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const day = String(wibTime.getDate()).padStart(2, '0');
      const month = months[wibTime.getMonth()];
      const year = wibTime.getFullYear();
      const hours = String(wibTime.getHours()).padStart(2, '0');
      const minutes = String(wibTime.getMinutes()).padStart(2, '0');
      
      return `${day} ${month} ${year} ${hours}:${minutes} WIB`;
    }
    
    function updateOrderTimes() {
      // Update order creation times
      document.querySelectorAll('.order-date[data-timestamp]').forEach(function(element) {
        const timestamp = parseInt(element.getAttribute('data-timestamp'));
        const timeText = element.querySelector('.order-time-text');
        if (timeText) {
          timeText.textContent = formatWIBTime(timestamp);
        }
      });
      
      // Update paid times
      document.querySelectorAll('.paid-time-text[data-timestamp]').forEach(function(element) {
        const timestamp = parseInt(element.getAttribute('data-timestamp'));
        element.textContent = formatWIBTime(timestamp);
      });
    }
    
    // Update times on page load and every second
    document.addEventListener('DOMContentLoaded', function() {
      updateOrderTimes();
      setInterval(updateOrderTimes, 1000);
    });
  </script>
</body>
</html>

