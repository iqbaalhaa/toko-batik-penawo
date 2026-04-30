<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'address',
        'city',
        'province',
        'postal_code',
        'birth_date',
        'gender',
        // Wilayah administratif (untuk perhitungan ongkir)
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'district_id',
        'district_name',
        'full_address',
        // Preferensi notifikasi (halaman Pengaturan)
        'notify_order_updates',
        'notify_promo',
    ];


    /**
     * Alamat pengiriman terstruktur untuk kalkulator ongkir.
     *
     * Sekarang membaca dari tabel `addresses` (multi-alamat). Jika user belum
     * punya alamat tersimpan, fallback ke kolom embedded lama supaya akun lama
     * yang belum dimigrasi tetap bisa checkout sekali.
     */
    public function shippingAddress(): ?array
    {
        $default = $this->defaultAddress();
        if ($default) {
            return $default->toShippingPayload();
        }
        if (! $this->city_id || ! $this->district_id) {
            return null;
        }
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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class)->orderByDesc('is_default')->orderBy('id');
    }

    public function defaultAddress(): ?Address
    {
        // Prefer flag is_default=true; jika tidak ada, ambil yang paling lama dibuat.
        return $this->addresses()->where('is_default', true)->first()
            ?? $this->addresses()->oldest()->first();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'birth_date'           => 'date',
            'notify_order_updates' => 'boolean',
            'notify_promo'         => 'boolean',
        ];
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class)->latest();
    }

    /**
     * Cek apakah produk sudah ada di wishlist user ini.
     */
    public function hasWishlisted(int $productId): bool
    {
        return $this->wishlists()->where('product_id', $productId)->exists();
    }
}
