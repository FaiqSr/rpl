// Chat functionality for buyer (home store and orders page)
let buyerChatId = null;
let buyerChatPollInterval = null;

// Get current user ID (set from Laravel)
// Priority: window.currentUserId (explicit) > window.currentUser?.user_id (fallback)
let currentUserId = window.currentUserId || window.currentUser?.user_id || null;

// Debug: Log currentUserId saat inisialisasi
console.log('Buyer currentUserId initialized:', currentUserId, 'Type:', typeof currentUserId);
console.log('window.currentUserId:', window.currentUserId);
console.log('window.currentUser:', window.currentUser);

// Update currentUserId jika window.currentUserId berubah (setelah DOM ready)
document.addEventListener('DOMContentLoaded', function() {
    if (window.currentUserId) {
        currentUserId = window.currentUserId;
        console.log('Buyer currentUserId updated from DOM:', currentUserId);
    } else if (window.currentUser?.user_id) {
        currentUserId = window.currentUser.user_id;
        console.log('Buyer currentUserId updated from currentUser:', currentUserId);
    }
});

// Open chat modal (general chat or for specific order)
async function openChatModal(orderId = null) {
  const modalElement = document.getElementById('chatModal');
  if (!modalElement) {
    console.error('Chat modal not found');
    return;
  }
  
  const modal = new bootstrap.Modal(modalElement);
  modal.show();
  
  // Reset chat messages display
  const chatMessages = document.getElementById('buyerChatMessages');
  if (chatMessages) {
    chatMessages.innerHTML = '<div class="text-center p-4 text-gray-500"><i class="fa-solid fa-spinner fa-spin"></i> Memuat pesan...</div>';
  }
  
  // Get or create chat
  // CATATAN: Selalu gunakan general chat (tanpa orderId) untuk memastikan semua chat terhubung
  // Bahkan jika dipanggil dari halaman orders, tetap gunakan general chat
  try {
    // Selalu gunakan general chat endpoint (tanpa orderId)
    const url = '/api/chat/get-or-create';
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });
    
    if (!response.ok) {
      let errorMessage = 'Failed to get chat';
      try {
        const errorData = await response.json();
        errorMessage = errorData.error || errorMessage;
      } catch (e) {
        errorMessage = `HTTP ${response.status}: ${response.statusText}`;
      }
      throw new Error(errorMessage);
    }
    
    const chat = await response.json();
    
    if (!chat || !chat.chat_id) {
      throw new Error('Invalid chat response: ' + JSON.stringify(chat));
    }
    
    buyerChatId = chat.chat_id;
    
    await loadBuyerMessages();
    
    // Start polling
    if (buyerChatPollInterval) {
      clearInterval(buyerChatPollInterval);
    }
    buyerChatPollInterval = setInterval(loadBuyerMessages, 3000);
  } catch (error) {
    console.error('Error opening chat:', error);
    if (chatMessages) {
      chatMessages.innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat chat: ' + error.message + '</div>';
    }
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: 'Gagal membuat chat: ' + error.message
      });
    }
  }
}

// Open chat for specific order (from orders page)
// CATATAN: Meskipun dipanggil dengan orderId, tetap menggunakan general chat
// untuk memastikan semua chat terhubung ke admin yang sama
function openChatForOrder(orderId) {
  // Tetap gunakan general chat (tanpa orderId) untuk konsistensi
  openChatModal(null);
}

// Load buyer messages
async function loadBuyerMessages() {
  if (!buyerChatId) return;
  
  const chatMessages = document.getElementById('buyerChatMessages');
  if (!chatMessages) return;
  
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
      console.error('CSRF token not found');
      return;
    }
    
    const response = await fetch(`/api/chat/${buyerChatId}/messages`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken.content,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Failed to load messages:', response.status, errorText);
      throw new Error('Failed to load messages: ' + response.status);
    }
    
    const responseData = await response.json();
    
    // Format: { chat_id, messages: [...] }
    const messages = responseData.messages || [];
    
    if (messages.length === 0) {
      chatMessages.innerHTML = '<div class="text-center p-4 text-gray-400">Belum ada pesan. Mulai percakapan dengan penjual!</div>';
      return;
    }
    
    // Debug: Log currentUserId dan messages
    console.log('=== BUYER CHAT DEBUG ===');
    console.log('currentUserId:', currentUserId, 'Type:', typeof currentUserId);
    console.log('Messages count:', messages.length);
    console.log('First message:', messages[0]);
    
    chatMessages.innerHTML = messages.map((msg, index) => {
      // Buyer: pesan dari buyer = kanan, pesan dari admin/seller = kiri
      // Logic WhatsApp: isMine = msg.sender_id === currentUserId
      // Convert to string untuk memastikan perbandingan benar
      const msgSenderId = String(msg.sender_id || '');
      const currentUserIdStr = String(currentUserId || '');
      const isMine = msgSenderId === currentUserIdStr;
      
      // Deklarasi sender HARUS sebelum digunakan
      const sender = msg.sender || {};
      const senderName = sender.name || sender.email || 'User';
      const senderRole = sender.role || '';
      
      // Cek apakah sender adalah admin (jika admin, jangan tampilkan nama)
      const isAdminMessage = senderRole === 'admin' || senderRole === 'seller';
      
      // Debug log untuk 3 pesan pertama
      if (index < 3) {
        console.log(`Message ${index}:`, {
          msg_sender_id: msgSenderId,
          currentUserId: currentUserIdStr,
          isMine: isMine,
          sender_role: senderRole,
          isAdminMessage: isAdminMessage,
          message: msg.message?.substring(0, 20),
          sender_name: senderName
        });
      }
      const senderInitials = senderName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
      const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      const messageText = msg.message || '';
      
      // Pastikan class diterapkan dengan benar
      const messageClass = isMine ? 'message-right' : 'message-left';
      
      // Tidak tampilkan sender name untuk semua pesan (hanya bubble saja)
      
      return `
        <div class="chat-message ${messageClass}">
          <div class="message-bubble">
            ${escapeHtml(messageText)}
          </div>
          <div class="message-time">${time}</div>
        </div>
      `;
    }).join('');
    
    // Force scroll to bottom after rendering
    setTimeout(() => {
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }, 100);
  } catch (error) {
    console.error('Error loading messages:', error);
    if (chatMessages) {
      chatMessages.innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat pesan</div>';
    }
  }
}

// Send buyer message
async function sendBuyerMessage() {
  if (!buyerChatId) {
    await openChatModal();
    return;
  }
  
  const input = document.getElementById('buyerChatInput');
  if (!input) return;
  
  const message = input.value.trim();
  if (!message) return;
  
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
      console.error('CSRF token not found for sending message');
      return;
    }
    
    const response = await fetch(`/api/chat/${buyerChatId}/send`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken.content,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ message })
    });
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({ error: 'Failed to send message' }));
      throw new Error(errorData.error || 'Failed to send message');
    }
    
    input.value = '';
    await loadBuyerMessages();
    
    // Update chat count
    if (typeof updateChatCount === 'function') {
      updateChatCount();
    }
  } catch (error) {
    console.error('Error sending message:', error);
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: error.message || 'Gagal mengirim pesan'
      });
    }
  }
}

// Helper functions
function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
