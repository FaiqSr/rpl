<!-- Navbar -->
<nav class="navbar">
  <div class="navbar-container">
    <a href="/" class="navbar-brand">ChickPatrol</a>
    
    <div class="navbar-category">
      <button class="navbar-category-btn" onclick="toggleCategory()">
        <span>Kategori</span>
      </button>
      <div class="category-dropdown" id="categoryDropdown">
        <div class="cat-header">
          <button type="button" id="catTabArticle" class="cat-tab active">Article</button>
          <button type="button" id="catTabBelanja" class="cat-tab">Belanja</button>
        </div>
        <div class="cat-body">
          <div class="cat-col" id="catColLeft">
            <!-- Content akan di-render oleh JavaScript -->
          </div>
          <div class="cat-col" id="catColRight"></div>
        </div>
      </div>
    </div>
    
    <div class="navbar-search">
      <form method="GET" action="/" onsubmit="return handleSearch(event)">
        <input type="text" id="searchInput" name="search" placeholder="Cari Produk ..." value="{{ request('search') }}" onkeyup="filterProducts()">
      </form>
    </div>
    
    <div class="navbar-actions">
      @if(Auth::check())
        <a href="{{ route('cart') }}" class="navbar-icon-link position-relative" title="Keranjang">
          <i class="fa-solid fa-shopping-cart"></i>
          <span id="cartBadge" class="badge position-absolute" style="display: none; font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 10px; top: -2px; right: -2px; min-width: 18px; text-align: center; line-height: 1.2; background-color: #F4C430; color: #000000; font-weight: 600;">0</span>
        </a>
        <a href="{{ route('orders') }}" class="navbar-icon-link" title="Pesanan Saya">
          <i class="fa-solid fa-shopping-bag"></i>
        </a>
        <a href="#" onclick="openChatModal(); return false;" class="navbar-icon-link position-relative" title="Chat">
          <i class="fa-solid fa-comments"></i>
          <span id="chatBadge" class="badge position-absolute" style="display: none; font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 10px; top: -2px; right: -2px; min-width: 18px; text-align: center; line-height: 1.2; background-color: #69B578; color: #ffffff; font-weight: 600;">0</span>
        </a>
        <div class="navbar-profile">
          <button class="navbar-profile-btn" onclick="toggleProfileDropdown(event)" title="{{ Auth::user()->name }}">
            <i class="fa-solid fa-user"></i>
          </button>
          <div class="navbar-profile-dropdown" id="profileDropdown">
            <a href="{{ route('profile') }}">
              <i class="fa-solid fa-user"></i> Profile
            </a>
            <a href="{{ route('logout') }}">
              <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" class="btn-outline-secondary">Masuk</a>
        <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
      @endif
    </div>
  </div>
</nav>

