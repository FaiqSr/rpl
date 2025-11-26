<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Get all chats for current user (buyer or seller)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSeller = $user->role === 'seller';

        if ($isSeller) {
            // Seller: Get all chats where seller_id matches
            $chats = Chat::with(['buyer', 'order', 'messages' => function($query) {
                    $query->latest()->limit(1);
                }])
                ->where('seller_id', $user->user_id)
                ->orderBy('last_message_at', 'desc')
                ->get()
                ->map(function($chat) {
                    $chat->latest_message = $chat->messages->first();
                    return $chat;
                });
        } else {
            // Buyer: Get all chats where buyer_id matches
            $chats = Chat::with(['seller', 'order', 'messages' => function($query) {
                    $query->latest()->limit(1);
                }])
                ->where('buyer_id', $user->user_id)
                ->orderBy('last_message_at', 'desc')
                ->get()
                ->map(function($chat) {
                    $chat->latest_message = $chat->messages->first();
                    return $chat;
                });
        }

        return response()->json($chats);
    }

    /**
     * Get or create chat for a specific order
     */
    public function getOrCreateChat(Request $request, $orderId = null)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized. Please login first.'], 401);
        }
        
        if ($orderId) {
            try {
                $order = Order::with('user')->findOrFail($orderId);
                
                // Check if user has access to this order (buyer, visitor, or admin can access)
                if (($user->role === 'buyer' || $user->role === 'visitor') && $order->user_id !== $user->user_id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                
                // Find existing chat for this order
                $chat = Chat::where('order_id', $orderId)->first();
                
                if (!$chat) {
                    // Get seller (first seller user, or if none, use first admin as seller)
                    $seller = \App\Models\User::where('role', 'seller')->first();
                    
                    // If no seller found, use first admin as seller (for demo purposes)
                    if (!$seller) {
                        $seller = \App\Models\User::where('role', 'admin')->first();
                    }
                    
                    if (!$seller) {
                        return response()->json(['error' => 'Seller not found. Please create a seller or admin user.'], 404);
                    }

                    // Create new chat
                    $chat = Chat::create([
                        'buyer_id' => $order->user_id,
                        'seller_id' => $seller->user_id,
                        'order_id' => $orderId,
                    ]);
                }

                return response()->json($chat->load(['buyer', 'seller', 'order']));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json(['error' => 'Order not found'], 404);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error creating chat for order: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to create chat: ' . $e->getMessage()], 500);
            }
        }

        // If no orderId, find or create general chat (for buyer, visitor, or admin acting as buyer)
        if ($user->role === 'buyer' || $user->role === 'visitor' || $user->role === 'admin') {
            // Get seller (first seller user, or if none, use first admin as seller)
            $seller = \App\Models\User::where('role', 'seller')->first();
            
            // If no seller found, use first admin as seller (for demo purposes)
            if (!$seller) {
                $seller = \App\Models\User::where('role', 'admin')->first();
            }
            
            if (!$seller) {
                return response()->json(['error' => 'Seller not found. Please create a seller or admin user.'], 404);
            }

            $chat = Chat::where('buyer_id', $user->user_id)
                ->where('seller_id', $seller->user_id)
                ->whereNull('order_id')
                ->first();

            if (!$chat) {
                $chat = Chat::create([
                    'buyer_id' => $user->user_id,
                    'seller_id' => $seller->user_id,
                ]);
            }

            return response()->json($chat->load(['buyer', 'seller']));
        }

        // For seller, return chat by chatId if provided (from query parameter or route)
        if ($user->role === 'seller') {
            $chatId = $request->query('chat_id') ?? $request->input('chat_id');
            if ($chatId) {
                try {
                    $chat = Chat::with(['buyer', 'seller', 'order'])
                        ->where('chat_id', $chatId)
                        ->where('seller_id', $user->user_id)
                        ->firstOrFail();
                    
                    return response()->json($chat);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    return response()->json(['error' => 'Chat not found'], 404);
                }
            }
        }

        return response()->json(['error' => 'Invalid request. User role: ' . ($user->role ?? 'unknown')], 400);
    }

    /**
     * Get messages for a specific chat
     */
    public function getMessages($chatId)
    {
        $user = Auth::user();
        
        $chat = Chat::findOrFail($chatId);
        
        // Check if user has access to this chat
        if ($chat->buyer_id !== $user->user_id && $chat->seller_id !== $user->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
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
        if ($user->role === 'buyer') {
            $chat->buyer_unread_count = 0;
        } else {
            $chat->seller_unread_count = 0;
        }
        $chat->save();

        $messages = Message::with('sender')
            ->where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, $chatId)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        
        $chat = Chat::findOrFail($chatId);
        
        // Check if user has access to this chat
        if ($chat->buyer_id !== $user->user_id && $chat->seller_id !== $user->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create message
        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => $user->user_id,
            'message' => $request->message,
        ]);

        // Update chat last message
        $chat->last_message = $request->message;
        $chat->last_message_at = now();

        // Update unread count for recipient
        if ($user->role === 'buyer') {
            $chat->seller_unread_count = ($chat->seller_unread_count ?? 0) + 1;
        } else {
            $chat->buyer_unread_count = ($chat->buyer_unread_count ?? 0) + 1;
        }

        $chat->save();

        return response()->json($message->load('sender'));
    }

    /**
     * Get unread count for current user
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        if ($user->role === 'seller') {
            $count = Chat::where('seller_id', $user->user_id)
                ->sum('seller_unread_count');
        } else {
            $count = Chat::where('buyer_id', $user->user_id)
                ->sum('buyer_unread_count');
        }

        return response()->json(['unread_count' => $count ?? 0]);
    }
}
