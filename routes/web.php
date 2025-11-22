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

// Monitoring API mock (sensor + ML predictions + anomaly detection + status)
Route::get('/api/monitoring/tools', function () {
    $now = now();
    // Build 24h history with random but plausible poultry farm values
    $history = [];
    for ($i = 23; $i >= 0; $i--) {
        $timestamp = $now->copy()->subHours($i)->format('Y-m-d H:00');
        $temp = 24 + rand(-3, 3) + ($i > 12 ? 0.5 : 0); // Slight afternoon increase
        $humidity = 65 + rand(-5, 5);
        $ammonia = max(5, 10 + rand(-3, 4));
        $light = ($i >= 6 && $i <= 18) ? 700 + rand(-100, 100) : 120 + rand(-30, 30);
        $history[] = [
            'time' => $timestamp,
            'temperature' => round($temp, 1),
            'humidity' => round($humidity, 1),
            'ammonia' => round($ammonia, 1),
            'light' => $light
        ];
    }

    $latest = end($history);

    // Simple trend prediction (next 6 hours) using last 6 deltas linear extrapolation
    $temps = array_column($history, 'temperature');
    $humids = array_column($history, 'humidity');
    $ammonias = array_column($history, 'ammonia');
    $lights = array_column($history, 'light');
    $predict = function ($arr) {
        $n = count($arr);
        $recent = array_slice($arr, -6);
        $deltas = [];
        for ($i = 1; $i < count($recent); $i++) $deltas[] = $recent[$i] - $recent[$i-1];
        $avgDelta = count($deltas) ? array_sum($deltas)/count($deltas) : 0;
        $base = end($arr);
        $out = [];
        for ($h = 1; $h <= 6; $h++) $out[] = round($base + $avgDelta*$h, 2);
        return $out;
    };

    // Simple anomaly detection: flag points beyond thresholds
    $anomalies = [];
    foreach ($history as $point) {
        if ($point['temperature'] > 30 || $point['temperature'] < 20) {
            $anomalies[] = [
                'type' => 'temperature',
                'value' => $point['temperature'],
                'time' => $point['time'],
                'message' => 'Suhu di luar rentang optimal (20-30°C)'
            ];
        }
        if ($point['ammonia'] > 25) {
            $anomalies[] = [
                'type' => 'ammonia',
                'value' => $point['ammonia'],
                'time' => $point['time'],
                'message' => 'Kadar amoniak tinggi, cek ventilasi'
            ];
        }
    }

    // Extend prediction to 24h (reuse avg delta, but dampen after 12h)
    $predict24 = function ($arr) {
        $recent = array_slice($arr, -6);
        $deltas = [];
        for ($i=1;$i<count($recent);$i++) $deltas[] = $recent[$i]-$recent[$i-1];
        $avgDelta = count($deltas)? array_sum($deltas)/count($deltas):0;
        $base = end($arr);
        $out = [];
        for ($h=1;$h<=24;$h++) {
            // Dampening factor: reduce impact after 12h
            $factor = $h <= 12 ? 1 : 0.5;
            $out[] = round($base + $avgDelta*$h*$factor,2);
        }
        return $out;
    };

    // Environment status classification helper
    $statusLabel = function($latest) {
        $issues = 0;
        if ($latest['temperature'] < 20 || $latest['temperature'] > 30) $issues++;
        if ($latest['humidity'] < 55 || $latest['humidity'] > 75) $issues++;
        if ($latest['ammonia'] > 25) $issues++;
        if ($latest['light'] < 200 && (int)date('G') >= 8 && (int)date('G') <= 17) $issues++;
        if ($issues === 0) return ['label'=>'baik','severity'=>'normal','message'=>'Semua parameter dalam batas aman'];
        if ($issues === 1) return ['label'=>'perlu perhatian ringan','severity'=>'warning','message'=>'Ada 1 parameter perlu ditinjau'];
        if ($issues === 2) return ['label'=>'kurang stabil','severity'=>'warning','message'=>'Beberapa parameter di luar kisaran ideal'];
        return ['label'=>'tidak optimal','severity'=>'critical','message'=>'Banyak parameter bermasalah, lakukan pemeriksaan'];
    };
    $currentStatus = $statusLabel($latest);

    // Forecast qualitative (next 6h & 24h) based on predicted temperature & ammonia trends
    $pred6 = [
        'temperature' => $predict($temps),
        'humidity' => $predict($humids),
        'ammonia' => $predict($ammonias),
        'light' => $predict($lights)
    ];
    $pred24 = [
        'temperature' => $predict24($temps),
        'humidity' => $predict24($humids),
        'ammonia' => $predict24($ammonias),
        'light' => $predict24($lights)
    ];

    $qualitativeForecast = function($series, $metric, $unit, $safeLow, $safeHigh) {
        $min = min($series); $max = max($series); $trend = $series[array_key_last($series)] - $series[0];
        $dir = $trend > 0.5 ? 'meningkat' : ($trend < -0.5 ? 'menurun' : 'stabil');
        $risk = ($min < $safeLow || $max > $safeHigh) ? 'potensi keluar batas aman' : 'dalam kisaran aman';
        return [
            'metric'=>$metric,
            'summary'=>"$metric $dir ($min–$max $unit) $risk",
            'range'=>['min'=>$min,'max'=>$max,'unit'=>$unit],
            'trend'=>$dir,
            'risk'=>$risk
        ];
    };

    $forecast6Summary = [
        $qualitativeForecast($pred6['temperature'],'Suhu','°C',20,30),
        $qualitativeForecast($pred6['humidity'],'Kelembaban','%',55,75),
        $qualitativeForecast($pred6['ammonia'],'Amoniak','ppm',0,25),
        $qualitativeForecast($pred6['light'],'Cahaya','lux',200,900)
    ];
    $forecast24Summary = [
        $qualitativeForecast($pred24['temperature'],'Suhu','°C',20,30),
        $qualitativeForecast($pred24['humidity'],'Kelembaban','%',55,75),
        $qualitativeForecast($pred24['ammonia'],'Amoniak','ppm',0,25),
        $qualitativeForecast($pred24['light'],'Cahaya','lux',200,900)
    ];

    return response()->json([
        'meta' => [
            'generated_at' => $now->toDateTimeString(),
            'interval' => 'hourly',
            'history_hours' => count($history)
        ],
        'latest' => $latest,
        'history' => $history,
        'prediction_6h' => $pred6,
        'prediction_24h' => $pred24,
        'status' => $currentStatus,
        'forecast_summary_6h' => $forecast6Summary,
        'forecast_summary_24h' => $forecast24Summary,
        'anomalies' => $anomalies
    ]);
});
