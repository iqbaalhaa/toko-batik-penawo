<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // Pesan validasi dalam bahasa Indonesia + petunjuk konkrit untuk admin.
    // `images.*.max` mengacu pada ukuran file dalam kilobyte (kita batasi 2 MB).
    private const VALIDATION_MESSAGES = [
        'name.required'          => 'Nama produk wajib diisi.',
        'sku.required'           => 'SKU wajib diisi.',
        'sku.unique'             => 'SKU sudah dipakai produk lain.',
        'category_ids.required'  => 'Pilih minimal satu kategori.',
        'category_ids.min'       => 'Pilih minimal satu kategori.',
        'category_ids.*.exists'  => 'Kategori yang dipilih tidak valid.',
        'price.required'         => 'Harga wajib diisi.',
        'price.integer'          => 'Harga harus berupa angka.',
        'stock.required'         => 'Stok wajib diisi.',
        'description.required'   => 'Deskripsi wajib diisi.',
        'weight_kg.required'     => 'Berat pengiriman (kg) wajib diisi untuk perhitungan ongkir.',
        'weight_kg.min'          => 'Berat pengiriman minimal 0,01 kg.',
        'status.in'              => 'Status produk tidak valid.',
        'images.max'             => 'Maksimal 7 foto per produk.',
        'images.*.image'         => 'Salah satu file yang diunggah bukan gambar yang valid.',
        'images.*.mimes'         => 'Format foto harus JPG, PNG, atau WebP.',
        'images.*.max'           => 'Ada foto yang melebihi 2 MB. Kompres atau ganti dengan foto berukuran lebih kecil.',
    ];

    private function rules(?int $ignoreId = null): array
    {
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
            // Berat numerik (kg) wajib > 0 — dipakai kalkulator ongkir.
            'weight_kg'   => 'required|numeric|min:0.01|max:9999.99',
            'material'    => 'nullable|string|max:100',
            'colors'      => 'nullable|string',
            'sizes'       => 'nullable|string',
            'status'      => 'required|in:aktif,arsip,habis',
            'images'      => 'nullable|array|max:7',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'existing_images'   => 'nullable|array',
            'existing_images.*' => 'string',
        ];
    }

    private function moveImages(Request $request, string $nameForSlug): array
    {
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
    }

    public function index(Request $request)
    {
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
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), self::VALIDATION_MESSAGES);

        $categoryIds = $data['category_ids'];
        unset($data['category_ids']);

        $data['slug']      = Str::slug($data['name']) . '-' . strtolower($data['sku']);
        $data['stock_min'] = $data['stock_min'] ?? 10;
        $data['colors']    = ! empty($data['colors']) ? array_values(array_filter(array_map('trim', explode(',', $data['colors'])))) : [];
        $data['sizes']     = ! empty($data['sizes'])  ? array_values(array_filter(array_map('trim', explode(',', $data['sizes']))))  : [];
        if ($data['stock'] == 0 && $data['status'] === 'aktif') {
            $data['status'] = 'habis';
        }

        $uploaded = $this->moveImages($request, $data['name']);
        $data['images'] = $uploaded ?: null;
        $data['image']  = $uploaded[0] ?? null;

        unset($data['existing_images']);
        $product = Product::create($data);
        $product->categories()->sync($categoryIds);

        return redirect()->route('admin.produk')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate($this->rules($product->id), self::VALIDATION_MESSAGES);

        $categoryIds = $data['category_ids'];
        unset($data['category_ids']);

        // Merge kept existing + newly uploaded, cap at 7
        $currentImages = is_array($product->images) ? $product->images : ($product->image ? [$product->image] : []);
        $kept = array_values(array_intersect($currentImages, (array) $request->input('existing_images', [])));
        $removed = array_diff($currentImages, $kept);

        $uploaded = $this->moveImages($request, $data['name']);
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
    }

    public function destroy(Product $product)
    {
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
    }
}
