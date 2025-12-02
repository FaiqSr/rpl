<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
// Helper functions (generateTrackingNumber, getPaymentAccount) moved to `app/helpers.php` and autoloaded via Composer

// Serve storage files (fix 403 Forbidden) - Must be before other routes
Route::get('/storage/products/{filename}', function ($filename) {
    try {
        // Security: prevent directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            abort(403, 'Forbidden');
        }

        $filePath = 'products/' . $filename;

        // Check if file exists using Storage
        if (!Storage::disk('public')->exists($filePath)) {
            \Log::error("Storage file not found: {$filePath}");
            abort(404, 'File not found');
        }

        // Serve file using Storage response
        return Storage::disk('public')->response($filePath, null, [
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*',
        ]);
    } catch (\Exception $e) {
        \Log::error("Error serving storage file: " . $e->getMessage());
        abort(500, 'Error serving file');
    }
})->name('storage.products');

// Serve tool images
Route::get('/storage/tools/{filename}', function ($filename) {
    try {
        // Security: prevent directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            abort(403, 'Forbidden');
        }

        $filePath = 'tools/' . $filename;

        // Check if file exists using Storage
        if (!Storage::disk('public')->exists($filePath)) {
            \Log::error("Storage file not found: {$filePath}");
            abort(404, 'File not found');
        }

        // Serve file using Storage response
        return Storage::disk('public')->response($filePath, null, [
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*',
        ]);
    } catch (\Exception $e) {
        \Log::error("Error serving tool image {$filename}: " . $e->getMessage());
        abort(500, 'Error serving file');
    }
})->name('storage.tools');

// Serve review images
Route::get('/storage/reviews/{filename}', function ($filename) {
    try {
        // Security: prevent directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            abort(403, 'Forbidden');
        }

        $filePath = 'reviews/' . $filename;

        // Check if file exists using Storage
        if (!Storage::disk('public')->exists($filePath)) {
            \Log::error("Storage file not found: {$filePath}");
            abort(404, 'File not found');
        }

        // Serve file using Storage response
        return Storage::disk('public')->response($filePath, null, [
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*',
        ]);
    } catch (\Exception $e) {
        \Log::error("Error serving review image {$filename}: " . $e->getMessage());
        abort(500, 'Error serving file');
    }
})->name('storage.reviews');

// Serve other storage files
Route::get('/storage/{path}', function ($path) {
    // Decode path jika ada encoding
    $path = urldecode($path);

    // Security: prevent directory traversal
    if (strpos($path, '..') !== false) {
        abort(403, 'Forbidden');
    }

    $filePath = storage_path('app/public/' . $path);

    if (!file_exists($filePath) || !is_file($filePath)) {
        abort(404, 'File not found');
    }

    // Get MIME type
    $mimeType = mime_content_type($filePath);
    if (!$mimeType) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    // Serve file with proper headers
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
        'Access-Control-Allow-Origin' => '*',
    ]);
})->where('path', '.*')->name('storage.serve');

