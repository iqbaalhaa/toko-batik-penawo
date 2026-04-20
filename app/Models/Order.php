<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_number', 'user_id', 'customer_name', 'customer_email',
        'total', 'payment_method', 'payment_proof', 'paid_at',
        'status', 'shipping_address', 'note',
    ];

    protected $casts = [
        'total' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->payment_proof ? asset($this->payment_proof) : null;
    }

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
