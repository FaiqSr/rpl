// Chat functionality for Admin Dashboard
// File terpisah untuk menghindari bentrok dengan chat-buyer.js

let adminCurrentChatId = null;
let adminCurrentBuyerId = null;
let adminCurrentBuyerName = null; // Store current buyer name for delete confirmation
let adminChatPollInterval = null;

// Get current user ID (set from Laravel)
// Priority: window.currentUserId (explicit) > window.currentAdminUser?.user_id (fallback)
let currentUserId = window.currentUserId || window.currentAdminUser?.user_id || null;

// Debug: Log currentUserId saat inisialisasi
console.log('Admin currentUserId initialized:', currentUserId, 'Type:', typeof currentUserId);
console.log('window.currentUserId:', window.currentUserId);
console.log('window.currentAdminUser:', window.currentAdminUser);

// Update currentUserId jika window.currentUserId berubah (setelah DOM ready)
document.addEventListener('DOMContentLoaded', function() {
    if (window.currentUserId) {
        currentUserId = window.currentUserId;
        console.log('Admin currentUserId updated from DOM:', currentUserId);
    } else if (window.currentAdminUser?.user_id) {
        currentUserId = window.currentAdminUser.user_id;
        console.log('Admin currentUserId updated from currentAdminUser:', currentUserId);
    }
});

// Load buyer list (untuk admin)
async function loadBuyerList() {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }

        const response = await fetch('/api/chat/admin/buyers', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            cache: 'no-cache' // Prevent caching
        });

        if (!response.ok) throw new Error('Failed to load buyer list');

        const buyers = await response.json();
        const chatList = document.getElementById('chatList');
        
        if (!chatList) return;

        // If current buyer was deleted and no longer in list, clear selection
        if (adminCurrentBuyerId && !buyers.find(b => b.buyer_id === adminCurrentBuyerId)) {
            adminCurrentBuyerId = null;
            adminCurrentBuyerName = null;
            adminCurrentChatId = null;
            
            // Clear chat display
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.innerHTML = '<div class="text-center p-8 text-gray-400"><i class="fa-solid fa-comments" style="font-size: 3rem; opacity: 0.3;"></i><p class="mt-4">Pilih chat untuk memulai percakapan</p></div>';
            }
            
            // Clear chat header
            const chatHeaderName = document.getElementById('chatHeaderName');
            const chatHeaderStatus = document.getElementById('chatHeaderStatus');
            const chatMainHeader = document.getElementById('chatMainHeader');
            if (chatHeaderName) {
                chatHeaderName.textContent = 'Pilih chat untuk memulai';
            }
            if (chatHeaderStatus) {
                chatHeaderStatus.textContent = '-';
            }
            if (chatMainHeader) {
                chatMainHeader.style.display = 'none';
            }
        }

        if (buyers.length === 0) {
            chatList.innerHTML = '<div class="text-center p-4 text-gray-400">Belum ada pembeli yang chat</div>';
            return;
        }

        chatList.innerHTML = buyers.map(buyer => {
            const unreadBadge = buyer.unread_count > 0 
                ? `<span class="badge bg-danger ms-2">${buyer.unread_count}</span>` 
                : '';
            
            return `
                <div class="chat-item" onclick="adminSelectBuyer('${buyer.buyer_id}')" data-buyer-id="${buyer.buyer_id}">
                    <div class="chat-item-avatar">
                        ${(buyer.name || buyer.email || 'P').charAt(0).toUpperCase()}
                    </div>
                    <div class="chat-item-content">
                        <div class="chat-item-header">
                            <span class="chat-item-name">${escapeHtml(buyer.name || buyer.email || 'Pembeli')} ${unreadBadge}</span>
                        </div>
                        <div class="chat-item-message">${escapeHtml(buyer.email || '')}</div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Update active state if current buyer still exists
        if (adminCurrentBuyerId) {
            document.querySelectorAll('.chat-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-buyer-id="${adminCurrentBuyerId}"]`)?.classList.add('active');
        }
    } catch (error) {
        console.error('Error loading buyer list:', error);
        const chatList = document.getElementById('chatList');
        if (chatList) {
            chatList.innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat daftar pembeli</div>';
        }
    }
}

