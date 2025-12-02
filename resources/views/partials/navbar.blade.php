<!-- Navbar -->
<nav class="navbar">
  <div class="navbar-container">
    <button class="navbar-toggle" id="navbarToggle" onclick="toggleNavbarMobile()">
      <i class="fa-solid fa-bars"></i>
    </button>
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
          <span id="cartBadge" class="badge position-absolute" style="display: none; font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 10px; top: -2px; right: -2px; min-width: 18px; text-align: center; line-height: 1.2; background-color: #EF4444; color: #ffffff; font-weight: 600;">0</span>
        </a>
        <a href="{{ route('orders') }}" class="navbar-icon-link" title="Pesanan Saya">
          <i class="fa-solid fa-shopping-bag"></i>
        </a>
        <a href="#" onclick="openChatModal(); return false;" class="navbar-icon-link position-relative" title="Chat">
          <i class="fa-solid fa-comments"></i>
          <span id="chatBadge" class="badge position-absolute" style="display: none; font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 10px; top: -2px; right: -2px; min-width: 18px; text-align: center; line-height: 1.2; background-color: #EF4444; color: #ffffff; font-weight: 600;">0</span>
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
  
  <!-- Mobile Menu -->
  <div class="navbar-mobile-menu" id="navbarMobileMenu">
    <div class="navbar-mobile-search">
      <form method="GET" action="/" onsubmit="return handleSearch(event)">
        <input type="text" name="search" placeholder="Cari Produk ..." value="{{ request('search') }}">
      </form>
    </div>
    <div class="navbar-mobile-category">
      <button class="navbar-mobile-category-btn" onclick="toggleCategory(); toggleNavbarMobile();">
        <i class="fa-solid fa-list"></i> Kategori
      </button>
    </div>
    @if(Auth::check())
    <div class="navbar-mobile-actions">
      <a href="{{ route('cart') }}" class="navbar-mobile-link">
        <i class="fa-solid fa-shopping-cart"></i> Keranjang
      </a>
      <a href="{{ route('orders') }}" class="navbar-mobile-link">
        <i class="fa-solid fa-shopping-bag"></i> Pesanan Saya
      </a>
      <a href="#" onclick="openChatModal(); toggleNavbarMobile(); return false;" class="navbar-mobile-link">
        <i class="fa-solid fa-comments"></i> Chat
      </a>
      <a href="{{ route('profile') }}" class="navbar-mobile-link">
        <i class="fa-solid fa-user"></i> Profile
      </a>
      <a href="{{ route('logout') }}" class="navbar-mobile-link">
        <i class="fa-solid fa-sign-out-alt"></i> Logout
      </a>
    </div>
    @else
    <div class="navbar-mobile-actions">
      <a href="{{ route('login') }}" class="navbar-mobile-link">Masuk</a>
      <a href="{{ route('register') }}" class="navbar-mobile-link navbar-mobile-link-primary">Daftar</a>
    </div>
    @endif
  </div>
</nav>

<script>
function toggleNavbarMobile() {
  const menu = document.getElementById('navbarMobileMenu');
  const toggle = document.getElementById('navbarToggle');
  if (menu && toggle) {
    menu.classList.toggle('show');
    const icon = toggle.querySelector('i');
    if (icon) {
      if (menu.classList.contains('show')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
      } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
      }
    }
  }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
  const menu = document.getElementById('navbarMobileMenu');
  const toggle = document.getElementById('navbarToggle');
  if (menu && toggle && !menu.contains(event.target) && !toggle.contains(event.target)) {
    menu.classList.remove('show');
    const icon = toggle.querySelector('i');
    if (icon) {
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    }
  }
});
</script>

