<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

// ---- Publik: katalog & halaman statis ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produk', [HomeController::class, 'produk'])->name('produk');
Route::get('/api/produk/cari', [HomeController::class, 'cariProduk'])->name('api.produk.cari');
Route::get('/produk/{slug}', [HomeController::class, 'produkDetail'])->name('produk.detail');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/kontak', [HomeController::class, 'kontak'])->name('kontak');

// ---- Keranjang (session-based) ----
Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang');
Route::post('/keranjang/add', [CartController::class, 'add'])->name('keranjang.add');
Route::patch('/keranjang/{cartKey}', [CartController::class, 'update'])->name('keranjang.update');
Route::delete('/keranjang/{cartKey}', [CartController::class, 'remove'])->name('keranjang.remove');
Route::post('/keranjang/clear', [CartController::class, 'clear'])->name('keranjang.clear');

// ---- Checkout (3 langkah) + invoice ----
Route::post('/checkout', [CheckoutController::class, 'start'])->name('checkout');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
Route::post('/pesanan/{invoice}/selesai', [CheckoutController::class, 'selesai'])->name('pesanan.selesai');
Route::get('/pesanan/{invoice}', [CheckoutController::class, 'sukses'])->name('pesanan.sukses');

// ---- Midtrans (token ulang + webhook; webhook dikecualikan dari CSRF di bootstrap/app.php) ----
Route::post('/pesanan/{invoice}/midtrans/token', [MidtransController::class, 'token'])->name('midtrans.token');
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])->name('midtrans.notification');

// ---- API wilayah untuk dropdown cascading alamat (publik, read-only) ----
Route::prefix('api/wilayah')->name('api.wilayah.')->group(function () {
    Route::get('/provinces', [WilayahController::class, 'provinces'])->name('provinces');
    Route::get('/regencies', [WilayahController::class, 'regencies'])->name('regencies');
    Route::get('/districts', [WilayahController::class, 'districts'])->name('districts');
});

// ---- Auth — session-based against users table ----
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ---- Akun pelanggan (profil, pesanan, alamat, pengaturan) ----
Route::prefix('akun')->name('akun.')->group(function () {
    Route::get('/profil', [AccountController::class, 'profil'])->name('profil');
    Route::post('/profil', [AccountController::class, 'updateProfil'])->name('profil.update');
    Route::get('/pesanan', [AccountController::class, 'pesanan'])->name('pesanan');

    // Alamat pengiriman: maks 3 per pelanggan.
    Route::post('/alamat', [AddressController::class, 'store'])->name('alamat.store');
    Route::put('/alamat/{address}', [AddressController::class, 'update'])->name('alamat.update');
    Route::delete('/alamat/{address}', [AddressController::class, 'destroy'])->name('alamat.destroy');
    Route::patch('/alamat/{address}/default', [AddressController::class, 'setDefault'])->name('alamat.default');

    Route::get('/pengaturan', [AccountController::class, 'pengaturan'])->name('pengaturan');
    Route::post('/pengaturan', [AccountController::class, 'updatePengaturan'])->name('pengaturan.update');
    Route::post('/pengaturan/password', [AccountController::class, 'updatePassword'])->name('pengaturan.password');
    Route::delete('/pengaturan/hapus-akun', [AccountController::class, 'hapusAkun'])->name('pengaturan.hapus-akun');
});

// ---- Panel admin (guarded by EnsureAdmin middleware, alias 'admin') ----
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [DashboardController::class, 'profil'])->name('profil');
    Route::post('/profil', [DashboardController::class, 'updateProfil'])->name('profil.update');

    // Produk
    Route::get('/produk', [ProductController::class, 'index'])->name('produk');
    Route::post('/produk', [ProductController::class, 'store'])->name('produk.store');
    Route::put('/produk/{product}', [ProductController::class, 'update'])->name('produk.update');
    Route::delete('/produk/{product}', [ProductController::class, 'destroy'])->name('produk.destroy');

    // Pesanan — route statis (cetak-massal, bulk) HARUS di atas route {order}.
    Route::get('/pesanan', [OrderController::class, 'index'])->name('pesanan');
    Route::patch('/pesanan/{order}/status', [OrderController::class, 'updateStatus'])->name('pesanan.status');
    Route::get('/pesanan/{order}/cetak', [OrderController::class, 'cetak'])->name('pesanan.cetak');
    Route::post('/pesanan/cetak-massal', [OrderController::class, 'cetakMassal'])->name('pesanan.cetak-massal');
    Route::delete('/pesanan/bulk', [OrderController::class, 'bulkDestroy'])->name('pesanan.bulk-destroy');
    Route::delete('/pesanan/{order}', [OrderController::class, 'destroy'])->name('pesanan.destroy');

    // Laporan stok
    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan');
    Route::get('/laporan/cetak', [ReportController::class, 'cetak'])->name('laporan.cetak');
    Route::post('/laporan/mutasi', [ReportController::class, 'storeMutasi'])->name('laporan.mutasi');

    // User
    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::post('/user', [UserController::class, 'store'])->name('user.store');
    Route::put('/user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

    // Kategori produk — halaman mandiri (diakses dari menu sidebar)
    Route::get('/kategori', [CategoryController::class, 'index'])->name('kategori');
    Route::post('/kategori', [CategoryController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('kategori.destroy');

    // CMS: site settings & banner
    Route::get('/cms', [CmsController::class, 'index'])->name('cms');
    Route::post('/cms/settings/{group}', [CmsController::class, 'saveSettings'])->name('cms.settings.save');
    Route::post('/cms/banner', [CmsController::class, 'storeBanner'])->name('cms.banner.store');
    Route::put('/cms/banner/{banner}', [CmsController::class, 'updateBanner'])->name('cms.banner.update');
    Route::delete('/cms/banner/{banner}', [CmsController::class, 'destroyBanner'])->name('cms.banner.destroy');
});
