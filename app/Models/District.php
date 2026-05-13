<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kecamatan (data referensi Kemendagri).
 *
 * Kolom `rajaongkir_id` opsional — diisi oleh `rajaongkir:sync-wilayah` agar
 * RajaOngkirService dapat menukar cuid lokal → ID kecamatan RajaOngkir tanpa
 * pencarian berbasis nama.
 */
class District extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';
    public $timestamps   = false;

    protected $fillable = ['id', 'code', 'name', 'regency_id', 'rajaongkir_id'];

    public function regency()
    {
        return $this->belongsTo(Regency::class);
    }
}
