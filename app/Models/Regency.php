<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kabupaten / Kota (data referensi Kemendagri).
 *
 * Kolom `rajaongkir_id` opsional — diisi oleh `rajaongkir:sync-wilayah`.
 */
class Regency extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';
    public $timestamps   = false;

    protected $fillable = ['id', 'code', 'name', 'province_id', 'rajaongkir_id'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
