// Chat functionality for buyer (home store and orders page)
let buyerChatId = null;
let buyerChatPollInterval = null;

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
  try {
    const url = orderId ? `/api/chat/get-or-create/${orderId}` : '/api/chat/get-or-create';
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
      chatMessages.innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat chat</div>';
    }
  }
}

// Open chat for specific order (from orders page)
async function openChatForOrder(orderId) {
  await openChatModal(orderId);
}

// Load buyer messages
async function loadBuyerMessages() {
  if (!buyerChatId) return;
  
  const chatMessages = document.getElementById('buyerChatMessages');
  if (!chatMessages) return;
  
  try {
    const response = await fetch(`/api/chat/${buyerChatId}/messages`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });
    
    if (!response.ok) throw new Error('Failed to load messages');
    
    const messages = await response.json();
    const currentUser = window.currentUser || {};
    
    if (messages.length === 0) {
      chatMessages.innerHTML = '<div class="text-center p-4 text-gray-400">Belum ada pesan. Mulai percakapan dengan penjual!</div>';
      return;
    }
    
    chatMessages.innerHTML = messages.map(msg => {
      // For buyer: sent = message from buyer (right side), received = message from seller/admin (left side)
      const isSent = msg.sender_id === currentUser.user_id;
      const sender = msg.sender || {};
      const senderName = sender.name || sender.email || 'User';
      const senderInitials = senderName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
      const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      
      return `
        <div class="d-flex mb-3 ${isSent ? 'justify-content-end' : 'justify-content-start'}">
          <div class="d-flex ${isSent ? 'flex-row-reverse' : 'flex-row'}" style="max-width: 70%;">
            <div class="rounded-circle ${isSent ? 'bg-success' : 'bg-primary'} text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 0.75rem; ${isSent ? 'margin-left: 0.5rem;' : 'margin-right: 0.5rem;'}">
              ${senderInitials}
            </div>
            <div>
              ${!isSent ? `<div style="font-size: 0.75rem; color: #6c757d; margin-bottom: 0.25rem; font-weight: 500;">${escapeHtml(senderName)}</div>` : ''}
              <div class="rounded p-2 ${isSent ? 'bg-success text-white' : 'bg-white border'}" style="word-wrap: break-word;">
                ${escapeHtml(msg.message)}
              </div>
              <small class="text-muted d-block ${isSent ? 'text-end' : 'text-start'}" style="font-size: 0.7rem; margin-top: 0.25rem;">${time}</small>
            </div>
          </div>
        </div>
      `;
    }).join('');
    
    chatMessages.scrollTop = chatMessages.scrollHeight;
  } catch (error) {
    console.error('Error loading messages:', error);
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
    const response = await fetch(`/api/chat/${buyerChatId}/send`, {
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
        text: 'Gagal mengirim pesan'
      });
    }
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Cleanup on modal close
document.addEventListener('DOMContentLoaded', function() {
  const chatModal = document.getElementById('chatModal');
  if (chatModal) {
    chatModal.addEventListener('hidden.bs.modal', function() {
      if (buyerChatPollInterval) {
        clearInterval(buyerChatPollInterval);
        buyerChatPollInterval = null;
      }
    });
  }
});

