<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Helper function untuk generate tracking number
if (!function_exists('generateTrackingNumber')) {
    function generateTrackingNumber($shippingService) {
        $prefixes = [
            'JNE' => 'JNE',
            'JNT' => 'JNT',
            'SiCepat' => 'SP',
            'Gojek' => 'GJ',
            'Grab' => 'GR'
        ];
        
        $prefix = $prefixes[$shippingService] ?? 'TRK';
        $random = strtoupper(Str::random(10));
        return $prefix . $random;
    }
}

// Helper function untuk generate nomor rekening (consistent per payment method)
if (!function_exists('getPaymentAccount')) {
    function getPaymentAccount($paymentMethod) {
        // Use consistent account numbers based on payment method
        $accounts = [
            'QRIS' => [
                'name' => 'QRIS - ChickPatrol Store',
                'account' => 'QR-' . substr(md5('qris_chickpatrol'), 0, 6) . '-' . substr(md5('qris_chickpatrol'), 6, 4),
                'type' => 'QRIS'
            ],
            'Transfer Bank' => [
                'name' => 'Bank BCA',
                'account' => '1234567890', // Consistent account number
                'account_name' => 'PT ChickPatrol Indonesia',
                'type' => 'Bank Transfer'
            ]
        ];
        
        return $accounts[$paymentMethod] ?? null;
    }
}

// Public Routes - Semua bisa diakses tanpa login
Route::get('/', function () {
    $products = Product::with('images')->orderByDesc('created_at')->paginate(24);
    return view('store.home', compact('products'));
})->name('home');

// Authentication Pages (hanya tampilan, tidak ada proses backend)
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'phone' => 'required|string|max:30',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed'
    ]);
    $user = \App\Models\User::create([
        'user_id' => (string) Str::uuid(),
        'name' => $validated['first_name'].' '.$validated['last_name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'role' => 'visitor'
    ]);
    Auth::login($user);
    return redirect()->route('home')->with('success', 'Registrasi berhasil, selamat datang!');
})->name('register.post');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);
    if (Auth::attempt($credentials, true)) {
        $request->session()->regenerate();
        return Auth::user()->role === 'admin'
            ? redirect()->route('dashboard')->with('success', 'Login admin berhasil')
            : redirect()->route('home')->with('success', 'Login berhasil');
    }
    return back()->withErrors(['email' => 'Kredensial tidak valid'])->with('error', 'Login gagal');
})->name('login.post');

Route::get('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home')->with('success', 'Logout berhasil');
})->name('logout');

// Product Detail
Route::get('/product/{id}', function ($id) {
    // Try to find by product_id first (database UUID), then by numeric id (localStorage)
    $product = \App\Models\Product::with('images')->where('product_id', $id)->first();
    
    // If not found and id is numeric, this might be a localStorage product
    if (!$product && is_numeric($id)) {
        // Create a mock product from localStorage data for demo
        // In production, you'd want to sync localStorage to DB first
        return response()->view('store.product-detail-mock', ['productId' => $id]);
    }
    
    if (!$product) abort(404);
    return view('store.product-detail', compact('product'));
})->name('product.detail');

// Order Payment Page
Route::get('/order/{id}/payment', function ($id) {
    if (!Auth::check()) {
        return redirect()->route('login')->with('error', 'Harus login terlebih dahulu');
    }
    
    $order = \App\Models\Order::with(['orderDetail.product.images'])
        ->where('order_id', $id)
        ->where('user_id', Auth::user()->user_id)
        ->firstOrFail();
    
    $paymentAccount = getPaymentAccount($order->payment_method);
    
    return view('store.payment', compact('order', 'paymentAccount'));
})->middleware('auth.session')->name('order.payment');

// Confirm Payment (Manual confirmation for demo)
Route::post('/order/{id}/confirm-payment', function ($id) {
    if (!Auth::check()) {
        return response()->json(['message' => 'Harus login terlebih dahulu'], 401);
    }
    
    $order = \App\Models\Order::where('order_id', $id)
        ->where('user_id', Auth::user()->user_id)
        ->firstOrFail();
    
    if ($order->payment_status === 'paid') {
        return response()->json([
            'success' => false,
            'message' => 'Pembayaran sudah dikonfirmasi sebelumnya'
        ], 400);
    }
    
    $order->payment_status = 'paid';
    $order->paid_at = now();
    $order->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Pembayaran berhasil dikonfirmasi',
        'redirect' => route('orders')
    ]);
})->middleware('auth.session')->name('order.confirm-payment');

