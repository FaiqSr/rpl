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
      justify-content: flex-start;
    }
    
    .chat-message.sent {
      flex-direction: row-reverse;
      justify-content: flex-end;
    }
    
    .chat-message:not(.sent) {
      flex-direction: row;
      justify-content: flex-start;
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
      font-weight: 500;
    }
    
    .chat-message.sent .chat-message-avatar {
      background: #22C55E;
      color: white;
    }
    
    .chat-message-content {
      max-width: 60%;
      display: flex;
      flex-direction: column;
    }
    
    .chat-message-sender-name {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.25rem;
      font-weight: 500;
    }
    
    .chat-message-bubble {
      padding: 0.75rem 1rem;
      border-radius: 10px;
      background: white;
      font-size: 0.875rem;
      color: #2F2F2F;
      line-height: 1.5;
      word-wrap: break-word;
      border: 1px solid #e9ecef;
    }
    
    .chat-message.sent .chat-message-bubble {
      background: #22C55E;
      color: white;
      border: none;
    }
    
    .chat-message-time {
      font-size: 0.75rem;
      color: #6c757d;
      margin-top: 0.25rem;
      padding: 0 0.5rem;
    }
    
    .chat-message.sent .chat-message-time {
      text-align: right;
    }
    
    .chat-message:not(.sent) .chat-message-time {
      text-align: left;
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
        
        <div class="chat-list" id="chatList">
          <div class="text-center p-4 text-gray-500">
            <i class="fa-solid fa-spinner fa-spin"></i> Memuat chat...
          </div>
        </div>
      </div>
      
      <!-- Chat Main Area -->
      <div class="chat-main">
        <div class="chat-main-header" id="chatMainHeader" style="display: none;">
          <div class="chat-main-header-left">
            <div class="chat-main-header-avatar" id="chatHeaderAvatar">
              <i class="fa-solid fa-user"></i>
            </div>
            <div class="chat-main-header-info">
              <h3 id="chatHeaderName">Pilih chat untuk memulai</h3>
              <p id="chatHeaderStatus">-</p>
            </div>
          </div>
          <div class="chat-main-header-icons">
            <i class="fa-solid fa-magnifying-glass"></i>
            <i class="fa-solid fa-ellipsis-vertical"></i>
          </div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
          <div class="text-center p-8 text-gray-400">
            <i class="fa-solid fa-comments" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-4">Pilih chat untuk memulai percakapan</p>
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
    
    let currentChatId = null;
    let currentChat = null;
    let messagePollInterval = null;
    
    // Load chats list
    async function loadChats() {
        try {
            const response = await fetch('/api/chat', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (!response.ok) throw new Error('Failed to load chats');
            
            const chats = await response.json();
            const chatList = document.getElementById('chatList');
            
            if (chats.length === 0) {
                chatList.innerHTML = '<div class="text-center p-4 text-gray-500">Belum ada chat</div>';
                return;
            }
            
            chatList.innerHTML = chats.map(chat => {
                const buyer = chat.buyer || {};
                const order = chat.order || {};
                const lastMessage = chat.last_message || (chat.latest_message ? chat.latest_message.message : null) || 'Belum ada pesan';
                const lastMessageTime = chat.last_message_at ? formatTime(chat.last_message_at) : '';
                const unreadCount = chat.seller_unread_count || 0;
                const unreadBadge = unreadCount > 0 ? `<span class="badge bg-danger ms-2">${unreadCount}</span>` : '';
                const orderInfo = order && order.order_id ? `<small class="text-muted d-block" style="font-size: 0.7rem;">Pesanan #${order.order_id.substring(0, 8)}</small>` : '';
                
                // Get buyer name - use actual name from database
                const buyerName = buyer.name || buyer.email || 'Pembeli';
                const buyerInitial = buyerName.charAt(0).toUpperCase();
                
                return `
                    <div class="chat-item" onclick="selectChat('${chat.chat_id}')" data-chat-id="${chat.chat_id}">
                        <div class="chat-item-avatar">
                            ${buyerInitial}
                        </div>
                        <div class="chat-item-content">
                            <div class="chat-item-header">
                                <span class="chat-item-name">${escapeHtml(buyerName)} ${unreadBadge}</span>
                                <span class="chat-item-time">${lastMessageTime}</span>
                            </div>
                            ${orderInfo}
                            <div class="chat-item-message">${escapeHtml(lastMessage).substring(0, 40)}${lastMessage.length > 40 ? '...' : ''}</div>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (error) {
            console.error('Error loading chats:', error);
            document.getElementById('chatList').innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat chat</div>';
        }
    }
    
    // Select Chat
    async function selectChat(chatId) {
        currentChatId = chatId;
        
        // Update active state
        document.querySelectorAll('.chat-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-chat-id="${chatId}"]`)?.classList.add('active');
        
        // Load chat details and messages
        await loadChatDetails(chatId);
        await loadMessages(chatId);
        
        // Start polling for new messages
        if (messagePollInterval) {
            clearInterval(messagePollInterval);
        }
        messagePollInterval = setInterval(() => {
            if (currentChatId) {
                loadMessages(currentChatId);
            }
        }, 3000); // Poll every 3 seconds
    }
    
    // Load chat details
    async function loadChatDetails(chatId) {
        try {
            const response = await fetch(`/api/chat/get-or-create?chat_id=${chatId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (!response.ok) throw new Error('Failed to load chat details');
            
            const chat = await response.json();
            currentChat = chat;
            
            const buyer = chat.buyer || {};
            const order = chat.order || {};
            const buyerName = buyer.name || buyer.email || 'Pembeli';
            const orderInfo = order && order.order_id ? ` (Pesanan #${order.order_id.substring(0, 8)})` : '';
            document.getElementById('chatHeaderName').textContent = buyerName + orderInfo;
            document.getElementById('chatHeaderStatus').textContent = 'Online';
            document.getElementById('chatHeaderAvatar').innerHTML = buyerName.charAt(0).toUpperCase();
            document.getElementById('chatMainHeader').style.display = 'flex';
        } catch (error) {
            console.error('Error loading chat details:', error);
        }
    }
    
    // Load messages
    async function loadMessages(chatId) {
        try {
            const response = await fetch(`/api/chat/${chatId}/messages`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (!response.ok) throw new Error('Failed to load messages');
            
            const messages = await response.json();
            const chatMessages = document.getElementById('chatMessages');
            const currentUser = {!! json_encode(Auth::user()) !!};
            
            if (messages.length === 0) {
                chatMessages.innerHTML = '<div class="text-center p-8 text-gray-400"><i class="fa-solid fa-comments" style="font-size: 3rem; opacity: 0.3;"></i><p class="mt-4">Belum ada pesan</p></div>';
                return;
            }
            
            chatMessages.innerHTML = messages.map(msg => {
                const isSent = msg.sender_id === currentUser.user_id;
                const sender = msg.sender || {};
                const senderInitials = sender.name ? sender.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'U';
                const time = formatTime(msg.created_at);
                
                return `
                    <div class="chat-message ${isSent ? 'sent' : ''}">
                        <div class="chat-message-avatar">${senderInitials}</div>
                        <div class="chat-message-content">
                            ${!isSent ? `<div class="chat-message-sender-name">${escapeHtml(sender.name || sender.email || 'User')}</div>` : ''}
                            <div class="chat-message-bubble">${escapeHtml(msg.message)}</div>
                            <div class="chat-message-time">${time}</div>
                        </div>
                    </div>
                `;
            }).join('');
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }
    
    // Send Message
    async function sendMessage() {
        if (!currentChatId) {
            showError('Pilih chat terlebih dahulu');
            return;
        }
        
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        try {
            const response = await fetch(`/api/chat/${currentChatId}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message })
            });
            
            if (!response.ok) throw new Error('Failed to send message');
            
            input.value = '';
            await loadMessages(currentChatId);
            await loadChats(); // Refresh chat list to update last message
        } catch (error) {
            console.error('Error sending message:', error);
            showError('Gagal mengirim pesan');
        }
    }
    
    // Helper functions
    function formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        if (minutes < 1) return 'Baru saja';
        if (minutes < 60) return `${minutes}m`;
        if (hours < 24) return `${hours}j`;
        if (days < 7) return `${days}d`;
        
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Handle Enter Key
    function handleEnter(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadChats();
        
        // Refresh chat list every 10 seconds
        setInterval(loadChats, 10000);
    });
    
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
