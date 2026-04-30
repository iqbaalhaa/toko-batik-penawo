<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fitur voucher tidak digunakan — hapus kolomnya supaya tidak rancu.
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'voucher_discount')) {
                $table->dropColumn('voucher_discount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('voucher_discount')->default(0)->after('shipping_total');
        });
    }
};
