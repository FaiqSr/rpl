<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ChickPatrol Store</title>
  <!-- Bootstrap 5 (minimal usage) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" />
  <style>
    :root {
      --primary-green: #69B578;
      --dark-green: #5a8c64;
      --cream: #F5E6D3;
      --yellow: #F4C430;
      --light-yellow: #FFF8DC;
      --brown: #6B5D4F;
      --light-green: #8FBC8F;
    }
    body { background:#FAFAF8; font-family: 'Inter', -apple-system, sans-serif; }
    .skeleton { background:#F5E6D3; border-radius:12px; }
    .chip { background:#FFF8DC; border:1px solid #F4C430; color: #6B5D4F; }
    .chip.active { border-color:#F4C430; background: #F4C430; color:#2F2F2F; font-weight: 600; }
    .card-border { border:1px solid #e6ebe7; transition: all 0.2s; }
    .card-border:hover { border-color: #F4C430; box-shadow: 0 2px 8px rgba(244, 196, 48, 0.15); }
    .footer-bg { background:#F5E6D3; }
    
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
      flex-shrink: 0;
    }
    
    .navbar-category {
      position: relative;
      flex-shrink: 0;
    }
    
    .navbar-category-btn {
      padding: 0.5rem 1rem;
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      font-size: 0.875rem;
      color: #2F2F2F;
      cursor: pointer;
      white-space: nowrap;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      min-width: 120px;
      justify-content: space-between;
    }
    
    .navbar-category-btn:hover {
      background: #f8f9fa;
    }
    
    .navbar-search {
      flex: 1;
      position: relative;
      max-width: 600px;
    }
    
    .navbar-search input {
      width: 100%;
      padding: 0.5rem 1rem;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      font-size: 0.875rem;
      background: #fafafa;
      transition: all 0.2s;
    }
    
    .navbar-search input:focus {
      outline: none;
      border-color: #69B578;
      background: white;
    }
    
    .navbar-search input::placeholder {
      color: #9ca3af;
    }
    
    .navbar-actions {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-shrink: 0;
      margin-left: auto;
    }
    
    .btn-outline-secondary {
      padding: 0.5rem 1rem;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      background: white;
      color: #2F2F2F;
      font-size: 0.875rem;
      font-weight: 500;
      text-decoration: none;
      white-space: nowrap;
      transition: all 0.2s;
    }
    
    .btn-outline-secondary:hover {
      background: #f8f9fa;
      color: #2F2F2F;
    }
    
    .btn-primary {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 6px;
      background: var(--primary-green);
      color: white;
      font-size: 0.875rem;
      font-weight: 500;
      text-decoration: none;
      white-space: nowrap;
      transition: all 0.2s;
    }
    
    .btn-primary:hover {
      background: var(--dark-green);
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(105, 181, 120, 0.3);
    }
    
    .navbar-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid #e9ecef;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #2F2F2F;
      text-decoration: none;
      transition: all 0.2s;
    }
    
    .navbar-icon:hover {
      background: #f8f9fa;
    }
    
    .category-dropdown {
      position: absolute;
      top: calc(100% + 0.5rem);
      left: 0;
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.06);
      display: none;
      width: 380px;
      z-index: 1000;
      padding: 0;
      overflow: hidden;
    }
    
    .category-dropdown.show {
      display: block;
    }
    /* Header row like mock: Article | Belanja */
    .cat-header {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      padding: 0.75rem 1rem;
      font-size: 0.9rem;
      background: #ffffff;
      border-bottom: 1px solid #e5e7eb;
    }
    .cat-tab {
      background: transparent;
      border: 0;
      color: #6b7280;
      cursor: pointer;
      padding: 0.25rem 0.25rem;
      border-bottom: 2px solid transparent;
      font-weight: 500;
    }
    .cat-tab.active { color: #111827; border-bottom-color: #e5e7eb; }
    /* Body two columns with vertical divider */
    .cat-body {
      display: block;
      background: #f9fafb;
    }
    .cat-col {
      padding: 0.75rem 1rem;
      background: #f9fafb;
    }
    /* hide right column for compact size */
    #catColRight { display: none; }
    .cat-link {
      display: block;
      padding: 0.5rem 0.25rem;
      color: #2F2F2F;
      text-decoration: none;
      font-size: 0.95rem;
      border-radius: 6px;
      transition: background-color 0.2s;
    }
    .cat-link:hover { background-color: #f3f4f6; }
  </style>
</head>
<body class="min-h-screen">
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-container">
      <a href="/" class="navbar-brand">ChickPatrol</a>
      
      <div class="navbar-category">
        <button class="navbar-category-btn" onclick="toggleCategory()">
          <span>Kategori</span>
          <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
        </button>
        <div class="category-dropdown" id="categoryDropdown">
          <div class="cat-header">
            <button type="button" id="catTabArticle" class="cat-tab active">Article</button>
            <button type="button" id="catTabBelanja" class="cat-tab">Belanja</button>
          </div>
          <div class="cat-body">
            <div class="cat-col" id="catColLeft">
              <a href="#" class="cat-link">Alat-alat</a>
              <a href="#" class="cat-link">Hewan</a>
              <a href="#" class="cat-link">Sayuran</a>
            </div>
            <div class="cat-col" id="catColRight"></div>
          </div>
        </div>
      </div>
      
      <div class="navbar-search">
        <input type="text" id="searchInput" placeholder="Cari Produk ..." onkeyup="filterProducts()">
      </div>
      
      <div class="navbar-actions">
        @if(Auth::check())
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

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Hero banners -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="skeleton h-40 md:h-44 md:col-span-2"></div>
      <div class="flex flex-col gap-4">
        <div class="skeleton h-20"></div>
        <div class="skeleton h-20"></div>
      </div>
      <div class="skeleton h-24 md:h-28 md:col-span-3"></div>
    </section>

    <!-- Categories chips -->
    <section class="mb-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Kategori</h2>
      <div class="flex flex-wrap gap-3 items-center">
        <div class="w-20 h-10 skeleton"></div>
        <div class="w-20 h-10 skeleton"></div>
        <div class="w-20 h-10 skeleton"></div>
        <div class="w-24 h-10 skeleton"></div>
        <div class="w-24 h-10 skeleton"></div>
      </div>
      <div class="mt-3 flex gap-3 text-[11px] text-gray-500">
        <button class="chip px-3 py-1 rounded-md bg-emerald-100 text-emerald-700 font-semibold" onclick="filterByCategory('')">Semua</button>
        <button class="chip px-3 py-1 rounded-md" onclick="filterByCategory('daging')">Daging</button>
        <button class="chip px-3 py-1 rounded-md" onclick="filterByCategory('telur')">Telur</button>
        <button class="chip px-3 py-1 rounded-md" onclick="filterByCategory('ayam')">Ayam Utuh</button>
        <button class="chip px-3 py-1 rounded-md" onclick="filterByCategory('jeroan')">Jeroan</button>
      </div>
    </section>

    <!-- For You section -->
    <section class="mb-4">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">For You</h2>

      <!-- Product grid -->
      <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-3">
        @isset($products)
          @forelse($products as $product)
            <a href="{{ route('product.detail', $product->product_id) }}" class="product-card card-border bg-white rounded-lg p-2 block hover:shadow-md transition-shadow" data-name="{{ strtolower($product->name) }}" data-slug="{{ strtolower($product->slug) }}">
              @php($img = optional($product->images->first())->url ?? null)
              @if($img)
                <img src="{{ $img }}" alt="{{ $product->name }}" class="h-24 w-full object-cover rounded-md mb-2">
              @else
                <div class="skeleton h-24 rounded-md mb-2"></div>
              @endif
              <div class="text-[12px] font-medium text-gray-800 truncate" title="{{ $product->name }}">{{ $product->name }}</div>
              <div class="text-[11px] text-gray-500 truncate">{{ $product->unit ?? '-' }}</div>
              <div class="flex items-center justify-between text-[11px] mt-1">
                <span class="text-emerald-700 font-semibold">Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</span>
                <button onclick="event.preventDefault(); event.stopPropagation();" class="text-gray-500 hover:text-emerald-700" title="Favorit"><i class="fa-regular fa-heart"></i></button>
              </div>
            </a>
          @empty
            @for($i=0;$i<24;$i++)
            <div class="card-border bg-white rounded-lg p-2">
              <div class="skeleton h-24 rounded-md mb-2"></div>
              <div class="h-3 bg-gray-100 rounded mb-1"></div>
              <div class="h-3 bg-gray-100 rounded w-4/5 mb-2"></div>
              <div class="flex items-center justify-between text-[11px]">
                <span class="text-emerald-700 font-semibold">Rp 1.000.000</span>
                <button class="text-gray-500"><i class="fa-regular fa-heart"></i></button>
              </div>
            </div>
            @endfor
          @endforelse
        @endisset
      </div>

      <!-- Pagination -->
      @isset($products)
      <div class="mt-6">{{ $products->withQueryString()->links() }}</div>
      @endisset
    </section>
  </main>

  <!-- Footer -->
  <footer class="bg-white mt-12 border-t border-gray-200">
    <div class="w-full py-8">
      <p class="text-center text-sm text-gray-600">©2025, ChickPatrol. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script>
    // Toggle category dropdown
    function toggleCategory() {
      const dropdown = document.getElementById('categoryDropdown');
      dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    window.addEventListener('click', function(e) {
      if (!e.target.closest('#categoryDropdown') && !e.target.closest('.navbar-category-btn')) {
        const dropdown = document.getElementById('categoryDropdown');
        if (dropdown.classList.contains('show')) {
          dropdown.classList.remove('show');
        }
      }
    });

    // Category tab logic
    const articleItems = ['Alat-alat', 'Hewan', 'Sayuran'];
    const belanjaItems = ['Pakan', 'Peralatan', 'Obat'];

    function renderCategory(items) {
      const left = document.getElementById('catColLeft');
      const right = document.getElementById('catColRight');
      if (!left || !right) return;
      left.innerHTML = items.map(t => `<a href="#" class="cat-link">${t}</a>`).join('');
      right.innerHTML = '';
    }

    function setActiveTab(tab) {
      const a = document.getElementById('catTabArticle');
      const b = document.getElementById('catTabBelanja');
      if (!a || !b) return;
      if (tab === 'article') { a.classList.add('active'); b.classList.remove('active'); renderCategory(articleItems); }
      else { b.classList.add('active'); a.classList.remove('active'); renderCategory(belanjaItems); }
    }

    document.addEventListener('DOMContentLoaded', () => {
      // initial render
      renderCategory(articleItems);
      const a = document.getElementById('catTabArticle');
      const b = document.getElementById('catTabBelanja');
      if (a) a.addEventListener('click', () => setActiveTab('article'));
      if (b) b.addEventListener('click', () => setActiveTab('belanja'));
      // close dropdown when selecting a link
      document.getElementById('categoryDropdown')?.addEventListener('click', (e) => {
        const link = e.target.closest('.cat-link');
        if (link) {
          document.getElementById('categoryDropdown').classList.remove('show');
        }
      });
    });

    // Product display is now fully handled by server-side rendering
    
    // Search filter
    function filterProducts() {
      const search = document.getElementById('searchInput').value.toLowerCase();
      const cards = document.querySelectorAll('.product-card');
      cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        card.style.display = name.includes(search) ? '' : 'none';
      });
    }
    
    // Category filter
    let currentCategory = '';
    function filterByCategory(category) {
      currentCategory = category;
      const cards = document.querySelectorAll('.product-card');
      const chips = document.querySelectorAll('.chip');
      chips.forEach(c => {
        c.classList.remove('bg-emerald-100','text-emerald-700','font-semibold');
        if (c.textContent.toLowerCase().includes(category) || (category==='' && c.textContent==='Semua')) {
          c.classList.add('bg-emerald-100','text-emerald-700','font-semibold');
        }
      });
      cards.forEach(card => {
        const slug = card.getAttribute('data-slug') || '';
        card.style.display = (category==='' || slug.includes(category)) ? '' : 'none';
      });
    }
    });
  </script>
</body>
</html>
