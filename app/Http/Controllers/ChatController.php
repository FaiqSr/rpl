<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Helper function: Get or create chat untuk buyer dan admin
     * Memastikan buyer dan admin selalu menggunakan chat_id yang sama
     */
    private function getOrCreateChatForBuyer($buyerId, $orderId = null)
    {
        // Get admin/seller (first admin, fallback to seller)
        // PENTING: Gunakan seller yang sama di semua fungsi
        $seller = User::where('role', 'admin')->orderBy('created_at', 'asc')->first();
        if (!$seller) {
            $seller = User::where('role', 'seller')->orderBy('created_at', 'asc')->first();
        }
        
        if (!$seller) {
            throw new \Exception('Admin not found');
        }
        
        $sellerId = $seller->user_id;
        
        // SOLUSI: Selalu gunakan general chat (tanpa order_id) untuk memastikan semua chat terhubung
        // Bahkan jika dipanggil dari halaman orders, tetap gunakan general chat
        // Ini memastikan buyer dan admin selalu menggunakan chat_id yang sama
        
        // Cari general chat terlebih dahulu
        $chat = Chat::where('buyer_id', $buyerId)
            ->where('seller_id', $sellerId)
            ->whereNull('order_id')
            ->first();

        // Jika tidak ada general chat, buat baru
        if (!$chat) {
            $chat = Chat::create([
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
            ]);
            
            Log::info('getOrCreateChatForBuyer - New general chat created', [
                'chat_id' => $chat->chat_id,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'requested_order_id' => $orderId
            ]);
        } else {
            Log::info('getOrCreateChatForBuyer - General chat found', [
                'chat_id' => $chat->chat_id,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'requested_order_id' => $orderId
            ]);
        }
        
        return $chat;
    }
    
    /**
     * Helper function: Get chat untuk admin berdasarkan buyer_id
     * Memastikan admin menggunakan chat_id yang sama dengan buyer
     * SELALU gunakan general chat (tanpa order_id) untuk konsistensi
     */
    private function getChatForAdmin($buyerId, $adminId)
    {
        // Get admin/seller yang sama dengan yang digunakan buyer
        $seller = User::where('role', 'admin')->orderBy('created_at', 'asc')->first();
        if (!$seller) {
            $seller = User::where('role', 'seller')->orderBy('created_at', 'asc')->first();
        }
        
        if (!$seller) {
            throw new \Exception('Admin not found');
        }
        
        $sellerId = $seller->user_id;
        
        // SELALU gunakan general chat (tanpa order_id) untuk memastikan konsistensi
        // Ini memastikan admin dan buyer selalu menggunakan chat_id yang sama
        $chat = Chat::where('buyer_id', $buyerId)
            ->where('seller_id', $sellerId)
            ->whereNull('order_id') // Hanya general chat
            ->first();

        // If no general chat found, create a new one
        if (!$chat) {
            $chat = Chat::create([
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
            ]);
            
            Log::info('getChatForAdmin - New general chat created', [
                'chat_id' => $chat->chat_id,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'admin_user_id' => $adminId
            ]);
        } else {
            Log::info('getChatForAdmin - General chat found', [
                'chat_id' => $chat->chat_id,
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'admin_user_id' => $adminId,
                'last_message_at' => $chat->last_message_at
            ]);
        }
        
        return $chat;
    }

    /**
     * Get all chats for current user (buyer or admin)
     * Buyer: hanya chat miliknya sendiri
     * Admin: semua chat dengan daftar buyer
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Admin/Seller: Get all chats dengan daftar buyer
            if ($user->role === 'admin' || $user->role === 'seller') {
                $chats = Chat::with(['buyer', 'order'])
                    ->where('seller_id', $user->user_id)
                    ->orderBy('last_message_at', 'desc')
                    ->get()
                    ->map(function($chat) {
                        return [
                            'chat_id' => $chat->chat_id,
                            'buyer_id' => $chat->buyer_id,
                            'buyer_name' => $chat->buyer->name ?? $chat->buyer->email ?? 'Pembeli',
                            'buyer_email' => $chat->buyer->email ?? '',
                            'order_id' => $chat->order_id,
                            'order_info' => $chat->order ? 'Pesanan #' . substr($chat->order_id, 0, 8) : null,
                            'last_message' => $chat->last_message,
                            'last_message_at' => $chat->last_message_at,
                            'unread_count' => $chat->seller_unread_count ?? 0,
                        ];
                    });

                return response()->json($chats);
            }

            // Buyer/Visitor: Hanya chat miliknya sendiri
            if ($user->role === 'buyer' || $user->role === 'visitor') {
                $chats = Chat::with(['seller', 'order'])
                    ->where('buyer_id', $user->user_id)
                    ->orderBy('last_message_at', 'desc')
                    ->get()
                    ->map(function($chat) {
                        return [
                            'chat_id' => $chat->chat_id,
                            'seller_id' => $chat->seller_id,
                            'seller_name' => $chat->seller->name ?? 'Admin',
                            'order_id' => $chat->order_id,
                            'order_info' => $chat->order ? 'Pesanan #' . substr($chat->order_id, 0, 8) : null,
                            'last_message' => $chat->last_message,
                            'last_message_at' => $chat->last_message_at,
                            'unread_count' => $chat->buyer_unread_count ?? 0,
                        ];
                    });

                return response()->json($chats);
            }

            return response()->json(['error' => 'Invalid role'], 403);
        } catch (\Exception $e) {
            Log::error('Error fetching chats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load chats'], 500);
        }
    }

    /**
     * Get or create chat (untuk buyer)
     * Memastikan hanya ada SATU chat general per buyer
     */
    public function getOrCreateChat(Request $request, $orderId = null)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized. Please login first.'], 401);
            }

            // Hanya buyer/visitor yang bisa create chat
            if ($user->role !== 'buyer' && $user->role !== 'visitor' && $user->role !== 'admin') {
                return response()->json(['error' => 'Only buyers can create chats'], 403);
            }

            // Jika ada orderId, validasi order
            if ($orderId) {
                try {
                    $order = Order::findOrFail($orderId);
                    
                    // Buyer hanya bisa akses order miliknya
                    if (($user->role === 'buyer' || $user->role === 'visitor') && $order->user_id !== $user->user_id) {
                        return response()->json(['error' => 'Unauthorized'], 403);
                    }
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    return response()->json(['error' => 'Order not found'], 404);
                }
            }

            // Gunakan helper function untuk memastikan chat_id konsisten
            $chat = $this->getOrCreateChatForBuyer($user->user_id, $orderId);
            
            Log::info('Buyer getOrCreateChat - Response', [
                'chat_id' => $chat->chat_id,
                'buyer_id' => $chat->buyer_id,
                'seller_id' => $chat->seller_id,
                'order_id' => $chat->order_id,
                'buyer_user_id' => $user->user_id
            ]);

            return response()->json([
                'chat_id' => $chat->chat_id,
                'buyer_id' => $chat->buyer_id,
                'seller_id' => $chat->seller_id,
                'order_id' => $chat->order_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating chat: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create chat: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get messages for a specific chat
     * Format: { chat_id, messages: [...] }
     */
    public function getMessages($chatId)
    {
        try {
        $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        
        $chat = Chat::findOrFail($chatId);
        
            // FILTER ROLE: Buyer hanya bisa akses chat miliknya
            if ($user->role === 'buyer' || $user->role === 'visitor') {
                if ($chat->buyer_id !== $user->user_id) {
                    return response()->json(['error' => 'Unauthorized. You can only access your own chats.'], 403);
                }
            }
            
            // Admin/Seller: bisa akses chat dengan seller_id yang sesuai
            if ($user->role === 'admin' || $user->role === 'seller') {
                // Get seller yang digunakan buyer
                $seller = User::where('role', 'admin')->orderBy('created_at', 'asc')->first();
                if (!$seller) {
                    $seller = User::where('role', 'seller')->orderBy('created_at', 'asc')->first();
                }
                
                // Admin bisa akses jika chat menggunakan seller_id yang sama dengan yang digunakan buyer
                if ($seller && $chat->seller_id !== $seller->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
                }
        }

        // Mark messages as read
        Message::where('chat_id', $chatId)
            ->where('sender_id', '!=', $user->user_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        // Update unread count
            if ($user->role === 'buyer' || $user->role === 'visitor') {
            $chat->buyer_unread_count = 0;
        } else {
            $chat->seller_unread_count = 0;
        }
        $chat->save();

            // Get messages dengan sender info - PASTIKAN sender info lengkap
        $messages = Message::with('sender')
            ->where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($msg) use ($chatId, $user) {
                    // Validasi: pastikan sender_id ada
                    if (!$msg->sender_id) {
                        Log::warning('Message without sender_id', ['message_id' => $msg->message_id]);
                    }

                    // Pastikan sender info lengkap
                    $sender = $msg->sender;
                    if (!$sender) {
                        Log::warning('Message without sender', ['message_id' => $msg->message_id, 'sender_id' => $msg->sender_id]);
                    }

                    // Debug log untuk 3 pesan pertama
                    static $logCount = 0;
                    if ($logCount < 3) {
                        Log::info('getMessages - Message data', [
                            'chat_id' => $chatId,
                            'message_id' => $msg->message_id,
                            'sender_id' => $msg->sender_id,
                            'sender_user_id' => $sender->user_id ?? null,
                            'current_user_id' => $user->user_id,
                            'user_role' => $user->role,
                            'message_preview' => substr($msg->message ?? '', 0, 20)
                        ]);
                        $logCount++;
                    }

                    return [
                        'id' => $msg->message_id,
                        'message_id' => $msg->message_id,
                        'chat_id' => $chatId,
                        'sender_id' => (string) $msg->sender_id, // Convert to string untuk konsistensi
                        'message' => $msg->message ?? '', // Prevent null
                        'is_read' => $msg->is_read,
                        'created_at' => $msg->created_at,
                        'sender' => $sender ? [
                            'user_id' => (string) $sender->user_id, // Convert to string
                            'name' => $sender->name ?? '',
                            'email' => $sender->email ?? '',
                        ] : [
                            'user_id' => (string) ($msg->sender_id ?? ''),
                            'name' => 'Unknown User',
                            'email' => '',
                        ],
                    ];
                });

            // Return format konsisten: { chat_id, messages: [...] }
            return response()->json([
                'chat_id' => $chatId,
                'messages' => $messages->toArray(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Chat not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error loading messages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load messages'], 500);
        }
    }

    /**
     * Send a message
     * Validation: message tidak boleh null
     */
    public function sendMessage(Request $request, $chatId)
    {
        try {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validasi: message tidak boleh null atau kosong
            if (empty($request->message) || $request->message === null) {
                return response()->json(['error' => 'Message cannot be empty'], 422);
            }
        
        $chat = Chat::findOrFail($chatId);
        
            // FILTER ROLE: Buyer hanya bisa kirim ke chat miliknya
            if ($user->role === 'buyer' || $user->role === 'visitor') {
                if ($chat->buyer_id !== $user->user_id) {
                    return response()->json(['error' => 'Unauthorized. You can only send messages to your own chats.'], 403);
                }
            }
            
            // Admin/Seller: bisa kirim ke chat dengan seller_id yang sesuai
            if ($user->role === 'admin' || $user->role === 'seller') {
                // Get seller yang digunakan buyer
                $seller = User::where('role', 'admin')->orderBy('created_at', 'asc')->first();
                if (!$seller) {
                    $seller = User::where('role', 'seller')->orderBy('created_at', 'asc')->first();
                }
                
                // Admin bisa kirim jika chat menggunakan seller_id yang sama dengan yang digunakan buyer
                if ($seller && $chat->seller_id !== $seller->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
            }

            // Create message - PASTIKAN sender_id = user yang sedang login
            $senderId = $user->user_id;
            
            Log::info('sendMessage - Creating message', [
                'chat_id' => $chatId,
                'sender_id' => $senderId,
                'sender_role' => $user->role,
                'sender_email' => $user->email,
                'message_preview' => substr($request->message, 0, 50)
            ]);
            
        $message = Message::create([
            'chat_id' => $chatId,
                'sender_id' => $senderId, // PASTIKAN menggunakan user yang sedang login
            'message' => $request->message,
        ]);
            
            Log::info('sendMessage - Message created', [
                'message_id' => $message->message_id,
                'chat_id' => $message->chat_id,
                'sender_id' => $message->sender_id,
                'actual_sender_id' => $senderId
            ]);

            // Load sender untuk response
            $message->load('sender');

        // Update chat last message
        $chat->last_message = $request->message;
        $chat->last_message_at = now();

        // Update unread count for recipient
            if ($user->role === 'buyer' || $user->role === 'visitor') {
            $chat->seller_unread_count = ($chat->seller_unread_count ?? 0) + 1;
        } else {
            $chat->buyer_unread_count = ($chat->buyer_unread_count ?? 0) + 1;
        }

        $chat->save();

            // Return message dengan format konsisten
            return response()->json([
                'id' => $message->message_id,
                'message_id' => $message->message_id,
                'chat_id' => $message->chat_id,
                'sender_id' => (string) $message->sender_id, // Convert to string untuk konsistensi
                'message' => $message->message,
                'created_at' => $message->created_at,
                'sender' => $message->sender ? [
                    'user_id' => (string) $message->sender->user_id, // Convert to string
                    'name' => $message->sender->name ?? '',
                    'email' => $message->sender->email ?? '',
                ] : [
                    'user_id' => (string) $message->sender_id,
                    'name' => 'Unknown User',
                    'email' => '',
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Chat not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get unread count for current user
     */
    public function getUnreadCount()
    {
        try {
        $user = Auth::user();
        
            if (!$user) {
                return response()->json(['unread_count' => 0]);
            }

            if ($user->role === 'admin' || $user->role === 'seller') {
            $count = Chat::where('seller_id', $user->user_id)
                ->sum('seller_unread_count');
        } else {
            $count = Chat::where('buyer_id', $user->user_id)
                ->sum('buyer_unread_count');
        }

            return response()->json(['unread_count' => (int)($count ?? 0)]);
        } catch (\Exception $e) {
            Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json(['unread_count' => 0]);
        }
    }

    /**
     * Admin: Get list of buyers who have chats
     */
    public function getBuyerList()
    {
        try {
            $user = Auth::user();
            
            if (!$user || ($user->role !== 'admin' && $user->role !== 'seller')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get distinct buyers who have chats with this admin
            $buyers = Chat::with('buyer')
                ->where('seller_id', $user->user_id)
                ->select('buyer_id')
                ->distinct()
                ->get()
                ->map(function($chat) use ($user) {
                    $buyer = $chat->buyer;
                    if (!$buyer) return null;
                    
                    // Get unread count for this buyer (sum dari semua chat dengan buyer ini)
                    $unreadCount = Chat::where('seller_id', $user->user_id)
                        ->where('buyer_id', $buyer->user_id)
                        ->sum('seller_unread_count');
                    
                    return [
                        'buyer_id' => $buyer->user_id,
                        'name' => $buyer->name ?? $buyer->email ?? 'Pembeli',
                        'email' => $buyer->email ?? '',
                        'unread_count' => (int)($unreadCount ?? 0),
                    ];
                })
                ->filter()
                ->values();

            return response()->json($buyers);
        } catch (\Exception $e) {
            Log::error('Error getting buyer list: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load buyer list'], 500);
        }
    }

    /**
     * Admin: Get messages for a specific buyer
     * Format: { chat_id, messages: [...] }
     * Memastikan admin dan buyer menggunakan chat_id yang sama
     */
    public function getMessagesByBuyer($buyerId)
    {
        try {
            $user = Auth::user();
            
            if (!$user || ($user->role !== 'admin' && $user->role !== 'seller')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Gunakan helper function untuk memastikan chat_id sama dengan buyer
            $chat = $this->getChatForAdmin($buyerId, $user->user_id);
            
            Log::info('Admin getMessagesByBuyer - Response', [
                'chat_id' => $chat->chat_id,
                'buyer_id' => $buyerId,
                'seller_id' => $chat->seller_id,
                'admin_user_id' => $user->user_id
            ]);

            // Mark messages as read for admin
            Message::where('chat_id', $chat->chat_id)
                ->where('sender_id', '!=', $user->user_id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            // Update unread count
            $chat->seller_unread_count = 0;
            $chat->save();

            // Get messages dengan sender info - PASTIKAN sender info lengkap
            $messages = Message::with('sender')
                ->where('chat_id', $chat->chat_id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($msg) use ($chat, $user) {
                    // Validasi: pastikan sender_id ada
                    if (!$msg->sender_id) {
                        Log::warning('Message without sender_id', ['message_id' => $msg->message_id]);
                    }

                    // Pastikan sender info lengkap
                    $sender = $msg->sender;
                    if (!$sender) {
                        Log::warning('Message without sender', ['message_id' => $msg->message_id, 'sender_id' => $msg->sender_id]);
                    }

                    // Debug log untuk 3 pesan pertama
                    static $logCount = 0;
                    if ($logCount < 3) {
                        Log::info('Admin getMessagesByBuyer - Message data', [
                            'message_id' => $msg->message_id,
                            'sender_id' => $msg->sender_id,
                            'sender_user_id' => $sender->user_id ?? null,
                            'current_user_id' => $user->user_id,
                            'message_preview' => substr($msg->message ?? '', 0, 20)
                        ]);
                        $logCount++;
                    }

                    return [
                        'id' => $msg->message_id,
                        'message_id' => $msg->message_id,
                        'chat_id' => $chat->chat_id,
                        'sender_id' => (string) $msg->sender_id, // Convert to string untuk konsistensi
                        'message' => $msg->message ?? '', // Prevent null
                        'is_read' => $msg->is_read,
                        'created_at' => $msg->created_at,
                        'sender' => $sender ? [
                            'user_id' => (string) $sender->user_id, // Convert to string
                            'name' => $sender->name ?? '',
                            'email' => $sender->email ?? '',
                        ] : [
                            'user_id' => (string) ($msg->sender_id ?? ''),
                            'name' => 'Unknown User',
                            'email' => '',
                        ],
                    ];
                });

            // Return format konsisten: { chat_id, messages: [...] }
            return response()->json([
                'chat_id' => $chat->chat_id,
                'messages' => $messages->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting messages by buyer: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load messages'], 500);
        }
    }

    /**
     * Delete entire chat history for a specific buyer
     * Only admin/seller can delete chat history
     * Only deletes chat from the selected buyer, not all buyers
     */
    public function deleteChatHistory($buyerId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Only admin/seller can delete chat history
            if ($user->role !== 'admin' && $user->role !== 'seller') {
                return response()->json(['error' => 'Unauthorized. Only admin can delete chat history.'], 403);
            }

            // Validate buyer exists
            $buyer = User::find($buyerId);
            if (!$buyer) {
                return response()->json(['error' => 'Buyer not found'], 404);
            }

            // Get seller ID (same logic as in getChatForAdmin)
            $seller = User::where('role', 'admin')->orderBy('created_at', 'asc')->first();
            if (!$seller) {
                $seller = User::where('role', 'seller')->orderBy('created_at', 'asc')->first();
            }
            
            if (!$seller) {
                return response()->json(['error' => 'Seller not found'], 404);
            }

            // IMPORTANT: Only find chats between THIS SPECIFIC buyer and seller
            // This ensures we only delete chat from the selected buyer, not all buyers
            $chats = Chat::where('buyer_id', $buyerId)
                ->where('seller_id', $seller->user_id)
                ->get();
            
            if ($chats->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada riwayat chat untuk dihapus',
                    'buyer_name' => $buyer->name ?? $buyer->email ?? 'Pembeli'
                ]);
            }

            $deletedMessagesCount = 0;
            $deletedChatsCount = 0;

            foreach ($chats as $chat) {
                // Delete all messages in this chat
                $messagesCount = Message::where('chat_id', $chat->chat_id)->count();
                Message::where('chat_id', $chat->chat_id)->delete();
                $deletedMessagesCount += $messagesCount;
                
                // Delete the chat itself
                $chat->delete();
                $deletedChatsCount++;
            }
            
            Log::info('Chat history deleted for specific buyer', [
                'buyer_id' => $buyerId,
                'buyer_name' => $buyer->name ?? $buyer->email ?? 'Unknown',
                'seller_id' => $seller->user_id,
                'deleted_chats' => $deletedChatsCount,
                'deleted_messages' => $deletedMessagesCount,
                'deleted_by' => $user->user_id,
                'deleted_by_role' => $user->role,
                'note' => 'Only chat from this specific buyer was deleted'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat chat berhasil dihapus',
                'buyer_name' => $buyer->name ?? $buyer->email ?? 'Pembeli',
                'deleted_chats' => $deletedChatsCount,
                'deleted_messages' => $deletedMessagesCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting chat history: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete chat history: ' . $e->getMessage()], 500);
        }
    }
}
