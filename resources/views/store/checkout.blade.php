<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Checkout - ChickPatrol Store</title>
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
    body { 
      background: #FAFAF8; 
      font-family: 'Inter', -apple-system, sans-serif; 
    }
    
    .checkout-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border: 1px solid #e9ecef;
    }
    
    .checkout-header {
      font-size: 1.125rem;
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .address-card {
      background: white;
      border-radius: 12px;
      padding: 1.25rem;
      border: 1px solid #e9ecef;
      margin-bottom: 1rem;
    }
    
    .address-label {
      font-size: 0.875rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
    }
    
    .address-content {
      display: flex;
      align-items: flex-start;
      gap: 0.75rem;
    }
    
    .address-icon {
      color: var(--primary-green);
      font-size: 1.25rem;
      margin-top: 0.25rem;
    }
    
    .address-text {
      flex: 1;
    }
    
    .address-name {
      font-weight: 600;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
    }
    
    .address-detail {
      font-size: 0.875rem;
      color: #6c757d;
      line-height: 1.5;
    }
    
    .btn-change {
      background: #f8f9fa;
      border: 1px solid #e9ecef;
      color: #6c757d;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-size: 0.875rem;
      transition: all 0.2s;
    }
    
    .btn-change:hover {
      background: #e9ecef;
      color: #2F2F2F;
    }
    
    .product-item {
      display: flex;
      gap: 1rem;
      padding: 1rem 0;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .product-item:last-child {
      border-bottom: none;
    }
    
    .product-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      flex-shrink: 0;
    }
    
    .product-info {
      flex: 1;
    }
    
    .product-name {
      font-size: 0.9375rem;
      font-weight: 500;
      color: #2F2F2F;
      margin-bottom: 0.5rem;
      line-height: 1.4;
    }
    
    .product-price {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .shipping-category-selector {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
      cursor: pointer;
      position: relative;
    }
    
    .shipping-category-selector:hover {
      border-color: var(--primary-green);
    }
    
    .shipping-category-selector-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .shipping-category-selector-name {
      font-weight: 600;
      color: #2F2F2F;
      font-size: 0.9375rem;
    }
    
    .shipping-category-selector-icon {
      color: #6c757d;
      transition: transform 0.2s;
    }
    
    .shipping-category-selector.active .shipping-category-selector-icon {
      transform: rotate(180deg);
    }
    
    .shipping-category-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #e9ecef;
      border-top: none;
      border-radius: 0 0 8px 8px;
      margin-top: -1px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
      z-index: 10;
    }
    
    .shipping-category-selector.active .shipping-category-dropdown {
      max-height: 200px;
      overflow-y: auto;
    }
    
    .shipping-category-option {
      padding: 0.75rem 1rem;
      cursor: pointer;
      transition: background 0.2s;
    }
    
    .shipping-category-option:hover {
      background: #f8f9fa;
    }
    
    .shipping-category-option.selected {
      background: #f0f9f2;
      color: var(--primary-green);
      font-weight: 600;
    }
    
    .shipping-service-selector {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 0;
      cursor: pointer;
      position: relative;
    }
    
    .shipping-service-selector:hover {
      border-color: var(--primary-green);
    }
    
    .shipping-service-selector-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .shipping-service-selector-info {
      flex: 1;
    }
    
    .shipping-service-selector-name {
      font-weight: 600;
      color: #2F2F2F;
      font-size: 0.9375rem;
      margin-bottom: 0.25rem;
    }
    
    .shipping-service-selector-estimate {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .shipping-service-selector-icon {
      color: #6c757d;
      transition: transform 0.2s;
      margin-left: 1rem;
    }
    
    .shipping-service-selector.active .shipping-service-selector-icon {
      transform: rotate(180deg);
    }
    
    .shipping-service-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #e9ecef;
      border-top: none;
      border-radius: 0 0 8px 8px;
      margin-top: -1px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
      z-index: 10;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .shipping-service-selector.active .shipping-service-dropdown {
      max-height: 500px;
      overflow-y: auto;
    }
    
    .shipping-options-list {
      display: none;
      padding: 0.5rem 0;
    }
    
    .shipping-options-list.active {
      display: block;
    }
    
    .shipping-option {
      padding: 0.875rem 1rem;
      cursor: pointer;
      transition: background 0.2s;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .shipping-option:last-child {
      border-bottom: none;
    }
    
    .shipping-option:hover {
      background: #f8fff9;
    }
    
    .shipping-option.selected {
      background: #f0f9f2;
    }
    
    .shipping-option-name {
      font-weight: 500;
      color: #2F2F2F;
      margin-bottom: 0.25rem;
      font-size: 0.9375rem;
    }
    
    .shipping-estimate {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .shipping-empty-state {
      text-align: center;
      padding: 2rem;
      color: #6c757d;
      font-size: 0.875rem;
    }
    
    .notes-input {
      width: 100%;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 0.75rem;
      font-size: 0.875rem;
      resize: none;
    }
    
    .notes-input:focus {
      outline: none;
      border-color: var(--primary-green);
    }
    
    .payment-option {
      background: white;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 0.75rem;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .payment-option:hover {
      border-color: var(--primary-green);
    }
    
    .payment-option.selected {
      border-color: var(--primary-green);
      background: #f0f9f2;
    }
    
    .payment-radio {
      width: 20px;
      height: 20px;
      accent-color: var(--primary-green);
    }
    
    .payment-label {
      flex: 1;
      font-weight: 500;
      color: #2F2F2F;
    }
    
    .summary-row {
      display: flex;
      justify-content: space-between;
      padding: 0.75rem 0;
      font-size: 0.9375rem;
      color: #2F2F2F;
    }
    
    .summary-row.total {
      border-top: 2px solid #e9ecef;
      margin-top: 0.5rem;
      padding-top: 1rem;
      font-size: 1.25rem;
      font-weight: 700;
      color: #2F2F2F;
    }
    
    .btn-pay {
      width: 100%;
      padding: 1rem;
      background: var(--primary-green);
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .btn-pay:hover {
      background: var(--dark-green);
    }
    
    .btn-pay:disabled {
      background: #ccc;
      cursor: not-allowed;
    }
    
    .promo-btn {
      width: 100%;
      padding: 0.875rem;
      background: #f0f9f2;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      color: var(--primary-green);
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .promo-btn:hover {
      background: #e0f5e5;
    }
    
    @media (max-width: 768px) {
      main {
        padding: 1rem !important;
      }
      .checkout-card {
        padding: 1rem !important;
      }
      .address-card {
        padding: 1rem !important;
      }
      .checkout-header {
        font-size: 1rem !important;
      }
      .row {
        margin: 0 !important;
      }
      .col-lg-8, .col-lg-4 {
        padding: 0 !important;
        margin-bottom: 1rem;
      }
      .shipping-category-options {
        flex-direction: column !important;
      }
      .shipping-category-option {
        width: 100% !important;
      }
    }
  </style>
</head>
<body>
  @include('partials.navbar')

  <main class="container py-5">
    <div class="row">
      <!-- Left Column: Shipping & Product Details -->
      <div class="col-lg-8">
        <!-- Shipping Address -->
        <div class="checkout-card">
          <div class="checkout-header">
            <i class="fa-solid fa-location-dot" style="color: var(--primary-green);"></i>
            ALAMAT PENGIRIMAN
          </div>
          <div class="address-card">
            <div class="address-label">Alamat Pengiriman</div>
            <div class="address-content">
              <i class="fa-solid fa-map-marker-alt address-icon"></i>
              <div class="address-text">
                <div class="address-name">{{ Auth::user()->name }}</div>
                <div class="address-detail">
                  {{ Auth::user()->address ?? 'Alamat belum diisi' }}<br>
                  {{ Auth::user()->phone ?? '' }}
                </div>
              </div>
              <button class="btn-change" onclick="changeAddress()">Ganti</button>
            </div>
          </div>
        </div>

        <!-- Product Details -->
        <div class="checkout-card">
          <div class="checkout-header">
            <i class="fa-solid fa-store" style="color: var(--primary-green);"></i>
            CHICKPATROL STORE
          </div>
          @foreach($cartItems as $item)
          <div class="product-item">
            @php
              $image = $item->product->images->first();
              $imageUrl = $image ? $image->url : "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iODAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+8J+RozwvdGV4dD48L3N2Zz4=";
            @endphp
            <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="product-image" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48cmVjdCB3aWR0aD0iODAiIGhlaWdodD0iODAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNmI3MjgwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+8J+RozwvdGV4dD48L3N2Zz4=';">
            <div class="product-info">
              <div class="product-name">{{ $item->product->name }}</div>
              <div class="product-price">{{ $item->qty }} x Rp {{ number_format($item->product->price, 0, ',', '.') }}</div>
            </div>
          </div>
          @endforeach
        </div>

        <!-- Shipping Options -->
        <div class="checkout-card">
          <div class="checkout-header">
            <i class="fa-solid fa-truck" style="color: var(--primary-green);"></i>
            PENGIRIMAN
          </div>
          
          <!-- Kotak 1: Pilih Kategori -->
          <div class="shipping-category-selector" onclick="toggleCategorySelector(this)" id="categorySelector">
            <div class="shipping-category-selector-header">
              <div class="shipping-category-selector-name" id="selectedCategory">Reguler</div>
              <i class="fa-solid fa-chevron-down shipping-category-selector-icon"></i>
            </div>
            <div class="shipping-category-dropdown">
              <div class="shipping-category-option selected" data-category="reguler" onclick="selectCategory('reguler', event)">
                Reguler
              </div>
              <div class="shipping-category-option" data-category="kargo" onclick="selectCategory('kargo', event)">
                Kargo
              </div>
            </div>
          </div>
          
          <!-- Kotak 2: Daftar Jasa Kirim (Dropdown) -->
          <div class="shipping-service-selector" onclick="toggleServiceSelector(this)" id="serviceSelector">
            <div class="shipping-service-selector-header">
              <div class="shipping-service-selector-info" id="selectedServiceInfo">
                <div class="shipping-service-selector-name" id="selectedServiceName">Pilih Jasa Kirim</div>
                <div class="shipping-service-selector-estimate" id="selectedServiceEstimate"></div>
              </div>
              <i class="fa-solid fa-chevron-down shipping-service-selector-icon"></i>
            </div>
            <div class="shipping-service-dropdown">
              <!-- Opsi Reguler -->
              <div class="shipping-options-list active" id="regulerOptions">
                <div class="shipping-option selected" data-service="JNE" data-cost="10000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">JNE</div>
                  <div class="shipping-estimate">Estimasi tiba 2-3 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="JNT" data-cost="12000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">J&T Express</div>
                  <div class="shipping-estimate">Estimasi tiba 1-2 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="SiCepat" data-cost="15000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">SiCepat</div>
                  <div class="shipping-estimate">Estimasi tiba 1 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="Pos Indonesia" data-cost="8000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">Pos Indonesia</div>
                  <div class="shipping-estimate">Estimasi tiba 3-4 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="Wahana" data-cost="11000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">Wahana</div>
                  <div class="shipping-estimate">Estimasi tiba 2-3 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="Lion Parcel" data-cost="13000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">Lion Parcel</div>
                  <div class="shipping-estimate">Estimasi tiba 1-2 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="Ninja Express" data-cost="14000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">Ninja Express</div>
                  <div class="shipping-estimate">Estimasi tiba 1 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="AnterAja" data-cost="12000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">AnterAja</div>
                  <div class="shipping-estimate">Estimasi tiba 1-2 hari kerja</div>
                </div>
              </div>
              
              <!-- Opsi Kargo -->
              <div class="shipping-options-list" id="kargoOptions">
                <div class="shipping-option" data-service="JNE Cargo" data-cost="25000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">JNE Cargo</div>
                  <div class="shipping-estimate">Estimasi tiba 5-7 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="Tiki Cargo" data-cost="28000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">Tiki Cargo</div>
                  <div class="shipping-estimate">Estimasi tiba 5-7 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="Pahala Express" data-cost="30000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">Pahala Express</div>
                  <div class="shipping-estimate">Estimasi tiba 5-7 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="Dakota Cargo" data-cost="27000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">Dakota Cargo</div>
                  <div class="shipping-estimate">Estimasi tiba 5-7 hari kerja</div>
                </div>
                <div class="shipping-option" data-service="Indah Logistik" data-cost="26000" onclick="selectShipping(this, event)">
                  <div class="shipping-option-name">Indah Logistik</div>
                  <div class="shipping-estimate">Estimasi tiba 5-7 hari kerja</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="checkout-card">
          <div class="checkout-header">
            <i class="fa-solid fa-note-sticky" style="color: var(--primary-green);"></i>
            CATATAN
          </div>
          <textarea name="notes" id="notes" class="notes-input" rows="3" placeholder="Kasih catatan untuk penjual (opsional)" maxlength="200"></textarea>
          <div class="text-end mt-2">
            <small class="text-muted"><span id="noteCount">0</span>/200</small>
          </div>
        </div>
      </div>

      <!-- Right Column: Payment & Summary -->
      <div class="col-lg-4">
        <div class="checkout-card" style="position: sticky; top: 100px;">
          <!-- Payment Methods -->
          <div class="checkout-header">
            <i class="fa-solid fa-credit-card" style="color: var(--primary-green);"></i>
            METODE PEMBAYARAN
          </div>
          <div class="payment-option selected" data-method="QRIS">
            <input type="radio" name="payment_method" class="payment-radio" value="QRIS" checked>
            <label class="payment-label">QRIS</label>
          </div>
          <div class="payment-option" data-method="Transfer Bank">
            <input type="radio" name="payment_method" class="payment-radio" value="Transfer Bank">
            <label class="payment-label">Transfer Bank</label>
          </div>

          <!-- Summary -->
          <div class="summary-row total">
            <span>Total Harga ({{ $cartItems->sum('qty') }} Barang)</span>
            <span id="subtotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
          </div>

          <!-- Pay Button -->
          <button class="btn-pay" onclick="processPayment()">
            <i class="fa-solid fa-check"></i>
            Bayar Sekarang
          </button>
          
          <p class="text-center mt-3" style="font-size: 0.75rem; color: #6c757d;">
            Dengan melanjutkan pembayaran, kamu menyetujui S&K Asuransi Pengiriman & Proteksi.
          </p>
        </div>
      </div>
    </div>
  </main>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/navbar.js') }}"></script>
  
  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const selectedCartIds = @json($selectedCartIds);
    let selectedShipping = { service: 'JNE', cost: 10000 };
    let selectedPayment = 'QRIS';
    const baseSubtotal = {{ $subtotal }};
    
    let selectedCategory = 'reguler';
    
    // Toggle category selector dropdown
    function toggleCategorySelector(selector) {
      selector.classList.toggle('active');
      // Close service selector if category selector is opened
      document.getElementById('serviceSelector').classList.remove('active');
    }
    
    // Toggle service selector dropdown
    function toggleServiceSelector(selector) {
      selector.classList.toggle('active');
      // Close category selector if service selector is opened
      document.getElementById('categorySelector').classList.remove('active');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const categorySelector = document.getElementById('categorySelector');
      const serviceSelector = document.getElementById('serviceSelector');
      
      if (!categorySelector.contains(event.target)) {
        categorySelector.classList.remove('active');
      }
      
      if (!serviceSelector.contains(event.target)) {
        serviceSelector.classList.remove('active');
      }
    });
    
    // Select category
    function selectCategory(category, event) {
      event.stopPropagation();
      
      selectedCategory = category;
      
      // Update selected category name
      document.getElementById('selectedCategory').textContent = category === 'reguler' ? 'Reguler' : 'Kargo';
      
      // Update category options
      document.querySelectorAll('.shipping-category-option').forEach(opt => {
        opt.classList.remove('selected');
        if (opt.dataset.category === category) {
          opt.classList.add('selected');
        }
      });
      
      // Show/hide shipping options
      document.getElementById('regulerOptions').classList.toggle('active', category === 'reguler');
      document.getElementById('kargoOptions').classList.toggle('active', category === 'kargo');
      
      // Reset selected shipping option
      document.querySelectorAll('.shipping-option').forEach(opt => opt.classList.remove('selected'));
      
      // Set default selection for new category
      if (category === 'reguler') {
        const firstOption = document.querySelector('#regulerOptions .shipping-option');
        if (firstOption) {
          firstOption.classList.add('selected');
          updateSelectedService(firstOption);
          selectedShipping = {
            service: firstOption.dataset.service,
            cost: parseInt(firstOption.dataset.cost)
          };
        }
      } else {
        const firstOption = document.querySelector('#kargoOptions .shipping-option');
        if (firstOption) {
          firstOption.classList.add('selected');
          updateSelectedService(firstOption);
          selectedShipping = {
            service: firstOption.dataset.service,
            cost: parseInt(firstOption.dataset.cost)
          };
        }
      }
      
      // Close dropdown
      document.getElementById('categorySelector').classList.remove('active');
    }
    
    // Update selected service display
    function updateSelectedService(option) {
      const serviceName = option.querySelector('.shipping-option-name').textContent;
      const serviceEstimate = option.querySelector('.shipping-estimate').textContent;
      
      document.getElementById('selectedServiceName').textContent = serviceName;
      document.getElementById('selectedServiceEstimate').textContent = serviceEstimate;
    }
    
    // Select shipping option
    function selectShipping(option, event) {
      event.stopPropagation();
      
      // Remove selected from all options in current category
      const currentOptionsList = option.closest('.shipping-options-list');
      currentOptionsList.querySelectorAll('.shipping-option').forEach(opt => opt.classList.remove('selected'));
      
      // Add selected to clicked option
      option.classList.add('selected');
      
      // Update selected shipping
      selectedShipping = {
        service: option.dataset.service,
        cost: parseInt(option.dataset.cost)
      };
      
      // Update display
      updateSelectedService(option);
      
      // Close dropdown
      document.getElementById('serviceSelector').classList.remove('active');
    }
    
    // Initialize default selection
    document.addEventListener('DOMContentLoaded', function() {
      const defaultOption = document.querySelector('#regulerOptions .shipping-option.selected');
      if (defaultOption) {
        updateSelectedService(defaultOption);
      }
    });
    
    // Payment option selection
    document.querySelectorAll('.payment-option').forEach(option => {
      option.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('.payment-radio');
        radio.checked = true;
        selectedPayment = radio.value;
      });
    });
    
    // Notes character counter
    document.getElementById('notes').addEventListener('input', function() {
      document.getElementById('noteCount').textContent = this.value.length;
    });
    
    // Update total (ongkir tidak ditampilkan, hanya untuk internal)
    function updateTotal() {
      // Ongkir tidak ditampilkan di UI, hanya disimpan untuk backend
      // Total yang ditampilkan hanya subtotal produk
    }
    
    // Process payment
    function processPayment() {
      // Validasi jasa kirim dipilih
      if (!selectedShipping.service || selectedShipping.service === '') {
        Swal.fire({
          icon: 'warning',
          title: 'Pilih Jasa Kirim',
          text: 'Silakan pilih jasa kirim terlebih dahulu',
          confirmButtonColor: '#69B578'
        });
        return;
      }
      
      const notes = document.getElementById('notes').value;
      
      const data = {
        buyer_name: '{{ Auth::user()->name }}',
        address: '{{ Auth::user()->address ?? "" }}',
        phone: '{{ Auth::user()->phone ?? "" }}',
        notes: notes,
        shipping_service: selectedShipping.service,
        payment_method: selectedPayment,
        selected_cart_ids: selectedCartIds
      };
      
      fetch('{{ route("cart.checkout") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(data)
      })
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          window.location.href = result.redirect;
        } else {
          throw new Error(result.message || 'Gagal membuat pesanan');
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: error.message || 'Terjadi kesalahan saat membuat pesanan',
          confirmButtonColor: '#dc3545'
        });
      });
    }
    
    function changeAddress() {
      // Simpan selectedCartIds ke session storage untuk kembali ke checkout
      if (selectedCartIds && selectedCartIds.length > 0) {
        sessionStorage.setItem('checkout_cart_ids', JSON.stringify(selectedCartIds));
      }
      // Redirect ke profile dengan parameter return_to
      window.location.href = '{{ route("profile") }}?return_to=checkout';
    }
    
  </script>
</body>
</html>

