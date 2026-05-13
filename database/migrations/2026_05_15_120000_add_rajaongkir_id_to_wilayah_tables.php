<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom `rajaongkir_id` di tabel wilayah administratif.
     *
     * Diperlukan supaya `RajaOngkirService` bisa map ID kecamatan Kemendagri/BPS
     * (cuid lokal) ke ID RajaOngkir (numerik) tanpa lookup berbasis nama —
     * lebih cepat, lebih akurat (tidak terganggu beda case/typo), dan hemat
     * panggilan API saat checkout.
     *
     * Mapping diisi via `php artisan rajaongkir:sync-wilayah`. Saat kolom masih
     * kosong, service otomatis fallback ke pencarian berbasis nama.
     */
    public function up(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->string('rajaongkir_id', 32)->nullable()->after('name');
            $table->index('rajaongkir_id', 'idx_provinces_rajaongkir_id');
        });

        Schema::table('regencies', function (Blueprint $table) {
            $table->string('rajaongkir_id', 32)->nullable()->after('name');
            $table->index('rajaongkir_id', 'idx_regencies_rajaongkir_id');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->string('rajaongkir_id', 32)->nullable()->after('name');
            $table->index('rajaongkir_id', 'idx_districts_rajaongkir_id');
        });
    }

    public function down(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropIndex('idx_provinces_rajaongkir_id');
            $table->dropColumn('rajaongkir_id');
        });

        Schema::table('regencies', function (Blueprint $table) {
            $table->dropIndex('idx_regencies_rajaongkir_id');
            $table->dropColumn('rajaongkir_id');
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropIndex('idx_districts_rajaongkir_id');
            $table->dropColumn('rajaongkir_id');
        });
    }
};
