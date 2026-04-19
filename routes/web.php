<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home.index'))->name('home');
Route::get('/produk', fn () => view('home.produk'))->name('produk');
Route::get('/tentang', fn () => view('home.tentang'))->name('tentang');
Route::get('/kontak', fn () => view('home.kontak'))->name('kontak');
