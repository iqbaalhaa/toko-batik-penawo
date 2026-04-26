<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image', 'image_max_height', 'link', 'cta_text', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'sort_order'       => 'integer',
        'image_max_height' => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        return $this->image ? asset($this->image) : asset('frontend/images/slide-01.jpg');
    }
}