// Public Routes - Semua bisa diakses tanpa login
Route::get('/', function () {
    $query = Product::with(['images', 'reviews.order']);

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
            // Use homepage category name to filter products with keyword mapping (lebih fleksibel)
            $categoryName = strtolower($homepageCategory->name);
            $categorySlug = strtolower($homepageCategory->slug);

            // Map category names to product keywords (diperluas dan lebih fleksibel)
            $categoryKeywords = [
                // Jeroan Ayam
                'jeroan' => ['jeroan', 'ati', 'ampela', 'hati', 'usus', 'paru', 'limpa', 'ginjal', 'jantung', 'paket jeroan', 'jeroan ayam', 'jeroan broiler'],
                'jeroan ayam' => ['jeroan', 'ati', 'ampela', 'hati', 'usus', 'paru', 'limpa', 'ginjal', 'jantung', 'paket jeroan', 'jeroan ayam', 'jeroan broiler'],
                'jeroan-ayam' => ['jeroan', 'ati', 'ampela', 'hati', 'usus', 'paru', 'limpa', 'ginjal', 'jantung', 'paket jeroan', 'jeroan ayam', 'jeroan broiler'],

                // Ayam Potong Segar / Daging Segar - Tidak termasuk dada (karena ada kategori khusus)
                'daging segar' => ['ayam broiler', 'ayam potong', 'ayam utuh', 'paha atas', 'paha bawah', 'drumstick', 'thigh', 'sayap ayam', 'sayap', 'wing', 'kulit ayam', 'kulit', 'kepala ayam', 'kepala', 'ceker ayam', 'ceker', 'daging ayam', 'daging segar', 'ayam segar', 'broiler', 'potong', 'paha', 'paha atas', 'paha bawah'],
                'daging-segar' => ['ayam broiler', 'ayam potong', 'ayam utuh', 'paha atas', 'paha bawah', 'drumstick', 'thigh', 'sayap ayam', 'sayap', 'wing', 'kulit ayam', 'kulit', 'kepala ayam', 'kepala', 'ceker ayam', 'ceker', 'daging ayam', 'daging segar', 'ayam segar', 'broiler', 'potong', 'paha', 'paha atas', 'paha bawah'],
                'daging' => ['ayam broiler', 'ayam potong', 'ayam utuh', 'paha atas', 'paha bawah', 'drumstick', 'thigh', 'sayap ayam', 'sayap', 'wing', 'kulit ayam', 'kulit', 'kepala ayam', 'kepala', 'ceker ayam', 'ceker', 'daging ayam', 'daging segar', 'ayam segar', 'broiler', 'potong', 'paha', 'paha atas', 'paha bawah'],
                'ayam potong segar' => ['ayam broiler', 'ayam potong', 'ayam utuh', 'paha atas', 'paha bawah', 'drumstick', 'thigh', 'sayap ayam', 'sayap', 'wing', 'kulit ayam', 'kulit', 'kepala ayam', 'kepala', 'ceker ayam', 'ceker', 'daging ayam', 'daging segar', 'ayam segar', 'broiler', 'potong', 'paha', 'paha atas', 'paha bawah'],

                // Dada Ayam - Berdasarkan data produk yang ada di database
                'dada' => ['dada ayam', 'dada-ayam', 'fillet', 'tenderloin', 'skinless', 'boneless', 'slice', 'cube', 'dada-ayam-utuh', 'dada-ayam-fillet', 'dada-ayam-slice', 'dada-ayam-cube', 'dada-ayam-skinless', 'dada-ayam-boneless', 'dada-ayam-beku', 'fillet-ayam-beku', 'tenderloin-ayam'],
                'dada-ayam' => ['dada ayam', 'dada-ayam', 'fillet', 'tenderloin', 'skinless', 'boneless', 'slice', 'cube', 'dada-ayam-utuh', 'dada-ayam-fillet', 'dada-ayam-slice', 'dada-ayam-cube', 'dada-ayam-skinless', 'dada-ayam-boneless', 'dada-ayam-beku', 'fillet-ayam-beku', 'tenderloin-ayam'],
                'dada ayam' => ['dada ayam', 'dada-ayam', 'fillet', 'tenderloin', 'skinless', 'boneless', 'slice', 'cube', 'dada-ayam-utuh', 'dada-ayam-fillet', 'dada-ayam-slice', 'dada-ayam-cube', 'dada-ayam-skinless', 'dada-ayam-boneless', 'dada-ayam-beku', 'fillet-ayam-beku', 'tenderloin-ayam'],

                // Ayam Karkas
                'ayam karkas' => ['karkas', 'carcass', 'ayam karkas', 'karkas ayam', 'karkas broiler', 'karkas potong', 'whole chicken', 'ayam utuh karkas'],
                'ayam-karkas' => ['karkas', 'carcass', 'ayam karkas', 'karkas ayam', 'karkas broiler', 'karkas potong', 'whole chicken', 'ayam utuh karkas'],
                'karkas' => ['karkas', 'carcass', 'ayam karkas', 'karkas ayam', 'karkas broiler', 'karkas potong', 'whole chicken', 'ayam utuh karkas'],

                // Produk Frozen
                'produk frozen' => ['beku', 'frozen', 'ayam beku', 'frozen chicken', 'produk beku', 'chicken frozen', 'frozen product'],
                'produk-frozen' => ['beku', 'frozen', 'ayam beku', 'frozen chicken', 'produk beku', 'chicken frozen', 'frozen product'],
                'frozen' => ['beku', 'frozen', 'ayam beku', 'frozen chicken', 'produk beku', 'chicken frozen', 'frozen product'],
                'ayam beku' => ['beku', 'frozen', 'ayam beku', 'frozen chicken', 'produk beku', 'chicken frozen', 'frozen product'],

                // Produk Olahan
                'produk olahan' => ['nugget', 'sosis', 'karage', 'popcorn', 'wings', 'chicken wings', 'olahan ayam', 'produk olahan ayam', 'olahan', 'chicken nugget', 'chicken sausage', 'chicken karage', 'chicken popcorn'],
                'produk-olahan' => ['nugget', 'sosis', 'karage', 'popcorn', 'wings', 'chicken wings', 'olahan ayam', 'produk olahan ayam', 'olahan', 'chicken nugget', 'chicken sausage', 'chicken karage', 'chicken popcorn'],
                'olahan' => ['nugget', 'sosis', 'karage', 'popcorn', 'wings', 'chicken wings', 'olahan ayam', 'produk olahan ayam', 'olahan', 'chicken nugget', 'chicken sausage', 'chicken karage', 'chicken popcorn'],
                'produk olahan ayam' => ['nugget', 'sosis', 'karage', 'popcorn', 'wings', 'chicken wings', 'olahan ayam', 'produk olahan ayam', 'olahan', 'chicken nugget', 'chicken sausage', 'chicken karage', 'chicken popcorn'],

                // Obat & Vitamin
                'obat vitamin' => ['vitamin', 'antibiotik', 'obat', 'probiotik', 'multivitamin', 'disinfectant', 'disinfektan', 'electrolyte', 'suplemen', 'antistress', 'vitachick', 'vitamix', 'obat ayam', 'vitamin ayam', 'obat & vitamin', 'obat dan vitamin', 'poligrip', 'electrovit', 'neobro', 'supertop', 'vito plex', 'vito plex', 'vitoplex'],
                'obat & vitamin' => ['vitamin', 'antibiotik', 'obat', 'probiotik', 'multivitamin', 'disinfectant', 'disinfektan', 'electrolyte', 'suplemen', 'antistress', 'vitachick', 'vitamix', 'obat ayam', 'vitamin ayam', 'obat & vitamin', 'obat dan vitamin', 'poligrip', 'electrovit', 'neobro', 'supertop', 'vito plex', 'vito plex', 'vitoplex'],
                'obat & vitamin ayam' => ['vitamin', 'antibiotik', 'obat', 'probiotik', 'multivitamin', 'disinfectant', 'disinfektan', 'electrolyte', 'suplemen', 'antistress', 'vitachick', 'vitamix', 'obat ayam', 'vitamin ayam', 'obat & vitamin', 'obat dan vitamin', 'poligrip', 'electrovit', 'neobro', 'supertop', 'vito plex', 'vito plex', 'vitoplex'],
                'obat-dan-vitamin-ayam' => ['vitamin', 'antibiotik', 'obat', 'probiotik', 'multivitamin', 'disinfectant', 'disinfektan', 'electrolyte', 'suplemen', 'antistress', 'vitachick', 'vitamix', 'obat ayam', 'vitamin ayam', 'obat & vitamin', 'obat dan vitamin', 'poligrip', 'electrovit', 'neobro', 'supertop', 'vito plex', 'vito plex', 'vitoplex'],
                'obat-vitamin-ayam' => ['vitamin', 'antibiotik', 'obat', 'probiotik', 'multivitamin', 'disinfectant', 'disinfektan', 'electrolyte', 'suplemen', 'antistress', 'vitachick', 'vitamix', 'obat ayam', 'vitamin ayam', 'obat & vitamin', 'obat dan vitamin', 'poligrip', 'electrovit', 'neobro', 'supertop', 'vito plex', 'vito plex', 'vitoplex'],

                // Pakan Ayam
                'pakan' => ['pakan', 'vaksin', 'desinfektan air', 'mineral feed', 'starter', 'finisher', 'nd/ib', 'pakan ayam', 'feed', 'chicken feed', 'pakan broiler'],
                'pakan ayam' => ['pakan', 'vaksin', 'desinfektan air', 'mineral feed', 'starter', 'finisher', 'nd/ib', 'pakan ayam', 'feed', 'chicken feed', 'pakan broiler'],
                'pakan-ayam' => ['pakan', 'vaksin', 'desinfektan air', 'mineral feed', 'starter', 'finisher', 'nd/ib', 'pakan ayam', 'feed', 'chicken feed', 'pakan broiler'],

                // Peralatan Kandang - Berdasarkan data produk yang ada di database
                'peralatan kandang' => ['tempat minum', 'tempat pakan', 'nipple', 'drinker', 'selang kandang', 'lampu penghangat', 'brooder', 'pemanas kandang', 'gasolec', 'infrared', 'timbangan digital', 'sensor suhu', 'kelembaban', 'tirai kandang', 'plastik uv', 'keranjang ayam', 'kandang doc', 'sprayer', 'disinfektan', 'mesin pencabut', 'knapsack', 'termometer kandang', 'exhaust', 'blower', 'timbangan pakan', 'tempat-minum-ayam', 'tempat-pakan-ayam', 'nipple-drinker', 'selang-kandang-ayam', 'lampu-penghangat', 'pemanas-kandang', 'timbangan-digital-ayam', 'sensor-suhu', 'tirai-kandang', 'keranjang-ayam', 'kandang-doc', 'sprayer-disinfektan', 'mesin-pencabut-bulu', 'knapsack-sprayer', 'termometer-kandang', 'exhaust-fan', 'timbangan-pakan', 'feeder'],
                'peralatan-kandang' => ['tempat minum', 'tempat pakan', 'nipple', 'drinker', 'selang kandang', 'lampu penghangat', 'brooder', 'pemanas kandang', 'gasolec', 'infrared', 'timbangan digital', 'sensor suhu', 'kelembaban', 'tirai kandang', 'plastik uv', 'keranjang ayam', 'kandang doc', 'sprayer', 'disinfektan', 'mesin pencabut', 'knapsack', 'termometer kandang', 'exhaust', 'blower', 'timbangan pakan', 'tempat-minum-ayam', 'tempat-pakan-ayam', 'nipple-drinker', 'selang-kandang-ayam', 'lampu-penghangat', 'pemanas-kandang', 'timbangan-digital-ayam', 'sensor-suhu', 'tirai-kandang', 'keranjang-ayam', 'kandang-doc', 'sprayer-disinfektan', 'mesin-pencabut-bulu', 'knapsack-sprayer', 'termometer-kandang', 'exhaust-fan', 'timbangan-pakan', 'feeder'],
                'peralatan' => ['tempat minum', 'tempat pakan', 'nipple', 'drinker', 'selang kandang', 'lampu penghangat', 'brooder', 'pemanas kandang', 'gasolec', 'infrared', 'timbangan digital', 'sensor suhu', 'kelembaban', 'tirai kandang', 'plastik uv', 'keranjang ayam', 'kandang doc', 'sprayer', 'disinfektan', 'mesin pencabut', 'knapsack', 'termometer kandang', 'exhaust', 'blower', 'timbangan pakan', 'tempat-minum-ayam', 'tempat-pakan-ayam', 'nipple-drinker', 'selang-kandang-ayam', 'lampu-penghangat', 'pemanas-kandang', 'timbangan-digital-ayam', 'sensor-suhu', 'tirai-kandang', 'keranjang-ayam', 'kandang-doc', 'sprayer-disinfektan', 'mesin-pencabut-bulu', 'knapsack-sprayer', 'termometer-kandang', 'exhaust-fan', 'timbangan-pakan', 'feeder'],
                'alat-alat' => ['tempat minum', 'tempat pakan', 'nipple', 'drinker', 'selang kandang', 'lampu penghangat', 'brooder', 'pemanas kandang', 'gasolec', 'infrared', 'timbangan digital', 'sensor suhu', 'kelembaban', 'tirai kandang', 'plastik uv', 'keranjang ayam', 'kandang doc', 'sprayer', 'disinfektan', 'mesin pencabut', 'knapsack', 'termometer kandang', 'exhaust', 'blower', 'timbangan pakan', 'tempat-minum-ayam', 'tempat-pakan-ayam', 'nipple-drinker', 'selang-kandang-ayam', 'lampu-penghangat', 'pemanas-kandang', 'timbangan-digital-ayam', 'sensor-suhu', 'tirai-kandang', 'keranjang-ayam', 'kandang-doc', 'sprayer-disinfektan', 'mesin-pencabut-bulu', 'knapsack-sprayer', 'termometer-kandang', 'exhaust-fan', 'timbangan-pakan', 'feeder'],

                // Robot ChickPatrol
                'robot' => ['robot', 'chickpatrol', 'chick patrol', 'chick-patrol', 'monitoring', 'robot monitoring'],
                'robot chickpatrol' => ['robot', 'chickpatrol', 'chick patrol', 'chick-patrol', 'monitoring', 'robot monitoring'],
                'robot-chickpatrol' => ['robot', 'chickpatrol', 'chick patrol', 'chick-patrol', 'monitoring', 'robot monitoring'],
            ];

            // Get keywords for this category
            $keywords = $categoryKeywords[$categorySlug] ?? $categoryKeywords[$categoryName] ?? [];

            // Jika tidak ada mapping, gunakan nama kategori sebagai keyword
            if (empty($keywords)) {
                $keywords = [$categoryName, $categorySlug];
            }

            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword);
                    if (!empty($keyword)) {
                        $q->orWhereRaw('LOWER(slug) LIKE ?', ['%' . strtolower($keyword) . '%'])
                          ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($keyword) . '%'])
                          ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($keyword) . '%']);
                    }
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

    $products = $query->select('products.*')
        ->selectRaw('COALESCE((
            SELECT SUM(order_details.qty)
            FROM order_details
            INNER JOIN orders ON order_details.order_id = orders.order_id
            WHERE order_details.product_id = products.product_id
            AND orders.payment_status = "paid"
        ), 0) as total_sold')
        ->orderByDesc('created_at')
        ->paginate(24);

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

    // Auto-fix sort_order for categories with missing or duplicate orders
    $allCategories = \App\Models\HomepageCategory::orderBy('sort_order')->orderBy('created_at')->get();
    $order = 1;
    foreach ($allCategories as $cat) {
        if ($cat->sort_order != $order) {
            $cat->update(['sort_order' => $order]);
        }
        $order++;
    }

    $homepageCategories = \App\Models\HomepageCategory::where('is_active', true)->orderBy('sort_order')->orderBy('created_at')->get();
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
        'password' => [
            'required',
            'min:8',
            'confirmed',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
        ]
    ], [
        'password.required' => 'Password harus diisi',
        'password.min' => 'Password minimal 8 karakter',
        'password.confirmed' => 'Konfirmasi password tidak cocok',
        'password.regex' => 'Password harus mengandung minimal 8 karakter dengan kombinasi huruf kapital, huruf kecil, angka, dan simbol',
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

// Firebase Google Authentication Routes
Route::post('/auth/google', function (\Illuminate\Http\Request $request) {
    try {
        $validated = $request->validate([
            'idToken' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'photoUrl' => 'nullable|string',
        ]);

        // Verify Firebase ID Token (you can use Firebase Admin SDK for server-side verification)
        // For now, we'll trust the client-side token and create/login user

        // Check if user exists by email or firebase_uid
        $user = \App\Models\User::where('email', $validated['email'])
            ->orWhere('firebase_uid', $request->input('uid'))
            ->first();

        if ($user) {
            // Update firebase_uid if not set
            if (!$user->firebase_uid) {
                $user->update([
                    'firebase_uid' => $request->input('uid'),
                    'provider' => 'google'
                ]);
            }

            // Login user
            \Illuminate\Support\Facades\Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => $user->role === 'admin' || $user->role === 'seller'
                    ? route('dashboard')
                    : route('home')
            ]);
        } else {
            // Create new user
            $nameParts = explode(' ', $validated['name'], 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            $user = \App\Models\User::create([
                'user_id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt(\Illuminate\Support\Str::random(32)), // Random password since using Firebase
                'role' => 'visitor',
                'firebase_uid' => $request->input('uid'),
                'provider' => 'google'
            ]);

            // Login user
            \Illuminate\Support\Facades\Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'redirect' => route('home')
            ]);
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Firebase auth error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
})->name('auth.google');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    try {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required'
        ]);

        // Check if input is email or phone
        $isEmail = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL);

        // Find user by email or phone
        $user = null;
        if ($isEmail) {
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        } else {
            $user = \App\Models\User::where('phone', $credentials['email'])->first();
        }

        if (!$user) {
            return back()->withErrors(['email' => 'Email/No. Telepon atau password salah'])->with('error', 'Login gagal');
        }

        // Verify password manually (because User model uses user_id as primary key, not id)
        if (!\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email/No. Telepon atau password salah'])->with('error', 'Login gagal');
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

// Password Reset Routes (Tanpa Email - Verifikasi Data Pribadi)
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password/verify', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|string',
        'name' => 'required|string',
        'phone' => 'required|string',
    ]);

    // Cari user berdasarkan email atau phone
    $user = \App\Models\User::where(function($query) use ($request) {
        $query->where('email', $request->email)
              ->orWhere('phone', $request->email);
    })->first();

    if (!$user) {
        return back()->withErrors(['email' => 'Email/No. Telepon tidak ditemukan'])->withInput();
    }

    // Verifikasi nama dan phone
    $nameMatch = strtolower(trim($user->name)) === strtolower(trim($request->name));
    $phoneMatch = trim($user->phone) === trim($request->phone);

    if (!$nameMatch || !$phoneMatch) {
        return back()->withErrors(['name' => 'Data verifikasi tidak cocok. Pastikan nama dan nomor telepon sesuai dengan data registrasi.'])->withInput();
    }

    // Jika verifikasi berhasil, redirect ke halaman reset password
    return redirect()->route('password.reset', ['user_id' => $user->user_id]);
})->name('password.verify');

