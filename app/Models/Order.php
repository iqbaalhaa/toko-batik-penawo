<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_number', 'user_id', 'customer_name', 'customer_email',
        'total', 'payment_method', 'status', 'shipping_address', 'note',
    ];

    protected $casts = [
        'total' => 'integer',
    ];

    public const STATUS_LABELS = [
        'menunggu_bayar' => 'Menunggu Bayar',
        'diproses'       => 'Diproses',
        'dikirim'        => 'Dikirim',
        'selesai'        => 'Selesai',
        'dibatalkan'     => 'Dibatalkan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
