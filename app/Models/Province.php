<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Provinsi (data referensi Kemendagri, di-seed dari ProvinceSeeder).
 *
 * Kolom `rajaongkir_id` opsional — diisi oleh `rajaongkir:sync-wilayah` agar
 * RajaOngkirService bisa langsung map tanpa lookup berbasis nama.
 */
class Province extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';
    public $timestamps   = false;

    protected $fillable = ['id', 'code', 'name', 'rajaongkir_id'];

    public function regencies()
    {
        return $this->hasMany(Regency::class);
    }
}