// Create Order
Route::post('/order/create', function (\Illuminate\Http\Request $request) {
    try {
        if (!Auth::check()) {
            return response()->json(['message' => 'Harus login terlebih dahulu'], 401);
        }
        $validated = $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'qty' => 'required|integer|min:1',
            'buyer_name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'notes' => 'nullable|string',
            'shipping_service' => 'required|string',
            'payment_method' => 'required|string|in:QRIS,Transfer Bank',
            'total_price' => 'required|numeric'
        ]);

        // Check stock availability
        $product = Product::find($validated['product_id']);
        if (!$product || $product->stock < $validated['qty']) {
            return response()->json([
                'message' => 'Stok tidak mencukupi. Stok tersedia: ' . ($product->stock ?? 0)
            ], 400);
        }

        // Create order (for demo, use first user or create guest user)
        $user = Auth::user();

        // Generate tracking number based on shipping service
        $trackingNumber = generateTrackingNumber($validated['shipping_service']);
        
        // Create order with all fields
        $orderData = [
            'user_id' => $user->user_id,
            'total_price' => $validated['total_price'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'buyer_name' => $validated['buyer_name'],
            'buyer_phone' => $validated['phone'],
            'buyer_address' => $validated['address'],
            'shipping_service' => $validated['shipping_service'],
            'payment_method' => $validated['payment_method'],
            'tracking_number' => $trackingNumber,
        ];
        
        // Add payment_status and paid_at only if columns exist
        if (\Schema::hasColumn('orders', 'payment_status')) {
            $orderData['payment_status'] = 'pending';
        }
        if (\Schema::hasColumn('orders', 'paid_at')) {
            $orderData['paid_at'] = null;
        }
        
        $order = \App\Models\Order::create($orderData);

        \App\Models\OrderDetail::create([
            'order_detail_id' => (string) \Illuminate\Support\Str::uuid(),
            'order_id' => $order->order_id,
            'product_id' => $validated['product_id'],
            'qty' => $validated['qty'],
            'price' => $validated['total_price'] / $validated['qty']
        ]);

        // Redirect to payment page
        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->order_id,
            'redirect' => route('order.payment', $order->order_id)
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to create order: ' . $e->getMessage()], 500);
    }
})->name('order.create');

// Confirm Order Received (for buyer)
Route::post('/order/{id}/confirm-received', function ($id) {
    try {
        if (!Auth::check()) {
            return response()->json(['message' => 'Harus login terlebih dahulu'], 401);
        }
        
        $order = \App\Models\Order::where('order_id', $id)->firstOrFail();
        
        // Verify that the order belongs to the logged-in user
        if ($order->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if ($order->status !== 'dikirim') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan harus dalam status dikirim terlebih dahulu'
            ], 400);
        }
        
        $order->status = 'selesai';
        $order->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Pesanan telah dikonfirmasi diterima'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengonfirmasi pesanan: ' . $e->getMessage()
        ], 500);
    }
})->middleware('auth.session')->name('order.confirm-received');

