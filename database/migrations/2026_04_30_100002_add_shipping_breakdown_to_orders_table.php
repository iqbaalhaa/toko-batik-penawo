<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Snapshot perhitungan saat checkout — disimpan agar invoice/laporan tidak
            // berubah meski tarif/voucher di masa depan diubah.
            $table->unsignedBigInteger('subtotal_products')->default(0)->after('total');
            $table->unsignedBigInteger('shipping_total')->default(0)->after('subtotal_products');
            $table->unsignedBigInteger('voucher_discount')->default(0)->after('shipping_total');
            $table->json('shipping_breakdown')->nullable()->after('voucher_discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal_products', 'shipping_total', 'voucher_discount', 'shipping_breakdown']);
        });
    }
};
