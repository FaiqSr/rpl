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
    $query = Product::with('images');
    
    // Filter by search
    if (request('search')) {
        $search = request('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%')
              ->orWhere('slug', 'like', '%' . $search . '%');
        });
    }
    
    // Filter by category
    if (request('category')) {
        $category = request('category');
        
        // Check if it's a homepage category slug
        $homepageCategory = \App\Models\HomepageCategory::where('slug', $category)->where('is_active', true)->first();
        
        if ($homepageCategory) {
            // Use homepage category name to filter products with keyword mapping
            $categoryName = strtolower($homepageCategory->name);
            $categorySlug = strtolower($homepageCategory->slug);
            
            // Map category names to product keywords
            $categoryKeywords = [
                'jeroan' => ['jeroan', 'ati', 'ampela', 'hati', 'usus', 'paru', 'limpa', 'ginjal'],
                'daging segar' => ['daging', 'ayam segar', 'ayam utuh', 'ayam potong'],
                'daging' => ['daging', 'ayam segar', 'ayam utuh', 'ayam potong'],
                'ayam karkas' => ['karkas', 'ayam karkas', 'carcass'],
                'dada' => ['dada', 'breast', 'fillet'],
                'paha' => ['paha', 'thigh', 'drumstick'],
                'sayap' => ['sayap', 'wing'],
            ];
            
            // Get keywords for this category
            $keywords = $categoryKeywords[$categorySlug] ?? $categoryKeywords[$categoryName] ?? [$categoryName, $categorySlug];
            
            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('slug', 'like', '%' . $keyword . '%')
                      ->orWhere('name', 'like', '%' . $keyword . '%');
                }
            });
        } else {
            // Fallback to old category map for backward compatibility
            $categoryMap = [
                'daging-ayam-segar' => ['daging', 'ayam-segar', 'ayam-utuh'],
                'olahan' => ['olahan', 'sosis', 'nugget', 'bakso'],
                'alat-alat' => ['alat', 'peralatan'],
                'pakan' => ['pakan', 'makanan-ayam'],
                'obat-vitamin' => ['obat', 'vitamin', 'suplemen'],
                'peralatan-kandang' => ['kandang', 'peralatan-kandang']
            ];
            
            if (isset($categoryMap[$category])) {
                $query->where(function($q) use ($categoryMap, $category) {
                    foreach ($categoryMap[$category] as $pattern) {
                        $q->orWhere('slug', 'like', '%' . $pattern . '%')
                          ->orWhere('name', 'like', '%' . $pattern . '%');
                    }
                });
            }
        }
    }
    
    $products = $query->orderByDesc('created_at')->paginate(24);
    
    // Get banners and group by banner_type
    $allBanners = \App\Models\HomepageBanner::where('is_active', true)
        ->orderBy('banner_type')
        ->orderBy('sort_order')
        ->orderBy('created_at')
        ->get();
    
    // Group banners by banner_type (default to 'square' if banner_type is null)
    $banners = [
        'square' => $allBanners->filter(function($banner) {
            return ($banner->banner_type ?? 'square') === 'square';
        })->values(),
        'rectangle_top' => $allBanners->filter(function($banner) {
            return ($banner->banner_type ?? 'square') === 'rectangle_top';
        })->values(),
        'rectangle_bottom' => $allBanners->filter(function($banner) {
            return ($banner->banner_type ?? 'square') === 'rectangle_bottom';
        })->values(),
    ];
    
    $homepageCategories = \App\Models\HomepageCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    $articleCategories = \App\Models\ArticleCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    return view('store.home', compact('products', 'banners', 'homepageCategories', 'articleCategories'));
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
    try {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        // Check if user exists
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'Kredensial tidak valid'])->with('error', 'Login gagal');
        }
        
        // Verify password manually (because User model uses user_id as primary key, not id)
        if (!\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Kredensial tidak valid'])->with('error', 'Login gagal');
        }
        
        // Regenerate session ID before login to prevent session fixation
        $request->session()->regenerate();
        
        // Login user manually
        \Illuminate\Support\Facades\Auth::login($user, $request->filled('remember'));
        
        // Ensure session is saved
        $request->session()->save();
        
        return $user->role === 'admin' || $user->role === 'seller'
            ? redirect()->route('dashboard')->with('success', 'Login admin berhasil')
            : redirect()->route('home')->with('success', 'Login berhasil');
    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Login error: ' . $e->getMessage());
        return back()->withErrors(['email' => 'Terjadi kesalahan saat login. Silakan coba lagi.'])->with('error', 'Login gagal');
    }
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
        // IMPORTANT: Use logged-in user's name, not from form (to prevent user impersonation)
        $orderData = [
            'user_id' => $user->user_id,
            'total_price' => $validated['total_price'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'buyer_name' => $user->name ?? $validated['buyer_name'], // Use logged-in user's name
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
    Route::get('/dashboard/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('dashboard.customers');
    
    // Article Categories Management
    Route::get('/dashboard/article-categories', function () {
        $categories = \App\Models\ArticleCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('dashboard.article-categories', compact('categories'));
    })->name('dashboard.article-categories');
    
    Route::get('/dashboard/article-categories/{id}', function ($id) {
        $category = \App\Models\ArticleCategory::findOrFail($id);
        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    });
    
    Route::post('/dashboard/article-categories', function (\Illuminate\Http\Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'slug' => 'nullable|string|max:120|unique:article_categories,slug',
                'description' => 'nullable|string',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);
            
            if (empty($validated['slug'])) {
                $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
            }
            
            $category = \App\Models\ArticleCategory::create([
                'category_id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $validated['is_active'] ?? true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dibuat',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kategori: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::put('/dashboard/article-categories/{id}', function (\Illuminate\Http\Request $request, $id) {
        try {
            $category = \App\Models\ArticleCategory::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'slug' => 'nullable|string|max:120|unique:article_categories,slug,' . $id . ',category_id',
                'description' => 'nullable|string',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);
            
            if (empty($validated['slug'])) {
                $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
            }
            
            $category->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::delete('/dashboard/article-categories/{id}', function ($id) {
        try {
            $category = \App\Models\ArticleCategory::findOrFail($id);
            
            // Check if category has articles
            if ($category->articles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena masih memiliki artikel'
                ], 400);
            }
            
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Homepage Management
    Route::get('/dashboard/homepage', function () {
        $banners = \App\Models\HomepageBanner::orderBy('banner_type')->orderBy('sort_order')->orderBy('created_at')->get();
        $categories = \App\Models\HomepageCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('dashboard.homepage', compact('banners', 'categories'));
    })->name('dashboard.homepage');
    
    // Banner Routes
    Route::get('/dashboard/homepage/banners/{id}', function ($id) {
        $banner = \App\Models\HomepageBanner::findOrFail($id);
        return response()->json([
            'success' => true,
            'banner' => $banner
        ]);
    });
    
    Route::post('/dashboard/homepage/banners', function (\Illuminate\Http\Request $request) {
        try {
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'banner_type' => 'required|string|in:square,rectangle_top,rectangle_bottom',
                'image_url' => 'nullable|string',
                'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'link_url' => 'nullable|string|max:500',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);
            
            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $destinationPath = public_path('storage/homepage-banners');
                
                // Ensure directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                // For JPEG, optimize quality
                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $image = imagecreatefromjpeg($file->getRealPath());
                    if ($image) {
                        imagejpeg($image, $destinationPath . '/' . $filename, 95); // 95% quality
                        imagedestroy($image);
                    } else {
                        $file->move($destinationPath, $filename);
                    }
                } elseif ($extension === 'png') {
                    $image = imagecreatefrompng($file->getRealPath());
                    if ($image) {
                        imagealphablending($image, false);
                        imagesavealpha($image, true);
                        imagepng($image, $destinationPath . '/' . $filename, 9); // 9 = highest compression but lossless
                        imagedestroy($image);
                    } else {
                        $file->move($destinationPath, $filename);
                    }
                } else {
                    $file->move($destinationPath, $filename);
                }
                
                $imageUrl = asset('storage/homepage-banners/' . $filename);
            } elseif ($request->filled('image_url')) {
                $imageUrl = $request->input('image_url');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gambar banner harus diisi (URL atau upload file)'
                ], 422);
            }
            
            $banner = \App\Models\HomepageBanner::create([
                'banner_id' => (string) \Illuminate\Support\Str::uuid(),
                'title' => $validated['title'] ?? null,
                'banner_type' => $validated['banner_type'],
                'image_url' => $imageUrl,
                'link_url' => $validated['link_url'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $validated['is_active'] ?? true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil dibuat',
                'banner' => $banner
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat banner: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::put('/dashboard/homepage/banners/{id}', function (\Illuminate\Http\Request $request, $id) {
        try {
            $banner = \App\Models\HomepageBanner::where('banner_id', $id)->firstOrFail();
            
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'banner_type' => 'required|string|in:square,rectangle_top,rectangle_bottom',
                'image_url' => 'nullable|string',
                'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'link_url' => 'nullable|string|max:500',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);
            
            // Handle image upload
            if ($request->hasFile('image_file')) {
                // Delete old image if exists
                if ($banner->image_url && strpos($banner->image_url, asset('storage/homepage-banners')) === 0) {
                    $oldFile = str_replace(asset('storage/homepage-banners'), public_path('storage/homepage-banners'), $banner->image_url);
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                
                $file = $request->file('image_file');
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $destinationPath = public_path('storage/homepage-banners');
                
                // Ensure directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                // For JPEG, optimize quality
                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $image = imagecreatefromjpeg($file->getRealPath());
                    if ($image) {
                        imagejpeg($image, $destinationPath . '/' . $filename, 95); // 95% quality
                        imagedestroy($image);
                    } else {
                        $file->move($destinationPath, $filename);
                    }
                } elseif ($extension === 'png') {
                    $image = imagecreatefrompng($file->getRealPath());
                    if ($image) {
                        imagealphablending($image, false);
                        imagesavealpha($image, true);
                        imagepng($image, $destinationPath . '/' . $filename, 9); // 9 = highest compression but lossless
                        imagedestroy($image);
                    } else {
                        $file->move($destinationPath, $filename);
                    }
                } else {
                    $file->move($destinationPath, $filename);
                }
                
                $validated['image_url'] = asset('storage/homepage-banners/' . $filename);
            } elseif ($request->filled('image_url')) {
                $validated['image_url'] = $request->input('image_url');
            } else {
                // Keep existing image if not provided
                unset($validated['image_url']);
            }
            
            $banner->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil diperbarui',
                'banner' => $banner
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui banner: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::delete('/dashboard/homepage/banners/{id}', function ($id) {
        try {
            $banner = \App\Models\HomepageBanner::where('banner_id', $id)->firstOrFail();
            $banner->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Banner berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus banner: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Category Routes
    Route::get('/dashboard/homepage/categories/{id}', function ($id) {
        try {
            \Log::info('Fetching category with ID: ' . $id);
            $category = \App\Models\HomepageCategory::where('category_id', $id)->first();
            
            if (!$category) {
                \Log::warning('Category not found with ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }
            
            \Log::info('Category found: ' . $category->name);
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching category: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat kategori: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::post('/dashboard/homepage/categories', function (\Illuminate\Http\Request $request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'slug' => 'nullable|string|max:120|unique:homepage_categories,slug',
                'image_url' => 'nullable|string',
                'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);
            
            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/homepage-categories'), $filename);
                $imageUrl = asset('storage/homepage-categories/' . $filename);
            } elseif ($request->filled('image_url')) {
                $imageUrl = $request->input('image_url');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto kategori harus diisi (URL atau upload file)'
                ], 422);
            }
            
            if (empty($validated['slug'])) {
                $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
            }
            
            $category = \App\Models\HomepageCategory::create([
                'category_id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'image_url' => $imageUrl,
                'link_url' => null, // Tidak digunakan lagi, kategori digunakan untuk filter
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $validated['is_active'] ?? true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dibuat',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kategori: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::put('/dashboard/homepage/categories/{id}', function (\Illuminate\Http\Request $request, $id) {
        try {
            $category = \App\Models\HomepageCategory::where('category_id', $id)->firstOrFail();
            
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'slug' => 'nullable|string|max:120|unique:homepage_categories,slug,' . $id . ',category_id',
                'image_url' => 'nullable|string',
                'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);
            
            // Handle image upload
            if ($request->hasFile('image_file')) {
                // Delete old image if exists
                if ($category->image_url && strpos($category->image_url, asset('storage/homepage-categories')) === 0) {
                    $oldFile = str_replace(asset('storage/homepage-categories'), public_path('storage/homepage-categories'), $category->image_url);
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                
                $file = $request->file('image_file');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('storage/homepage-categories'), $filename);
                $validated['image_url'] = asset('storage/homepage-categories/' . $filename);
            } elseif ($request->filled('image_url')) {
                $validated['image_url'] = $request->input('image_url');
            } else {
                // Keep existing image if not provided
                unset($validated['image_url']);
            }
            
            if (empty($validated['slug'])) {
                $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
            }
            
            // Remove link_url from update (not used anymore)
            unset($validated['link_url']);
            $validated['link_url'] = null;
            
            $category->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::delete('/dashboard/homepage/categories/{id}', function ($id) {
        try {
            $category = \App\Models\HomepageCategory::where('category_id', $id)->firstOrFail();
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Articles Management
    Route::get('/dashboard/articles', function () {
        $articles = \App\Models\Article::with(['user', 'category'])->orderByDesc('created_at')->get();
        $categories = \App\Models\ArticleCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        return view('dashboard.articles', compact('articles', 'categories'));
    })->name('dashboard.articles');
    
    Route::get('/dashboard/articles/{id}', function ($id) {
        $article = \App\Models\Article::findOrFail($id);
        return response()->json([
            'success' => true,
            'article' => $article
        ]);
    });
    
    Route::post('/dashboard/articles', function (\Illuminate\Http\Request $request) {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'category_id' => 'required|exists:article_categories,category_id',
                'content' => 'required|string'
            ]);
            
            $article = \App\Models\Article::create([
                'article_id' => (string) \Illuminate\Support\Str::uuid(),
                'author_id' => Auth::user()->user_id,
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'content' => $validated['content']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Artikel berhasil dibuat',
                'article' => $article
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat artikel: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::put('/dashboard/articles/{id}', function (\Illuminate\Http\Request $request, $id) {
        try {
            $article = \App\Models\Article::findOrFail($id);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'category_id' => 'required|exists:article_categories,category_id',
                'content' => 'required|string'
            ]);
            
            $article->update([
                'title' => $validated['title'],
                'category_id' => $validated['category_id'],
                'content' => $validated['content']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Artikel berhasil diperbarui',
                'article' => $article
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui artikel: ' . $e->getMessage()
            ], 500);
        }
    });
    
    Route::delete('/dashboard/articles/{id}', function ($id) {
        try {
            $article = \App\Models\Article::findOrFail($id);
            $article->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Artikel berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus artikel: ' . $e->getMessage()
            ], 500);
        }
    });
    Route::get('/api/customers', [\App\Http\Controllers\CustomerController::class, 'getCustomers'])->name('api.customers');
    Route::get('/api/customers/{id}', [\App\Http\Controllers\CustomerController::class, 'show'])->name('api.customers.show');
    Route::post('/api/customers/{id}/toggle-status', [\App\Http\Controllers\CustomerController::class, 'toggleStatus'])->name('api.customers.toggle-status');
});
    
// Chat API Routes - Accessible by both buyer/visitor and seller/admin
Route::middleware('auth.session')->group(function() {
    Route::prefix('api/chat')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
        Route::get('/unread-count', [\App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
        // Route dengan orderId harus didefinisikan sebelum route tanpa parameter
        // UUID pattern: 8-4-4-4-12 hex characters with dashes
        Route::get('/get-or-create/{orderId}', [\App\Http\Controllers\ChatController::class, 'getOrCreateChat'])
            ->where('orderId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
            ->name('chat.get-or-create.order');
        Route::get('/get-or-create', [\App\Http\Controllers\ChatController::class, 'getOrCreateChat'])->name('chat.get-or-create');
        Route::get('/{chatId}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])
            ->where('chatId', '[a-f0-9\-]{36}')
            ->name('chat.messages');
        Route::post('/{chatId}/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])
            ->where('chatId', '[a-f0-9\-]{36}')
            ->name('chat.send');
        Route::delete('/delete-history/{buyerId}', [\App\Http\Controllers\ChatController::class, 'deleteChatHistory'])
            ->where('buyerId', '[a-f0-9\-]{36}')
            ->name('chat.delete-history');
        
        // Admin routes
        Route::prefix('admin')->group(function () {
            Route::get('/buyers', [\App\Http\Controllers\ChatController::class, 'getBuyerList'])->name('chat.admin.buyers');
            Route::get('/buyer/{buyerId}/messages', [\App\Http\Controllers\ChatController::class, 'getMessagesByBuyer'])
                ->where('buyerId', '[a-f0-9\-]{36}')
                ->name('chat.admin.buyer.messages');
        });
    });
    
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
    $query = \App\Models\Article::with(['user', 'category'])->whereHas('category', function($q) {
        $q->where('is_active', true);
    });
    
    // Filter by category slug
    if (request('category')) {
        $categorySlug = request('category');
        $query->whereHas('category', function($q) use ($categorySlug) {
            $q->where('slug', $categorySlug)->where('is_active', true);
        });
    }
    
    $articles = $query->orderByDesc('created_at')->paginate(12);
    $categories = \App\Models\ArticleCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    return view('articles', compact('articles', 'categories'));
})->name('articles');

// Article Detail Page
Route::get('/articles/{id}', function ($id) {
    $article = \App\Models\Article::with(['user', 'category'])->findOrFail($id);
    return view('article-detail', compact('article'));
})->name('article.detail');

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
            'payment_method' => 'required|string|in:QRIS,Transfer Bank',
            'selected_cart_ids' => 'required|array',
            'selected_cart_ids.*' => 'required|string|exists:carts,cart_id'
        ]);
        
        $user = Auth::user();
        
        // Get only selected cart items
        $cartItems = \App\Models\Cart::with(['product'])
            ->where('user_id', $user->user_id)
            ->whereIn('cart_id', $validated['selected_cart_ids'])
            ->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Tidak ada produk yang dipilih'], 400);
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
        
        // Clear only selected cart items
        \App\Models\Cart::where('user_id', $user->user_id)
            ->whereIn('cart_id', $validated['selected_cart_ids'])
            ->delete();
        
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
    // Set headers to prevent caching
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    try {
        $mlService = new \App\Services\MachineLearningService();
        
        // Get threshold from database (ALWAYS FRESH - no cache)
        // Prioritas: 1) Profile dari query parameter, 2) Default profile
        $profileKey = request()->query('profile', 'default');
        
        // Force fresh query - clear any model cache
        \App\Models\ThresholdProfile::clearBootedModels();
        \App\Models\ThresholdValue::clearBootedModels();
        
        $thresholdProfile = \App\Models\ThresholdProfile::where('profile_key', $profileKey)
            ->with('thresholdValues')
            ->first();
        
        // Jika profile tidak ditemukan, gunakan default
        if (!$thresholdProfile) {
            $thresholdProfile = \App\Models\ThresholdProfile::where('profile_key', 'default')
                ->with('thresholdValues')
                ->first();
        }
        
        $thresholds = [];
        if ($thresholdProfile) {
            foreach ($thresholdProfile->thresholdValues as $value) {
                if ($value->sensor_type === 'amonia_ppm') {
                    $thresholds['ammonia'] = [
                        'ideal_max' => (float) $value->ideal_max,
                        'warn_max' => (float) $value->warn_max,
                        'danger_max' => (float) $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'suhu_c') {
                    $thresholds['temperature'] = [
                        'ideal_min' => (float) $value->ideal_min,
                        'ideal_max' => (float) $value->ideal_max,
                        'danger_low' => (float) $value->danger_min,
                        'danger_high' => (float) $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'kelembaban_rh') {
                    $thresholds['humidity'] = [
                        'ideal_min' => (float) $value->ideal_min,
                        'ideal_max' => (float) $value->ideal_max,
                        'warn_low' => (float) $value->ideal_min, // Use ideal_min as warn_low
                        'warn_high' => (float) $value->warn_max,
                        'danger_high' => (float) $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'cahaya_lux') {
                    $thresholds['light'] = [
                        'ideal_low' => (float) $value->ideal_min,
                        'ideal_high' => (float) $value->ideal_max,
                        'warn_low' => (float) $value->warn_min,
                        'warn_high' => (float) $value->warn_max,
                    ];
                }
            }
        }
        
        // Fallback to default thresholds if database is empty
        if (empty($thresholds)) {
            $thresholds = [
                'temperature' => ['ideal_min' => 23, 'ideal_max' => 34, 'danger_low' => 20, 'danger_high' => 37],
                'humidity' => ['ideal_min' => 50, 'ideal_max' => 70, 'warn_low' => 50, 'warn_high' => 80, 'danger_high' => 80],
                'ammonia' => ['ideal_max' => 20, 'warn_max' => 35, 'danger_max' => 35],
                'light' => ['ideal_low' => 20, 'ideal_high' => 40, 'warn_low' => 10, 'warn_high' => 60],
            ];
        }
    
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
    $latestSensor = $latest; // For threshold validation

    // Get ML predictions dengan error handling
    try {
        $mlResults = $mlService->getPredictions($history);
        $pred6 = $mlResults['prediction_6h'] ?? ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
        $pred24 = $mlResults['prediction_24h'] ?? ['temperature' => [], 'humidity' => [], 'ammonia' => [], 'light' => []];
        $anomalies = $mlResults['anomalies'] ?? [];
        $mlStatus = $mlResults['status'] ?? ['label' => 'tidak diketahui', 'severity' => 'warning', 'message' => 'Status tidak dapat ditentukan'];
        $mlMetadata = $mlResults['ml_metadata'] ?? [];
        
        // ============================================
        // PROBABILITY ADJUSTMENT BASED ON NEW THRESHOLDS
        // ============================================
        
        // Helper function: Calculate threshold score berdasarkan threshold baru dari database
        $calculateThresholdScore = function($sensorData, $thresholds) {
            $issues = 0;
            $criticalIssues = 0;
            $warnings = 0;
            
            // Validasi setiap sensor dengan threshold BARU
            // Suhu
            $temp = $sensorData['temperature'] ?? $sensorData['suhu_c'] ?? 0;
            $suhuTh = $thresholds['temperature'] ?? null;
            if ($suhuTh) {
                if ($temp < ($suhuTh['danger_low'] ?? 20) || $temp > ($suhuTh['danger_high'] ?? 37)) {
                    $criticalIssues++;
                } elseif ($temp < ($suhuTh['ideal_min'] ?? 23) || $temp > ($suhuTh['ideal_max'] ?? 34)) {
                    $warnings++;
                }
            }
            
            // Kelembaban
            $humidity = $sensorData['humidity'] ?? $sensorData['kelembaban_rh'] ?? 0;
            $kelembabanTh = $thresholds['humidity'] ?? null;
            if ($kelembabanTh) {
                if ($humidity > ($kelembabanTh['danger_high'] ?? 80)) {
                    $criticalIssues++;
                } elseif ($humidity < ($kelembabanTh['ideal_min'] ?? 50) || $humidity > ($kelembabanTh['warn_high'] ?? 80)) {
                    $warnings++;
                }
            }
            
            // Amonia
            $amonia = $sensorData['ammonia'] ?? $sensorData['amonia_ppm'] ?? 0;
            $amoniaTh = $thresholds['ammonia'] ?? null;
            if ($amoniaTh) {
                if ($amonia > ($amoniaTh['danger_max'] ?? 35)) {
                    $criticalIssues++;
                } elseif ($amonia >= ($amoniaTh['warn_max'] ?? 35)) {
                    $warnings++;
                }
            }
            
            // Cahaya
            $cahaya = $sensorData['light'] ?? $sensorData['cahaya_lux'] ?? 0;
            $cahayaTh = $thresholds['light'] ?? null;
            if ($cahayaTh) {
                if ($cahaya < ($cahayaTh['warn_low'] ?? 10) || $cahaya > ($cahayaTh['warn_high'] ?? 60)) {
                    $criticalIssues++;
                } elseif ($cahaya < ($cahayaTh['ideal_low'] ?? 20) || $cahaya > ($cahayaTh['ideal_high'] ?? 40)) {
                    $warnings++;
                }
            }
            
            // Hitung probability berdasarkan threshold validation
            $thresholdProb = ['BAIK' => 0, 'PERHATIAN' => 0, 'BURUK' => 0];
            
            if ($criticalIssues >= 3) {
                $thresholdProb['BURUK'] = 0.9;
                $thresholdProb['PERHATIAN'] = 0.1;
                $thresholdProb['BAIK'] = 0.0;
            } elseif ($criticalIssues >= 2) {
                $thresholdProb['BURUK'] = 0.7;
                $thresholdProb['PERHATIAN'] = 0.3;
                $thresholdProb['BAIK'] = 0.0;
            } elseif ($criticalIssues >= 1 || $warnings >= 2) {
                $thresholdProb['BURUK'] = 0.3;
                $thresholdProb['PERHATIAN'] = 0.6;
                $thresholdProb['BAIK'] = 0.1;
            } elseif ($warnings >= 1) {
                $thresholdProb['PERHATIAN'] = 0.8;
                $thresholdProb['BAIK'] = 0.2;
                $thresholdProb['BURUK'] = 0.0;
            } else {
                // Semua sensor dalam range ideal
                $thresholdProb['BAIK'] = 0.95;
                $thresholdProb['PERHATIAN'] = 0.05;
                $thresholdProb['BURUK'] = 0.0;
            }
            
            return $thresholdProb;
        };
        
        // Helper function: Adjust probabilities berdasarkan threshold baru
        $adjustProbabilitiesBasedOnThreshold = function($mlProbabilities, $thresholdScore, $sensorData, $thresholds) {
            // Base probabilities dari ML model
            $baseProb = [
                'BAIK' => (float)($mlProbabilities['BAIK'] ?? 0),
                'PERHATIAN' => (float)($mlProbabilities['PERHATIAN'] ?? 0),
                'BURUK' => (float)($mlProbabilities['BURUK'] ?? 0)
            ];
            
            // Combine ML probability dengan threshold score (weighted)
            $mlWeight = 0.6;  // 60% dari ML
            $thresholdWeight = 0.4;  // 40% dari threshold validation
            
            $adjustedProb = [
                'BAIK' => ($baseProb['BAIK'] * $mlWeight) + ($thresholdScore['BAIK'] * $thresholdWeight),
                'PERHATIAN' => ($baseProb['PERHATIAN'] * $mlWeight) + ($thresholdScore['PERHATIAN'] * $thresholdWeight),
                'BURUK' => ($baseProb['BURUK'] * $mlWeight) + ($thresholdScore['BURUK'] * $thresholdWeight)
            ];
            
            // Normalize (pastikan total = 1.0)
            $total = array_sum($adjustedProb);
            if ($total > 0) {
                foreach ($adjustedProb as $key => $value) {
                    $adjustedProb[$key] = $value / $total;
                }
            }
            
            return $adjustedProb;
        };
        
        // Get ML probabilities (original dari model)
        $mlProbabilities = $mlStatus['probability'] ?? [
            'BAIK' => 0.0,
            'PERHATIAN' => 0.0,
            'BURUK' => 0.0
        ];
        
        // Calculate threshold score berdasarkan threshold BARU dari database
        // Jika thresholds kosong, gunakan default threshold score (semua BAIK)
        if (empty($thresholds)) {
            $thresholdScore = ['BAIK' => 0.95, 'PERHATIAN' => 0.05, 'BURUK' => 0.0];
        } else {
            $thresholdScore = $calculateThresholdScore($latestSensor, $thresholds);
        }
        
        // Adjust probability (combine ML + Threshold)
        // Jika thresholds kosong, gunakan ML probabilities saja (weight 100% ML)
        if (empty($thresholds)) {
            $adjustedProbabilities = $mlProbabilities;
        } else {
            $adjustedProbabilities = $adjustProbabilitiesBasedOnThreshold(
                $mlProbabilities,
                $thresholdScore,
                $latestSensor,
                $thresholds
            );
        }
        
        // Determine final status dari adjusted probability
        $finalStatusFromAdjustedProb = array_search(max($adjustedProbabilities), $adjustedProbabilities);
        $finalConfidenceFromAdjustedProb = max($adjustedProbabilities);
        
        // Log untuk debugging
        \Illuminate\Support\Facades\Log::info('=== PROBABILITY ADJUSTMENT ===', [
            'ml_probabilities_original' => $mlProbabilities,
            'threshold_score' => $thresholdScore,
            'adjusted_probabilities' => $adjustedProbabilities,
            'final_status_from_adjusted' => $finalStatusFromAdjustedProb,
            'final_confidence_from_adjusted' => $finalConfidenceFromAdjustedProb
        ]);
        
        // Update ML status dengan adjusted probabilities
        $mlStatus['probability'] = $adjustedProbabilities;
        $mlStatus['ml_probabilities_original'] = $mlProbabilities; // Simpan original untuk reference
        $mlStatus['threshold_score'] = $thresholdScore; // Simpan threshold score untuk debugging
        
        // ============================================
        // THRESHOLD VALIDATION & HYBRID DECISION
        // ============================================
        $currentStatus = $mlStatus; // Default: use ML status (dengan adjusted probabilities)
        
        // Helper functions untuk hybrid decision
        $calculateAgreement = function($mlStatus, $thresholdStatus, $mlConfidence) {
            $statusValue = ['BAIK' => 0, 'PERHATIAN' => 1, 'BURUK' => 2];
            $mlLabel = strtoupper($mlStatus['label'] ?? 'BAIK');
            $thresholdLabel = strtoupper($thresholdStatus);
            
            $mlValue = $statusValue[$mlLabel] ?? 1;
            $thresholdValue = $statusValue[$thresholdLabel] ?? 1;
            
            if ($mlValue == $thresholdValue) {
                return 1.0;
            }
            
            if (abs($mlValue - $thresholdValue) == 1) {
                if ($thresholdValue > $mlValue) {
                    return 0.3; // Threshold lebih kritis, agreement rendah
                } else {
                    return 0.6; // ML lebih kritis, agreement medium
                }
            }
            
            return 0.1; // Agreement sangat rendah
        };
        
        $determineFinalStatus = function($mlStatus, $thresholdStatus, $agreementScore, $criticalIssues, $thresholdIssues) {
            $mlLabel = strtoupper($mlStatus['label'] ?? 'BAIK');
            $thresholdLabel = strtoupper($thresholdStatus);
            $statusValue = ['BAIK' => 0, 'PERHATIAN' => 1, 'BURUK' => 2];
            
            // ✅ LOG INPUT
            \Illuminate\Support\Facades\Log::info('=== FINAL STATUS DETERMINATION ===', [
                'ml_status' => $mlLabel,
                'threshold_status' => $thresholdLabel,
                'agreement_score' => $agreementScore,
                'critical_issues' => $criticalIssues,
                'threshold_issues' => $thresholdIssues
            ]);
            
            // PRIORITAS 1: Jika threshold validation BAIK dan 0 issues → HARUS BAIK
            if ($thresholdLabel == 'BAIK' && $thresholdIssues == 0 && $criticalIssues == 0) {
                \Illuminate\Support\Facades\Log::info('→ Decision: BAIK (hard override - semua sensor aman)');
                return 'BAIK';
            }
            
            // PRIORITAS 2: Jika 3+ critical issues → HARUS BURUK (safety first)
            if ($criticalIssues >= 3) {
                \Illuminate\Support\Facades\Log::info('→ Decision: BURUK (hard override - 3+ critical issues)');
                return 'BURUK';
            }
            
            // PRIORITAS 3: Jika 2 critical issues → BURUK
            if ($criticalIssues >= 2) {
                \Illuminate\Support\Facades\Log::info('→ Decision: BURUK (2 critical issues)');
                return 'BURUK';
            }
            
            // PRIORITAS 4: Jika threshold BAIK dan ML juga BAIK → BAIK
            if ($thresholdLabel == 'BAIK' && $mlLabel == 'BAIK') {
                \Illuminate\Support\Facades\Log::info('→ Decision: BAIK (agreement)');
                return 'BAIK';
            }
            
            // PRIORITAS 5: Agreement tinggi → gunakan ML
            if ($agreementScore >= 0.8) {
                \Illuminate\Support\Facades\Log::info('→ Decision: ' . $mlLabel . ' (high agreement)');
                return $mlLabel;
            }
            
            // PRIORITAS 6: Ambil yang lebih kritis
            $finalStatus = ($statusValue[$thresholdLabel] ?? 1) > ($statusValue[$mlLabel] ?? 1)
                ? $thresholdLabel
                : $mlLabel;
            
            \Illuminate\Support\Facades\Log::info('→ Decision: ' . $finalStatus . ' (weighted - more critical)');
            return $finalStatus;
        };
        
        $calculateFinalConfidence = function($mlConfidence, $thresholdStatus, $mlStatus, $agreementScore, $criticalThresholdIssues, $finalStatus) {
            $baseConfidence = $mlConfidence;
            $mlLabel = strtoupper($mlStatus['label'] ?? 'BAIK');
            $thresholdLabel = strtoupper($thresholdStatus);
            $statusValue = ['BAIK' => 0, 'PERHATIAN' => 1, 'BURUK' => 2];
            
            // CRITICAL RULE: Jika ada 3+ critical issues
            if ($criticalThresholdIssues >= 3) {
                if ($finalStatus == 'BURUK') {
                    return min(0.85, $baseConfidence + 0.2); // Boost confidence
                } else {
                    return max(0.3, $baseConfidence - 0.4); // Large penalty
                }
            }
            
            // Agreement bonus
            if ($mlLabel == $thresholdLabel) {
                $baseConfidence = min($baseConfidence + 0.05, 1.0);
            }
            
            // Disagreement penalty
            if ($mlLabel != $thresholdLabel) {
                $penalty = 0.0;
                
                if (($statusValue[$thresholdLabel] ?? 1) > ($statusValue[$mlLabel] ?? 1)) {
                    $penalty = 0.40; // Large penalty
                } else {
                    $penalty = 0.20; // Small penalty
                }
                
                $baseConfidence = max($baseConfidence - $penalty, 0.1);
            }
            
            // Critical issues boost
            if ($criticalThresholdIssues >= 2) {
                $baseConfidence = max($baseConfidence, 0.6);
            }
            
            return round($baseConfidence, 2);
        };
        
        // Apply threshold validation jika ada ML status dan threshold
        if ($mlStatus && $latestSensor && !empty($thresholds)) {
            // ============================================
            // STEP 1: Log threshold yang digunakan
            // ============================================
            \Illuminate\Support\Facades\Log::info('=== THRESHOLD YANG DIGUNAKAN ===', [
                'profile' => $profileKey,
                'thresholds' => $thresholds
            ]);
            
            // ============================================
            // STEP 2: Validasi threshold (MENGIKUTI LOGIKA FRONTEND)
            // ============================================
            $temp = (float) ($latestSensor['temperature'] ?? 0);
            $humid = (float) ($latestSensor['humidity'] ?? 0);
            $ammonia = (float) ($latestSensor['ammonia'] ?? 0);
            $light = (float) ($latestSensor['light'] ?? 0);
            
            // Ambil threshold values
            $tempIdealMin = (float) ($thresholds['temperature']['ideal_min'] ?? 23);
            $tempIdealMax = (float) ($thresholds['temperature']['ideal_max'] ?? 34);
            $tempDangerLow = (float) ($thresholds['temperature']['danger_low'] ?? 20);
            $tempDangerHigh = (float) ($thresholds['temperature']['danger_high'] ?? 37);
            
            $humidIdealMin = (float) ($thresholds['humidity']['ideal_min'] ?? 50);
            $humidIdealMax = (float) ($thresholds['humidity']['ideal_max'] ?? 70);
            $humidWarnLow = (float) ($thresholds['humidity']['warn_low'] ?? 50);
            $humidWarnHigh = (float) ($thresholds['humidity']['warn_high'] ?? 80);
            $humidDangerHigh = (float) ($thresholds['humidity']['danger_high'] ?? 80);
            
            $ammoniaIdealMax = (float) ($thresholds['ammonia']['ideal_max'] ?? 20);
            $ammoniaWarnMax = (float) ($thresholds['ammonia']['warn_max'] ?? 35);
            $ammoniaDangerMax = (float) ($thresholds['ammonia']['danger_max'] ?? 35);
            
            $lightIdealLow = (float) ($thresholds['light']['ideal_low'] ?? 20);
            $lightIdealHigh = (float) ($thresholds['light']['ideal_high'] ?? 40);
            $lightWarnLow = (float) ($thresholds['light']['warn_low'] ?? 10);
            $lightWarnHigh = (float) ($thresholds['light']['warn_high'] ?? 60);
            
            // Validasi threshold (LOGIKA SAMA DENGAN FRONTEND)
            $thresholdBasedLabel = 'baik';
            $thresholdIssues = 0;
            $criticalThresholdIssues = 0;
            $warningThresholdIssues = 0;
            $sensorIssues = [];
            
            // Suhu
            if ($temp >= $tempIdealMin && $temp <= $tempIdealMax) {
                // AMAN - tidak ada issue
            } elseif ($temp < $tempDangerLow || $temp > $tempDangerHigh) {
                $thresholdIssues++;
                $criticalThresholdIssues++;
                $thresholdBasedLabel = 'buruk';
                $sensorIssues[] = ['sensor' => 'Suhu', 'value' => $temp, 'status' => 'critical'];
            } else {
                $thresholdIssues++;
                $warningThresholdIssues++;
                if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
                $sensorIssues[] = ['sensor' => 'Suhu', 'value' => $temp, 'status' => 'warning'];
            }
            
            // Kelembaban
            if ($humid >= $humidIdealMin && $humid <= $humidIdealMax) {
                // AMAN - tidak ada issue
            } elseif ($humid > $humidDangerHigh) {
                $thresholdIssues++;
                $criticalThresholdIssues++;
                $thresholdBasedLabel = 'buruk';
                $sensorIssues[] = ['sensor' => 'Kelembaban', 'value' => $humid, 'status' => 'critical'];
            } elseif ($humid < $humidWarnLow || ($humid > $humidIdealMax && $humid <= $humidWarnHigh)) {
                $thresholdIssues++;
                $warningThresholdIssues++;
                if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
                $sensorIssues[] = ['sensor' => 'Kelembaban', 'value' => $humid, 'status' => 'warning'];
            }
            
            // Amoniak
            if ($ammonia <= $ammoniaIdealMax) {
                // AMAN - tidak ada issue
            } elseif ($ammonia > $ammoniaDangerMax) {
                $thresholdIssues++;
                $criticalThresholdIssues++;
                $thresholdBasedLabel = 'buruk';
                $sensorIssues[] = ['sensor' => 'Amoniak', 'value' => $ammonia, 'status' => 'critical'];
            } elseif ($ammonia > $ammoniaWarnMax) {
                $thresholdIssues++;
                $warningThresholdIssues++;
                if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
                $sensorIssues[] = ['sensor' => 'Amoniak', 'value' => $ammonia, 'status' => 'warning'];
            }
            
            // Cahaya (konversi dari ratusan ke puluhan)
            $lightForCheck = $light / 10;
            if ($lightForCheck >= $lightIdealLow && $lightForCheck <= $lightIdealHigh) {
                // AMAN - tidak ada issue
            } elseif ($lightForCheck < $lightWarnLow || $lightForCheck > $lightWarnHigh) {
                $thresholdIssues++;
                $criticalThresholdIssues++;
                $thresholdBasedLabel = 'buruk';
                $sensorIssues[] = ['sensor' => 'Cahaya', 'value' => $lightForCheck, 'status' => 'critical'];
            } else {
                $thresholdIssues++;
                $warningThresholdIssues++;
                if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
                $sensorIssues[] = ['sensor' => 'Cahaya', 'value' => $lightForCheck, 'status' => 'warning'];
            }
            
            // Jika ada 3+ sensor di luar batas, pastikan status adalah BURUK
            if ($thresholdIssues >= 3) {
                $thresholdBasedLabel = 'buruk';
            }
            
            // Normalisasi label
            $mlLabel = strtolower($mlStatus['label'] ?? 'baik');
            $thresholdLabel = strtolower($thresholdBasedLabel);
            
            // Log detail threshold validation
            \Illuminate\Support\Facades\Log::info('=== THRESHOLD VALIDATION RESULT ===', [
                'sensor_values' => [
                    'temperature' => $temp,
                    'humidity' => $humid,
                    'ammonia' => $ammonia,
                    'light' => $lightForCheck
                ],
                'thresholds_used' => [
                    'temperature' => ['ideal_min' => $tempIdealMin, 'ideal_max' => $tempIdealMax, 'danger_low' => $tempDangerLow, 'danger_high' => $tempDangerHigh],
                    'humidity' => ['ideal_min' => $humidIdealMin, 'ideal_max' => $humidIdealMax, 'danger_high' => $humidDangerHigh],
                    'ammonia' => ['ideal_max' => $ammoniaIdealMax, 'danger_max' => $ammoniaDangerMax],
                    'light' => ['ideal_low' => $lightIdealLow, 'ideal_high' => $lightIdealHigh, 'warn_low' => $lightWarnLow, 'warn_high' => $lightWarnHigh]
                ],
                'sensor_issues' => $sensorIssues,
                'total_issues' => $thresholdIssues,
                'critical_issues' => $criticalThresholdIssues,
                'warning_issues' => $warningThresholdIssues,
                'threshold_based_label' => $thresholdLabel,
                'ml_label' => $mlLabel
            ]);
            
            // Step 3: Calculate Agreement Score
            $agreementScore = $calculateAgreement($mlStatus, $thresholdLabel, (float)($mlStatus['confidence'] ?? 0.7));
            
            // Step 4: Determine Final Status
            // PRIORITAS: Gunakan adjusted probabilities sebagai primary source
            // Tapi tetap lakukan safety checks dengan threshold validation
            $adjustedStatusLabel = strtoupper($finalStatusFromAdjustedProb ?? 'BAIK');
            
            // Safety override: Jika ada 3+ critical issues, HARUS BURUK
            if ($criticalThresholdIssues >= 3) {
                $finalStatusLabel = 'BURUK';
                \Illuminate\Support\Facades\Log::info('→ Decision: BURUK (safety override - 3+ critical issues)');
            } 
            // Safety override: Jika semua sensor aman (0 issues), HARUS BAIK
            elseif ($thresholdIssues == 0 && $criticalThresholdIssues == 0) {
                $finalStatusLabel = 'BAIK';
                \Illuminate\Support\Facades\Log::info('→ Decision: BAIK (safety override - semua sensor aman)');
            }
            // Gunakan adjusted probability sebagai primary decision
            else {
                $finalStatusLabel = $adjustedStatusLabel;
                \Illuminate\Support\Facades\Log::info('→ Decision: ' . $finalStatusLabel . ' (from adjusted probabilities)');
            }
            
            // Step 5: Calculate Final Confidence
            // ============================================
            // KEYAKINAN (CONFIDENCE) DITENTUKAN BERDASARKAN:
            // 1. Adjusted probability tertinggi (dari kombinasi ML + Threshold)
            // 2. Agreement antara ML prediction dan threshold validation
            // 3. Jumlah issues dari sensor
            // ============================================
            
            // Base confidence: probabilitas tertinggi dari adjusted probabilities
            $baseConfidence = $finalConfidenceFromAdjustedProb;
            
            // BOOST 1: Jika adjusted probability sesuai dengan threshold validation
            if (strtoupper($thresholdLabel) == $finalStatusLabel) {
                $baseConfidence = min($baseConfidence + 0.15, 1.0); // Boost lebih besar
            }
            
            // BOOST 2: Untuk status BAIK dengan semua sensor aman (0 issues)
            // Ini meningkatkan keyakinan karena semua sensor menunjukkan kondisi optimal
            if ($finalStatusLabel === 'BAIK' && $thresholdIssues == 0 && $criticalThresholdIssues == 0) {
                $baseConfidence = min($baseConfidence + 0.25, 1.0); // Boost besar untuk BAIK
            }
            
            // BOOST 3: Agreement tinggi antara ML dan threshold
            if ($agreementScore >= 0.8) {
                $baseConfidence = min($baseConfidence + 0.1, 1.0);
            }
            
            // PENALTY: Hanya untuk critical cases yang berbeda dengan threshold validation
            if (strtoupper($thresholdLabel) != $finalStatusLabel && $criticalThresholdIssues >= 2) {
                $baseConfidence = max($baseConfidence - 0.2, 0.3);
            }
            
            // PENALTY: Agreement sangat rendah
            if ($agreementScore < 0.2) {
                $baseConfidence = max($baseConfidence - 0.15, 0.3);
            }
            
            $finalConfidence = round($baseConfidence, 2);
            
            // Step 6: Determine if Manual Review Needed
            // ============================================
            // LOGIKA: Hanya perlu manual review untuk kasus yang benar-benar meragukan
            // Jangan terlalu ketat untuk status BAIK dengan confidence tinggi
            // ============================================
            $needsManualReview = false;
            
            // KONDISI 1: Status BURUK dengan confidence rendah (< 60%)
            // Ini perlu manual review karena status kritis tapi tidak yakin
            if ($finalStatusLabel === 'BURUK' && $finalConfidence < 0.6) {
                $needsManualReview = true;
                \Illuminate\Support\Facades\Log::info('→ Manual review needed: BURUK dengan confidence rendah');
            }
            // KONDISI 2: Ada 3+ critical issues (safety override)
            // Ini perlu manual review karena kondisi sangat kritis
            elseif ($criticalThresholdIssues >= 3) {
                $needsManualReview = true;
                \Illuminate\Support\Facades\Log::info('→ Manual review needed: 3+ critical issues');
            }
            // KONDISI 3: Agreement sangat rendah (< 0.2) DAN confidence rendah (< 50%)
            // Ini perlu manual review karena ML dan threshold tidak setuju
            elseif ($agreementScore < 0.2 && $finalConfidence < 0.5) {
                $needsManualReview = true;
                \Illuminate\Support\Facades\Log::info('→ Manual review needed: Agreement sangat rendah');
            }
            // KONDISI 4: Status BAIK tapi confidence sangat rendah (< 40%)
            // Ini perlu manual review karena seharusnya BAIK tapi tidak yakin
            elseif ($finalStatusLabel === 'BAIK' && $finalConfidence < 0.4) {
                $needsManualReview = true;
                \Illuminate\Support\Facades\Log::info('→ Manual review needed: BAIK dengan confidence sangat rendah');
            }
            // KONDISI 5: Status PERHATIAN dengan confidence rendah (< 50%) DAN ada critical issues
            elseif ($finalStatusLabel === 'PERHATIAN' && $finalConfidence < 0.5 && $criticalThresholdIssues > 0) {
                $needsManualReview = true;
                \Illuminate\Support\Facades\Log::info('→ Manual review needed: PERHATIAN dengan critical issues');
            }
            
            // Jika tidak ada kondisi di atas, tidak perlu manual review
            if (!$needsManualReview) {
                \Illuminate\Support\Facades\Log::info('→ Tidak perlu manual review: Confidence cukup tinggi atau kondisi jelas');
            }
            
            // Step 7: Update current status
            $currentStatus['label'] = $finalStatusLabel;
            $currentStatus['confidence'] = $finalConfidence;
            $currentStatus['agreement_score'] = $agreementScore;
            $currentStatus['needs_manual_review'] = $needsManualReview;
            // ✅ Gunakan adjusted probabilities (bukan original dari model)
            $currentStatus['probability'] = $adjustedProbabilities;
            
            // Update severity berdasarkan final status label (untuk warna banner)
            $severityMap = [
                'BAIK' => 'normal',
                'PERHATIAN' => 'warning',
                'BURUK' => 'critical'
            ];
            $currentStatus['severity'] = $severityMap[$finalStatusLabel] ?? 'normal';
            
            // Generate reasoning
            if ($criticalThresholdIssues >= 3) {
                $currentStatus['reasoning'] = "Terdapat {$criticalThresholdIssues} sensor dalam kondisi kritis. Kondisi ini membahayakan kesehatan ayam dan memerlukan tindakan segera.";
            } else {
                $reasoning = [];
                if (strtoupper($mlLabel) == $finalStatusLabel) {
                    $reasoning[] = "Model ML memprediksi kondisi ini berdasarkan pola historis.";
                }
                if (strtoupper($thresholdLabel) == $finalStatusLabel) {
                    $reasoning[] = sprintf("Validasi threshold menunjukkan %d sensor di luar batas aman.", $thresholdIssues);
                }
                if ($mlLabel != $thresholdLabel) {
                    $reasoning[] = "Terdapat perbedaan antara prediksi ML dan validasi threshold. Status dipilih berdasarkan kondisi yang lebih kritis.";
                }
                $currentStatus['reasoning'] = implode(' ', $reasoning);
            }
            
            // Simpan original ML prediction (dari model yang dilatih dengan threshold lama)
            $currentStatus['ml_prediction'] = [
                'status' => strtoupper($mlLabel),
                'probabilities' => $mlStatus['ml_probabilities_original'] ?? $mlStatus['probability'] ?? null,
                'confidence' => (float)($mlStatus['confidence'] ?? 0.7)
            ];
            
            // Simpan adjusted probabilities (sudah disesuaikan dengan threshold baru)
            $currentStatus['adjusted_probabilities'] = $adjustedProbabilities;
            $currentStatus['threshold_score'] = $thresholdScore;
            
            $currentStatus['threshold_validation'] = [
                'status' => strtoupper($thresholdLabel),
                'issues_count' => $thresholdIssues,
                'critical_issues' => $criticalThresholdIssues,
                'warning_issues' => $warningThresholdIssues,
                'sensor_issues' => $sensorIssues
            ];
            
            // Log final decision
            \Illuminate\Support\Facades\Log::info('=== HYBRID ML + THRESHOLD DECISION ===', [
                'ml_prediction' => $currentStatus['ml_prediction'],
                'threshold_validation' => $currentStatus['threshold_validation'],
                'agreement_score' => $agreementScore,
                'final_status' => $finalStatusLabel,
                'final_confidence' => $finalConfidence,
                'needs_manual_review' => $needsManualReview,
                'reasoning' => $currentStatus['reasoning']
            ]);
        } else {
            // Jika threshold validation tidak tersedia, gunakan adjusted probabilities saja
            // (probabilities sudah di-adjust sebelumnya)
            $finalStatusLabelNoThreshold = strtoupper($finalStatusFromAdjustedProb ?? 'BAIK');
            $currentStatus['label'] = $finalStatusLabelNoThreshold;
            $currentStatus['confidence'] = $finalConfidenceFromAdjustedProb;
            $currentStatus['probability'] = $adjustedProbabilities;
            $currentStatus['ml_prediction'] = [
                'status' => strtoupper($mlStatus['label'] ?? 'BAIK'),
                'probabilities' => $mlStatus['ml_probabilities_original'] ?? $mlProbabilities,
                'confidence' => (float)($mlStatus['confidence'] ?? 0.7)
            ];
            $currentStatus['adjusted_probabilities'] = $adjustedProbabilities;
            $currentStatus['threshold_score'] = $thresholdScore;
            
            // Update severity berdasarkan final status label (untuk warna banner)
            $severityMap = [
                'BAIK' => 'normal',
                'PERHATIAN' => 'warning',
                'BURUK' => 'critical'
            ];
            $currentStatus['severity'] = $severityMap[$finalStatusLabelNoThreshold] ?? 'normal';
            
            // Set needs_manual_review untuk kasus tanpa threshold validation
            // Hanya perlu manual review jika confidence sangat rendah
            $currentStatus['needs_manual_review'] = ($finalConfidenceFromAdjustedProb < 0.4);
            
            \Illuminate\Support\Facades\Log::info('=== USING ADJUSTED PROBABILITIES (NO THRESHOLD VALIDATION) ===', [
                'final_status' => $currentStatus['label'],
                'adjusted_probabilities' => $adjustedProbabilities,
                'ml_probabilities_original' => $mlProbabilities,
                'severity' => $currentStatus['severity']
            ]);
        }
        
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

    // NOTIFIKASI TELEGRAM DIHAPUS DARI ROUTE INI
    // Notifikasi Telegram hanya dikirim melalui scheduler command (telegram:send-monitoring)
    // yang berjalan setiap 5 menit dan hanya mengirim saat kondisi PERHATIAN atau BURUK
    // Hal ini mencegah notifikasi spam saat user membuka/merefresh halaman monitoring
    
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
        'ml_metadata' => $mlMetadata,
        'thresholds' => $thresholds // Tambahkan thresholds dari database
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

// Debug endpoint untuk testing threshold validation
Route::get('/api/debug/threshold-validation', function () {
    try {
        $profileKey = request()->query('profile', 'default');
        
        // Clear cache
        \App\Models\ThresholdProfile::clearBootedModels();
        \App\Models\ThresholdValue::clearBootedModels();
        
        // Get threshold from database
        $thresholdProfile = \App\Models\ThresholdProfile::where('profile_key', $profileKey)
            ->with('thresholdValues')
            ->first();
        
        if (!$thresholdProfile) {
            $thresholdProfile = \App\Models\ThresholdProfile::where('profile_key', 'default')
                ->with('thresholdValues')
                ->first();
        }
        
        $thresholds = [];
        if ($thresholdProfile) {
            foreach ($thresholdProfile->thresholdValues as $value) {
                if ($value->sensor_type === 'amonia_ppm') {
                    $thresholds['ammonia'] = [
                        'ideal_max' => (float) $value->ideal_max,
                        'warn_max' => (float) $value->warn_max,
                        'danger_max' => (float) $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'suhu_c') {
                    $thresholds['temperature'] = [
                        'ideal_min' => (float) $value->ideal_min,
                        'ideal_max' => (float) $value->ideal_max,
                        'danger_low' => (float) $value->danger_min,
                        'danger_high' => (float) $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'kelembaban_rh') {
                    $thresholds['humidity'] = [
                        'ideal_min' => (float) $value->ideal_min,
                        'ideal_max' => (float) $value->ideal_max,
                        'warn_low' => (float) $value->ideal_min,
                        'warn_high' => (float) $value->warn_max,
                        'danger_high' => (float) $value->danger_max,
                    ];
                } elseif ($value->sensor_type === 'cahaya_lux') {
                    $thresholds['light'] = [
                        'ideal_low' => (float) $value->ideal_min,
                        'ideal_high' => (float) $value->ideal_max,
                        'warn_low' => (float) $value->warn_min,
                        'warn_high' => (float) $value->warn_max,
                    ];
                }
            }
        }
        
        // Get latest sensor data
        $latestSensor = \App\Models\SensorReading::orderBy('recorded_at', 'desc')->first();
        
        if (!$latestSensor) {
            return response()->json([
                'error' => 'No sensor data found'
            ], 404);
        }
        
        $temp = (float) ($latestSensor->temperature ?? 0);
        $humid = (float) ($latestSensor->humidity ?? 0);
        $ammonia = (float) ($latestSensor->ammonia ?? 0);
        $light = (float) ($latestSensor->light ?? 0);
        
        // Validate thresholds (same logic as main route)
        $thresholdBasedLabel = 'baik';
        $thresholdIssues = 0;
        $criticalThresholdIssues = 0;
        $warningThresholdIssues = 0;
        $sensorDetails = [];
        
        // Temperature
        $tempIdealMin = (float) ($thresholds['temperature']['ideal_min'] ?? 23);
        $tempIdealMax = (float) ($thresholds['temperature']['ideal_max'] ?? 34);
        $tempDangerLow = (float) ($thresholds['temperature']['danger_low'] ?? 20);
        $tempDangerHigh = (float) ($thresholds['temperature']['danger_high'] ?? 37);
        
        if ($temp >= $tempIdealMin && $temp <= $tempIdealMax) {
            $sensorDetails['temperature'] = ['status' => 'aman', 'value' => $temp];
        } elseif ($temp < $tempDangerLow || $temp > $tempDangerHigh) {
            $thresholdIssues++;
            $criticalThresholdIssues++;
            $thresholdBasedLabel = 'buruk';
            $sensorDetails['temperature'] = ['status' => 'critical', 'value' => $temp];
        } else {
            $thresholdIssues++;
            $warningThresholdIssues++;
            if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
            $sensorDetails['temperature'] = ['status' => 'warning', 'value' => $temp];
        }
        
        // Humidity
        $humidIdealMin = (float) ($thresholds['humidity']['ideal_min'] ?? 50);
        $humidIdealMax = (float) ($thresholds['humidity']['ideal_max'] ?? 70);
        $humidDangerHigh = (float) ($thresholds['humidity']['danger_high'] ?? 80);
        
        if ($humid >= $humidIdealMin && $humid <= $humidIdealMax) {
            $sensorDetails['humidity'] = ['status' => 'aman', 'value' => $humid];
        } elseif ($humid > $humidDangerHigh) {
            $thresholdIssues++;
            $criticalThresholdIssues++;
            $thresholdBasedLabel = 'buruk';
            $sensorDetails['humidity'] = ['status' => 'critical', 'value' => $humid];
        } else {
            $thresholdIssues++;
            $warningThresholdIssues++;
            if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
            $sensorDetails['humidity'] = ['status' => 'warning', 'value' => $humid];
        }
        
        // Ammonia
        $ammoniaIdealMax = (float) ($thresholds['ammonia']['ideal_max'] ?? 20);
        $ammoniaDangerMax = (float) ($thresholds['ammonia']['danger_max'] ?? 35);
        
        if ($ammonia <= $ammoniaIdealMax) {
            $sensorDetails['ammonia'] = ['status' => 'aman', 'value' => $ammonia];
        } elseif ($ammonia > $ammoniaDangerMax) {
            $thresholdIssues++;
            $criticalThresholdIssues++;
            $thresholdBasedLabel = 'buruk';
            $sensorDetails['ammonia'] = ['status' => 'critical', 'value' => $ammonia];
        } else {
            $thresholdIssues++;
            $warningThresholdIssues++;
            if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
            $sensorDetails['ammonia'] = ['status' => 'warning', 'value' => $ammonia];
        }
        
        // Light
        $lightForCheck = $light / 10;
        $lightIdealLow = (float) ($thresholds['light']['ideal_low'] ?? 20);
        $lightIdealHigh = (float) ($thresholds['light']['ideal_high'] ?? 40);
        $lightWarnLow = (float) ($thresholds['light']['warn_low'] ?? 10);
        $lightWarnHigh = (float) ($thresholds['light']['warn_high'] ?? 60);
        
        if ($lightForCheck >= $lightIdealLow && $lightForCheck <= $lightIdealHigh) {
            $sensorDetails['light'] = ['status' => 'aman', 'value' => $lightForCheck];
        } elseif ($lightForCheck < $lightWarnLow || $lightForCheck > $lightWarnHigh) {
            $thresholdIssues++;
            $criticalThresholdIssues++;
            $thresholdBasedLabel = 'buruk';
            $sensorDetails['light'] = ['status' => 'critical', 'value' => $lightForCheck];
        } else {
            $thresholdIssues++;
            $warningThresholdIssues++;
            if ($thresholdBasedLabel === 'baik') $thresholdBasedLabel = 'perhatian';
            $sensorDetails['light'] = ['status' => 'warning', 'value' => $lightForCheck];
        }
        
        return response()->json([
            'profile' => $profileKey,
            'sensor_data' => [
                'temperature' => $temp,
                'humidity' => $humid,
                'ammonia' => $ammonia,
                'light' => $light,
                'light_for_check' => $lightForCheck
            ],
            'thresholds' => $thresholds,
            'validation_result' => [
                'status' => strtoupper($thresholdBasedLabel),
                'issues_count' => $thresholdIssues,
                'critical_issues' => $criticalThresholdIssues,
                'warning_issues' => $warningThresholdIssues
            ],
            'sensor_details' => $sensorDetails,
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
