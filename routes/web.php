<?php

use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    $products = Product::with('categories')->where('status', '!=', 'arsip')->latest()->take(16)->get();
    $categories = Category::orderBy('sort_order')->get();
    $banners = Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    return view('home.index', compact('products', 'categories', 'banners'));
})->name('home');
Route::get('/produk', function () {
    $products = Product::with('categories')->where('status', '!=', 'arsip')->latest()->paginate(12)->withQueryString();
    $categories = Category::orderBy('sort_order')->get();
    return view('home.produk', compact('products', 'categories'));
})->name('produk');

Route::get('/produk/{slug}', function (string $slug) {
    $product = Product::with('categories')->where('slug', $slug)->firstOrFail();
    return view('home.produk-detail', ['product' => $product]);
})->name('produk.detail');

Route::get('/keranjang', fn () => view('home.keranjang'))->name('keranjang');

Route::post('/keranjang/add', function (Request $request) {
    if (! session('auth_user')) {
        return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk menambahkan ke keranjang.']);
    }

    $data = $request->validate([
        'slug' => 'required|string|exists:products,slug',
        'qty'  => 'required|integer|min:1|max:99',
    ]);

    $cart = session('cart', []);
    $cart[$data['slug']] = min(99, (int) ($cart[$data['slug']] ?? 0) + $data['qty']);
    session(['cart' => $cart]);

    return redirect()->back()->with('status', 'Produk ditambahkan ke keranjang.');
})->name('keranjang.add');

Route::patch('/keranjang/{slug}', function (Request $request, string $slug) {
    $cart = session('cart', []);
    if (! isset($cart[$slug])) {
        abort(404);
    }
    $qty = (int) $request->input('qty', 1);
    $cart[$slug] = max(1, min(99, $qty));
    session(['cart' => $cart]);
    return redirect()->route('keranjang')->with('status', 'Jumlah produk diperbarui.');
})->name('keranjang.update');

Route::delete('/keranjang/{slug}', function (string $slug) {
    $cart = session('cart', []);
    unset($cart[$slug]);
    session(['cart' => $cart]);
    return redirect()->route('keranjang')->with('status', 'Produk dihapus dari keranjang.');
})->name('keranjang.remove');

Route::post('/keranjang/clear', function () {
    session()->forget('cart');
    return redirect()->route('keranjang')->with('status', 'Keranjang dikosongkan.');
})->name('keranjang.clear');

// Step 1: dari keranjang — stash item terpilih ke session, redirect ke halaman bayar
Route::post('/checkout', function (Request $request) {
    $authUser = session('auth_user');
    if (! $authUser) {
        return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk checkout.']);
    }

    $data = $request->validate([
        'selected'   => 'required|array|min:1',
        'selected.*' => 'required|string',
    ]);

    $cart = session('cart', []);
    $selectedSlugs = array_values(array_intersect($data['selected'], array_keys($cart)));
    if (empty($selectedSlugs)) {
        return redirect()->route('keranjang')->withErrors(['selected' => 'Produk yang dipilih tidak ada di keranjang.']);
    }

    session(['checkout_pending' => $selectedSlugs]);
    return redirect()->route('checkout.show');
})->name('checkout');

// Step 2: halaman bayar — tampilkan ringkasan + form alamat + pilih metode bayar
Route::get('/checkout', function () {
    $authUser = session('auth_user');
    if (! $authUser) {
        return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk checkout.']);
    }

    $pending = session('checkout_pending', []);
    if (empty($pending)) {
        return redirect()->route('keranjang')->with('status', 'Pilih produk dari keranjang untuk checkout.');
    }

    $cart = session('cart', []);
    $slugs = array_values(array_intersect($pending, array_keys($cart)));
    if (empty($slugs)) {
        session()->forget('checkout_pending');
        return redirect()->route('keranjang')->withErrors(['selected' => 'Item checkout sudah tidak tersedia di keranjang.']);
    }

    $products = Product::whereIn('slug', $slugs)->get()->keyBy('slug');
    $items = [];
    $subtotal = 0;
    foreach ($slugs as $slug) {
        $p = $products[$slug] ?? null;
        if (! $p) continue;
        $qty = (int) $cart[$slug];
        $items[] = [
            'slug'      => $p->slug,
            'name'      => $p->name,
            'qty'       => $qty,
            'price'     => (int) $p->price,
            'image_url' => $p->image_url,
            'subtotal'  => $p->price * $qty,
        ];
        $subtotal += $p->price * $qty;
    }

    $total = $subtotal;

    return view('home.checkout', compact('items', 'subtotal', 'total'));
})->name('checkout.show');

