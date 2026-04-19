<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'sku', 'name', 'slug', 'description',
        'price', 'stock', 'stock_min', 'image', 'weight',
        'material', 'colors', 'sizes', 'status',
    ];

    protected $casts = [
        'colors' => 'array',
        'sizes' => 'array',
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

    public function isLowStock(): bool
    {
        return $this->stock < $this->stock_min;
    }
}
