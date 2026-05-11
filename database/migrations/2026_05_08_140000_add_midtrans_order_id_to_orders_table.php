<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom `midtrans_order_id` — order_id unik (umumnya
     * `{invoice_number}-{timestamp}`) yang dikirim ke Midtrans saat membuat
     * Snap token. Diperlukan agar `Transaction::status()` mencocokkan transaksi
     * yang tepat, bukan transaksi lama dari sandbox dengan invoice_number yang
     * sama (penyebab status "dibayar" otomatis padahal belum bayar setelah
     * `migrate:fresh`).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->after('snap_token');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('midtrans_order_id');
        });
    }
};