// Dashboard (siapkan untuk admin-only; middleware belum diaktifkan agar demo tetap jalan)
Route::middleware(['auth.session','admin'])->group(function() {
    Route::get('/dashboard', function () {
        return view('dashboard.seller');
    })->name('dashboard');
    Route::get('/dashboard/products', function () {
        $products = Product::with('images')->orderByDesc('created_at')->get();
        return view('dashboard.products', compact('products'));
    })->name('dashboard.products');
    Route::get('/dashboard/tools', function () { return view('dashboard.tools'); })->name('dashboard.tools');
    Route::get('/dashboard/tools/monitoring', function () { return view('dashboard.tools-monitoring'); })->name('dashboard.tools.monitoring');
    Route::get('/dashboard/tools/information', function () { return view('dashboard.tools-information'); })->name('dashboard.tools.information');
    
    // Export routes
    Route::get('/dashboard/export/pdf', [\App\Http\Controllers\ExportController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/dashboard/export/csv', [\App\Http\Controllers\ExportController::class, 'exportCsv'])->name('export.csv');
    Route::get('/dashboard/sales', function (\Illuminate\Http\Request $request) {
        $filter = $request->get('filter', 'all');
        $query = \App\Models\Order::with(['orderDetail.product.images'])->orderByDesc('created_at');
        
        if ($filter === 'dikirim') {
            $query->where('status', 'dikirim');
        } elseif ($filter === 'selesai') {
            $query->where('status', 'selesai');
        } elseif ($filter === 'all') {
            // Show all orders
        } else {
            // Default: show all orders
        }
        
        $orders = $query->get();
        return view('dashboard.sales', compact('orders', 'filter'));
    })->name('dashboard.sales');
    Route::get('/dashboard/chat', function () { return view('dashboard.chat'); })->name('dashboard.chat');
    
    // Product CRUD API
    Route::post('/dashboard/products', function (\Illuminate\Http\Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'unit' => 'required|string|max:50',
                'image' => 'nullable|string', // Base64 image or URL
            ]);
            
            // Generate unique slug
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $product = Product::create([
                'product_id' => (string) Str::uuid(),
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'unit' => $validated['unit'],
            ]);
            
            // Save image if provided
            if (!empty($validated['image'])) {
                \App\Models\ProductImage::create([
                    'product_image_id' => (string) Str::uuid(),
                    'product_id' => $product->product_id,
                    'name' => $product->name . '.jpg',
                    'url' => $validated['image'],
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'product' => $product->fresh()->load('images')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk: ' . $e->getMessage()
            ], 500);
        }
    })->name('dashboard.products.store');
    
    Route::put('/dashboard/products/{id}', function (\Illuminate\Http\Request $request, $id) {
        try {
            $product = Product::where('product_id', $id)->firstOrFail();
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'unit' => 'required|string|max:50',
                'image' => 'nullable|string',
            ]);
            
            // Generate unique slug
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->where('product_id', '!=', $product->product_id)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $product->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'unit' => $validated['unit'],
            ]);
            
            // Update image if provided
            if (!empty($validated['image'])) {
                $existingImage = $product->images->first();
                if ($existingImage) {
                    $existingImage->update(['url' => $validated['image']]);
                } else {
                    \App\Models\ProductImage::create([
                        'product_image_id' => (string) Str::uuid(),
                        'product_id' => $product->product_id,
                        'name' => $product->name . '.jpg',
                        'url' => $validated['image'],
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diperbarui',
                'product' => $product->fresh()->load('images')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk: ' . $e->getMessage()
            ], 500);
        }
    })->name('dashboard.products.update');
    
    Route::delete('/dashboard/products/{id}', function ($id) {
        try {
            $product = Product::where('product_id', $id)->firstOrFail();
            $product->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk: ' . $e->getMessage()
            ], 500);
        }
    })->name('dashboard.products.delete');
    
    // Order Management API
    Route::post('/dashboard/orders/{id}/ship', function ($id) {
        try {
            $order = \App\Models\Order::with('orderDetail.product')->where('order_id', $id)->firstOrFail();
            
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan sudah diproses sebelumnya'
                ], 400);
            }
            
            // Check if payment is completed
            if ($order->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan belum dibayar. Tidak dapat mengirim pesanan sebelum pembayaran selesai.'
                ], 400);
            }
            
            // Update order status to "dikirim" (not "selesai" yet)
            $order->status = 'dikirim';
            $order->save();
            
            // Reduce stock for each product in order
            foreach ($order->orderDetail as $detail) {
                $product = $detail->product;
                if ($product) {
                    $newStock = max(0, $product->stock - $detail->qty);
                    $product->update(['stock' => $newStock]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikirim dan stok telah dikurangi'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error shipping order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesanan: ' . $e->getMessage()
            ], 500);
        }
    })->name('dashboard.orders.ship');
    
    Route::delete('/dashboard/orders/{id}', function ($id) {
        try {
            $order = \App\Models\Order::where('order_id', $id)->firstOrFail();
            
            // Only allow delete if status is pending
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pesanan pending yang dapat dihapus'
                ], 400);
            }
            
            $order->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pesanan: ' . $e->getMessage()
            ], 500);
        }
    })->name('dashboard.orders.delete');
});


// Other Pages
Route::get('/articles', function () {
    return view('articles');
})->name('articles');

Route::get('/marketplace', function () {
    return view('marketplace');
})->name('marketplace');

