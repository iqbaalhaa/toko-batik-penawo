<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::with('categories')->where('status', '!=', 'arsip')->latest()->take(16)->get();
        $categories = Category::orderBy('sort_order')->get();
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
        return view('home.index', compact('products', 'categories', 'banners'));
    }

    public function produk(Request $request)
    {
        $categories = Category::orderBy('sort_order')->get();

        // Filter via ?kategori=<slug>. Dipakai oleh link footer & tombol filter
        // di halaman produk supaya bisa di-bookmark / di-share dengan kategori
        // sudah ter-apply.
        $activeCategorySlug = $request->query('kategori');
        $activeCategory     = $activeCategorySlug
            ? $categories->firstWhere('slug', $activeCategorySlug)
            : null;

        // Pencarian via ?q=<kata kunci> — dipakai modal cari di header & panel
        // cari di halaman produk. Cocokkan ke nama, SKU, dan deskripsi.
        $searchTerm = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $query = Product::with('categories')->where('status', '!=', 'arsip')->latest();
        if ($activeCategory) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $activeCategory->id));
        }
        if ($searchTerm !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $searchTerm) . '%';
            $query->where(fn ($q) => $q
                ->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('description', 'like', $like));
        }

        $products = $query->paginate(12)->withQueryString();
        return view('home.produk', compact('products', 'categories', 'activeCategory', 'searchTerm'));
    }

    // Saran pencarian produk (live search di modal cari header) — publik, read-only.
    public function cariProduk(Request $request)
    {
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        if (mb_strlen($q) < 2) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
        $base = Product::with('categories')
            ->where('status', '!=', 'arsip')
            ->where(fn ($w) => $w
                ->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('description', 'like', $like));

        $total = (clone $base)->count();
        $items = $base->latest()->take(8)->get()->map(fn ($p) => [
            'name'     => $p->name,
            'price'    => 'Rp' . number_format($p->price, 0, ',', '.'),
            'image'    => $p->image_url,
            'url'      => route('produk.detail', $p->slug),
            'category' => $p->categories->first()->name ?? null,
            'stock'    => (int) $p->stock,
        ]);

        return response()->json(['items' => $items, 'total' => $total]);
    }

    public function produkDetail(string $slug)
    {
        $product = Product::with('categories')->where('slug', $slug)->firstOrFail();
        return view('home.produk-detail', ['product' => $product]);
    }

    public function tentang()
    {
        return view('home.tentang');
    }

    public function kontak()
    {
        return view('home.kontak');
    }
}
