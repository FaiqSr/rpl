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
    body { background:#f3f6f4; }
    .skeleton { background:#e9ecef; border-radius:12px; }
    .chip { background:#eef2ef; border:1px solid #e0e6e2; }
    .chip.active { border-color:#69B578; color:#2F2F2F; }
    .card-border { border:1px solid #e6ebe7; }
    .footer-bg { background:#f8f6ee; }
  </style>
</head>
<body class="min-h-screen">
  <!-- Top Nav -->
  <header class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3">
      <a href="/" class="text-lg font-bold text-emerald-600">ChickPatrol</a>
      <!-- Category dropdown -->
      <div class="relative">
        <select class="form-select form-select-sm rounded-md border-gray-300">
          <option>Kategori</option>
          <option>Pakan</option>
          <option>Peralatan</option>
          <option>Obat</option>
        </select>
      </div>
      <!-- Search -->
      <div class="flex-1">
        <div class="relative">
          <input type="text" class="w-full rounded-md border border-gray-300 py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400"
                 placeholder="Cari Produk ..." />
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
      </div>
      <!-- Auth / Logged in actions -->
      @auth
      <div class="flex items-center gap-3">
        <a href="{{ url('/cart') }}" title="Keranjang" class="w-9 h-9 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
          <i class="fa-solid fa-cart-shopping text-gray-700"></i>
        </a>
        <a href="{{ route('profile') }}" title="Akun" class="w-9 h-9 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
          <i class="fa-regular fa-user text-gray-700"></i>
        </a>
      </div>
      @else
      <div class="flex items-center gap-2">
        <a href="{{ route('login') }}" class="px-3 py-1.5 text-sm border rounded-md hover:bg-gray-50">Masuk</a>
        <a href="{{ route('register') }}" class="px-3 py-1.5 text-sm bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Daftar</a>
      </div>
      @endauth
    </div>
  </header>

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
        <button class="chip px-3 py-1 rounded-md">Filter Default</button>
        <button class="chip px-3 py-1 rounded-md">Filter Sale</button>
        <button class="chip px-3 py-1 rounded-md">Filter Deka</button>
        <button class="chip px-3 py-1 rounded-md">Filter Date</button>
        <button class="chip px-3 py-1 rounded-md">Filter Diskon</button>
      </div>
    </section>

    <!-- For You section -->
    <section class="mb-4">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">For You</h2>
      <!-- Row of featured items -->
      <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-4">
        <template id="featured-template">
          <div class="card-border bg-white rounded-lg p-2">
            <div class="skeleton h-20 rounded-md mb-2"></div>
            <div class="h-3 bg-gray-100 rounded mb-1"></div>
            <div class="h-3 bg-gray-100 rounded w-3/4 mb-2"></div>
            <div class="text-[11px] text-emerald-700 font-semibold">Rp 1.000.000</div>
          </div>
        </template>
      </div>

      <!-- Product grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-3">
        <template id="card-template">
          <div class="card-border bg-white rounded-lg p-2">
            <div class="skeleton h-24 rounded-md mb-2"></div>
            <div class="h-3 bg-gray-100 rounded mb-1"></div>
            <div class="h-3 bg-gray-100 rounded w-4/5 mb-2"></div>
            <div class="flex items-center justify-between text-[11px]">
              <span class="text-emerald-700 font-semibold">Rp 1.000.000</span>
              <button class="text-gray-500 hover:text-emerald-700"><i class="fa-regular fa-heart"></i></button>
            </div>
          </div>
        </template>
      </div>

      <!-- Pagination -->
      <div class="flex justify-center items-center gap-2 mt-6 text-xs">
        <button class="px-2.5 py-1 rounded border bg-black text-white">1</button>
        <button class="px-2.5 py-1 rounded border">2</button>
        <button class="px-2.5 py-1 rounded border">3</button>
        <span class="text-gray-400">…</span>
        <button class="px-2.5 py-1 rounded border">48</button>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="footer-bg mt-8 border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <p class="text-center text-[11px] text-gray-500">©2025, ChickPatrol. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script>
    // Populate placeholder cards quickly to match Figma density
    function repeat(node, into, times){
      const frag = document.createDocumentFragment();
      for(let i=0;i<times;i++){ frag.appendChild(node.cloneNode(true)); }
      into.appendChild(frag);
    }
    document.addEventListener('DOMContentLoaded', () => {
      const featuredWrap = document.querySelector('.grid.grid-cols-2.md\\:grid-cols-6.gap-3.mb-4');
      const featuredTpl = document.getElementById('featured-template').content.firstElementChild;
      repeat(featuredTpl, featuredWrap, 6);

      const gridWrap = document.querySelector('.grid.grid-cols-2.sm\\:grid-cols-3.md\\:grid-cols-5.lg\\:grid-cols-6.gap-3');
      const cardTpl = document.getElementById('card-template').content.firstElementChild;
      repeat(cardTpl, gridWrap, 24);
    });
  </script>
</body>
</html>