Route::get('/reset-password/{user_id}', function (string $user_id) {
    $user = \App\Models\User::find($user_id);

    if (!$user) {
        return redirect()->route('password.request')->with('error', 'Link reset password tidak valid');
    }

    return view('auth.reset-password', [
        'user_id' => $user_id
    ]);
})->name('password.reset');

Route::post('/reset-password', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'user_id' => 'required|string',
        'password' => [
            'required',
            'min:8',
            'confirmed',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
        ],
    ], [
        'password.required' => 'Password harus diisi',
        'password.min' => 'Password minimal 8 karakter',
        'password.confirmed' => 'Konfirmasi password tidak cocok',
        'password.regex' => 'Password harus mengandung minimal 8 karakter dengan kombinasi huruf kapital, huruf kecil, angka, dan simbol',
    ]);

    $user = \App\Models\User::find($request->user_id);

    if (!$user) {
        return back()->withErrors(['password' => 'User tidak ditemukan'])->withInput();
    }

    // Update password
    $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
    $user->save();

    return redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login dengan password baru.');
})->name('password.update');

// Product Detail
Route::get('/product/{id}', function ($id) {
    // Try to find by product_id first (database UUID), then by numeric id (localStorage)
    $product = \App\Models\Product::with(['images', 'reviews.user', 'reviews.replies.user'])
        ->select('products.*')
        ->selectRaw('COALESCE((
            SELECT SUM(order_details.qty)
            FROM order_details
            INNER JOIN orders ON order_details.order_id = orders.order_id
            WHERE order_details.product_id = products.product_id
            AND orders.payment_status = "paid"
        ), 0) as total_sold')
        ->where('product_id', $id)
        ->first();

    // If not found and id is numeric, this might be a localStorage product
    if (!$product && is_numeric($id)) {
        // Create a mock product from localStorage data for demo
        // In production, you'd want to sync localStorage to DB first
        return response()->view('store.product-detail-mock', ['productId' => $id]);
    }

    if (!$product) abort(404);

    $avgRating = $product->average_rating;
    $totalReviews = $product->total_reviews;
    $totalSold = $product->total_sold ?? 0;
    // Only get top-level reviews (no parent_id)
    $reviews = $product->reviews->whereNull('parent_id')->map(function($review) {
        // Process review image URLs (support multiple images)
        if ($review->image) {
            $images = is_array($review->image) ? $review->image : [$review->image];
            $review->image_urls = array_map(function($img) {
                if ($img && !preg_match('/^(https?:\/\/|data:)/', $img)) {
                    if (strpos($img, 'storage/reviews/') !== false) {
                        return asset($img);
                    } else {
                        return asset('storage/' . $img);
                    }
                }
                return $img;
            }, array_filter($images));
        }
        return $review;
    });

    // Calculate rating statistics
    $ratingStats = [
        5 => $reviews->where('rating', 5)->count(),
        4 => $reviews->where('rating', 4)->count(),
        3 => $reviews->where('rating', 3)->count(),
        2 => $reviews->where('rating', 2)->count(),
        1 => $reviews->where('rating', 1)->count(),
    ];

    // Count reviews with comments
    $reviewsWithComments = $reviews->filter(function($review) {
        return !empty($review->review);
    })->count();

    // Count reviews with media
    $reviewsWithMedia = $reviews->filter(function($review) {
        return !empty($review->image_urls) && count($review->image_urls) > 0;
    })->count();

    return view('store.product-detail', compact('product', 'avgRating', 'totalReviews', 'reviews', 'totalSold', 'ratingStats', 'reviewsWithComments', 'reviewsWithMedia'));
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

// Confirm Payment (Buyer states they have paid - status becomes "processing")
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

    if ($order->payment_status === 'processing') {
        return response()->json([
            'success' => false,
            'message' => 'Pembayaran sedang diproses oleh admin'
        ], 400);
    }

    // Change status to "processing" - waiting for admin validation
    $order->payment_status = 'processing';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Pembayaran Anda sedang diproses. Admin akan memvalidasi pembayaran Anda.',
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
    Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
        // Get statistics from database
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // Penjualan Hari Ini
        $salesToday = \App\Models\Order::whereDate('created_at', $today)->count();
        $salesYesterday = \App\Models\Order::whereDate('created_at', now()->subDay()->startOfDay())->count();
        $salesTodayChange = $salesYesterday > 0 ? (($salesToday - $salesYesterday) / $salesYesterday) * 100 : ($salesToday > 0 ? 100 : 0);

        // Penjualan Perbulan
        $salesThisMonth = \App\Models\Order::where('created_at', '>=', $thisMonth)->count();
        $lastMonth = now()->subMonth()->startOfMonth();
        $salesLastMonth = \App\Models\Order::where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)
            ->count();
        $salesMonthChange = $salesLastMonth > 0 ? (($salesThisMonth - $salesLastMonth) / $salesLastMonth) * 100 : ($salesThisMonth > 0 ? 100 : 0);

        // Produk Aktif (produk dengan stok > 0)
        $activeProducts = \App\Models\Product::where('stock', '>', 0)->count();
        $activeProductsPrev = \App\Models\Product::where('stock', '>', 0)
            ->where('updated_at', '<', now()->subDay())
            ->count();
        $activeProductsChange = $activeProductsPrev > 0 ? (($activeProducts - $activeProductsPrev) / $activeProductsPrev) * 100 : 0;

        // Alat Aktif (tools yang aktif, bukan offline)
        $activeTools = \App\Models\Tools::where('operational_status', '!=', 'offline')->count();
        $activeToolsPrev = \App\Models\Tools::where('operational_status', '!=', 'offline')
            ->where('updated_at', '<', now()->subDay())
            ->count();
        $activeToolsChange = $activeToolsPrev > 0 ? (($activeTools - $activeToolsPrev) / $activeToolsPrev) * 100 : 0;

        // Ulasan Baru (dalam 7 hari terakhir)
        $newReviews = \App\Models\ProductReview::where('created_at', '>=', now()->subDays(7))
            ->whereNull('parent_id')
            ->whereNotNull('order_id')
            ->whereHas('order')
            ->count();
        $newReviewsPrev = \App\Models\ProductReview::where('created_at', '>=', now()->subDays(14))
            ->where('created_at', '<', now()->subDays(7))
            ->whereNull('parent_id')
            ->whereNotNull('order_id')
            ->whereHas('order')
            ->count();
        $newReviewsChange = $newReviewsPrev > 0 ? (($newReviews - $newReviewsPrev) / $newReviewsPrev) * 100 : ($newReviews > 0 ? 100 : 0);

        // Total Pendapatan Perbulan
        $totalRevenue = \App\Models\Order::where('created_at', '>=', $thisMonth)
            ->where('payment_status', 'paid')
            ->sum('total_price');
        $totalRevenueLastMonth = \App\Models\Order::where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)
            ->where('payment_status', 'paid')
            ->sum('total_price');
        $totalRevenueChange = $totalRevenueLastMonth > 0 ? (($totalRevenue - $totalRevenueLastMonth) / $totalRevenueLastMonth) * 100 : ($totalRevenue > 0 ? 100 : 0);

        // Produk Terpopuler (berdasarkan jumlah terjual dari order yang sudah dibayar)
        // Hanya tampilkan produk yang sudah pernah terjual (total_sold > 0)
        $popularProducts = \App\Models\Product::with(['images'])
            ->select('products.*')
            ->selectRaw('COALESCE((
                SELECT SUM(order_details.qty)
                FROM order_details
                INNER JOIN orders ON order_details.order_id = orders.order_id
                WHERE order_details.product_id = products.product_id
                AND orders.payment_status = "paid"
            ), 0) as total_sold')
            ->selectRaw('COALESCE((
                SELECT AVG(product_reviews.rating)
                FROM product_reviews
                WHERE product_reviews.product_id = products.product_id
                AND product_reviews.parent_id IS NULL
                AND product_reviews.rating > 0
                AND product_reviews.order_id IS NOT NULL
            ), 0) as avg_rating')
            ->selectRaw('COALESCE((
                SELECT COUNT(*)
                FROM product_reviews
                WHERE product_reviews.product_id = products.product_id
                AND product_reviews.parent_id IS NULL
                AND product_reviews.rating > 0
                AND product_reviews.order_id IS NOT NULL
            ), 0) as total_reviews')
            ->havingRaw('total_sold > 0')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Data untuk chart penjualan produk (default 7 hari, bisa diubah via request)
        $dateRange = $request->get('chart_range', '7days');
        $productDateRange = $request->get('product_chart_range', $dateRange); // Default sama dengan chart penjualan
        $revenueDateRange = $request->get('revenue_chart_range', $dateRange); // Default sama dengan chart penjualan
        $days = 7;
        if ($dateRange === '3days') $days = 3;
        elseif ($dateRange === '30days') $days = 30;
        elseif ($dateRange === '3months') $days = 90;
        elseif ($dateRange === '1year') $days = 365;

        // Days untuk chart produk
        $productDays = 7;
        if ($productDateRange === '3days') $productDays = 3;
        elseif ($productDateRange === '30days') $productDays = 30;
        elseif ($productDateRange === '3months') $productDays = 90;
        elseif ($productDateRange === '1year') $productDays = 365;

        // Days untuk chart pendapatan
        $revenueDays = 7;
        if ($revenueDateRange === '3days') $revenueDays = 3;
        elseif ($revenueDateRange === '30days') $revenueDays = 30;
        elseif ($revenueDateRange === '3months') $revenueDays = 90;
        elseif ($revenueDateRange === '1year') $revenueDays = 365;

        $salesChartData = [];
        $salesChartDataCompare = [];
        $revenueChartData = [];
        $revenueChartDataCompare = [];

        // Data grafik penjualan
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $dateEnd = now()->subDays($i)->endOfDay();

            // Data penjualan (jumlah order)
            $salesCount = \App\Models\Order::whereBetween('created_at', [$date, $dateEnd])->count();
            $salesChartData[] = [
                'date' => $date->format($days <= 30 ? 'd M' : ($days <= 90 ? 'd/m' : 'M Y')),
                'sales' => $salesCount
            ];

            // Data untuk comparison (periode sebelumnya)
            $compareDate = $date->copy()->subDays($days);
            $compareDateEnd = $dateEnd->copy()->subDays($days);

            $compareSalesCount = \App\Models\Order::whereBetween('created_at', [$compareDate, $compareDateEnd])->count();
            $salesChartDataCompare[] = [
                'date' => $date->format($days <= 30 ? 'd M' : ($days <= 90 ? 'd/m' : 'M Y')),
                'sales' => $compareSalesCount
            ];
        }

        // Data grafik pendapatan (periode terpisah)
        for ($i = $revenueDays - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $dateEnd = now()->subDays($i)->endOfDay();

            // Data pendapatan (total revenue dari order yang sudah dibayar)
            $revenue = \App\Models\Order::whereBetween('created_at', [$date, $dateEnd])
                ->where('payment_status', 'paid')
                ->sum('total_price');
            $revenueChartData[] = [
                'date' => $date->format($revenueDays <= 30 ? 'd M' : ($revenueDays <= 90 ? 'd/m' : 'M Y')),
                'revenue' => $revenue ?? 0
            ];

            // Data untuk comparison (periode sebelumnya)
            $compareDate = $date->copy()->subDays($revenueDays);
            $compareDateEnd = $dateEnd->copy()->subDays($revenueDays);

            $compareRevenue = \App\Models\Order::whereBetween('created_at', [$compareDate, $compareDateEnd])
                ->where('payment_status', 'paid')
                ->sum('total_price');
            $revenueChartDataCompare[] = [
                'date' => $date->format($revenueDays <= 30 ? 'd M' : ($revenueDays <= 90 ? 'd/m' : 'M Y')),
                'revenue' => $compareRevenue ?? 0
            ];
        }

        // Data penjualan produk per produk (top 5) - menggunakan periode yang dipilih
        $productStartDate = now()->subDays($productDays)->startOfDay();
        $productEndDate = now()->endOfDay();

        $productSalesData = \App\Models\OrderDetail::select('products.name', \DB::raw('SUM(order_details.qty) as total_sold'))
            ->join('products', 'order_details.product_id', '=', 'products.product_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->whereBetween('orders.created_at', [$productStartDate, $productEndDate])
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.product_id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Data penjualan produk untuk periode sebelumnya (perbandingan)
        $productCompareStartDate = $productStartDate->copy()->subDays($productDays);
        $productCompareEndDate = $productStartDate->copy()->subDay()->endOfDay();

        $productSalesDataCompare = \App\Models\OrderDetail::select('products.name', \DB::raw('SUM(order_details.qty) as total_sold'))
            ->join('products', 'order_details.product_id', '=', 'products.product_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->whereBetween('orders.created_at', [$productCompareStartDate, $productCompareEndDate])
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.product_id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Gabungkan data untuk memastikan semua produk muncul di kedua periode
        $allProductNames = $productSalesData->pluck('name')->merge($productSalesDataCompare->pluck('name'))->unique();
        $productSalesDataMerged = [];
        $productSalesDataCompareMerged = [];

        foreach ($allProductNames as $productName) {
            $currentData = $productSalesData->firstWhere('name', $productName);
            $compareData = $productSalesDataCompare->firstWhere('name', $productName);

            $productSalesDataMerged[] = [
                'name' => $productName,
                'total_sold' => $currentData ? $currentData->total_sold : 0
            ];

            $productSalesDataCompareMerged[] = [
                'name' => $productName,
                'total_sold' => $compareData ? $compareData->total_sold : 0
            ];
        }

        // Sort by current period sales (descending) and limit to top 5
        usort($productSalesDataMerged, function($a, $b) {
            return $b['total_sold'] - $a['total_sold'];
        });
        $productSalesDataMerged = array_slice($productSalesDataMerged, 0, 5);

        // Get corresponding compare data for top 5 products
        $top5Names = collect($productSalesDataMerged)->pluck('name');
        $productSalesDataCompareMerged = collect($productSalesDataCompareMerged)
            ->whereIn('name', $top5Names)
            ->values()
            ->toArray();

        // Reorder compare data to match current data order
        $productSalesDataCompareMerged = collect($productSalesDataCompareMerged)
            ->sortBy(function($item) use ($top5Names) {
                return $top5Names->search($item['name']);
            })
            ->values()
            ->toArray();

        // Convert to collection for view
        $productSalesData = collect($productSalesDataMerged);
        $productSalesDataCompare = collect($productSalesDataCompareMerged);

        return view('dashboard.seller', compact(
            'salesToday',
            'salesTodayChange',
            'salesThisMonth',
            'salesMonthChange',
            'activeProducts',
            'activeProductsChange',
            'activeTools',
            'activeToolsChange',
            'newReviews',
            'newReviewsChange',
            'totalRevenue',
            'totalRevenueChange',
            'popularProducts',
            'salesChartData',
            'salesChartDataCompare',
            'revenueChartData',
            'revenueChartDataCompare',
            'productSalesData',
            'productSalesDataCompare',
            'dateRange',
            'productDateRange',
            'revenueDateRange'
        ));
    })->name('dashboard');

    // Reviews Page
    Route::get('/dashboard/reviews', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\ProductReview::with(['product.images', 'user', 'order', 'replies.user'])
            ->whereNull('parent_id') // Only top-level reviews
            ->whereNotNull('order_id') // Only reviews with valid order
            ->whereHas('order'); // Ensure order still exists

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('review', 'like', "%{$search}%")
                  ->orWhereHas('product', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Rating filter
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Product filter
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Status filter (has reply or not)
        if ($request->filled('status')) {
            if ($request->status === 'unreplied') {
                $query->doesntHave('replies');
            } elseif ($request->status === 'replied') {
                $query->has('replies');
            }
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at_desc');
        switch ($sortBy) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'rating_desc':
                $query->orderByDesc('rating')->orderByDesc('created_at');
                break;
            case 'rating_asc':
                $query->orderBy('rating', 'asc')->orderByDesc('created_at');
                break;
            case 'product_asc':
                $query->join('products', 'product_reviews.product_id', '=', 'products.product_id')
                      ->orderBy('products.name', 'asc')
                      ->orderByDesc('product_reviews.created_at')
                      ->select('product_reviews.*');
                break;
            case 'product_desc':
                $query->join('products', 'product_reviews.product_id', '=', 'products.product_id')
                      ->orderBy('products.name', 'desc')
                      ->orderByDesc('product_reviews.created_at')
                      ->select('product_reviews.*');
                break;
            case 'user_asc':
                $query->join('users', 'product_reviews.user_id', '=', 'users.user_id')
                      ->orderBy('users.name', 'asc')
                      ->orderByDesc('product_reviews.created_at')
                      ->select('product_reviews.*');
                break;
            case 'user_desc':
                $query->join('users', 'product_reviews.user_id', '=', 'users.user_id')
                      ->orderBy('users.name', 'desc')
                      ->orderByDesc('product_reviews.created_at')
                      ->select('product_reviews.*');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        $reviews = $query->paginate(20)->withQueryString();

        // Reload relationships after join to ensure eager loading works
        // Prevent duplicate replies by reloading properly
        $reviews->getCollection()->each(function($review) {
            $review->load(['product.images', 'user', 'order']);
            // Reload replies to ensure fresh data and no duplicates
            $review->load(['replies' => function($query) {
                $query->with('user')->orderBy('created_at', 'asc');
            }]);
            // Ensure no duplicate replies by using unique on review_id
            if ($review->relationLoaded('replies')) {
                $uniqueReplies = $review->replies->unique('review_id');
                $review->setRelation('replies', $uniqueReplies);
            }
        });

        // Get statistics
        $totalReviews = \App\Models\ProductReview::whereNull('parent_id')
            ->whereNotNull('order_id')
            ->whereHas('order')
            ->count();

        $unrepliedCount = \App\Models\ProductReview::whereNull('parent_id')
            ->whereNotNull('order_id')
            ->whereHas('order')
            ->doesntHave('replies')
            ->count();

        $avgRating = \App\Models\ProductReview::whereNull('parent_id')
            ->whereNotNull('order_id')
            ->whereHas('order')
            ->where('rating', '>', 0)
            ->avg('rating');

        $stats = [
            'total' => $totalReviews,
            'unreplied' => $unrepliedCount,
            'avg_rating' => round($avgRating, 1),
            'replied' => $totalReviews - $unrepliedCount
        ];

        // Get products list for filter
        $products = \App\Models\Product::whereHas('reviews', function($q) {
            $q->whereNull('parent_id')
              ->whereNotNull('order_id')
              ->whereHas('order');
        })->orderBy('name')->get(['product_id', 'name']);

        // Process review images
        $reviews->getCollection()->transform(function($review) {
            if ($review->image && is_array($review->image)) {
                $review->image_urls = array_map(function($img) {
                    if ($img && !preg_match('/^(https?:\/\/|data:)/', $img)) {
                        if (strpos($img, 'storage/reviews/') !== false) {
                            return asset($img);
                        } else {
                            return asset('storage/' . $img);
                        }
                    }
                    return $img;
                }, array_filter($review->image));
            }
            return $review;
        });

        return view('dashboard.reviews', compact('reviews', 'stats', 'products'));
    })->name('dashboard.reviews');

    // Reply to Review API (for dashboard)
    Route::post('/api/dashboard/reviews/{reviewId}/reply', function ($reviewId, \Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'reply' => 'required|string|max:1000'
        ]);

        $parentReview = \App\Models\ProductReview::where('review_id', $reviewId)
            ->whereNull('parent_id')
            ->firstOrFail();

        $user = Auth::user();

        $reply = \App\Models\ProductReview::create([
            'product_id' => $parentReview->product_id,
            'user_id' => $user->user_id,
            'parent_id' => $parentReview->review_id,
            'rating' => 0, // Replies don't have ratings
            'review' => $validated['reply']
        ]);

        $reply->load('user');

        return response()->json([
            'success' => true,
            'reply' => $reply
        ]);
    })->name('api.dashboard.review.reply');

    // Delete Reply API (for dashboard - allows any admin to delete admin replies)
    Route::delete('/api/dashboard/reviews/{reviewId}/reply/{replyId}', function ($reviewId, $replyId, \Illuminate\Http\Request $request) {
        $user = Auth::user();

        // Check if user is admin
        if (($user->role ?? 'visitor') !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: hanya admin yang dapat menghapus balasan'
            ], 403);
        }

        $parentReview = \App\Models\ProductReview::where('review_id', $reviewId)
            ->whereNull('parent_id')
            ->firstOrFail();

        $reply = \App\Models\ProductReview::where('review_id', $replyId)
            ->where('parent_id', $parentReview->review_id)
            ->firstOrFail();

        // Check if reply is from an admin (allow any admin to delete admin replies)
        $replyUser = $reply->user;
        if ($replyUser && ($replyUser->role ?? 'visitor') === 'admin') {
            // Allow any admin to delete admin replies
            $reply->delete();

            return response()->json([
                'success' => true,
                'message' => 'Balasan berhasil dihapus'
            ]);
        } else {
            // For non-admin replies, only allow the owner to delete
            if ($reply->user_id !== $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat menghapus balasan Anda sendiri'
                ], 403);
            }

            $reply->delete();

            return response()->json([
                'success' => true,
                'message' => 'Balasan berhasil dihapus'
            ]);
        }
    })->name('api.dashboard.review.delete-reply');

    // Bulk Reply to Reviews API
    Route::post('/api/dashboard/reviews/bulk-reply', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'review_ids' => 'required|array',
            'review_ids.*' => 'required|uuid',
            'reply' => 'required|string|max:1000'
        ]);

        $user = Auth::user();
        $reviews = \App\Models\ProductReview::whereIn('review_id', $validated['review_ids'])
            ->whereNull('parent_id')
            ->get();

        $replies = [];
        foreach ($reviews as $review) {
            $reply = \App\Models\ProductReview::create([
                'product_id' => $review->product_id,
                'user_id' => $user->user_id,
                'parent_id' => $review->review_id,
                'rating' => 0,
                'review' => $validated['reply']
            ]);
            $replies[] = $reply;
        }

        return response()->json([
            'success' => true,
            'message' => count($replies) . ' balasan berhasil dikirim',
            'count' => count($replies)
        ]);
    })->name('api.dashboard.reviews.bulk-reply');

    // Export Reviews to Excel/CSV
    Route::get('/dashboard/reviews/export', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\ProductReview::with(['product', 'user', 'order', 'replies'])
            ->whereNull('parent_id')
            ->whereNotNull('order_id')
            ->whereHas('order');

        // Apply same filters as main page
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('review', 'like', "%{$search}%")
                  ->orWhereHas('product', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'unreplied') {
                $query->doesntHave('replies');
            } elseif ($request->status === 'replied') {
                $query->has('replies');
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->orderByDesc('created_at')->get();

        $format = $request->get('format', 'csv');

        if ($format === 'excel' || $format === 'csv') {
            $filename = 'ulasan-produk-' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($reviews) {
                $file = fopen('php://output', 'w');

                // Add BOM for UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Header
                fputcsv($file, [
                    'Tanggal',
                    'Produk',
                    'Pelanggan',
                    'Rating',
                    'Ulasan',
                    'Status Balasan',
                    'Jumlah Balasan',
                    'Order ID'
                ]);

                // Data
                foreach ($reviews as $review) {
                    fputcsv($file, [
                        $review->created_at->format('Y-m-d H:i:s'),
                        $review->product->name ?? 'Produk Dihapus',
                        $review->user->name ?? 'User',
                        $review->rating,
                        $review->review ?? '',
                        $review->replies->count() > 0 ? 'Sudah Dibalas' : 'Belum Dibalas',
                        $review->replies->count(),
                        substr($review->order_id ?? '', 0, 8)
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } else {
            // PDF Export
            $now = now()->setTimezone('Asia/Jakarta');
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.reviews-export-pdf', [
                'reviews' => $reviews,
                'generatedAt' => $now->format('d/m/Y H:i:s') . ' WIB'
            ]);

            return $pdf->download('ulasan-produk-' . $now->format('Y-m-d') . '.pdf');
        }
    })->name('dashboard.reviews.export');

    Route::get('/dashboard/products', function () {
        $products = Product::with(['images', 'reviews.order'])->orderByDesc('created_at')->get();
        return view('dashboard.products', compact('products'));
    })->name('dashboard.products');
    Route::get('/dashboard/tools', function () { return view('dashboard.tools'); })->name('dashboard.tools');
    Route::get('/dashboard/tools/monitoring', function () { return view('dashboard.tools-monitoring'); })->name('dashboard.tools.monitoring');
    Route::get('/dashboard/tools/information', function () { return view('dashboard.tools-information'); })->name('dashboard.tools.information');

    // Export routes
    Route::get('/dashboard/export/pdf', [\App\Http\Controllers\ExportController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/dashboard/export/csv', [\App\Http\Controllers\ExportController::class, 'exportCsv'])->name('export.csv');
    Route::get('/dashboard/sales', function (\Illuminate\Http\Request $request) {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // Statistik ringkas
        $salesStats = [
            'today_revenue' => \App\Models\Order::whereDate('created_at', $today)
                ->where('payment_status', 'paid')
                ->sum('total_price'),
            'today_orders' => \App\Models\Order::whereDate('created_at', $today)->count(),
            'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
            'shipped_orders' => \App\Models\Order::where('status', 'dikirim')->count(),
            'month_revenue' => \App\Models\Order::where('created_at', '>=', $thisMonth)
                ->where('payment_status', 'paid')
                ->sum('total_price')
        ];
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
        return view('dashboard.sales', compact('orders', 'filter', 'salesStats'));
    })->name('dashboard.sales');

    // Export Sales to Excel/PDF
    Route::get('/dashboard/sales/export', function (\Illuminate\Http\Request $request) {
        $filter = $request->get('filter', 'all');
        $query = \App\Models\Order::with(['orderDetail.product', 'orderDetail'])
            ->orderByDesc('created_at');

        if ($filter === 'dikirim') {
            $query->where('status', 'dikirim');
        } elseif ($filter === 'selesai') {
            $query->where('status', 'selesai');
        }

        $orders = $query->get();
        $format = $request->get('format', 'excel');
        $now = now()->setTimezone('Asia/Jakarta');

        if ($format === 'excel' || $format === 'csv') {
            $filename = 'laporan-penjualan-' . $now->format('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($orders, $now) {
                $file = fopen('php://output', 'w');

                // Add BOM for UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Header
                fputcsv($file, [
                    'Tanggal & Waktu (WIB)',
                    'Order ID',
                    'Nama Pembeli',
                    'No. Telepon',
                    'Alamat',
                    'Produk',
                    'Jumlah',
                    'Harga Satuan',
                    'Subtotal',
                    'Total Harga',
                    'Status',
                    'Status Pembayaran',
                    'Kurir',
                    'Resi'
                ]);

                // Data
                foreach ($orders as $order) {
                    $orderDate = $order->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s');
                    $orderId = substr($order->order_id, 0, 8);

                    if ($order->orderDetail->count() > 0) {
                        foreach ($order->orderDetail as $detail) {
                            fputcsv($file, [
                                $orderDate,
                                $orderId,
                                $order->buyer_name,
                                $order->buyer_phone,
                                $order->buyer_address,
                                $detail->product->name ?? 'Produk Dihapus',
                                $detail->qty,
                                $detail->price,
                                $detail->qty * $detail->price,
                                $order->orderDetail->count() > 1 && $detail !== $order->orderDetail->last() ? '' : $order->total_price,
                                ucfirst($order->status ?? 'pending'),
                                ucfirst($order->payment_status ?? 'pending'),
                                $order->shipping_service ?? '-',
                                $order->tracking_number ?? '-'
                            ]);
                        }
                    } else {
                        fputcsv($file, [
                            $orderDate,
                            $orderId,
                            $order->buyer_name,
                            $order->buyer_phone,
                            $order->buyer_address,
                            '-',
                            '-',
                            '-',
                            '-',
                            $order->total_price,
                            ucfirst($order->status ?? 'pending'),
                            ucfirst($order->payment_status ?? 'pending'),
                            $order->shipping_service ?? '-',
                            $order->tracking_number ?? '-'
                        ]);
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } else {
            // PDF Export
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.sales-export-pdf', [
                'orders' => $orders,
                'filter' => $filter,
                'generatedAt' => $now->format('d/m/Y H:i:s') . ' WIB'
            ]);

            return $pdf->download('laporan-penjualan-' . $now->format('Y-m-d') . '.pdf');
        }
    })->name('dashboard.sales.export');
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

            // Auto-generate sort_order if not provided or is 0
            if (empty($validated['sort_order']) || $validated['sort_order'] == 0) {
                $maxOrder = \App\Models\HomepageCategory::max('sort_order') ?? 0;
                $validated['sort_order'] = $maxOrder + 1;
            }

            $category = \App\Models\HomepageCategory::create([
                'category_id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'image_url' => $imageUrl,
                'link_url' => null, // Tidak digunakan lagi, kategori digunakan untuk filter
                'sort_order' => $validated['sort_order'],
                'is_active' => $validated['is_active'] ?? true
            ]);

            // Reorder all categories to ensure sequential order
            $allCategories = \App\Models\HomepageCategory::orderBy('sort_order')->orderBy('created_at')->get();
            $order = 1;
            foreach ($allCategories as $cat) {
                if ($cat->sort_order != $order) {
                    $cat->update(['sort_order' => $order]);
                }
                $order++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dibuat',
                'category' => $category->fresh()
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

            // Auto-generate sort_order if not provided or is 0
            if (empty($validated['sort_order']) || $validated['sort_order'] == 0) {
                $maxOrder = \App\Models\HomepageCategory::where('category_id', '!=', $id)->max('sort_order') ?? 0;
                $validated['sort_order'] = $maxOrder + 1;
            }

            // Remove link_url from update (not used anymore)
            unset($validated['link_url']);
            $validated['link_url'] = null;

            $category->update($validated);

            // Reorder all categories to ensure sequential order
            $allCategories = \App\Models\HomepageCategory::orderBy('sort_order')->orderBy('created_at')->get();
            $order = 1;
            foreach ($allCategories as $cat) {
                if ($cat->sort_order != $order) {
                    $cat->update(['sort_order' => $order]);
                }
                $order++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui',
                'category' => $category->fresh()
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

            // Reorder remaining categories
            $categories = \App\Models\HomepageCategory::orderBy('sort_order')->orderBy('created_at')->get();
            $order = 1;
            foreach ($categories as $cat) {
                if ($cat->sort_order != $order) {
                    $cat->update(['sort_order' => $order]);
                }
                $order++;
            }

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
        $articles = \App\Models\Article::with(['user', 'categories'])->orderByDesc('created_at')->get();
        $categories = \App\Models\ArticleCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        return view('dashboard.articles', compact('articles', 'categories'));
    })->name('dashboard.articles');

    Route::get('/dashboard/articles/{id}', function ($id) {
        $article = \App\Models\Article::with(['user', 'categories'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'article' => $article
        ]);
    });

    Route::post('/dashboard/articles', function (\Illuminate\Http\Request $request) {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'category_ids' => 'required|string', // JSON string from FormData
                'content' => 'required|string',
                'featured_image_url' => 'nullable|url',
                'featured_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);

            $categoryIds = json_decode($validated['category_ids'], true);
            if (!is_array($categoryIds) || count($categoryIds) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih minimal satu kategori'
                ], 422);
            }

            // Handle image upload
            $featuredImage = null;
            if ($request->hasFile('featured_image_file')) {
                $file = $request->file('featured_image_file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $directory = public_path('storage/articles');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $file->move($directory, $filename);
                $featuredImage = 'storage/articles/' . $filename;
            } elseif ($request->filled('featured_image_url')) {
                $featuredImage = $validated['featured_image_url'];
            }

            $article = \App\Models\Article::create([
                'article_id' => (string) \Illuminate\Support\Str::uuid(),
                'author_id' => Auth::user()->user_id,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'featured_image' => $featuredImage
            ]);

            // Attach categories
            $article->categories()->attach($categoryIds);

            // Load user relationship to ensure author name is available
            $article->load(['user', 'categories']);

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
                'category_ids' => 'required|string', // JSON string from FormData
                'content' => 'required|string',
                'featured_image_url' => 'nullable|url',
                'featured_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);

            $categoryIds = json_decode($validated['category_ids'], true);
            if (!is_array($categoryIds) || count($categoryIds) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih minimal satu kategori'
                ], 422);
            }

            // Handle image upload
            $featuredImage = $article->featured_image; // Keep existing if not updated
            if ($request->hasFile('featured_image_file')) {
                // Delete old image if exists
                if ($article->featured_image && (strpos($article->featured_image, 'storage/') === 0 || strpos($article->featured_image, '/storage/') === 0)) {
                    $oldPath = strpos($article->featured_image, '/') === 0 ? public_path($article->featured_image) : public_path($article->featured_image);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $file = $request->file('featured_image_file');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $directory = public_path('storage/articles');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $file->move($directory, $filename);
                $featuredImage = 'storage/articles/' . $filename;
            } elseif ($request->filled('featured_image_url')) {
                $featuredImage = $validated['featured_image_url'];
            }

            $article->update([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'featured_image' => $featuredImage
            ]);

            // Sync categories
            $article->categories()->sync($categoryIds);

            // Load user relationship to ensure author name is available
            $article->load(['user', 'categories']);

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
                $imageUrl = $validated['image'];

                // Check if it's a base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $imageUrl, $matches)) {
                    // Decode base64 image
                    $imageData = substr($imageUrl, strpos($imageUrl, ',') + 1);
                    $imageData = base64_decode($imageData);
                    $extension = $matches[1] ?? 'jpg';

                    // Generate unique filename
                    $filename = Str::uuid() . '.' . $extension;
                    $destinationPath = storage_path('app/public/products');

                    // Create directory if it doesn't exist
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    // Save file
                    file_put_contents($destinationPath . '/' . $filename, $imageData);

                    // Use asset URL
                    $imageUrl = asset('storage/products/' . $filename);
                }

                \App\Models\ProductImage::create([
                    'product_image_id' => (string) Str::uuid(),
                    'product_id' => $product->product_id,
                    'name' => $product->name . '.jpg',
                    'url' => $imageUrl,
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
                $imageUrl = $validated['image'];

                // Check if it's a base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $imageUrl, $matches)) {
                    // Decode base64 image
                    $imageData = substr($imageUrl, strpos($imageUrl, ',') + 1);
                    $imageData = base64_decode($imageData);
                    $extension = $matches[1] ?? 'jpg';

                    // Generate unique filename
                    $filename = Str::uuid() . '.' . $extension;
                    $destinationPath = storage_path('app/public/products');

                    // Create directory if it doesn't exist
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    // Delete old image file if exists
                    $existingImage = $product->images->first();
                    if ($existingImage && $existingImage->url && strpos($existingImage->url, 'storage/products/') !== false) {
                        $oldFilename = basename($existingImage->url);
                        $oldPath = $destinationPath . '/' . $oldFilename;
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    // Save file
                    file_put_contents($destinationPath . '/' . $filename, $imageData);

                    // Use asset URL
                    $imageUrl = asset('storage/products/' . $filename);
                }

                $existingImage = $product->images->first();
                if ($existingImage) {
                    $existingImage->update(['url' => $imageUrl]);
                } else {
                    \App\Models\ProductImage::create([
                        'product_image_id' => (string) Str::uuid(),
                        'product_id' => $product->product_id,
                        'name' => $product->name . '.jpg',
                        'url' => $imageUrl,
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
    // Validate Payment (Admin confirms payment received)
    Route::post('/dashboard/orders/{id}/validate-payment', function ($id) {
        try {
            $order = \App\Models\Order::where('order_id', $id)->firstOrFail();

            if ($order->payment_status !== 'processing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak dalam status proses pembayaran'
                ], 400);
            }

            // PERBAIKAN: Kurangi stok saat payment divalidasi (hanya sekali)
            // Cek apakah payment_status sudah pernah "paid" sebelumnya untuk mencegah duplikasi
            // Jika sudah pernah "paid", tidak perlu mengurangi stok lagi
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah divalidasi sebelumnya'
                ], 400);
            }

            // Load order details dengan product terlebih dahulu
            $order->load('orderDetail.product');

            // Cek stok untuk semua produk sebelum mengurangi
            foreach ($order->orderDetail as $detail) {
                $product = $detail->product;
                if ($product && $product->stock < $detail->qty) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak mencukupi untuk produk: ' . $product->name . '. Stok tersedia: ' . $product->stock
                    ], 400);
                }
            }

            // Jika semua stok cukup, kurangi stok untuk semua produk (HANYA SEKALI)
            foreach ($order->orderDetail as $detail) {
                $product = $detail->product;
                if ($product) {
                    // Kurangi stok (hanya sekali, saat payment divalidasi)
                    $product->stock -= $detail->qty;
                    $product->save();
                }
            }

            // Setelah stok berhasil dikurangi, update payment status
            $order->payment_status = 'paid';
            $order->paid_at = now();
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil divalidasi. Status pesanan sekarang "Lunas" dan stok telah dikurangi.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error validating payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memvalidasi pembayaran: ' . $e->getMessage()
            ], 500);
        }
    })->name('dashboard.orders.validate-payment');

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
                    'message' => 'Pesanan belum dibayar atau pembayaran belum divalidasi. Tidak dapat mengirim pesanan sebelum pembayaran divalidasi.'
                ], 400);
            }

            // Generate tracking number when shipping (only if not exists)
            if (empty($order->tracking_number)) {
                $order->tracking_number = generateTrackingNumber($order->shipping_service);
            }

            // Update order status to "dikirim" (not "selesai" yet)
            $order->status = 'dikirim';
            $order->save();

            // PERBAIKAN: Stok TIDAK dikurangi di sini karena sudah dikurangi saat checkout
            // Stok sudah dikurangi saat checkout (baris 2080-2082 di cart.checkout route)
            // Jika dikurangi lagi di sini, stok akan berkurang 2 kali (double reduction)
            // Hanya update status order, tidak perlu mengurangi stok lagi

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dikirim. Nomor resi: ' . $order->tracking_number
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
    $query = \App\Models\Article::with(['user', 'categories']);

    // Filter by category slug
    if (request('category')) {
        $categorySlug = request('category');
        $query->whereHas('categories', function($q) use ($categorySlug) {
            $q->where('slug', $categorySlug)->where('is_active', true);
        });
    }

    $articles = $query->orderByDesc('created_at')->paginate(12);
    $categories = \App\Models\ArticleCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    return view('articles', compact('articles', 'categories'));
})->name('articles');

