<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Berat numerik (kg) khusus untuk perhitungan ongkir.
            // Kolom string `weight` lama tetap dipertahankan untuk teks display ("100 g", "0.5 kg", dll).
            $table->decimal('weight_kg', 8, 2)->nullable()->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight_kg');
        });
    }
};
