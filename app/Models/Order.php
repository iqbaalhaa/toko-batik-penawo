<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_number', 'user_id', 'customer_name', 'customer_email',
        'total', 'payment_method', 'payment_proof', 'paid_at',
        'status', 'shipping_address', 'note',
        'snap_token', 'midtrans_transaction_id', 'midtrans_payment_type',
        'midtrans_transaction_status',
        'subtotal_products', 'shipping_total', 'shipping_breakdown',
    ];

    protected $casts = [
        'total' => 'integer',
        'subtotal_products' => 'integer',
        'shipping_total' => 'integer',
        'shipping_breakdown' => 'array',
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