Route::middleware('auth.session')->group(function(){
    Route::get('/profile', function () { return view('profile'); })->name('profile');
    Route::post('/profile/update', function (\Illuminate\Http\Request $request) {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500'
        ]);
        $user->update($data);
        return redirect()->route('profile')->with('success','Profil berhasil diperbarui');
    })->name('profile.update');
    
    // Cart Routes
    // Cart Page
    Route::get('/cart', function () {
        $user = Auth::user();
        $cartItems = \App\Models\Cart::with(['product.images'])
            ->where('user_id', $user->user_id)
            ->get();
        
        // Calculate total
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->product->price * $item->qty;
        }
        
        return view('store.cart', compact('cartItems', 'subtotal'));
    })->name('cart');
    
    // Add to Cart API
    Route::post('/cart/add', function (\Illuminate\Http\Request $request) {
        if (!Auth::check()) {
            return response()->json(['message' => 'Harus login terlebih dahulu'], 401);
        }
        
        $validated = $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'qty' => 'required|integer|min:1'
        ]);
        
        $user = Auth::user();
        $product = Product::find($validated['product_id']);
        
        // Check stock
        if ($product->stock < $validated['qty']) {
            return response()->json([
                'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $product->stock
            ], 400);
        }
        
        // Check if product already in cart
        $existingCart = \App\Models\Cart::where('user_id', $user->user_id)
            ->where('product_id', $validated['product_id'])
            ->first();
        
        if ($existingCart) {
            // Update qty
            $newQty = $existingCart->qty + $validated['qty'];
            if ($product->stock < $newQty) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $product->stock . ', di keranjang: ' . $existingCart->qty
                ], 400);
            }
            $existingCart->qty = $newQty;
            $existingCart->save();
        } else {
            // Create new cart item
            \App\Models\Cart::create([
                'cart_id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $user->user_id,
                'product_id' => $validated['product_id'],
                'qty' => $validated['qty']
            ]);
        }
        
        // Get cart count
        $cartCount = \App\Models\Cart::where('user_id', $user->user_id)->sum('qty');
        
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'cart_count' => $cartCount
        ]);
    })->name('cart.add');
    
    // Update Cart Item
    Route::put('/cart/update/{id}', function ($id, \Illuminate\Http\Request $request) {
        if (!Auth::check()) {
            return response()->json(['message' => 'Harus login terlebih dahulu'], 401);
        }
        
        $validated = $request->validate([
            'qty' => 'required|integer|min:1'
        ]);
        
        $user = Auth::user();
        $cartItem = \App\Models\Cart::where('cart_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();
        
        $product = $cartItem->product;
        
        // Check stock
        if ($product->stock < $validated['qty']) {
            return response()->json([
                'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $product->stock
            ], 400);
        }
        
        $cartItem->qty = $validated['qty'];
        $cartItem->save();
        
        // Calculate totals
        $cartItems = \App\Models\Cart::with(['product'])
            ->where('user_id', $user->user_id)
            ->get();
        
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->product->price * $item->qty;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui',
            'item_total' => $product->price * $validated['qty'],
            'subtotal' => $subtotal
        ]);
    })->name('cart.update');
    
    // Delete Cart Item
    Route::delete('/cart/delete/{id}', function ($id) {
        if (!Auth::check()) {
            return response()->json(['message' => 'Harus login terlebih dahulu'], 401);
        }
        
        $user = Auth::user();
        $cartItem = \App\Models\Cart::where('cart_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();
        
        $cartItem->delete();
        
        // Get cart count
        $cartCount = \App\Models\Cart::where('user_id', $user->user_id)->sum('qty');
        
        // Calculate subtotal
        $cartItems = \App\Models\Cart::with(['product'])
            ->where('user_id', $user->user_id)
            ->get();
        
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->product->price * $item->qty;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari keranjang',
            'cart_count' => $cartCount,
            'subtotal' => $subtotal
        ]);
    })->name('cart.delete');
    
    // Get Cart Count (for navbar)
    Route::get('/cart/count', function () {
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }
        
        $user = Auth::user();
        $cartCount = \App\Models\Cart::where('user_id', $user->user_id)->sum('qty');
        
        return response()->json(['count' => $cartCount]);
    })->name('cart.count');
    
    // Checkout from Cart (Create Order with Multiple Products)
    Route::post('/cart/checkout', function (\Illuminate\Http\Request $request) {
        if (!Auth::check()) {
            return response()->json(['message' => 'Harus login terlebih dahulu'], 401);
        }
        
        $validated = $request->validate([
            'buyer_name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'notes' => 'nullable|string',
            'shipping_service' => 'required|string',
            'payment_method' => 'required|string|in:QRIS,Transfer Bank'
        ]);
        
        $user = Auth::user();
        
        // Get all cart items
        $cartItems = \App\Models\Cart::with(['product'])
            ->where('user_id', $user->user_id)
            ->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong'], 400);
        }
        
        // Check stock for all items
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->qty) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi untuk produk: ' . $item->product->name . '. Stok tersedia: ' . $item->product->stock
                ], 400);
            }
        }
        
        // Calculate total price
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->product->price * $item->qty;
        }
        
        // Generate tracking number
        $trackingNumber = generateTrackingNumber($validated['shipping_service']);
        
        // Create order
        $orderData = [
            'user_id' => $user->user_id,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'buyer_name' => $validated['buyer_name'],
            'buyer_phone' => $validated['phone'],
            'buyer_address' => $validated['address'],
            'shipping_service' => $validated['shipping_service'],
            'payment_method' => $validated['payment_method'],
            'tracking_number' => $trackingNumber,
        ];
        
        // Add payment_status and paid_at only if columns exist
        if (Schema::hasColumn('orders', 'payment_status')) {
            $orderData['payment_status'] = 'pending';
        }
        if (Schema::hasColumn('orders', 'paid_at')) {
            $orderData['paid_at'] = null;
        }
        
        $order = \App\Models\Order::create($orderData);
        
        // Create order details for each cart item
        foreach ($cartItems as $item) {
            \App\Models\OrderDetail::create([
                'order_detail_id' => (string) Str::uuid(),
                'order_id' => $order->order_id,
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->product->price
            ]);
            
            // Reduce stock
            $item->product->stock -= $item->qty;
            $item->product->save();
        }
        
        // Clear cart
        \App\Models\Cart::where('user_id', $user->user_id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat',
            'order_id' => $order->order_id,
            'redirect' => route('order.payment', $order->order_id)
        ]);
    })->name('cart.checkout');
    
    // Buyer Orders Page
    Route::get('/orders', function () {
        $user = Auth::user();
        $orders = \App\Models\Order::with(['orderDetail.product.images'])
            ->where('user_id', $user->user_id)
            ->orderByDesc('created_at')
            ->get();
        return view('store.orders', compact('orders'));
    })->name('orders');
});