// Step 3: konfirmasi — buat Order + OrderItems, bersihkan cart/pending, redirect ke halaman sukses
Route::post('/checkout/confirm', function (Request $request) {
    $authUser = session('auth_user');
    if (! $authUser) {
        return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk checkout.']);
    }

    $data = $request->validate([
        'recipient_name'   => 'required|string|max:100',
        'recipient_phone'  => 'required|string|max:25',
        'shipping_address' => 'required|string|max:500',
        'note'             => 'nullable|string|max:300',
    ]);

    $pending = session('checkout_pending', []);
    $cart = session('cart', []);
    $slugs = array_values(array_intersect($pending, array_keys($cart)));
    if (empty($slugs)) {
        return redirect()->route('keranjang')->withErrors(['selected' => 'Item checkout sudah tidak tersedia.']);
    }

    $products = Product::whereIn('slug', $slugs)->get()->keyBy('slug');
    $items = [];
    $subtotal = 0;
    foreach ($slugs as $slug) {
        $p = $products[$slug] ?? null;
        if (! $p) continue;
        $qty = (int) $cart[$slug];
        $items[] = [
            'product_id'   => $p->id,
            'product_name' => $p->name,
            'qty'          => $qty,
            'price'        => (int) $p->price,
        ];
        $subtotal += $p->price * $qty;
    }

    $total = $subtotal;

    $user = User::find($authUser['id'] ?? null);
    $invoice = 'INV-' . now()->format('Ymd') . '-' . str_pad((string) (\App\Models\Order::count() + 1), 4, '0', STR_PAD_LEFT);

    $noteParts = array_filter([
        'Penerima: ' . $data['recipient_name'] . ' (' . $data['recipient_phone'] . ')',
        $data['note'] ? 'Catatan: ' . $data['note'] : null,
    ]);

    $order = \App\Models\Order::create([
        'invoice_number'   => $invoice,
        'user_id'          => $user?->id,
        'customer_name'    => $authUser['name'],
        'customer_email'   => $authUser['email'],
        'total'            => $total,
        'payment_method'   => 'Midtrans',
        'status'           => 'menunggu_bayar',
        'shipping_address' => $data['shipping_address'],
        'note'             => implode(' · ', $noteParts),
    ]);

    foreach ($items as $item) {
        $order->items()->create($item);
    }

    // Generate Midtrans Snap token
    $snapToken = null;
    $snapError = null;
    try {
        \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized  = config('services.midtrans.is_sanitized');
        \Midtrans\Config::$is3ds        = config('services.midtrans.is_3ds');
        // Workaround SSL untuk environment lokal (Windows/XAMPP tanpa CA bundle)
        if (app()->environment('local')) {
            \Midtrans\Config::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER     => [], // required by midtrans-php (diakses tanpa isset)
            ];
        }

        $itemDetails = [];
        foreach ($items as $it) {
            $itemDetails[] = [
                'id'       => (string) ($it['product_id'] ?? $it['product_name']),
                'price'    => (int) $it['price'],
                'quantity' => (int) $it['qty'],
                'name'     => \Illuminate\Support\Str::limit($it['product_name'], 50, ''),
            ];
        }

        [$firstName, $lastName] = array_pad(explode(' ', trim($data['recipient_name']), 2), 2, '');

        $payload = [
            'transaction_details' => [
                'order_id'     => $order->invoice_number,
                'gross_amount' => (int) $total,
            ],
            'item_details'        => $itemDetails,
            'customer_details'    => [
                'first_name'   => $firstName ?: $authUser['name'],
                'last_name'    => $lastName,
                'email'        => $authUser['email'],
                'phone'        => $data['recipient_phone'],
                'shipping_address' => [
                    'first_name' => $firstName ?: $authUser['name'],
                    'last_name'  => $lastName,
                    'phone'      => $data['recipient_phone'],
                    'address'    => \Illuminate\Support\Str::limit($data['shipping_address'], 200, ''),
                ],
            ],
            'callbacks' => [
                'finish' => route('pesanan.sukses', $order->invoice_number),
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($payload);
        $order->snap_token = $snapToken;
        $order->save();
    } catch (\Throwable $e) {
        Log::error('Midtrans snap token error', ['invoice' => $order->invoice_number, 'err' => $e->getMessage()]);
        $snapError = $e->getMessage();
    }

    // Bersihkan cart dan checkout pending
    foreach ($slugs as $slug) {
        unset($cart[$slug]);
    }
    session(['cart' => $cart]);
    session()->forget('checkout_pending');

    $redirectUrl = route('pesanan.sukses', $order->invoice_number);

    // AJAX / Midtrans flow: kembalikan JSON supaya front-end bisa buka Snap langsung
    if ($request->expectsJson() || $request->ajax()) {
        if (! $snapToken) {
            return response()->json([
                'error'        => 'Gagal menyiapkan pembayaran Midtrans. ' . ($snapError ? '(' . $snapError . ')' : ''),
                'redirect_url' => $redirectUrl,
            ], 500);
        }
        return response()->json([
            'invoice'      => $order->invoice_number,
            'snap_token'   => $snapToken,
            'redirect_url' => $redirectUrl,
        ]);
    }

    return redirect($redirectUrl);
})->name('checkout.confirm');

// Midtrans Snap: regenerate token (kalau hilang / kadaluarsa)
Route::post('/pesanan/{invoice}/midtrans/token', function (string $invoice) {
    $order = \App\Models\Order::where('invoice_number', $invoice)->firstOrFail();
    $authUser = session('auth_user');
    if (! $authUser || ($order->customer_email !== $authUser['email'] && ($authUser['role'] ?? null) !== 'admin')) {
        abort(403);
    }
    if ($order->payment_method !== 'Midtrans' || $order->status !== 'menunggu_bayar') {
        return response()->json(['error' => 'Pesanan tidak dapat dibayar ulang.'], 422);
    }

    try {
        \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized  = config('services.midtrans.is_sanitized');
        \Midtrans\Config::$is3ds        = config('services.midtrans.is_3ds');
        if (app()->environment('local')) {
            \Midtrans\Config::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ];
        }

        $itemDetails = [];
        foreach ($order->items as $it) {
            $itemDetails[] = [
                'id'       => (string) ($it->product_id ?? $it->product_name),
                'price'    => (int) $it->price,
                'quantity' => (int) $it->qty,
                'name'     => \Illuminate\Support\Str::limit($it->product_name, 50, ''),
            ];
        }
        $payload = [
            'transaction_details' => [
                'order_id'     => $order->invoice_number . '-' . time(),
                'gross_amount' => (int) $order->total,
            ],
            'item_details'        => $itemDetails,
            'customer_details'    => [
                'first_name' => $order->customer_name,
                'email'      => $order->customer_email,
            ],
            'callbacks' => ['finish' => route('pesanan.sukses', $order->invoice_number)],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($payload);
        $order->snap_token = $snapToken;
        $order->save();

        return response()->json(['snap_token' => $snapToken]);
    } catch (\Throwable $e) {
        Log::error('Midtrans re-token error', ['invoice' => $invoice, 'err' => $e->getMessage()]);
        return response()->json(['error' => 'Gagal memuat pembayaran. Coba lagi.'], 500);
    }
})->name('midtrans.token');

// Midtrans Webhook — dipanggil oleh server Midtrans ketika status berubah
Route::post('/midtrans/notification', function (Request $request) {
    try {
        \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        if (app()->environment('local')) {
            \Midtrans\Config::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER     => [],
            ];
        }

        $notif = new \Midtrans\Notification();

        $orderIdRaw    = $notif->order_id;         // bisa INV-...-{timestamp}
        $invoice       = explode('-', $orderIdRaw);
        // invoice_number pattern: INV-YYYYMMDD-0001 → 3 bagian. Kalau ada suffix retry, potong.
        $invoiceNumber = count($invoice) >= 3 ? implode('-', array_slice($invoice, 0, 3)) : $orderIdRaw;

        $order = \App\Models\Order::where('invoice_number', $invoiceNumber)->first();
        if (! $order) {
            Log::warning('Midtrans notif: order tidak ditemukan', ['order_id' => $orderIdRaw]);
            return response()->json(['status' => 'order_not_found'], 404);
        }

        $txStatus   = $notif->transaction_status;
        $fraud      = $notif->fraud_status ?? null;
        $paymentType = $notif->payment_type ?? null;
        $txId        = $notif->transaction_id ?? null;

        $order->midtrans_transaction_status = $txStatus;
        $order->midtrans_payment_type       = $paymentType;
        $order->midtrans_transaction_id     = $txId;

        if (in_array($txStatus, ['capture', 'settlement']) && (! $fraud || $fraud === 'accept')) {
            if ($order->status === 'menunggu_bayar') {
                $order->status  = 'diproses';
                $order->paid_at = now();
            }
        } elseif (in_array($txStatus, ['deny', 'cancel', 'expire'])) {
            if ($order->status === 'menunggu_bayar') {
                $order->status = 'dibatalkan';
            }
        } elseif ($txStatus === 'pending') {
            // biarkan menunggu_bayar
        }

        $order->save();

        return response()->json(['status' => 'ok']);
    } catch (\Throwable $e) {
        Log::error('Midtrans notif error', ['err' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
})->name('midtrans.notification');

// Step 4: halaman sukses / invoice
Route::get('/pesanan/{invoice}', function (string $invoice) {
    $order = \App\Models\Order::with('items')->where('invoice_number', $invoice)->firstOrFail();
    // Simple access guard: user matches, OR admin
    $authUser = session('auth_user');
    $isOwner = $authUser && ($order->customer_email === $authUser['email'] || ($authUser['role'] ?? null) === 'admin');
    if (! $isOwner) {
        return redirect()->route('home')->withErrors(['email' => 'Anda tidak dapat melihat pesanan ini.']);
    }

    // Sinkronkan status dengan Midtrans — webhook bisa tidak sampai di lokal,
    // jadi saat user mendarat di halaman invoice, kita cek langsung ke API.
    if ($order->payment_method === 'Midtrans' && $order->status === 'menunggu_bayar') {
        try {
            \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
            if (app()->environment('local')) {
                \Midtrans\Config::$curlOptions = [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_HTTPHEADER     => [],
                ];
            }

            $status = \Midtrans\Transaction::status($order->invoice_number);
            $status = is_object($status) ? (array) $status : (array) $status;

            $txStatus    = $status['transaction_status'] ?? null;
            $fraud       = $status['fraud_status'] ?? null;
            $paymentType = $status['payment_type'] ?? null;
            $txId        = $status['transaction_id'] ?? null;

            if ($txStatus) {
                $order->midtrans_transaction_status = $txStatus;
                $order->midtrans_payment_type       = $paymentType;
                $order->midtrans_transaction_id     = $txId;

                if (in_array($txStatus, ['capture', 'settlement']) && (! $fraud || $fraud === 'accept')) {
                    $order->status  = 'diproses';
                    $order->paid_at = $order->paid_at ?? now();
                } elseif (in_array($txStatus, ['deny', 'cancel', 'expire'])) {
                    $order->status = 'dibatalkan';
                }
                $order->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Midtrans sync status error', ['invoice' => $invoice, 'err' => $e->getMessage()]);
        }
    }

    return view('home.pesanan-sukses', compact('order'));
})->name('pesanan.sukses');
Route::get('/tentang', fn () => view('home.tentang'))->name('tentang');
Route::get('/kontak', fn () => view('home.kontak'))->name('kontak');

// Auth — session-based against users table
Route::get('/login', function () {
    $authUser = session('auth_user');
    if ($authUser) {
        return redirect(($authUser['role'] ?? 'pelanggan') === 'admin' ? route('admin.dashboard') : route('home'));
    }
    return view('home.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    $user = User::where('email', $data['email'])->first();

    if (! $user || ! Hash::check($data['password'], $user->password)) {
        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput($request->only('email'));
    }

    if ($user->status !== 'aktif') {
        return back()->withErrors(['email' => 'Akun Anda nonaktif. Silakan hubungi admin.'])->withInput($request->only('email'));
    }

    session(['auth_user' => [
        'id'    => $user->id,
        'name'  => $user->name,
        'email' => $user->email,
        'role'  => $user->role,
    ]]);

    return redirect($user->role === 'admin' ? route('admin.dashboard') : route('home'));
})->name('login.submit');

Route::get('/register', function () {
    if (session('auth_user')) {
        return redirect(route('home'));
    }
    return view('home.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name'     => 'required|string|min:2|max:60',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
    ]);

    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => Hash::make($data['password']),
        'role'     => 'pelanggan',
        'status'   => 'aktif',
    ]);

    session(['auth_user' => [
        'id'    => $user->id,
        'name'  => $user->name,
        'email' => $user->email,
        'role'  => $user->role,
    ]]);

    return redirect(route('home'));
})->name('register.submit');

Route::post('/logout', function () {
    session()->forget('auth_user');
    return redirect(route('home'));
})->name('logout');

// Akun pelanggan (Profil + Pesanan Saya)
Route::prefix('akun')->name('akun.')->group(function () {
    Route::get('/profil', function () {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk mengakses profil.']);
        }
        $user = User::findOrFail($authUser['id']);
        return view('home.akun.profil', compact('user'));
    })->name('profil');

    Route::post('/profil', function (Request $request) {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login');
        }
        $user = User::findOrFail($authUser['id']);

        $data = $request->validate([
            'name'             => 'required|string|min:2|max:60',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'phone'            => 'nullable|string|max:25',
            'current_password' => 'nullable|string',
            'new_password'     => 'nullable|string|min:6|confirmed',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (! empty($data['new_password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        // Sinkronisasi session
        session(['auth_user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]]);

        return redirect()->route('akun.profil')->with('status', 'Profil berhasil diperbarui.');
    })->name('profil.update');

    Route::get('/pesanan', function () {
        $authUser = session('auth_user');
        if (! $authUser) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk melihat pesanan.']);
        }
        $orders = \App\Models\Order::with('items')
            ->where(function ($q) use ($authUser) {
                $q->where('user_id', $authUser['id'])->orWhere('customer_email', $authUser['email']);
            })
            ->latest()
            ->paginate(10);
        return view('home.akun.pesanan', compact('orders'));
    })->name('pesanan');
});

// Admin dashboard — admin only (guarded by EnsureAdmin middleware)
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard', [
            'totalProducts'  => Product::count(),
            'totalOrders'    => Order::count(),
            'totalRevenue'   => Order::whereIn('status', ['dikirim', 'selesai'])->sum('total'),
            'totalCustomers' => User::where('role', 'pelanggan')->where('status', 'aktif')->count(),
            'recentOrders'   => Order::latest()->take(5)->get(),
            'topProducts'    => Product::orderBy('stock', 'desc')->take(5)->get(),
            'stockIn7Day'    => StockMovement::where('type', 'masuk')->where('occurred_at', '>=', now()->subDays(7))->sum('qty'),
            'stockOut7Day'   => StockMovement::where('type', 'keluar')->where('occurred_at', '>=', now()->subDays(7))->sum('qty'),
            'trxIn7Day'      => StockMovement::where('type', 'masuk')->where('occurred_at', '>=', now()->subDays(7))->count(),
            'trxOut7Day'     => StockMovement::where('type', 'keluar')->where('occurred_at', '>=', now()->subDays(7))->count(),
        ]);
    })->name('dashboard');

    Route::get('/produk', function (Request $request) {
        $query = Product::with('categories');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->category_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('admin.produk', [
            'products'   => $query->latest()->paginate(10)->withQueryString(),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    })->name('produk');

    $moveProductImages = function (Request $request, string $nameForSlug): array {
        $destination = public_path('uploads/products');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $paths = [];
        foreach ((array) $request->file('images', []) as $file) {
            if (! $file) {
                continue;
            }
            $filename = time() . '-' . uniqid() . '-' . Str::slug($nameForSlug) . '.' . $file->getClientOriginalExtension();
            $file->move($destination, $filename);
            $paths[] = 'uploads/products/' . $filename;
        }
        return $paths;
    };

    $productValidationRules = function (?int $ignoreId = null) {
        return [
            'name'           => 'required|string|max:150',
            'sku'            => 'required|string|max:30|unique:products,sku' . ($ignoreId ? ",{$ignoreId}" : ''),
            'category_ids'   => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'price'       => 'required|integer|min:0',
            'stock'       => 'required|integer|min:0',
            'stock_min'   => 'nullable|integer|min:0',
            'description' => 'required|string',
            'weight'      => 'nullable|string|max:50',
            'material'    => 'nullable|string|max:100',
            'colors'      => 'nullable|string',
            'sizes'       => 'nullable|string',
            'status'      => 'required|in:aktif,arsip,habis',
            'images'      => 'nullable|array|max:7',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'existing_images'   => 'nullable|array',
            'existing_images.*' => 'string',
        ];
    };

    Route::post('/produk', function (Request $request) use ($moveProductImages, $productValidationRules) {
        $data = $request->validate($productValidationRules());

        $categoryIds = $data['category_ids'];
        unset($data['category_ids']);

        $data['slug']      = Str::slug($data['name']) . '-' . strtolower($data['sku']);
        $data['stock_min'] = $data['stock_min'] ?? 10;
        $data['colors']    = ! empty($data['colors']) ? array_values(array_filter(array_map('trim', explode(',', $data['colors'])))) : [];
        $data['sizes']     = ! empty($data['sizes'])  ? array_values(array_filter(array_map('trim', explode(',', $data['sizes']))))  : [];
        if ($data['stock'] == 0 && $data['status'] === 'aktif') {
            $data['status'] = 'habis';
        }

        $uploaded = $moveProductImages($request, $data['name']);
        $data['images'] = $uploaded ?: null;
        $data['image']  = $uploaded[0] ?? null;

        unset($data['existing_images']);
        $product = Product::create($data);
        $product->categories()->sync($categoryIds);

        return redirect()->route('admin.produk')->with('status', 'Produk berhasil ditambahkan.');
    })->name('produk.store');

    Route::put('/produk/{product}', function (Request $request, Product $product) use ($moveProductImages, $productValidationRules) {
        $data = $request->validate($productValidationRules($product->id));

        $categoryIds = $data['category_ids'];
        unset($data['category_ids']);

        // Merge kept existing + newly uploaded, cap at 7
        $currentImages = is_array($product->images) ? $product->images : ($product->image ? [$product->image] : []);
        $kept = array_values(array_intersect($currentImages, (array) $request->input('existing_images', [])));
        $removed = array_diff($currentImages, $kept);

        $uploaded = $moveProductImages($request, $data['name']);
        $finalImages = array_merge($kept, $uploaded);

        if (count($finalImages) > 7) {
            foreach ($uploaded as $p) {
                @unlink(public_path($p));
            }
            return back()->withErrors(['images' => 'Total foto tidak boleh lebih dari 7.'])->withInput();
        }

        // Delete removed physical files
        foreach ($removed as $p) {
            if (str_starts_with($p, 'uploads/')) {
                @unlink(public_path($p));
            }
        }

        if ($data['name'] !== $product->name || $data['sku'] !== $product->sku) {
            $data['slug'] = Str::slug($data['name']) . '-' . strtolower($data['sku']);
        }
        $data['stock_min'] = $data['stock_min'] ?? 10;
        $data['colors']    = ! empty($data['colors']) ? array_values(array_filter(array_map('trim', explode(',', $data['colors'])))) : [];
        $data['sizes']     = ! empty($data['sizes'])  ? array_values(array_filter(array_map('trim', explode(',', $data['sizes']))))  : [];
        if ($data['stock'] == 0 && $data['status'] === 'aktif') {
            $data['status'] = 'habis';
        }

        $data['images'] = $finalImages ?: null;
        $data['image']  = $finalImages[0] ?? null;

        unset($data['existing_images']);
        $product->update($data);
        $product->categories()->sync($categoryIds);

        return redirect()->route('admin.produk')->with('status', 'Produk berhasil diperbarui.');
    })->name('produk.update');

    Route::delete('/produk/{product}', function (Product $product) {
        $all = is_array($product->images) ? $product->images : [];
        if ($product->image) {
            $all[] = $product->image;
        }
        foreach (array_unique($all) as $p) {
            if (str_starts_with($p, 'uploads/')) {
                $full = public_path($p);
                if (is_file($full)) {
                    @unlink($full);
                }
            }
        }
        $product->delete();

        return redirect()->route('admin.produk')->with('status', 'Produk berhasil dihapus.');
    })->name('produk.destroy');

    Route::get('/pesanan', function (Request $request) {
        $query = Order::with('items');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', "%{$q}%")
                  ->orWhere('customer_name', 'like', "%{$q}%")
                  ->orWhere('customer_email', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return view('admin.pesanan', [
            'orders' => $query->latest()->paginate(10)->withQueryString(),
            'counts' => [
                'total'          => Order::count(),
                'perlu_diproses' => Order::whereIn('status', ['diproses', 'menunggu_bayar'])->count(),
                'selesai'        => Order::where('status', 'selesai')->count(),
                'dibatalkan'     => Order::where('status', 'dibatalkan')->count(),
            ],
        ]);
    })->name('pesanan');

    Route::patch('/pesanan/{order}/status', function (Request $request, \App\Models\Order $order) {
        $data = $request->validate([
            'status' => 'required|in:menunggu_bayar,diproses,dikirim,selesai,dibatalkan',
        ]);
        $oldStatus = $order->status;
        $oldLabel  = $order->status_label;
        $order->status = $data['status'];
        $order->save();

        // Auto stok keluar: saat order baru PERTAMA KALI masuk status "dikirim"
        // (dari non-dikirim/selesai), potong stok + log mutasi.
        $newStatus = $data['status'];
        $wasShipped = in_array($oldStatus, ['dikirim', 'selesai']);
        $nowShipped = in_array($newStatus, ['dikirim', 'selesai']);
        if (! $wasShipped && $nowShipped) {
            $authUser = session('auth_user');
            foreach ($order->items as $item) {
                if (! $item->product_id) continue;
                $p = Product::find($item->product_id);
                if (! $p) continue;

                StockMovement::create([
                    'product_id'  => $p->id,
                    'user_id'     => $authUser['id'] ?? null,
                    'type'        => 'keluar',
                    'qty'         => $item->qty,
                    'reference'   => $order->invoice_number,
                    'note'        => 'Auto: pengiriman pesanan',
                    'occurred_at' => now(),
                ]);
                $p->stock = max(0, $p->stock - $item->qty);
                if ($p->stock === 0 && $p->status === 'aktif') {
                    $p->status = 'habis';
                }
                $p->save();
            }
        }

        return back()->with('status', "Status {$order->invoice_number} diubah: {$oldLabel} → {$order->status_label}.");
    })->name('pesanan.status');

    Route::delete('/pesanan/bulk', function (Request $request) {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:orders,id',
        ]);

        $orders = \App\Models\Order::whereIn('id', $data['ids'])->get();
        foreach ($orders as $order) {
            if ($order->payment_proof && str_starts_with($order->payment_proof, 'uploads/')) {
                $full = public_path($order->payment_proof);
                if (is_file($full)) {
                    @unlink($full);
                }
            }
            $order->delete();
        }

        return redirect()->route('admin.pesanan')->with('status', "{$orders->count()} pesanan berhasil dihapus.");
    })->name('pesanan.bulk-destroy');

    Route::delete('/pesanan/{order}', function (\App\Models\Order $order) {
        $invoice = $order->invoice_number;

        // Hapus file bukti transfer (legacy) jika ada
        if ($order->payment_proof && str_starts_with($order->payment_proof, 'uploads/')) {
            $full = public_path($order->payment_proof);
            if (is_file($full)) {
                @unlink($full);
            }
        }

        // order_items akan ikut terhapus via cascadeOnDelete
        $order->delete();

        return redirect()->route('admin.pesanan')->with('status', "Pesanan {$invoice} berhasil dihapus.");
    })->name('pesanan.destroy');

    Route::get('/laporan', function (Request $request) {
        // Base query untuk filter
        $baseQuery = StockMovement::query();
        if ($request->filled('q')) {
            $q = $request->q;
            $baseQuery->whereHas('product', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
            });
        }
        if ($request->filled('type') && in_array($request->type, ['masuk', 'keluar'])) {
            $baseQuery->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $baseQuery->whereDate('occurred_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $baseQuery->whereDate('occurred_at', '<=', $request->to);
        }

        // Totals periode (dari query yang SAMA supaya konsisten dengan tabel)
        $totalIn  = (clone $baseQuery)->where('type', 'masuk')->sum('qty');
        $totalOut = (clone $baseQuery)->where('type', 'keluar')->sum('qty');
        $trxIn    = (clone $baseQuery)->where('type', 'masuk')->count();
        $trxOut   = (clone $baseQuery)->where('type', 'keluar')->count();

        $movements = $baseQuery->with(['product', 'user'])
            ->orderBy('occurred_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $lowStock = Product::whereColumn('stock', '<', 'stock_min')->get();
        $products = Product::orderBy('name')->get(['id', 'sku', 'name', 'stock']);

        return view('admin.laporan', compact(
            'movements', 'lowStock', 'products',
            'totalIn', 'totalOut', 'trxIn', 'trxOut'
        ));
    })->name('laporan');

    // Tambah mutasi stok manual (masuk / keluar)
    Route::post('/laporan/mutasi', function (Request $request) {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:masuk,keluar',
            'qty'        => 'required|integer|min:1|max:9999',
            'reference'  => 'nullable|string|max:100',
            'note'       => 'nullable|string|max:200',
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['type'] === 'keluar' && $product->stock < $data['qty']) {
            return back()->withErrors(['qty' => "Stok {$product->name} hanya {$product->stock}, tidak cukup untuk dikurangi {$data['qty']}."])->withInput();
        }

        $authUser = session('auth_user');

        StockMovement::create([
            'product_id'  => $product->id,
            'user_id'     => $authUser['id'] ?? null,
            'type'        => $data['type'],
            'qty'         => $data['qty'],
            'reference'   => $data['reference'] ?? null,
            'note'        => $data['note'] ?? null,
            'occurred_at' => now(),
        ]);

        // Update stock di tabel products
        $product->stock = $data['type'] === 'masuk'
            ? $product->stock + $data['qty']
            : max(0, $product->stock - $data['qty']);

        // Auto-sync status: stok 0 → habis; stok > 0 dan status habis → aktif
        if ($product->stock === 0 && $product->status === 'aktif') {
            $product->status = 'habis';
        } elseif ($product->stock > 0 && $product->status === 'habis') {
            $product->status = 'aktif';
        }

        $product->save();

        $label = $data['type'] === 'masuk' ? 'masuk' : 'keluar';
        return back()->with('status', "Mutasi stok {$label} sebanyak {$data['qty']} unit untuk {$product->name} berhasil dicatat.");
    })->name('laporan.mutasi');

    Route::get('/user', function () {
        return view('admin.user', [
            'users' => User::withCount('orders')->latest()->get(),
        ]);
    })->name('user');

    Route::get('/cms', function () {
        return view('admin.cms', [
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
            'banners'    => Banner::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    })->name('cms');

    // ---- CMS: Site Settings (Tentang / Kontak / Footer) ----
    Route::post('/cms/settings/{group}', function (Request $request, string $group) {
        $allowedKeys = [
            'tentang' => [
                'about_title', 'about_subtitle', 'about_story', 'about_mission', 'about_quote',
            ],
            'kontak'  => [
                'store_name', 'contact_email', 'contact_phone', 'contact_address',
                'contact_hours', 'contact_maps_embed',
                'social_facebook', 'social_instagram', 'social_pinterest', 'social_youtube',
            ],
            'footer'  => [
                'footer_copyright', 'footer_newsletter_text', 'footer_topbar_promo',
            ],
        ];
        if (! isset($allowedKeys[$group])) {
            abort(404);
        }

        $data = $request->only($allowedKeys[$group]);
        $pairs = [];
        foreach ($allowedKeys[$group] as $key) {
            $pairs[$key] = $data[$key] ?? null;
        }
        SiteSetting::setMany($pairs);

        return redirect()->route('admin.cms', ['#tab-' . $group])
            ->with('status', 'Pengaturan ' . ucfirst($group) . ' berhasil disimpan.');
    })->name('cms.settings.save');

    // ---- CMS: Banner CRUD ----
    $moveBannerImage = function (Request $request, string $field = 'image'): ?string {
        $file = $request->file($field);
        if (! $file) {
            return null;
        }
        $destination = public_path('uploads/banners');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);
        return 'uploads/banners/' . $filename;
    };

    $bannerImageMessages = [
        'image.uploaded' => 'Upload gambar gagal. File mungkin lebih besar dari batas server (cek upload_max_filesize di php.ini).',
        'image.max'      => 'Ukuran gambar tidak boleh lebih dari 3 MB.',
        'image.image'    => 'File harus berupa gambar (JPG, PNG, atau WebP).',
        'image.mimes'    => 'Format gambar harus JPG, PNG, atau WebP.',
    ];

    Route::post('/cms/banner', function (Request $request) use ($moveBannerImage, $bannerImageMessages) {
        $data = $request->validate([
            'title'      => 'required|string|max:120',
            'subtitle'   => 'nullable|string|max:160',
            'image'            => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
            'image_max_height' => 'nullable|integer|min:120|max:1200',
            'link'             => 'nullable|string|max:200',
            'cta_text'         => 'nullable|string|max:60',
            'sort_order'       => 'nullable|integer|min:0|max:9999',
            'is_active'        => 'nullable|in:0,1',
        ], $bannerImageMessages);

        $data['image']      = $moveBannerImage($request);
        $data['cta_text']   = $data['cta_text']   ?? 'Belanja Sekarang';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = (bool) ($data['is_active'] ?? 0);

        Banner::create($data);
        return redirect()->route('admin.cms', ['#tab-banner'])->with('status', 'Banner ditambahkan.');
    })->name('cms.banner.store');

    Route::put('/cms/banner/{banner}', function (Request $request, Banner $banner) use ($moveBannerImage, $bannerImageMessages) {
        $data = $request->validate([
            'title'            => 'required|string|max:120',
            'subtitle'         => 'nullable|string|max:160',
            'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'image_max_height' => 'nullable|integer|min:120|max:1200',
            'link'             => 'nullable|string|max:200',
            'cta_text'         => 'nullable|string|max:60',
            'sort_order'       => 'nullable|integer|min:0|max:9999',
            'is_active'        => 'nullable|in:0,1',
        ], $bannerImageMessages);

        if ($request->hasFile('image')) {
            // hapus file lama
            if ($banner->image && str_starts_with($banner->image, 'uploads/')) {
                @unlink(public_path($banner->image));
            }
            $data['image'] = $moveBannerImage($request);
        } else {
            unset($data['image']);
        }
        $data['cta_text']   = $data['cta_text']   ?? 'Belanja Sekarang';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = (bool) ($data['is_active'] ?? 0);

        $banner->update($data);
        return redirect()->route('admin.cms', ['#tab-banner'])->with('status', 'Banner diperbarui.');
    })->name('cms.banner.update');

    Route::delete('/cms/banner/{banner}', function (Banner $banner) {
        if ($banner->image && str_starts_with($banner->image, 'uploads/')) {
            @unlink(public_path($banner->image));
        }
        $banner->delete();
        return redirect()->route('admin.cms', ['#tab-banner'])->with('status', 'Banner dihapus.');
    })->name('cms.banner.destroy');

    // ---- CMS: Kategori CRUD ----
    Route::post('/cms/kategori', function (Request $request) {
        $data = $request->validate([
            'name'       => 'required|string|max:80',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);
        $slug = \Illuminate\Support\Str::slug($data['name']);
        // pastikan unik
        $base = $slug; $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        Category::create([
            'name'       => $data['name'],
            'slug'       => $slug,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        return redirect()->route('admin.cms', ['#tab-kategori'])->with('status', 'Kategori ditambahkan.');
    })->name('cms.kategori.store');

    Route::put('/cms/kategori/{category}', function (Request $request, Category $category) {
        $data = $request->validate([
            'name'       => 'required|string|max:80',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);
        $newSlug = \Illuminate\Support\Str::slug($data['name']);
        if ($newSlug !== $category->slug) {
            $base = $newSlug; $i = 1;
            while (Category::where('slug', $newSlug)->where('id', '!=', $category->id)->exists()) {
                $newSlug = $base . '-' . (++$i);
            }
            $category->slug = $newSlug;
        }
        $category->name = $data['name'];
        $category->sort_order = $data['sort_order'] ?? $category->sort_order;
        $category->save();
        return redirect()->route('admin.cms', ['#tab-kategori'])->with('status', 'Kategori diperbarui.');
    })->name('cms.kategori.update');

    Route::delete('/cms/kategori/{category}', function (Category $category) {
        if ($category->products()->exists()) {
            return redirect()->route('admin.cms', ['#tab-kategori'])
                ->withErrors(['kategori' => "Kategori '{$category->name}' masih dipakai produk dan tidak bisa dihapus."]);
        }
        $category->delete();
        return redirect()->route('admin.cms', ['#tab-kategori'])->with('status', 'Kategori dihapus.');
    })->name('cms.kategori.destroy');
});
