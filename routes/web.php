<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $products = Product::with('category')->where('status', '!=', 'arsip')->latest()->take(16)->get();
    $categories = Category::orderBy('sort_order')->get();
    return view('home.index', compact('products', 'categories'));
})->name('home');
Route::get('/produk', function () {
    $products = Product::with('category')->where('status', '!=', 'arsip')->latest()->get();
    $categories = Category::orderBy('sort_order')->get();
    return view('home.produk', compact('products', 'categories'));
})->name('produk');

Route::get('/produk/{slug}', function (string $slug) {
    $product = Product::with('category')->where('slug', $slug)->firstOrFail();
    return view('home.produk-detail', ['product' => $product]);
})->name('produk.detail');

Route::get('/keranjang', fn () => view('home.keranjang'))->name('keranjang');
Route::get('/tentang', fn () => view('home.tentang'))->name('tentang');
Route::get('/kontak', fn () => view('home.kontak'))->name('kontak');

// Auth (session-based mock)
Route::get('/login', fn () => view('home.login'))->name('login');
Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    session(['auth_user' => [
        'email' => $data['email'],
        'name' => ucfirst(explode('@', $data['email'])[0]),
    ]]);

    return redirect()->intended(route('home'));
})->name('login.submit');

Route::get('/register', fn () => view('home.register'))->name('register');
Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string|min:2|max:60',
        'email' => 'required|email',
        'password' => 'required|min:6|confirmed',
    ]);

    session(['auth_user' => [
        'email' => $data['email'],
        'name' => $data['name'],
    ]]);

    return redirect(route('home'));
})->name('register.submit');

Route::post('/logout', function () {
    session()->forget('auth_user');
    return redirect(route('home'));
})->name('logout');

// Admin dashboard
Route::prefix('admin')->name('admin.')->group(function () {
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

    Route::get('/produk', function () {
        return view('admin.produk', [
            'products'   => Product::with('category')->latest()->get(),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    })->name('produk');

    Route::get('/pesanan', function () {
        return view('admin.pesanan', [
            'orders' => Order::with('items')->latest()->get(),
        ]);
    })->name('pesanan');

    Route::get('/laporan', function () {
        return view('admin.laporan', [
            'movements' => StockMovement::with(['product', 'user'])->orderBy('occurred_at', 'desc')->get(),
            'lowStock'  => Product::whereColumn('stock', '<', 'stock_min')->get(),
        ]);
    })->name('laporan');

    Route::get('/user', function () {
        return view('admin.user', [
            'users' => User::withCount('orders')->latest()->get(),
        ]);
    })->name('user');

    Route::get('/cms', function () {
        return view('admin.cms', [
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
        ]);
    })->name('cms');
});