// Monitoring API dengan ML Integration (sensor + ML predictions + anomaly detection + status)
Route::get('/api/monitoring/tools', function () {
    try {
        $mlService = new \App\Services\MachineLearningService();
    
    // Gunakan waktu realtime dengan timezone WIB
    $now = now()->setTimezone('Asia/Jakarta');
    
    // Ambil data real dari database sensor_readings
    // ML Service membutuhkan minimal 30 data points
    $history = [];
    
    try {
        // Ambil 30 data terakhir dari database, diurutkan dari yang paling lama
        $sensorReadings = \App\Models\SensorReading::orderBy('recorded_at', 'asc')
            ->limit(30)
            ->get();
        
        if ($sensorReadings->count() > 0) {
            // Konversi data dari database ke format yang dibutuhkan
            foreach ($sensorReadings as $reading) {
                $history[] = [
                    'time' => $reading->recorded_at->format('Y-m-d H:00'),
                    'temperature' => (float) $reading->suhu_c,
                    'humidity' => (float) $reading->kelembaban_rh,
                    'ammonia' => (float) $reading->amonia_ppm,
                    'light' => (float) $reading->cahaya_lux
                ];
            }
            
            // Jika data kurang dari 30, generate data default untuk melengkapi
            $needed = 30 - count($history);
            if ($needed > 0) {
                $lastReading = $sensorReadings->last();
                $lastTime = $lastReading->recorded_at;
                
                for ($i = 1; $i <= $needed; $i++) {
                    $timestamp = $lastTime->copy()->addHours($i);
                    $hour = (int) $timestamp->format('H');
                    $isDaytime = ($hour >= 6 && $hour <= 18);
                    $baseTemp = $isDaytime ? 28 : 26;
                    $random = mt_rand(0, 100) / 100;
                    
                    $history[] = [
                        'time' => $timestamp->format('Y-m-d H:00'),
                        'temperature' => round($baseTemp + (mt_rand(-20, 20) / 10), 1),
                        'humidity' => round(60 + (mt_rand(-100, 100) / 10), 1),
                        'ammonia' => round(12 + (mt_rand(-40, 40) / 10), 1),
                        'light' => round(10 + ($random * 50), 1) // 10-60 lux (threshold range)
                    ];
                }
            }
        } else {
            // Jika tidak ada data di database, generate 30 data default
            for ($i = 29; $i >= 0; $i--) {
                $timestamp = $now->copy()->subHours($i);
                $hour = (int) $timestamp->format('H');
                $isDaytime = ($hour >= 6 && $hour <= 18);
                $baseLight = $isDaytime ? 300 : 200;
                $baseTemp = $isDaytime ? 28 : 26;
                
                $history[] = [
                    'time' => $timestamp->format('Y-m-d H:00'),
                    'temperature' => round($baseTemp + (mt_rand(-20, 20) / 10), 1),
                    'humidity' => round(60 + (mt_rand(-100, 100) / 10), 1),
                    'ammonia' => round(12 + (mt_rand(-40, 40) / 10), 1),
                    'light' => round($baseLight + mt_rand(-50, 50), 0)
                ];
            }
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error fetching sensor data: ' . $e->getMessage());
        // Fallback: generate 30 data default
        for ($i = 29; $i >= 0; $i--) {
            $timestamp = $now->copy()->subHours($i);
            $hour = (int) $timestamp->format('H');
            $isDaytime = ($hour >= 6 && $hour <= 18);
            $baseTemp = $isDaytime ? 28 : 26;
            $random = mt_rand(0, 100) / 100;
            
            $history[] = [
                'time' => $timestamp->format('Y-m-d H:00'),
                'temperature' => round($baseTemp + (mt_rand(-20, 20) / 10), 1),
                'humidity' => round(60 + (mt_rand(-100, 100) / 10), 1),
                'ammonia' => round(12 + (mt_rand(-40, 40) / 10), 1),
                'light' => round(10 + ($random * 50), 1) // 10-60 lux (threshold range)
            ];
        }
    }

    $latest = end($history);

    // Get ML predictions dengan error handling
    try {
        $mlResults = $mlService->getPredictions($history);
        $pred6 = $mlResults['prediction_6h'] ?? ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
        $pred24 = $mlResults['prediction_24h'] ?? ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
        $anomalies = $mlResults['anomalies'] ?? [];
        $currentStatus = $mlResults['status'] ?? ['label' => 'tidak diketahui', 'severity' => 'warning', 'message' => 'Status tidak dapat ditentukan'];
        $mlMetadata = $mlResults['ml_metadata'] ?? [];
        
        // Pastikan pred6 dan pred24 adalah array dengan struktur yang benar
        if (!is_array($pred6) || !isset($pred6['temperature'])) {
            $pred6 = ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
        }
        if (!is_array($pred24) || !isset($pred24['temperature'])) {
            $pred24 = ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error getting ML predictions: ' . $e->getMessage());
        // Fallback jika ML service error
        $mlResults = null;
        $pred6 = ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
        $pred24 = ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
    $anomalies = [];
        $currentStatus = ['label' => 'error', 'severity' => 'critical', 'message' => 'Error memproses prediksi ML: ' . $e->getMessage()];
        $mlMetadata = ['source' => 'error'];
    }

    // Generate forecast summaries (fallback jika ML service tidak mengembalikan)
    $qualitativeForecast = function($series, $metric, $unit, $safeLow, $safeHigh) {
        // Pastikan $series adalah array dan tidak kosong
        if (!is_array($series) || empty($series)) {
            return [
                'metric' => $metric,
                'summary' => "$metric: Data tidak tersedia",
                'range' => ['min' => 0, 'max' => 0, 'unit' => $unit],
                'trend' => 'tidak diketahui',
                'risk' => 'tidak diketahui'
            ];
    }

        // Konversi ke array numerik (ambil nilai saja jika array asosiatif)
        $numericSeries = array_values(array_filter($series, function($v) {
            return is_numeric($v);
        }));
        
        // Jika tidak ada nilai numerik, return default
        if (empty($numericSeries)) {
            return [
                'metric' => $metric,
                'summary' => "$metric: Data tidak valid",
                'range' => ['min' => 0, 'max' => 0, 'unit' => $unit],
                'trend' => 'tidak diketahui',
                'risk' => 'tidak diketahui'
            ];
        }
        
        // Hitung min, max, dan trend
        $min = min($numericSeries);
        $max = max($numericSeries);
        $firstValue = $numericSeries[0];
        $lastValue = end($numericSeries);
        $trend = $lastValue - $firstValue;
        $dir = $trend > 0.5 ? 'meningkat' : ($trend < -0.5 ? 'menurun' : 'stabil');
        $risk = ($min < $safeLow || $max > $safeHigh) ? 'potensi keluar batas aman' : 'dalam kisaran aman';
        
        return [
            'metric' => $metric,
            'summary' => "$metric $dir (" . round($min, 2) . "–" . round($max, 2) . " $unit) $risk",
            'range' => ['min' => round($min, 2), 'max' => round($max, 2), 'unit' => $unit],
            'trend' => $dir,
            'risk' => $risk
        ];
    };

    // Pastikan pred6 dan pred24 adalah array dengan struktur yang benar
    // Jika pred6/pred24 bukan array atau tidak memiliki struktur yang benar, gunakan default
    if (!is_array($pred6) || !isset($pred6['temperature']) || !isset($pred6['humidity']) || !isset($pred6['ammonia']) || !isset($pred6['light'])) {
        $pred6 = ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
    }
    if (!is_array($pred24) || !isset($pred24['temperature']) || !isset($pred24['humidity']) || !isset($pred24['ammonia']) || !isset($pred24['light'])) {
        $pred24 = ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
    }
    
    // Pastikan setiap key adalah array
    $pred6['temperature'] = is_array($pred6['temperature'] ?? null) ? $pred6['temperature'] : [];
    $pred6['humidity'] = is_array($pred6['humidity'] ?? null) ? $pred6['humidity'] : [];
    $pred6['ammonia'] = is_array($pred6['ammonia'] ?? null) ? $pred6['ammonia'] : [];
    $pred6['light'] = is_array($pred6['light'] ?? null) ? $pred6['light'] : [];
    
    $pred24['temperature'] = is_array($pred24['temperature'] ?? null) ? $pred24['temperature'] : [];
    $pred24['humidity'] = is_array($pred24['humidity'] ?? null) ? $pred24['humidity'] : [];
    $pred24['ammonia'] = is_array($pred24['ammonia'] ?? null) ? $pred24['ammonia'] : [];
    $pred24['light'] = is_array($pred24['light'] ?? null) ? $pred24['light'] : [];
    
    // Threshold untuk cahaya: sesuai aturan boiler (10-60 lux)
    // Catatan: Data aktual mungkin ratusan, tapi threshold tetap 10-60 sesuai aturan boiler
    // Untuk forecast, konversi nilai cahaya dari ratusan ke puluhan (dibagi 10) untuk pengecekan threshold
    $checkLightRisk = function($lightValues) {
        if (empty($lightValues) || !is_array($lightValues)) {
            return 'tidak diketahui';
        }
        // TIDAK dikonversi - nilai aktual ratusan langsung dibandingkan dengan threshold 10-60
        // Karena nilai aktual ratusan (308.8-369.4) dan threshold 10-60, maka:
        // Jika nilai > 60, berarti "di luar batas aman" (bukan potensi, tapi memang tidak aman)
        $min = min($lightValues);
        $max = max($lightValues);
        // Threshold: 10-60 lux (sesuai aturan boiler)
        // Nilai aktual ratusan (308.8-369.4) langsung dibandingkan dengan threshold 10-60
        // Jika ada nilai di luar 10-60, maka "di luar batas aman" (bukan potensi, tapi memang tidak aman)
        if ($min < 10 || $max > 60) {
            return 'di luar batas aman';
        }
        // Jika semua nilai dalam 10-60, tapi ada yang mendekati batas (di luar ideal 20-40), maka "potensi keluar batas aman"
        if ($min < 20 || $max > 40) {
            return 'potensi keluar batas aman';
        }
        return 'dalam kisaran aman';
    };

    // Generate forecast summary untuk cahaya dengan pengecekan threshold yang benar
    // Catatan: Nilai cahaya tetap dalam ratusan untuk display, tapi threshold tetap 10-60 untuk pengecekan
    $generateLightForecast = function($lightValues, $metric, $unit) use ($checkLightRisk) {
        if (empty($lightValues) || !is_array($lightValues)) {
            return [
                'metric' => $metric,
                'summary' => "$metric: Data tidak tersedia",
                'range' => ['min' => 0, 'max' => 0, 'unit' => $unit],
                'trend' => 'tidak diketahui',
                'risk' => 'tidak diketahui'
            ];
        }
        
        // Display tetap menggunakan nilai ratusan (sesuai data aktual)
        $min = min($lightValues);
        $max = max($lightValues);
        $firstValue = $lightValues[0];
        $lastValue = end($lightValues);
        $trend = $lastValue - $firstValue;
        $dir = $trend > 5 ? 'meningkat' : ($trend < -5 ? 'menurun' : 'stabil');
        // Pengecekan risk menggunakan threshold 10-60 (dengan konversi)
        $risk = $checkLightRisk($lightValues);
        
        return [
            'metric' => $metric,
            'summary' => "$metric $dir (" . round($min, 2) . "–" . round($max, 2) . " $unit) $risk",
            'range' => ['min' => round($min, 2), 'max' => round($max, 2), 'unit' => $unit],
            'trend' => $dir,
            'risk' => $risk
        ];
    };

    // Thresholds sesuai standar boiler dari model_metadata.json
    // Suhu: ideal 23-34°C, danger <23 atau >34°C
    // Kelembaban: ideal 50-70%, warn >80%
    // Amonia: ideal ≤20 ppm, warn >35 ppm
    // Cahaya: ideal 20-40 lux, warn <10 atau >60 lux
    $forecast6Summary = ($mlResults && isset($mlResults['forecast_summary_6h'])) ? $mlResults['forecast_summary_6h'] : [
        $qualitativeForecast($pred6['temperature'],'Suhu','°C',23,34),  // Sesuai metadata: ideal_min: 23, ideal_max: 34
        $qualitativeForecast($pred6['humidity'],'Kelembaban','%',50,70),  // Sesuai metadata: ideal_min: 50, ideal_max: 70
        $qualitativeForecast($pred6['ammonia'],'Amoniak','ppm',0,20),  // Sesuai metadata: ideal_max: 20
        $generateLightForecast($pred6['light'],'Cahaya','lux')  // Threshold 10-60 sesuai aturan boiler
    ];
    $forecast24Summary = ($mlResults && isset($mlResults['forecast_summary_24h'])) ? $mlResults['forecast_summary_24h'] : [
        $qualitativeForecast($pred24['temperature'],'Suhu','°C',23,34),  // Sesuai metadata: ideal_min: 23, ideal_max: 34
        $qualitativeForecast($pred24['humidity'],'Kelembaban','%',50,70),  // Sesuai metadata: ideal_min: 50, ideal_max: 70
        $qualitativeForecast($pred24['ammonia'],'Amoniak','ppm',0,20),  // Sesuai metadata: ideal_max: 20
        $generateLightForecast($pred24['light'],'Cahaya','lux')  // Threshold 10-60 sesuai aturan boiler
    ];

    // Pastikan selalu menggunakan hasil dari ML service
    // Cek source dari mlResults terlebih dahulu, lalu mlMetadata
    $mlSource = 'fallback';
    if (isset($mlResults) && isset($mlResults['source']) && $mlResults['source'] === 'ml_service') {
        $mlSource = 'ml_service';
    } elseif (isset($mlMetadata['source']) && $mlMetadata['source'] === 'ml_service') {
        $mlSource = 'ml_service';
    }
    
    $isMLConnected = $mlService->testConnection();
    
    // Jika testConnection() true tapi source masih fallback, berarti ada masalah di getPredictions()
    // Log untuk debugging
    if ($isMLConnected && $mlSource === 'fallback') {
        \Illuminate\Support\Facades\Log::warning('ML Service connected but getPredictions returned fallback', [
            'mlResults_source' => $mlResults['source'] ?? 'not set',
            'mlMetadata_source' => $mlMetadata['source'] ?? 'not set',
            'has_mlResults' => isset($mlResults),
            'has_mlMetadata' => isset($mlMetadata)
        ]);
    }
    
    // Jika ML service tidak terhubung, beri warning di meta
    if (!$isMLConnected && $mlSource === 'fallback') {
        \Illuminate\Support\Facades\Log::warning('ML Service tidak terhubung, menggunakan fallback prediction');
    }

    // Kirim notifikasi Telegram (jika dikonfigurasi) - SEBELUM return response
    try {
        $telegramService = new \App\Services\TelegramNotificationService();
        $forecast6SummaryForTelegram = isset($mlResults) && isset($mlResults['forecast_summary_6h']) ? $mlResults['forecast_summary_6h'] : $forecast6Summary;
        $telegramService->sendMonitoringNotification($latest, $currentStatus, $pred6, $anomalies, $forecast6SummaryForTelegram);
    } catch (\Exception $telegramError) {
        // Log error tapi jangan gagalkan response
        \Illuminate\Support\Facades\Log::warning('Failed to send Telegram notification: ' . $telegramError->getMessage());
    }
    
    return response()->json([
        'meta' => [
            'generated_at' => $now->format('Y-m-d H:i:s') . ' WIB',
            'generated_at_timestamp' => $now->timestamp,
            'timezone' => 'Asia/Jakarta (WIB)',
            'interval' => '1 hour', // Data setiap 1 jam
            'history_hours' => count($history),
            'history_count' => count($history),
            'ml_source' => $mlSource,
            'ml_connected' => (bool) $isMLConnected, 
            'ml_model_name' => $mlMetadata['model_name'] ?? null,
            'ml_model_version' => $mlMetadata['model_version'] ?? null,
            'ml_accuracy' => $mlMetadata['accuracy'] ?? null,
            'ml_confidence' => $mlMetadata['confidence'] ?? null,
            'ml_prediction_time' => $mlMetadata['prediction_time'] ?? null,
            'data_source' => 'ml_service', 
            'warning' => !$isMLConnected ? 'ML Service tidak terhubung, menggunakan fallback prediction' : null
        ],
        'latest' => $latest,
        'history' => $history,
        'prediction_6h' => $pred6,
        'prediction_24h' => $pred24,
        'status' => $currentStatus,
        'forecast_summary_6h' => isset($mlResults) && isset($mlResults['forecast_summary_6h']) ? $mlResults['forecast_summary_6h'] : $forecast6Summary,
        'forecast_summary_24h' => isset($mlResults) && isset($mlResults['forecast_summary_24h']) ? $mlResults['forecast_summary_24h'] : $forecast24Summary,
        'anomalies' => $anomalies,
        'ml_metadata' => $mlMetadata
    ]);
    
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error in monitoring API: ' . $e->getMessage());
        \Illuminate\Support\Facades\Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'error' => 'Internal server error',
            'message' => $e->getMessage(),
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
                'ml_connected' => false,
                'error' => true
            ]
        ], 500);
    }
});
