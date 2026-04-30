<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class, // dipertahankan — admin butuh kategori untuk tambah produk via panel
            // Wilayah administratif (Kemendagri) — referensi untuk kalkulator ongkir.
            // Urutan penting karena ada FK: province → regency → district.
            ProvinceSeeder::class,
            RegencySeeder::class,
            DistrictSeeder::class,
            // ProductSeeder::class, OrderSeeder::class, StockMovementSeeder::class dimatikan
            // sehingga produk, pesanan, dan mutasi stok dimulai kosong. Aktifkan kembali
            // dengan menghapus komentar di atas bila perlu data contoh.
        ]);
    }
}