// Admin select buyer
async function adminSelectBuyer(buyerId) {
    adminCurrentBuyerId = buyerId;
    
    // Get buyer name from the clicked item
    const clickedItem = document.querySelector(`[data-buyer-id="${buyerId}"]`);
    if (clickedItem) {
        const buyerNameElement = clickedItem.querySelector('.chat-item-name');
        if (buyerNameElement) {
            // Remove unread badge from name if exists
            adminCurrentBuyerName = buyerNameElement.textContent.replace(/\d+/g, '').trim();
        }
    }
    
    // Update active state
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
    });
    clickedItem?.classList.add('active');
    
    // Load messages for this buyer
    await loadAdminMessages(buyerId);
}

// Load messages for specific buyer (admin)
async function loadAdminMessages(buyerId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }

        const response = await fetch(`/api/chat/admin/buyer/${buyerId}/messages`, {
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
        const chatId = responseData.chat_id;
        const messages = responseData.messages || [];
        
        const chatMessages = document.getElementById('chatMessages');
        
        if (!chatMessages) return;

        // Set chat_id for sending messages
        if (chatId) {
            adminCurrentChatId = chatId;
            console.log('Admin Chat ID set:', adminCurrentChatId);
        }
        
        if (!messages || messages.length === 0) {
            chatMessages.innerHTML = '<div class="text-center p-8 text-gray-400"><i class="fa-solid fa-comments" style="font-size: 3rem; opacity: 0.3;"></i><p class="mt-4">Belum ada pesan</p></div>';
            return;
        }

        // Debug: Log currentUserId dan messages
        console.log('=== ADMIN CHAT DEBUG ===');
        console.log('Chat ID:', chatId);
        console.log('currentUserId:', currentUserId, 'Type:', typeof currentUserId);
        console.log('Messages count:', messages.length);
        console.log('First message:', messages[0]);
        console.log('All messages sender_ids:', messages.map(m => m.sender_id));
        
        chatMessages.innerHTML = messages.map((msg, index) => {
            // Admin: pesan dari admin = kanan, pesan dari buyer = kiri
            // Logic WhatsApp: isMine = msg.sender_id === currentUserId
            // Convert to string untuk memastikan perbandingan benar
            const msgSenderId = String(msg.sender_id || '');
            const currentUserIdStr = String(currentUserId || '');
            const isMine = msgSenderId === currentUserIdStr;
            
            // Deklarasi sender HARUS sebelum digunakan
            const sender = msg.sender || {};
            const senderName = sender.name || sender.email || 'User';
            
            // Debug log untuk 3 pesan pertama
            if (index < 3) {
                console.log(`Message ${index}:`, {
                    msg_sender_id: msgSenderId,
                    currentUserId: currentUserIdStr,
                    isMine: isMine,
                    message: msg.message?.substring(0, 20),
                    sender_name: senderName
                });
            }
            const senderInitials = senderName.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            const time = formatTime(msg.created_at);
            const messageText = msg.message || '';
            
            return `
                <div class="chat-message ${isMine ? 'message-right' : 'message-left'}" data-message-id="${msg.message_id || msg.id}">
                    ${!isMine ? `<div class="message-sender-name">${escapeHtml(senderName)}</div>` : ''}
                    <div class="message-bubble">
                        ${escapeHtml(messageText)}
                    </div>
                    <div class="message-time">${time}</div>
                </div>
            `;
        }).join('');

        chatMessages.scrollTop = chatMessages.scrollHeight;
    } catch (error) {
        console.error('Error loading admin messages:', error);
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.innerHTML = '<div class="text-center p-4 text-danger">Gagal memuat pesan</div>';
        }
    }
}

