<!-- Sidebar Component -->
@php
  $currentRoute = request()->route()->getName() ?? '';
  $isHome = $currentRoute === 'dashboard' || request()->is('dashboard');
  $isProducts = $currentRoute === 'dashboard.products' || request()->is('dashboard/products');
  $isTools = $currentRoute === 'dashboard.tools' || request()->is('dashboard/tools');
  $isToolsMonitoring = $currentRoute === 'dashboard.tools.monitoring' || request()->is('dashboard/tools/monitoring');
  $isToolsInformation = $currentRoute === 'dashboard.tools.information' || request()->is('dashboard/tools/information');
  $isSales = $currentRoute === 'dashboard.sales' || request()->is('dashboard/sales');
  $isChat = $currentRoute === 'dashboard.chat' || request()->is('dashboard/chat');
  $isCustomers = $currentRoute === 'dashboard.customers' || request()->is('dashboard/customers');
  $isArticles = $currentRoute === 'dashboard.articles' || request()->is('dashboard/articles*');
  $isArticleCategories = $currentRoute === 'dashboard.article-categories' || request()->is('dashboard/article-categories*');
  $isHomepage = $currentRoute === 'dashboard.homepage' || request()->is('dashboard/homepage*');
  
  // Check if any tools submenu is active
  $isToolsActive = $isTools || $isToolsMonitoring || $isToolsInformation;
@endphp

<div class="sidebar">
  <div class="sidebar-header">ChickPatrol Seller</div>
  <div class="sidebar-profile">
    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=22C55E&color=fff" alt="Profile">
    <div class="sidebar-profile-info">
      <h6>{{ Auth::user()->name ?? 'Admin' }}</h6>
      <p>{{ Auth::user()->role === 'admin' ? 'Admin' : 'Penjual' }}</p>
    </div>
  </div>
  <div class="sidebar-menu">
    <a href="{{ route('dashboard') }}" class="sidebar-menu-item {{ $isHome ? 'active' : '' }}">
      <i class="fa-solid fa-house"></i>
      <span>Home</span>
    </a>
    <a href="{{ route('dashboard.products') }}" class="sidebar-menu-item {{ $isProducts ? 'active' : '' }}">
      <i class="fa-solid fa-box"></i>
      <span>Produk</span>
    </a>
    <div class="sidebar-menu-item {{ $isToolsActive ? 'active' : '' }}" onclick="toggleSubmenu()" style="cursor: pointer;">
      <i class="fa-solid fa-wrench"></i>
      <span>Alat</span>
      <i class="fa-solid fa-chevron-down chevron-icon {{ $isToolsActive ? 'rotate' : '' }}"></i>
    </div>
    <div class="sidebar-submenu {{ $isToolsActive ? 'show' : '' }}">
      <a href="{{ route('dashboard.tools') }}" class="{{ $isTools ? 'active' : '' }}">Daftar alat</a>
      <a href="{{ route('dashboard.tools.monitoring') }}" class="{{ $isToolsMonitoring ? 'active' : '' }}">Monitoring Alat</a>
      <a href="{{ route('dashboard.tools.information') }}" class="{{ $isToolsInformation ? 'active' : '' }}">Manajemen Informasi</a>
    </div>
    <a href="{{ route('dashboard.sales') }}" class="sidebar-menu-item {{ $isSales ? 'active' : '' }}">
      <i class="fa-solid fa-cart-shopping"></i>
      <span>Penjualan</span>
    </a>
    <a href="{{ route('dashboard.chat') }}" class="sidebar-menu-item {{ $isChat ? 'active' : '' }}">
      <i class="fa-solid fa-comments"></i>
      <span>Chat</span>
    </a>
    <a href="/dashboard/customers" class="sidebar-menu-item {{ $isCustomers ? 'active' : '' }}">
      <i class="fa-solid fa-users"></i>
      <span>Pelanggan</span>
    </a>
    <a href="{{ route('dashboard.articles') }}" class="sidebar-menu-item {{ $isArticles ? 'active' : '' }}">
      <i class="fa-solid fa-newspaper"></i>
      <span>Konten Artikel</span>
    </a>
    <a href="{{ route('dashboard.homepage') }}" class="sidebar-menu-item {{ $isHomepage ? 'active' : '' }}">
      <i class="fa-solid fa-home"></i>
      <span>Pengaturan Homestore</span>
    </a>
  </div>
  <div class="sidebar-footer">
    <a href="{{ route('logout') }}" class="sidebar-menu-item">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
      <span>Logout</span>
    </a>
  </div>
</div>

<style>
  .sidebar {
    width: 220px;
    background: white;
    border-right: 1px solid #e9ecef;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 100;
  }
  
  .sidebar-header {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid #e9ecef;
    font-weight: 700;
    font-size: 0.95rem;
    color: #2F2F2F;
  }
  
  .sidebar-profile {
    padding: 1.25rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 1px solid #e9ecef;
  }
  
  .sidebar-profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
  }
  
  .sidebar-profile-info h6 {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #2F2F2F;
  }
  
  .sidebar-profile-info p {
    margin: 0;
    font-size: 0.75rem;
    color: #6c757d;
  }
  
  .sidebar-menu {
    padding: 1rem 0;
  }
  
  .sidebar-menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 1rem;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
    cursor: pointer;
  }
  
  .sidebar-menu-item:hover {
    background: #f8f9fa;
    color: #2F2F2F;
  }
  
  .sidebar-menu-item.active {
    background: #f8f9fa;
    border-left: 3px solid #22C55E;
    padding-left: calc(1rem - 3px);
    color: #22C55E;
  }
  
  .sidebar-menu-item i {
    width: 20px;
    text-align: center;
  }
  
  .sidebar-submenu {
    display: none;
    padding-left: 2.5rem;
  }
  
  .sidebar-submenu.show {
    display: block;
  }
  
  .sidebar-submenu a {
    display: block;
    padding: 0.5rem 1rem;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
  }
  
  .sidebar-submenu a:hover,
  .sidebar-submenu a.active {
    color: #22C55E;
  }
  
  .chevron-icon {
    margin-left: auto;
    font-size: 0.7rem;
    transition: transform 0.2s;
  }
  
  .chevron-icon.rotate {
    transform: rotate(180deg);
  }
  
  .sidebar-footer {
    position: absolute;
    bottom: 1rem;
    left: 0;
    right: 0;
    padding: 0 1rem;
  }
</style>

<script>
  // Toggle submenu for Alat
  function toggleSubmenu() {
    const submenu = document.querySelector('.sidebar-submenu');
    const chevron = document.querySelector('.chevron-icon');
    if (submenu && chevron) {
      submenu.classList.toggle('show');
      chevron.classList.toggle('rotate');
    }
  }
</script>
