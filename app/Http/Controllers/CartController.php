<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('home.keranjang');
    }

    public function add(Request $request)
    {
        if (! session('auth_user')) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan masuk untuk menambahkan ke keranjang.']);
        }

        $data = $request->validate([
            'slug'  => 'required|string|exists:products,slug',
            'qty'   => 'required|integer|min:1|max:99',
            'size'  => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ]);

        // Normalisasi: placeholder "Pilih ukuran/warna" dianggap kosong
        $size  = $data['size']  ?? null;
        $color = $data['color'] ?? null;
        if ($size  && stripos($size,  'pilih') === 0) $size  = null;
        if ($color && stripos($color, 'pilih') === 0) $color = null;

        $cartKey = substr(md5($data['slug'] . '|' . ($size ?? '') . '|' . ($color ?? '')), 0, 12);

        $cart = session('cart', []);
        // Buang sisa entry format lama (slug => int) supaya tidak bentrok
        foreach ($cart as $k => $v) {
            if (! is_array($v)) unset($cart[$k]);
        }

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty'] = min(99, (int) $cart[$cartKey]['qty'] + (int) $data['qty']);
        } else {
            $cart[$cartKey] = [
                'slug'  => $data['slug'],
                'qty'   => min(99, (int) $data['qty']),
                'size'  => $size,
                'color' => $color,
            ];
        }
        session(['cart' => $cart]);

        $product = Product::where('slug', $data['slug'])->first();
        return redirect()->back()->with('cart_added', [
            'name'  => $product?->name ?? 'Produk',
            'qty'   => (int) $data['qty'],
            'image' => $product?->image_url,
            'size'  => $size,
            'color' => $color,
        ]);
    }

    public function update(Request $request, string $cartKey)
    {
        $cart = session('cart', []);
        if (! isset($cart[$cartKey]) || ! is_array($cart[$cartKey])) {
            abort(404);
        }
        $qty = (int) $request->input('qty', 1);
        $cart[$cartKey]['qty'] = max(1, min(99, $qty));
        session(['cart' => $cart]);
        return redirect()->route('keranjang')->with('status', 'Jumlah produk diperbarui.');
    }

    public function remove(string $cartKey)
    {
        $cart = session('cart', []);
        unset($cart[$cartKey]);
        session(['cart' => $cart]);
        return redirect()->route('keranjang')->with('status', 'Produk dihapus dari keranjang.');
    }

    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('keranjang')->with('status', 'Keranjang dikosongkan.');
    }
}