// Article Detail Page
Route::get('/articles/{id}', function ($id) {
    $article = \App\Models\Article::with(['user', 'categories', 'comments.user', 'comments.replies.user'])->findOrFail($id);
    return view('article-detail', compact('article'));
})->name('article.detail');

// Article Comments API
Route::middleware('auth.session')->group(function() {
    // Post comment
    Route::post('/api/articles/{id}/comments', function ($id, \Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|uuid|exists:article_comments,comment_id'
        ]);

        $article = \App\Models\Article::findOrFail($id);

        $comment = \App\Models\Comment::create([
            'article_id' => $article->article_id,
            'user_id' => Auth::user()->user_id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content']
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => $comment
        ]);
    })->name('api.article.comment');

    // Delete comment (only own comment)
    Route::delete('/api/comments/{id}', function ($id) {
        $comment = \App\Models\Comment::where('comment_id', $id)
            ->where('user_id', Auth::user()->user_id)
            ->firstOrFail();

        $comment->delete();

        return response()->json(['success' => true]);
    })->name('api.comment.delete');
});

Route::get('/marketplace', function () {
    return view('marketplace');
})->name('marketplace');

Route::middleware('auth.session')->group(function(){
    Route::get('/profile', function () {
        $returnTo = request()->query('return_to');
        $checkoutCartIds = request()->query('items', []);
        return view('profile', compact('returnTo', 'checkoutCartIds'));
    })->name('profile');
    Route::post('/profile/update', function (\Illuminate\Http\Request $request) {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500'
        ]);
        $user->update($data);

        // Check jika user datang dari checkout
        $returnTo = $request->input('return_to');
        $checkoutCartIds = $request->input('checkout_cart_ids');

        if ($returnTo === 'checkout' && !empty($checkoutCartIds)) {
            // Parse checkout_cart_ids dari JSON string
            $itemsParam = [];
            if (is_string($checkoutCartIds)) {
                $decoded = json_decode($checkoutCartIds, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $itemsParam = $decoded;
                }
            } elseif (is_array($checkoutCartIds)) {
                $itemsParam = $checkoutCartIds;
            }

            if (!empty($itemsParam) && is_array($itemsParam)) {
                // Redirect kembali ke checkout dengan items
                return redirect()->route('checkout', ['items' => $itemsParam])->with('success','Profil berhasil diperbarui');
            }
        }

        return redirect()->route('profile')->with('success','Profil berhasil diperbarui');
    })->name('profile.update');

    // Cart Routes
    // Cart Page
    Route::get('/cart', function () {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk melihat keranjang');
        }

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

        // Refresh product to get latest stock
        $product->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui',
            'item_total' => $product->price * $validated['qty'],
            'subtotal' => $subtotal,
            'stock' => $product->stock, // Return latest stock
            'qty' => $validated['qty'] // Return updated qty
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
        // PERBAIKAN: Stok TIDAK dikurangi di sini (saat checkout)
        // Stok akan dikurangi saat payment status menjadi "paid" (setelah admin validasi pembayaran)
        // Ini mencegah stok berkurang jika pembayaran gagal atau order dibatalkan
        foreach ($cartItems as $item) {
            \App\Models\OrderDetail::create([
                'order_detail_id' => (string) Str::uuid(),
                'order_id' => $order->order_id,
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->product->price
            ]);

            // TIDAK mengurangi stok di sini - akan dikurangi saat payment divalidasi
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

    // Checkout Page (GET) - Show checkout form
    Route::get('/checkout', function () {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        $user = Auth::user();
        $selectedCartIds = request()->get('items', []);

        if (empty($selectedCartIds)) {
            return redirect()->route('cart')->with('error', 'Pilih produk terlebih dahulu');
        }

        $cartItems = \App\Models\Cart::with(['product.images'])
            ->where('user_id', $user->user_id)
            ->whereIn('cart_id', $selectedCartIds)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Tidak ada produk yang dipilih');
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->product->price * $item->qty;
        }

        // Shipping cost (default)
        $shippingCost = 10000;
        $total = $subtotal + $shippingCost;

        return view('store.checkout', compact('cartItems', 'subtotal', 'shippingCost', 'total', 'selectedCartIds'));
    })->middleware('auth.session')->name('checkout');

    // Buyer Orders Page
    Route::get('/orders', function () {
        $user = Auth::user();
        $orders = \App\Models\Order::with(['orderDetail.product.images', 'orderDetail.product.reviews' => function($q) use ($user) {
            $q->where('user_id', $user->user_id);
        }])
            ->where('user_id', $user->user_id)
            ->orderByDesc('created_at')
            ->get();
        return view('store.orders', compact('orders'));
    })->name('orders');

    // Product Review API
    Route::post('/api/products/{id}/reviews', function ($id, \Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'order_id' => 'required|uuid|exists:orders,order_id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'removed_images' => 'nullable|array'
        ]);

        $product = \App\Models\Product::findOrFail($id);
        $user = Auth::user();

        // Check if order belongs to user and contains this product
        $order = \App\Models\Order::where('order_id', $validated['order_id'])
            ->where('user_id', $user->user_id)
            ->where('status', 'selesai')
            ->firstOrFail();

        $orderDetail = \App\Models\OrderDetail::where('order_id', $order->order_id)
            ->where('product_id', $product->product_id)
            ->firstOrFail();

        // Check if review already exists
        $existingReview = \App\Models\ProductReview::where('product_id', $product->product_id)
            ->where('user_id', $user->user_id)
            ->where('order_id', $order->order_id)
            ->first();

        // Handle removed images
        $removedImages = $validated['removed_images'] ?? [];
        $finalImages = [];

        // If editing existing review, start with existing images
        if ($existingReview && $existingReview->image) {
            $oldImages = is_array($existingReview->image) ? $existingReview->image : [$existingReview->image];
            // Keep images that are not in removed_images list
            foreach ($oldImages as $oldImage) {
                if ($oldImage && !in_array($oldImage, $removedImages)) {
                    $finalImages[] = $oldImage;
                } else if ($oldImage && in_array($oldImage, $removedImages)) {
                    // Delete removed image from storage
                    $oldPath = str_replace('storage/', '', $oldImage);
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                    }
                }
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = \Illuminate\Support\Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('reviews', $filename, 'public');
                $finalImages[] = 'storage/' . $path;
            }
        }

        $updateData = [
                'rating' => $validated['rating'],
                'review' => $validated['review']
        ];

        // Update images (can be empty array if all removed)
        $updateData['image'] = !empty($finalImages) ? $finalImages : null;

        if ($existingReview) {
            $existingReview->update($updateData);
            $review = $existingReview;
        } else {
            $review = \App\Models\ProductReview::create([
                'product_id' => $product->product_id,
                'user_id' => $user->user_id,
                'order_id' => $order->order_id,
                'rating' => $validated['rating'],
                'review' => $validated['review'],
                'image' => !empty($finalImages) ? $finalImages : null
            ]);
        }

        $review->load('user');

        return response()->json([
            'success' => true,
            'review' => $review
        ]);
    })->name('api.product.review');

    // Reply to Review API
    Route::post('/api/products/{id}/reviews/{reviewId}/reply', function ($id, $reviewId, \Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'reply' => 'required|string|max:1000'
        ]);

        $product = \App\Models\Product::findOrFail($id);
        $parentReview = \App\Models\ProductReview::where('review_id', $reviewId)
            ->where('product_id', $product->product_id)
            ->firstOrFail();

        $user = Auth::user();

        $reply = \App\Models\ProductReview::create([
            'product_id' => $product->product_id,
            'user_id' => $user->user_id,
            'parent_id' => $parentReview->review_id,
            'rating' => 0, // Replies don't have ratings
            'review' => $validated['reply']
        ]);

        $reply->load('user');

        return response()->json([
            'success' => true,
            'reply' => $reply
        ]);
    })->name('api.product.review.reply');

    // Delete Reply API
    Route::delete('/api/products/{id}/reviews/{reviewId}/reply/{replyId}', function ($id, $reviewId, $replyId, \Illuminate\Http\Request $request) {
        $product = \App\Models\Product::findOrFail($id);
        $parentReview = \App\Models\ProductReview::where('review_id', $reviewId)
            ->where('product_id', $product->product_id)
            ->firstOrFail();

        $user = Auth::user();

        $reply = \App\Models\ProductReview::where('review_id', $replyId)
            ->where('parent_id', $parentReview->review_id)
            ->where('user_id', $user->user_id) // Only allow user to delete their own reply
            ->firstOrFail();

        $reply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil dihapus'
        ]);
    })->name('api.product.review.reply.delete');
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

        // Check if it's a connection error to ML service
        $isMLConnectionError = (
            strpos($e->getMessage(), 'ML Service') !== false ||
            strpos($e->getMessage(), 'Connection') !== false ||
            strpos($e->getMessage(), 'timeout') !== false ||
            strpos($e->getMessage(), 'Failed to connect') !== false
        );

        return response()->json([
            'error' => 'Internal server error',
            'message' => $e->getMessage(),
            'ml_connection_error' => $isMLConnectionError,
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
                'ml_connected' => false,
                'error' => true,
                'error_type' => $isMLConnectionError ? 'ml_connection' : 'general'
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
