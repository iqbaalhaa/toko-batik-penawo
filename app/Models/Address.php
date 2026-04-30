<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    public const MAX_PER_USER = 3;

    protected $fillable = [
        'user_id', 'label',
        'province_id', 'province_name',
        'city_id', 'city_name',
        'district_id', 'district_name',
        'full_address', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Payload alamat untuk ShippingCalculator.
     */
    public function toShippingPayload(): array
    {
        return [
            'province_id'   => $this->province_id,
            'province_name' => $this->province_name,
            'city_id'       => $this->city_id,
            'city_name'     => $this->city_name,
            'district_id'   => $this->district_id,
            'district_name' => $this->district_name,
            'full_address'  => $this->full_address,
        ];
    }

    /**
     * Representasi teks untuk disimpan ke Order.shipping_address dan tampilkan
     * di invoice.
     */
    public function toFormattedText(): string
    {
        return trim(implode(', ', array_filter([
            $this->full_address,
            $this->district_name ? 'Kec. ' . $this->district_name : null,
            $this->city_name,
            $this->province_name,
        ])));
    }
}
