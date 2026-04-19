<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home.index'))->name('home');
Route::get('/produk', fn () => view('home.produk'))->name('produk');

Route::get('/produk/{slug}', function (string $slug) {
    $product = collect(config('products', []))->firstWhere('slug', $slug);
    abort_unless($product, 404);

    return view('home.produk-detail', ['product' => $product]);
})->name('produk.detail');

Route::get('/tentang', fn () => view('home.tentang'))->name('tentang');
Route::get('/kontak', fn () => view('home.kontak'))->name('kontak');
