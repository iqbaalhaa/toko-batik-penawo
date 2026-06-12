<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Kelola kategori produk — halaman mandiri (sebelumnya tab di CMS).
 */
class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.kategori', [
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:80',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);
        $slug = Str::slug($data['name']);
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
        return redirect()->route('admin.kategori')->with('status', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:80',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);
        $newSlug = Str::slug($data['name']);
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
        return redirect()->route('admin.kategori')->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()->route('admin.kategori')
                ->withErrors(['kategori' => "Kategori '{$category->name}' masih dipakai produk dan tidak bisa dihapus."]);
        }
        $category->delete();
        return redirect()->route('admin.kategori')->with('status', 'Kategori dihapus.');
    }
}