// Send admin message
async function sendAdminMessage() {
    if (!adminCurrentChatId) {
        if (!adminCurrentBuyerId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih pembeli terlebih dahulu'
                });
            } else {
                alert('Pilih pembeli terlebih dahulu');
            }
            return;
        }
        // Create chat if doesn't exist by loading messages
        console.log('No chat ID, loading messages for buyer:', adminCurrentBuyerId);
        try {
            await loadAdminMessages(adminCurrentBuyerId);
            if (!adminCurrentChatId) {
                throw new Error('Chat ID tidak ditemukan. Silakan refresh halaman dan coba lagi.');
            }
        } catch (error) {
            console.error('Error creating chat:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal membuat chat: ' + error.message
                });
            } else {
                alert('Gagal membuat chat: ' + error.message);
            }
            return;
        }
    }

    const input = document.getElementById('chatInput');
    if (!input) return;

    const message = input.value.trim();
    if (!message) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }

        const response = await fetch(`/api/chat/${adminCurrentChatId}/send`, {
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
        
        // Reload messages
        if (adminCurrentBuyerId) {
            await loadAdminMessages(adminCurrentBuyerId);
        }
        
        // Refresh buyer list to update unread count
        await loadBuyerList();
    } catch (error) {
        console.error('Error sending admin message:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error.message || 'Gagal mengirim pesan'
            });
        } else {
            alert('Gagal mengirim pesan: ' + error.message);
        }
    }
}

// Helper functions
function formatTime(dateString) {
    if (!dateString) return '';
    
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
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Delete chat history for a buyer
async function deleteChatHistory(buyerId) {
    if (!buyerId) {
        console.error('Buyer ID is required');
        return;
    }
    
    // Get buyer name for confirmation
    const buyerName = adminCurrentBuyerName || 'Pembeli ini';
    
    // Confirmation dialog with buyer name
    const result = await Swal.fire({
        title: 'Hapus Riwayat Chat?',
        html: `
            <div class="text-start">
                <p>Anda akan menghapus <strong>seluruh riwayat chat</strong> dengan:</p>
                <p class="mb-3"><strong class="text-primary">${escapeHtml(buyerName)}</strong></p>
                <p class="text-danger"><strong>⚠️ Peringatan:</strong> Semua pesan akan dihapus secara permanen dan tidak dapat dikembalikan.</p>
                <p class="text-muted small mt-3">Hanya chat dari akun ini yang akan dihapus, chat dari akun lain tidak akan terpengaruh.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Chat Ini',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        width: '450px'
    });
    
    if (!result.isConfirmed) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }
        
        const response = await fetch(`/api/chat/delete-history/${buyerId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ error: 'Failed to delete chat history' }));
            throw new Error(errorData.error || 'Failed to delete chat history');
        }
        
        const data = await response.json();
        
        if (data.success) {
            const deletedBuyerName = data.buyer_name || adminCurrentBuyerName || 'Pembeli';
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: `<p>Riwayat chat dengan <strong>${escapeHtml(deletedBuyerName)}</strong> berhasil dihapus.</p><p class="text-muted small mt-2">${data.deleted_messages || 0} pesan telah dihapus.</p>`,
                timer: 2500,
                showConfirmButton: false
            });
            
            // Clear current chat
            adminCurrentBuyerId = null;
            adminCurrentBuyerName = null;
            adminCurrentChatId = null;
            
            // Clear messages display
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.innerHTML = '<div class="text-center p-8 text-gray-400"><i class="fa-solid fa-comments" style="font-size: 3rem; opacity: 0.3;"></i><p class="mt-4">Pilih chat untuk memulai percakapan</p></div>';
            }
            
            // Clear chat header
            const chatHeaderName = document.getElementById('chatHeaderName');
            const chatHeaderStatus = document.getElementById('chatHeaderStatus');
            const chatMainHeader = document.getElementById('chatMainHeader');
            if (chatHeaderName) {
                chatHeaderName.textContent = 'Pilih chat untuk memulai';
            }
            if (chatHeaderStatus) {
                chatHeaderStatus.textContent = '-';
            }
            if (chatMainHeader) {
                chatMainHeader.style.display = 'none';
            }
            
            // Remove active state from chat items
            document.querySelectorAll('.chat-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Reload buyer list to remove deleted buyer from the list
            // Force refresh by clearing cache and reloading
            await loadBuyerList();
            
            // Additional refresh after a short delay to ensure backend has processed the deletion
            setTimeout(async () => {
                await loadBuyerList();
            }, 300);
        } else {
            throw new Error(data.error || 'Failed to delete chat history');
        }
    } catch (error) {
        console.error('Error deleting chat history:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: error.message || 'Gagal menghapus riwayat chat'
        });
    }
}

