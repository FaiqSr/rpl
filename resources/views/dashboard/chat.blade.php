<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Chat - ChickPatrol Seller</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts - Inter (Premium Typography) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
  
  <style>
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    body { background: #F8F9FB; margin: 0; }
    
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
    
    .sidebar-menu-item:hover,
    .sidebar-menu-item.active {
      background: #f8f9fa;
      color: #22C55E;
    }
    
    .sidebar-menu-item.active {
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
    
    .main-content {
      margin-left: 220px;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .chat-container {
      display: flex;
      height: 100%;
      background: white;
    }
    
    .chat-sidebar {
      width: 320px;
      border-right: 1px solid #e9ecef;
      display: flex;
      flex-direction: column;
    }
    
    .chat-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .chat-header h2 {
      font-size: 1.125rem;
      font-weight: 600;
      color: #2F2F2F;
      margin: 0;
    }
    
    .chat-header-icons {
      display: flex;
      gap: 0.75rem;
    }
    
    .chat-header-icons i {
      font-size: 1rem;
      color: #6c757d;
      cursor: pointer;
      transition: color 0.2s;
    }
    
    .chat-header-icons i:hover {
      color: #2F2F2F;
    }
    
    .chat-search {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #e9ecef;
    }
    
    .chat-search input {
      width: 100%;
      padding: 0.5rem 0.75rem;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      font-size: 0.875rem;
      background: #f8f9fa;
    }
    
    .chat-search input:focus {
      outline: none;
      border-color: #22C55E;
      background: white;
    }
    
    .chat-list {
      flex: 1;
      overflow-y: auto;
    }
    
    .chat-item {
      padding: 1rem 1.5rem;
      display: flex;
      gap: 0.75rem;
      cursor: pointer;
      transition: background 0.2s;
      border-bottom: 1px solid #f8f9fa;
    }
    
    .chat-item:hover {
      background: #f8f9fa;
    }
    
    .chat-item.active {
      background: #e8f5e9;
      border-left: 3px solid #69B578;
      padding-left: calc(1.5rem - 3px);
    }
    
    .chat-item-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: #e9ecef;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.125rem;
      color: #6c757d;
    }
    
    .chat-item-content {
      flex: 1;
      min-width: 0;
    }
    
    .chat-item-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.25rem;
    }
    
    .chat-item-name {
      font-size: 0.875rem;
      font-weight: 600;
      color: #2F2F2F;
    }
    
    .chat-item-time {
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .chat-item-message {
      font-size: 0.875rem;
      color: #6c757d;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .chat-main {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    
    .chat-main-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .chat-main-header-left {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .chat-main-header-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: #e9ecef;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.125rem;
      color: #6c757d;
    }
    
    .chat-main-header-info h3 {
      font-size: 0.875rem;
      font-weight: 600;
      color: #2F2F2F;
      margin: 0;
    }
    
    .chat-main-header-info p {
      font-size: 0.75rem;
      color: #6c757d;
      margin: 0;
    }
    
    .chat-main-header-icons {
      display: flex;
      gap: 1rem;
    }
    
    .chat-main-header-icons i {
      font-size: 1rem;
      color: #6c757d;
      cursor: pointer;
      transition: color 0.2s;
    }
    
    .chat-main-header-icons i:hover {
      color: #2F2F2F;
    }
    
    .chat-messages {
      flex: 1;
      padding: 1.5rem;
      overflow-y: auto;
      background: #f8f9fa;
    }
    
    .chat-message {
      display: flex;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }
    
    .chat-message.sent {
      flex-direction: row-reverse;
    }
    
    .chat-message-avatar {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: #e9ecef;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .chat-message-content {
      max-width: 60%;
    }
    
    .chat-message-bubble {
      padding: 0.75rem 1rem;
      border-radius: 10px;
      background: white;
      font-size: 0.875rem;
      color: #2F2F2F;
      line-height: 1.5;
      word-wrap: break-word;
    }
    
    .chat-message.sent .chat-message-bubble {
      background: #22C55E;
      color: white;
    }
    
    .chat-message-time {
      font-size: 0.75rem;
      color: #6c757d;
      margin-top: 0.25rem;
      padding: 0 0.5rem;
    }
    
    .chat-input-area {
      padding: 1rem 1.5rem;
      border-top: 1px solid #e9ecef;
      background: white;
    }
    
    .chat-input-wrapper {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }
    
    .chat-input-wrapper input {
      flex: 1;
      padding: 0.75rem 1rem;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      font-size: 0.875rem;
      background: white;
    }
    
    .chat-input-wrapper input:focus {
      outline: none;
      border-color: #22C55E;
      background: white;
    }
    
    .chat-send-btn {
      min-width: 80px;
      padding: 0.5rem 1.25rem;
      border-radius: 6px;
      background: white;
      color: #22C55E;
      border: 1px solid #69B578;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
      flex-shrink: 0;
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    .chat-send-btn:hover {
      background: #22C55E;
      color: white;
    }
    
    .performa-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.25rem 0.75rem;
      background: #f8f9fa;
      border-radius: 6px;
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .performa-value {
      font-weight: 700;
      color: #2F2F2F;
    }

    .chat-empty {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      color: #6c757d;
    }

    .chat-empty i {
      font-size: 4rem;
      margin-bottom: 1rem;
      color: #dee2e6;
    }

    .chat-empty p {
      font-size: 0.875rem;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      ChickPatrol Seller
    </div>
    
    <div class="sidebar-profile">
      <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%23e9ecef'/%3E%3C/svg%3E" alt="Profile">
      <div class="sidebar-profile-info">
        <h6>Anto Farm</h6>
        <p>Penjual</p>
      </div>
    </div>
    
    <div class="performa-badge mx-3 mt-3">
      Performa Toko
      <span class="performa-value">95/100</span>
    </div>
    
    <nav class="sidebar-menu">
      <a href="{{ route('dashboard') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
      </a>
      <a href="{{ route('dashboard.products') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-box"></i>
        <span>Produk</span>
      </a>
      <div class="sidebar-menu-item" onclick="toggleSubmenu()" style="cursor: pointer;">
        <i class="fa-solid fa-wrench"></i>
        <span>Alat</span>
        <i class="fa-solid fa-chevron-down chevron-icon"></i>
      </div>
      <div class="sidebar-submenu">
        <a href="{{ route('dashboard.tools') }}">Daftar alat</a>
        <a href="{{ route('dashboard.tools.monitoring') }}">Monitoring Alat</a>
        <a href="{{ route('dashboard.tools.information') }}">Manajemen Informasi</a>
      </div>
      <a href="{{ route('dashboard.sales') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-shopping-cart"></i>
        <span>Penjualan</span>
      </a>
      <a href="{{ route('dashboard.chat') }}" class="sidebar-menu-item active">
        <i class="fa-solid fa-comment"></i>
        <span>Chat</span>
      </a>
    </nav>
    
    <div class="sidebar-footer">
      <a href="{{ route('logout') }}" class="sidebar-menu-item">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </div>
  </aside>
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="chat-container">
      <!-- Chat Sidebar -->
      <div class="chat-sidebar">
        <div class="chat-header">
          <h2>Chat</h2>
          <div class="chat-header-icons">
            <i class="fa-solid fa-ellipsis-vertical"></i>
            <i class="fa-solid fa-pen-to-square"></i>
          </div>
        </div>
        
        <div class="chat-search">
          <input type="text" placeholder="🔍">
        </div>
        
        <div class="chat-list">
          <div class="chat-item active" onclick="selectChat(1)">
            <div class="chat-item-avatar">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="chat-item-content">
              <div class="chat-item-header">
                <span class="chat-item-name">Ratna Sulawasti</span>
                <span class="chat-item-time">12p</span>
              </div>
              <div class="chat-item-message">+62</div>
            </div>
          </div>
          
          <div class="chat-item" onclick="selectChat(2)">
            <div class="chat-item-avatar">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="chat-item-content">
              <div class="chat-item-header">
                <span class="chat-item-name">Ratna Sulawasti</span>
                <span class="chat-item-time">12p</span>
              </div>
              <div class="chat-item-message">+62</div>
            </div>
          </div>
          
          <div class="chat-item" onclick="selectChat(3)">
            <div class="chat-item-avatar">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="chat-item-content">
              <div class="chat-item-header">
                <span class="chat-item-name">Ratna Sulawasti</span>
                <span class="chat-item-time">12p</span>
              </div>
              <div class="chat-item-message">+62</div>
            </div>
          </div>
          
          <div class="chat-item" onclick="selectChat(4)">
            <div class="chat-item-avatar">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="chat-item-content">
              <div class="chat-item-header">
                <span class="chat-item-name">Ratna Sulawasti</span>
                <span class="chat-item-time">12p</span>
              </div>
              <div class="chat-item-message">+62</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Chat Main Area -->
      <div class="chat-main">
        <div class="chat-main-header">
          <div class="chat-main-header-left">
            <div class="chat-main-header-avatar">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="chat-main-header-info">
              <h3>Ratna Sulawasti</h3>
              <p>Online</p>
            </div>
          </div>
          <div class="chat-main-header-icons">
            <i class="fa-solid fa-magnifying-glass"></i>
            <i class="fa-solid fa-ellipsis-vertical"></i>
          </div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
          <div class="chat-message">
            <div class="chat-message-avatar">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="chat-message-content">
              <div class="chat-message-bubble">
                Lorem ipsum dolor sit amet
              </div>
              <div class="chat-message-time">12:00</div>
            </div>
          </div>
          
          <div class="chat-message sent">
            <div class="chat-message-avatar">
              AF
            </div>
            <div class="chat-message-content">
              <div class="chat-message-bubble">
                Baik Terima kasih banyak
              </div>
              <div class="chat-message-time">12:01</div>
            </div>
          </div>
        </div>
        
        <div class="chat-input-area">
          <div class="chat-input-wrapper">
            <input type="text" id="chatInput" placeholder="Ketik pesan disini..." onkeypress="handleEnter(event)">
            <button class="chat-send-btn" onclick="sendMessage()">
              Kirim
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
  <script src="{{ asset('js/dashboard-alerts.js') }}"></script>
  
  <script>
    // SweetAlert Helper Functions
    window.showSuccess = function(message) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            confirmButtonColor: '#22C55E',
            confirmButtonText: 'OK'
        });
    };
    
    window.showError = function(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'OK'
        });
    };
    
    // Toggle Submenu
    function toggleSubmenu() {
        const submenu = document.querySelector('.sidebar-submenu');
        const chevron = document.querySelector('.chevron-icon');
        submenu.classList.toggle('show');
        chevron.classList.toggle('rotate');
    }
    
    // Select Chat
    function selectChat(id) {
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
        });
        event.currentTarget.classList.add('active');
    }
    
    // Send Message
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (message) {
            const chatMessages = document.getElementById('chatMessages');
            const now = new Date();
            const time = now.getHours() + ':' + (now.getMinutes() < 10 ? '0' : '') + now.getMinutes();
            
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message sent';
            messageDiv.innerHTML = `
                <div class="chat-message-avatar">AF</div>
                <div class="chat-message-content">
                    <div class="chat-message-bubble">${message}</div>
                    <div class="chat-message-time">${time}</div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            input.value = '';
        }
    }
    
    // Handle Enter Key
    function handleEnter(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }
    
    // Show success message if redirected with success
    @if(session('success'))
        showSuccess('{{ session('success') }}');
    @endif
    
    // Show error message if redirected with error
    @if(session('error'))
        showError('{{ session('error') }}');
    @endif
  </script>
</body>
</html>
