<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'sku', 'name', 'slug', 'description',
        'price', 'stock', 'stock_min', 'image', 'images', 'weight',
        'material', 'colors', 'sizes', 'status',
    ];

    protected $casts = [
        'colors' => 'array',
        'sizes' => 'array',
        'images' => 'array',
        'price' => 'integer',
        'stock' => 'integer',
        'stock_min' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp' . number_format($this->price, 0, ',', '.');
    }

    public function getImageUrlAttribute(): string
    {
        $gallery = $this->image_urls;
        return $gallery[0] ?? asset('frontend/images/product-01.jpg');
    }

    public function getImageUrlsAttribute(): array
    {
        $resolve = fn (string $path) => str_starts_with($path, 'uploads/')
            ? asset($path)
            : asset('frontend/images/' . $path);

        if (! empty($this->images) && is_array($this->images)) {
            return array_map($resolve, $this->images);
        }

        if ($this->image) {
            return [$resolve($this->image)];
        }

        return [];
    }

    public function isLowStock(): bool
    {
        return $this->stock < $this->stock_min;
    }
}