// Filter buyer list by search
function filterBuyerList(searchTerm) {
    const chatList = document.getElementById('chatList');
    if (!chatList) return;
    
    const chatItems = chatList.querySelectorAll('.chat-item');
    const searchLower = searchTerm.toLowerCase().trim();
    
    chatItems.forEach(item => {
        const name = item.querySelector('.chat-item-name')?.textContent?.toLowerCase() || '';
        const email = item.querySelector('.chat-item-message')?.textContent?.toLowerCase() || '';
        
        if (name.includes(searchLower) || email.includes(searchLower)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Show chat menu (ellipsis icon)
function showChatMenu() {
    if (!adminCurrentBuyerId) {
        Swal.fire({
            title: 'Menu Chat',
            html: `
                <div class="text-start">
                    <button class="btn btn-outline-primary w-100 mb-2" onclick="Swal.close(); clearChatSearch();">
                        <i class="fa-solid fa-search me-2"></i> Reset Pencarian
                    </button>
                    <button class="btn btn-outline-info w-100 mb-2" onclick="Swal.close(); refreshChatList();">
                        <i class="fa-solid fa-refresh me-2"></i> Refresh Chat
                    </button>
                    <button class="btn btn-outline-secondary w-100" onclick="Swal.close();">
                        <i class="fa-solid fa-times me-2"></i> Tutup
                    </button>
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: false,
            width: '300px'
        });
        return;
    }
    
    Swal.fire({
        title: 'Menu Chat',
        html: `
            <div class="text-start">
                <button class="btn btn-outline-primary w-100 mb-2" onclick="Swal.close(); clearChatSearch();">
                    <i class="fa-solid fa-search me-2"></i> Reset Pencarian
                </button>
                <button class="btn btn-outline-info w-100 mb-2" onclick="Swal.close(); refreshChatList();">
                    <i class="fa-solid fa-refresh me-2"></i> Refresh Chat
                </button>
                <button class="btn btn-outline-danger w-100 mb-2" onclick="Swal.close(); deleteChatHistory('${adminCurrentBuyerId}');">
                    <i class="fa-solid fa-trash me-2"></i> Hapus Riwayat Chat
                </button>
                <button class="btn btn-outline-secondary w-100" onclick="Swal.close();">
                    <i class="fa-solid fa-times me-2"></i> Tutup
                </button>
            </div>
        `,
        showConfirmButton: false,
        showCancelButton: false,
        width: '300px'
    });
}

// Clear chat search
function clearChatSearch() {
    const searchInput = document.getElementById('chatSearchInput');
    if (searchInput) {
        searchInput.value = '';
        filterBuyerList('');
    }
}

// Refresh chat list
async function refreshChatList() {
    await loadBuyerList();
    if (adminCurrentBuyerId) {
        await loadAdminMessages(adminCurrentBuyerId);
    }
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Chat berhasil di-refresh',
        timer: 1500,
        showConfirmButton: false
    });
}

// Start new chat (pencil icon)
function startNewChat() {
    Swal.fire({
        title: 'Chat Baru',
        text: 'Pilih pembeli dari daftar di sebelah kiri untuk memulai chat baru',
        icon: 'info',
        confirmButtonColor: '#69B578',
        confirmButtonText: 'OK'
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load buyer list
    loadBuyerList();
    
    // Start polling for new messages
    if (adminChatPollInterval) {
        clearInterval(adminChatPollInterval);
    }
    adminChatPollInterval = setInterval(() => {
        if (adminCurrentBuyerId) {
            loadAdminMessages(adminCurrentBuyerId);
        }
        loadBuyerList(); // Refresh buyer list for unread counts
    }, 3000);
});
